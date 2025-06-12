<?php


namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Str;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Illuminate\Support\Facades\Storage;

use App\Models\Upload;

class MediaUploadService
{
    public function upload(UploadedFile $file, string $modelType, int $modelId, string $collection = 'default'): Media
    {
        if (!class_exists($modelType)) {
            throw new \InvalidArgumentException("Model type $modelType not found.");
        }

        /** @var \Illuminate\Database\Eloquent\Model $model */
        $model = app($modelType)::findOrFail($modelId);

        $media = $model
            ->addMedia($file)
            ->usingName(pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME))
            ->usingFileName(Str::uuid() . '.' . $file->getClientOriginalExtension())
            ->toMediaCollection($collection);

        
        // Insérer dans la table uploads
        Upload::create(['uuid' => $media->uuid]);

        return $media;
    }







    public function delete(int $media_id): bool
    {
        $media = Media::find($media_id);

        if (!$media){
            return false;
        }

        // Récupère le chemin physique du fichier
        $filePath = $media->getPath(); // ex: 123/image.jpg

        // Suppression du fichier
        if (Storage::disk($media->disk)->exists($filePath)) {
            Storage::disk($media->disk)->delete($filePath);
        }

        // Supprimer le dossier contenant le fichier (ex: storage/app/123)
        $folder = dirname($filePath); // ex: 123
        if (Storage::disk($media->disk)->exists($folder)) {
            Storage::disk($media->disk)->deleteDirectory($folder);
        }

        // Supprimer l'entrée en base
        $media->delete();

        return true;
    }
}

