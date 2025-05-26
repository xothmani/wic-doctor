<?php
/*
 * File name: UserAPIController.php
 * Last modified: 2024.07.16 at 11:40:24
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\UpdateUserRequest;
use App\Models\User;
use App\Repositories\CustomFieldRepository;
use App\Repositories\RoleRepository;
use App\Repositories\UploadRepository;
use App\Repositories\UserRepository;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Prettus\Repository\Exceptions\RepositoryException;
use App\Models\Patient;
use Illuminate\Support\Facades\Log;

class UserAPIController extends Controller
{
    private UserRepository $userRepository;
    private UploadRepository $uploadRepository;
    private RoleRepository $roleRepository;
    private CustomFieldRepository $customFieldRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(UserRepository $userRepository, UploadRepository $uploadRepository, RoleRepository $roleRepository, CustomFieldRepository $customFieldRepo)
    {
        $this->userRepository = $userRepository;
        $this->uploadRepository = $uploadRepository;
        $this->roleRepository = $roleRepository;
        $this->customFieldRepository = $customFieldRepo;
        parent::__construct();
    }

    /*function login(Request $request) v1
    {
        try {
            $this->validate($request, [
                'email' => 'required|email',
                'password' => 'required',
            ]);
            if (auth()->attempt(['email' => $request->input('email'), 'password' => $request->input('password')])) {
                // Authentication passed...
                $user = auth()->user();
                $user->device_token = $request->input('device_token', '');

                //ajouter par hamza pour voir role utilisateur connecter
                $user = $user->load('roles');

                $user->save();
                return $this->sendResponse($user, 'User retrieved successfully');
            } else {
                return $this->sendError(__('auth.failed'), 200);
            }
        } catch (ValidationException $e) {
            return $this->sendError(array_values($e->errors()));
        } catch (Exception $e) {
            return $this->sendError('ERREUURRRRRRRRRR', 200);
        }

    }*/



    /*public function login(Request $request)  v2
    {
        try {
            // Vérifie si c’est un login par email ou téléphone
            if ($request->filled('phone_number')) {
                $this->validate($request, [
                    'phone_number' => 'required',
                    'password' => 'required',
                ]);

                $credentials = [
                    'phone_number' => $request->input('phone_number'),
                    'password' => $request->input('password'),
                ];
            } else {
                $this->validate($request, [
                    'email' => 'required|email',
                    'password' => 'required',
                ]);

                $credentials = [
                    'email' => $request->input('email'),
                    'password' => $request->input('password'),
                ];
            }

            // Tente l'authentification avec les credentials préparés
            if (auth()->attempt($credentials)) {
                $user = auth()->user();
                $user->device_token = $request->input('device_token', '');

                // Charger les rôles (si relation définie)
                $user = $user->load('roles');

                $user->save();

                return $this->sendResponse($user, 'User retrieved successfully');
            } else {
                return $this->sendError(__('auth.failed'), 200);
            }

        } catch (ValidationException $e) {
            return $this->sendError(array_values($e->errors()));
        } catch (Exception $e) {
            return $this->sendError('ERREUR', 200);
        }
    }*/


