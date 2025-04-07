<?php

namespace App\Repositories;

use App\Models\Room;
use Illuminate\Database\Eloquent\Collection;

class RoomRepository
{
    /**
     * Récupérer toutes les rooms avec leurs appointments associés
     */
    public function getAll(): Collection
    {
        return Room::with('appointment')->get();
    }

    /**
     * Récupérer une room par son ID avec son appointment
     */
    public function getById(int $id): ?Room
    {
        return Room::where('appointment_id', $id)->first();
    }

    /**
     * Créer une nouvelle room
     */
    public function create(array $data): Room
    {
        return Room::create($data);
    }

    /**
     * Mettre à jour une room existante
     */
    public function update(int $id, array $data): ?Room
    {
        $room = Room::find($id);
        if ($room) {
            $room->update($data);
            return $room;
        }
        return null;
    }

    /**
     * Supprimer une room par son ID
     */
    public function delete(int $id): bool
    {
        $room = Room::find($id);
        if ($room) {
            return $room->delete();
        }
        return false;
    }
}
