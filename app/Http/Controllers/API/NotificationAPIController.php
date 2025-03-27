<?php
/*
 * File name: NotificationAPIController.php
 * Last modified: 2021.09.15 at 13:28:01
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;


use App\Criteria\Notifications\UnReadCriteria;
use App\Http\Controllers\Controller;
use App\Models\Notification;

use App\Models\User;
use App\Notifications\FCMServices;

use App\Notifications\NewMessage;

use App\Repositories\NotificationRepository;
use App\Repositories\UserRepository;
use Carbon\Carbon;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use InfyOm\Generator\Criteria\LimitOffsetCriteria;
use Prettus\Repository\Criteria\RequestCriteria;
use Prettus\Repository\Exceptions\RepositoryException;
use Prettus\Validator\Exceptions\ValidatorException;

use Illuminate\Support\Facades\Log;



/**
 * Class NotificationController
 * @package App\Http\Controllers\API
 */
class NotificationAPIController extends Controller
{
    /** @var  NotificationRepository */
    private NotificationRepository $notificationRepository;

    /** @var UserRepository */
    private UserRepository $userRepository;

    protected $fcmService;


    public function __construct(NotificationRepository $notificationRepo, UserRepository $userRepository, FCMServices $fcmServices)
    {
        $this->notificationRepository = $notificationRepo;
        $this->userRepository = $userRepository;
        $this->fcmService = $fcmServices;
        parent::__construct();
    }




    public function send(Request $request)
    {
        $request->validate([
            'device_token' => 'required|string',
        ]);

        $deviceToken = $request->device_token;

        $this->fcmService->sendNotification(
            $deviceToken,
            'Test Notification',
            'Ceci est un test de notification FCM v1',
            ['id' => 'App\\Notifications\\NewMessage']
        );

        $body = "Votre rendez-vous avec le Dr. Hamza nechi a été mis à jour. Consultez les nouvelles informations dans votre espace personnel.";
        $data = $data = ['appointment_id' => 888];
        $user = User::find(121);
        Notification::create([
            'notifiable_id' => 121,  // Utilise l'ID de l'utilisateur
            'notifiable_type' => get_class($user), // Utilise le nom de la classe de l'utilisateur
            'data' => $data, // Utilise json_encode pour formater les données
            'type' => 'App\Notifications\StatusChangedAppointment',
            'read_at' => null,
            'read' => false,
            'body' => $body,
            'created_at' => now(),
            'updated_at' => now()
        ]);

        return response()->json(['message' => 'Notification envoyée !', 'success' => true]);
    }