public function login(Request $request)//v3 syncronisation avec web
{
    try {
        $this->validate($request, [
            'password' => 'required',
        ]);

        // Préparer les données d'entrée
        $loginField = $request->filled('phone_number') ? 'phone_number' : 'email';

        $this->validate($request, [
            $loginField => $loginField === 'phone_number' ? 'required' : 'required|email',
        ]);

        $identifier = $request->input($loginField);
        $passwordInput = $request->input('password');

        // Trouver l'utilisateur avec le champ correspondant
        $user = \App\Models\User::where($loginField, $identifier)->first();

        if (!$user || !Hash::check($passwordInput, $user->passwordpatient)) {
            return $this->sendError(__('auth.failed'), 200);
        }

        // Authentifier manuellement
        auth()->login($user);

        // Mettre à jour le device_token si fourni
        $user->device_token = $request->input('device_token', '');
        $user->save();

        // Charger les relations nécessaires
        $user->load('roles');

        return $this->sendResponse($user, 'User retrieved successfully');

    } catch (ValidationException $e) {
        return $this->sendError(array_values($e->errors()));
    } catch (\Exception $e) {
        return $this->sendError('ERREUR', 200);
    }
}


    function decodeIfJson($value) {
        if (is_string($value)) {
            $decoded = json_decode($value, true);
            return json_last_error() === JSON_ERROR_NONE ? $decoded : $value;
        }
        return $value;
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param array $data
     * @return
     */
    function register(Request $request)
    {
        try {
            $messages = [
                'email.unique' => __('validation.custom.email.unique'),
                'phone_number.unique' => __('validation.custom.phone_number.unique'),
                // ajoute les autres au besoin
            ];


            //$this->validate($request, User::$rules);
            $validator = Validator::make($request->all(), User::$rules, $messages);
            if ($validator->fails()) {
                throw new ValidationException($validator);
            }
            
            $user = new User;
            $user->name = $request->input('firstName');
            $user->lastname = $request->input('lastname');
            $user->email = $request->input('email');
            $user->phone_number = $request->input('phone_number');
            $user->phone_verified_at = $request->input('phone_verified_at');
            $user->device_token = $request->input('device_token', '');
            //$user->password = Hash::make($request->input('passwordpatient'));
            $user->passwordpatient = Hash::make($request->input('passwordpatient'));
            $user->api_token = Str::random(60);
            $user->save();


            /****** Save patient */
            

            $data = [
                'user_id'=> $user->id,
                'first_name'     => $this->decodeIfJson($request->input('firstName')),
                'last_name'      => $this->decodeIfJson($request->input('lastname')),
                'email'          => $request->input('email'),
                'phone_number'   => $request->input('phone_number'),
                //'mobile_number'  => $request->input('phone_number'),
                'is_main_profil' => true,
            ];

            // Ajouter date_naissance seulement si elle est fournie
            if ($request->filled('date_naissance')) {
                $data['date_naissance'] = $request->input('date_naissance');
            }

            $patient = Patient::create($data);

            /******* End save patient */

            

            $defaultRoles = $this->roleRepository->findByField('default', '1');
            $defaultRoles = $defaultRoles->pluck('name')->toArray();
            $user->assignRole($defaultRoles);

            $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());

            foreach (getCustomFieldsValues($customFields, $request) as $value) {
                $user->customFieldsValues()
                    ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
            }
        } catch (ValidationException $e) {
            return $this->sendError(array_values($e->errors()));
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 200);
        }


        return $this->sendResponse($user, 'User retrieved successfully');
    }


    function resetPassword(string $phoneNumber, Request $request): JsonResponse
    {
        // Find user by phone number
        $user = $this->userRepository->findWhere(['phone_number' => $phoneNumber])->first();
        if (!$user) {
            return $this->sendError(__('User not found'), 404);
        }

        // Validate the new password
        $validator = Validator::make($request->all(), [
            'new_password' => 'required|string|min:6|confirmed', // Ensure password confirmation
        ]);

        if ($validator->fails()) {
            return $this->sendError($validator->errors()->first(), 422); // Return validation error
        }

        try {
            // Update the user's password
            $user->password = Hash::make($request->input('new_password'));
            $user->passwordpatient = Hash::make($request->input('new_password'));
            $user->save();

            return $this->sendResponse($user, __('Password updated successfully.'));
        } catch (Exception $e) {
            return $this->sendError(__('An error occurred while updating the password.'), 500);
        }
    }


    public function checkPhoneNumber(Request $request)
    {
        $phoneNumber = $request->input('phone_number');

        if (!$phoneNumber) {
            return response()->json([
                'success' => false,
                'message' => 'Phone number is required.',
            ], 400);
        }

        // Check if the phone number exists in the database
        $user = $this->userRepository->findWhere(['phone_number' => $phoneNumber])->first();

        if ($user) {
            return response()->json([
                'success' => true,
                'message' => 'Phone number exists.',
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'Phone number does not exist.',
            ], 404);
        }
    }

    function logout(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();
        if (!$user) {
            return $this->sendError('User not found', 200);
        }
        try {
            auth()->logout();
        } catch (Exception $e) {
            $this->sendError($e->getMessage(), 200);
        }
        return $this->sendResponse($user['name'], 'User logout successfully');

    }

    function user(Request $request)
    {
        $user = $this->userRepository->findByField('api_token', $request->input('api_token'))->first();

        if (!$user) {
            return $this->sendError('User not found', 200);
        }

        return $this->sendResponse($user->load('roles'), 'User retrieved successfully');
    }

    function settings(Request $request)
    {
        $settings = setting()->all();
        $settings = array_intersect_key(
            $settings,
            [
                'default_tax' => '',
                'default_currency' => '',
                'default_currency_decimal_digits' => '',
                'app_name' => '',
                'currency_right' => '',
                'enable_paypal' => '',
                'enable_stripe' => '',
                'enable_razorpay' => '',
                'main_color' => '',
                'main_dark_color' => '',
                'second_color' => '',
                'second_dark_color' => '',
                'accent_color' => '',
                'accent_dark_color' => '',
                'scaffold_dark_color' => '',
                'scaffold_color' => '',
                'google_maps_key' => '',
                'fcm_key' => 'fRqP0GobRv-ySbriK-VOYm:APA91bF632KTder0AeN-5sibdvK0I7IqzjvFMYonoFG-WNaH8FBDaGh-ADrgjVkcD3_apcSu1TVNLQC2SORuMA7F4xiJi83ZoA0XP5f9h7Jd3Jh-ym8RxYU',
                'mobile_language' => '',
                'app_version' => '',
                'enable_version' => '',
                'distance_unit' => '',
                'default_theme' => '',
                'app_short_description' => '',
                'default_country_code' => '',
                'enable_otp' => '',
                'enable_payment_before_appointment_is_completed' => '',
            ]
        );
        if (!$settings) {
            return $this->sendError('Settings not found', 200);
        }
        $upload = $this->uploadRepository->findByField('uuid', setting('app_logo', ''))->first();
        $settings['app_logo'] = asset('images/logo_default.png');
        if ($upload && $upload->hasMedia('app_logo')) {
            $settings['app_logo'] = $upload->getFirstMediaUrl('app_logo');
        }

        return $this->sendResponse($settings, 'Settings retrieved successfully');
    }

    /**
     * Update the specified User in storage.
     *
     * @param int $id
     * @param UpdateUserRequest $request
     *
     */
    public function update(int $id, UpdateUserRequest $request): JsonResponse
    {
        $user = $this->userRepository->findWithoutFail($id);
        if (empty($user)) {
            return $this->sendError('User not found');
        }
        $input = $request->except(['api_token']);
        try {
            if ($request->has('device_token')) {
                $user = $this->userRepository->update($request->only('device_token'), $id);
            } else {
                $customFields = $this->customFieldRepository->findByField('custom_field_model', $this->userRepository->model());
                if (isset($input['password'])) {
                    $input['password'] = Hash::make($request->input('password'));
                    $input['passwordpatient'] = Hash::make($request->input('password'));
                }

                


                if (isset($input['avatar']) && $input['avatar']) {
                    $cacheUpload = $this->uploadRepository->getByUuid($input['avatar']);
                    $mediaItem = $cacheUpload->getMedia('avatar')->first();
                    if ($user->hasMedia('avatar')) {
                        $user->getFirstMedia('avatar')->delete();
                    }
                    $mediaItem->copy($user, 'avatar');
                }
                $user = $this->userRepository->update($input, $id);

                foreach (getCustomFieldsValues($customFields, $request) as $value) {
                    $user->customFieldsValues()
                        ->updateOrCreate(['custom_field_id' => $value['custom_field_id']], $value);
                }
            }
        } catch (Exception $e) {
            return $this->sendError($e->getMessage(), 200);
        }

        return $this->sendResponse($user, __('lang.updated_successfully', ['operator' => __('lang.user')]));
    }


    public function updateUserEmail($id, Request $request): JsonResponse{
        $user = User::find($id);
        if (empty($user) || !empty($user->email)) {
            return $this->sendError('User not found or email already exists');
        }

        $user->email = $request->input('email');
        Log::info("user email: {$user->email}");
        Log::info("request email: {$request->input('email')}");
        $user->save();
        //$user = $this->userRepository->update($request->only('email'), $id);
        return $this->sendResponse($user, __('lang.updated_successfully', ['operator' => __('lang.user')]));
    }

    function sendResetLinkEmail(Request $request): JsonResponse
    {
        try {
            $this->validate($request, ['email' => 'required|email|exists:users']);
            $response = Password::broker()->sendResetLink(
                $request->only('email')
            );
            if ($response == Password::RESET_LINK_SENT) {
                return $this->sendResponse(true, 'Reset link was sent successfully');
            } else {
                return $this->sendError('Reset link not sent');
            }
        } catch (ValidationException $e) {
            return $this->sendError($e->getMessage());
        } catch (Exception $e) {
            return $this->sendError("Email not configured in your admin panel settings");
        }

    }

    /**
     * Remove the authenticated user from storage.
     *
     *
     * @return JsonResponse
     * @throws RepositoryException
     */
    public function destroy(): JsonResponse
    {
        try {
            $user = $this->userRepository->delete(auth()->id());
        } catch (Exception $e) {
            return $this->sendError($e->getMessage());
        }
        return $this->sendResponse($user, __('lang.deleted_successfully', ['operator' => __('lang.user')]));
    }
}
