<?php

namespace App\Http\Controllers\API;

use App\Repositories\RoomRepository;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use Exception;
use App\Enums\RoomStatus;



class RoomAPIController extends Controller
{
    private $roomRepository;

    public function __construct(RoomRepository $roomRepository)
    {
        $this->roomRepository = $roomRepository;
    }

    /**
     * Liste de toutes les rooms avec leurs appointments
     */
    public function index(): JsonResponse
    {
        return response()->json($this->roomRepository->getAll());
    }

    /**
     * Afficher une room spécifique avec son appointment
     */
    public function show(int $id): JsonResponse
    {
        $room = $this->roomRepository->getById($id);
        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }
        
        return response()->json([
            'success' => true,
            'data' => $room
        ]);
    }

    /**
     * Créer une nouvelle room
     */
    public function store(Request $request): JsonResponse
    {

        $data = $request->all();


        if (!in_array($data['status'], RoomStatus::values())) {
            return response()->json(['message' => 'Room status not valid. Réssayer avec (Pending, active, completed)'], 400);
        }


        try {
            $room = $this->roomRepository->create($data);
        }catch(Exception $e){
            return response()->json("Room not created {$e}", 400);
        }
        
        return response()->json($room, 201);
    }

    /**
     * Mettre à jour une room existante
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $data = $request->validate([
            'room_name' => 'string|max:255',
            'meet_link' => 'nullable|string',
            'owner_id' => 'exists:users,id',
            'appointment_id' => 'exists:appointments,id',
            'patient_id' => 'exists:users,id',
            'date' => 'date',
            'time' => 'string',
            'status' => 'nullable|string',
        ]);

        $room = $this->roomRepository->update($id, $data);

        if (!$room) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        return response()->json($room);
    }

    /**
     * Supprimer une room
     */
    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->roomRepository->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Room not found'], 404);
        }

        return response()->json(['message' => 'Room deleted successfully']);
    }
}