    /**
     * Display a listing of the Notification.
     * GET|HEAD /notifications
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $this->notificationRepository->pushCriteria(new RequestCriteria($request));
            $this->notificationRepository->pushCriteria(new LimitOffsetCriteria($request));
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage(), 200);
        }
        $notifications = $this->notificationRepository->all();
        $this->filterCollection($request, $notifications);

        return $this->sendResponse($notifications->toArray(), 'Notifications retrieved successfully');
    }

    /**
     * Display a count of Notifications.
     * GET|HEAD /notifications/count
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function count(Request $request): JsonResponse
    {
        try {
            $this->notificationRepository->pushCriteria(new RequestCriteria($request));
            $this->notificationRepository->pushCriteria(new UnReadCriteria());
        } catch (RepositoryException $e) {
            return $this->sendError($e->getMessage(), 200);
        }


        //count seulement les notifications non lues (NB: lues pas open)
        $count = $this->notificationRepository->where('read', false)->count();


        return $this->sendResponse($count, 'Notifications count retrieved successfully');
    }

    /**
     * Store a newly created Notification in storage.
     * POST /notifications
     *
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function store(Request $request): JsonResponse
    {
        try {

            Log::info("Store function");

            $usersId = $request->get('users');
            $fromId = $request->get('from');
            $text = $request->get('text');
            $messageId = $request->get('id');
            $users = $this->userRepository->findWhereIn('id', $usersId);
            Log::info("Store function", ["user device token" => $users->first()->device_token]);
            $from = $this->userRepository->find($fromId);
            Log::info("Store function", ["from" => $from, "users" => $users]);
            //\Illuminate\Support\Facades\Notification::send($users, new NewMessage($from, $text, $messageId));
            // Envoi de la notification FCM
            $sender_name = $from->name;
            $this->fcmService->sendNotification(
                $users->first()->device_token,
                "Nouveau message de {$sender_name}",
                "Vous avez reçu un message de {$sender_name}. Cliquez ici pour le lire.",
                [
                    'message_id' => $messageId,
                    'sender_name' => $sender_name
                ]
            );
            Log::info("Store function aprés send notification");
            $data = [
                'message_id' => $messageId,
                'from' => $sender_name
            ];

            Log::info("Add notification in database Notification API");
            Notification::create([
                'notifiable_id' => $users->first()->id,  // Utilise l'ID de l'utilisateur
                'notifiable_type' => get_class($users->first()), // Utilise le nom de la classe de l'utilisateur
                'data' => $data,
                'type' => 'App\Notifications\NewMessage',
                'read_at' => null,
                'read' => false,
                'body' => "Vous avez reçu un message de {$from->name}. Cliquez ici pour le lire.",
                'created_at' => now(),
                'updated_at' => now()
            ]);


        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return response()->json(['success' => true]);
    }


    public function storeReminderAppointment(Request $request): JsonResponse
    {
        try {
            Log::info('Store reminder notification', [$request->get('notifiable_id')]);
            $user = User::find($request->get('notifiable_id'));
            $data = [
                'appointment_id' => $request->get('appointment_id'),
            ];

            Log::info("Store notification reminder now");
            Notification::create([
                'notifiable_id' => $request->get('notifiable_id'),
                'notifiable_type' => get_class($user),
                'data' => $data,
                'type' => 'App\Notifications\ReminderAppointment',
                'read_at' => null,
                'read' => false,
                'body' => $request->get('body'),
                'show_at' => $request->get('show_at')
            ]);
            Log::info("End store notification reminder now");

        } catch (Exception $e) {
            Log::error("Store function notification reminder", ["error" => $e->getMessage()]);
            return $this->sendError($e->getMessage());
        }

        return response()->json(['success' => true]);

    }

    /**
     * Display the specified Notification.
     * GET|HEAD /notifications/{id}
     *
     * @param int $id
     * @param Request $request
     * @return JsonResponse
     */
    public function show(int $id, Request $request): JsonResponse
    {
        /** @var Notification $notification */
        if (!empty($this->notificationRepository)) {
            $notification = $this->notificationRepository->findWithoutFail($id);
        }

        if (empty($notification)) {
            return $this->sendError('Notification not found', 200);
        }

        return $this->sendResponse($notification->toArray(), 'Notification retrieved successfully');
    }

    /**
     * Update the specified Notification in storage.
     *
     * @param $id
     * @param Request $request
     *
     * @return JsonResponse
     */
    public function update($id, Request $request): JsonResponse
    {

        $notification = $this->notificationRepository->findWithoutFail(id: $id);
        if ($notification->read_at == null) {
            $notification->read_at = Carbon::now();
            $notification->save();
        }

        return $this->sendResponse($notification->toArray(), __('lang.saved_successfully', ['operator' => __('lang.notification')]));
    }





    public function readAll($id_user): JsonResponse
    {
        try {
            // Met à jour toutes les notifications de l'utilisateur en mettant 'read' à true
            $updated = Notification::where('notifiable_id', $id_user)->update(['read' => true]);

            if ($updated == 0) {
                return $this->sendError('Aucune notification trouvée pour cet utilisateur', 404);
            }

        } catch (\Exception $e) {
            return $this->sendError($e->getMessage(), 500);
        }

        return $this->sendResponse(['success' => true], 'Toutes les notifications ont été marquées comme lues');
    }



    /**
     * Remove the specified Favorite from storage.
     *
     * @param $id
     *
     * @return JsonResponse
     */
    public function destroy($id): JsonResponse
    {
        $notification = $this->notificationRepository->findWithoutFail($id);

        if (empty($notification)) {
            return $this->sendError('Notification not found', 200);
        }

        if ($this->notificationRepository->delete($id) < 1) {
            $this->sendError('Notification not deleted', 200);
        }

        return $this->sendResponse($notification, __('lang.deleted_successfully', ['operator' => __('lang.notification')]));

    }
}
