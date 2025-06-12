<?php

namespace App\Http\Controllers\API;
use App\Http\Controllers\Controller;
use App\Services\MediaUploadService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use App\Enums\MediaModelType;
use Illuminate\Http\JsonResponse;



class CustomMediaApiController extends Controller
{
    protected MediaUploadService $uploadService;

    public function __construct(MediaUploadService $uploadService)
    {
        $this->uploadService = $uploadService;
    }

    
    public function upload(Request $request, MediaUploadService $uploadService): JsonResponse
    {
        $validated = $request->validate([
            'file' => 'required|file|mimes:jpg,jpeg,png,webp|max:2048',
            'model_type' => 'required|string',
            'model_id' => 'required|integer',
            'collection' => 'sometimes|string',
        ]);

        $enumCase = MediaModelType::tryFromName(strtoupper($request->model_type));

        $modelClass = $enumCase->value;

        $media = $uploadService->upload(
            $validated['file'],
            $modelClass,
            $validated['model_id'],
            $validated['collection'] ?? 'default'
        );

        return response()->json([
            'id' => (string) $media->id,
            'name' => $media->name,
            'url' => $media->getFullUrl(),
            'thumb' => $media->getFullUrl('thumb'), // conversion 'thumb'
            'icon' => $media->getFullUrl('icon'),   // conversion 'icon'
            'formatted_size' => $this->formatBytes($media->size),
            'uuid' => $media->uuid,
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $deleted = $this->uploadService->delete($id);

        if (!$deleted) {
            return response()->json(['message' => 'Media not found or already deleted'], 404);
        }

        return response()->json(['message' => 'Media deleted successfully']);
    }


    private function formatBytes($bytes, $precision = 2): string
    {
        $units = ['B', 'KB', 'MB', 'GB', 'TB'];

        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);

        $bytes /= pow(1024, $pow);

        return round($bytes, $precision) . ' ' . $units[$pow];
    }
}
