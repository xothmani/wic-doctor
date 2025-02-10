<?php

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Prettus\Validator\Exceptions\ValidatorException;

class DoctorsGalleryController extends Controller
{
    private UploadRepository $uploadRepository;

    public function __construct(UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->uploadRepository = $uploadRepository;
    }

    /**
     * Show main "doctors_gallery" index.
     */
    public function index()
    {
        return view('doctors_gallery.index');
    }

    public function collections(): JsonResponse
    {
        // Fetch all collections from the repository
        $allCollections = $this->uploadRepository->collectionsNames();

        // Ensure categories are unique
        $formatted = collect($allCollections)
            ->unique('value') // Ensure no duplicate 'value'
            ->map(function ($c) {
                return [
                    'value' => $c['value'] ?? $c,
                    'title' => $c['title'] ?? $c,
                ];
            })->values(); // Reset array keys

        return response()->json($formatted);
    }

    /**
     * Fetch and return all categories (folders) inside the public storage.
     */
    public function categories(): JsonResponse
    {
        $doctorId = auth()->user()->doctor->id;

        $doctorPath = "doctors/{$doctorId}";

        // Check if the doctor-specific directory exists
        if (!Storage::exists($doctorPath)) {
            return response()->json([]); // Return an empty array if no directory exists
        }

        // Fetch all directories under the doctor-specific path
        $directories = Storage::directories($doctorPath);

        // Extract category names by removing the path prefix
        $categories = collect($directories)->map(function ($dir) use ($doctorPath) {
            return str_replace("{$doctorPath}/", '', $dir);
        })->unique()->values();

        return response()->json($categories);
    }



    /**
     * Store a newly uploaded file inside a selected category.
     */
    /*public function store(UploadRequest $request): JsonResponse
    {
        $category = trim($request->get('category', 'default')); // Get or default to "default"
        $uuid = $request->get('uuid');

        try {
            // Ensure category directory exists
            $storagePath = 'public/' . $category;
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            // Save the file inside the category folder
            $file = $request->file('file');
            $filePath = $file->store($category, 'public'); // Store in `public/{category}`
            Log::info("File stored in category: {$category}, Path: {$filePath}");

            // Create an "upload" record in the database
            $upload = $this->uploadRepository->create([
                'name' => $file->getClientOriginalName(),
                'file_name' => basename($filePath),
                'collection_name' => $category,
                'uuid' => $uuid,
                'disk' => 'public',
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'custom_properties' => [
                    'uuid' => $uuid,
                    'user_id' => auth()->id(),
                ],
            ]);

            return $this->sendResponse($uuid, "Uploaded Successfully");

        } catch (ValidatorException $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }*/
    public function store(UploadRequest $request): JsonResponse
    {
        $category = trim($request->get('category', 'Default')); // Get or default to "default"
        $uuid = $request->get('uuid');
        $doctorId = auth()->user()->doctor->id;


        try {
            // Log input data
            Log::info("Starting file upload", [
                'category' => $category,
                'uuid' => $uuid,
                'doctorId' => $doctorId,
            ]);

            // Combine doctor ID and category to create a unique folder path
            $storagePath = "doctors/{$doctorId}/{$category}";

            // Ensure the directory exists
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            // Save the file inside the doctor-specific category folder
            $file = $request->file('file');
            $filePath = $file->storeAs($storagePath, $file->getClientOriginalName(), 'public'); // Store with the original file name

            Log::info("File stored in doctor-specific category: {$storagePath}, Path: {$filePath}");

            // Save record in the database with doctor-specific storage information
            $upload = $this->uploadRepository->create([
                'name' => $file->getClientOriginalName(),
                'file_name' => basename($filePath),
                'collection_name' => $category,
                'uuid' => $uuid,
                'disk' => 'public',
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'custom_properties' => [
                    'uuid' => $uuid,
                    'user_id' => $doctorId, // Link the file to the current doctor
                ],
            ]);

            return $this->sendResponse($uuid, "Uploaded Successfully");
        } catch (ValidatorException $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }
    /**
     * Fetch all media files inside a given category.
     */
    /*public function all(Request $request, $category = null)
    {
        // Ensure a valid category is used, default to 'default'
        $category = $category ?? 'default';

        Log::info("Fetching media for category: {$category}");

        // Directory path in the public storage
        $directoryPath = storage_path("app/public/{$category}");

        // Check if the directory exists
        if (!is_dir($directoryPath)) {
            Log::warning("Category directory does not exist: {$directoryPath}");
            return response()->json([]);
        }

        // Retrieve all files in the directory
        $files = array_diff(scandir($directoryPath), ['.', '..']); // Exclude . and ..
        $mediaFiles = [];

        foreach ($files as $file) {
            $fullPath = $directoryPath . '/' . $file;

            // Ensure it's a file
            if (is_file($fullPath)) {
                $fileUrl = asset("storage/{$category}/{$file}");
                $mediaFiles[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'file_name' => $file,
                    'url' => $fileUrl,
                    'thumb' => $fileUrl, // Adjust if you generate thumbnails
                    'icon' => $fileUrl,  // Adjust if you generate icons
                    'formated_size' => round(filesize($fullPath) / 1024, 2) . ' KB', // File size in KB
                ];
            }
        }

        Log::info('Media files retrieved:', $mediaFiles);

        return response()->json($mediaFiles);
    }*/
    public function all(Request $request, $category = null)
    {
        $category = $category ?? 'default'; // Default to 'default' if no category is provided
        $doctorId = auth()->user()->doctor->id;


        Log::info("Fetching media for doctor: {$doctorId}, category: {$category}");

        // Directory path in the public storage for the doctor
        $directoryPath = storage_path("app/public/doctors/{$doctorId}/{$category}");

        // Check if the directory exists
        if (!is_dir($directoryPath)) {
            Log::warning("Category directory does not exist: {$directoryPath}");
            return response()->json([]);
        }

        // Retrieve all files in the directory
        $files = array_diff(scandir($directoryPath), ['.', '..']); // Exclude . and ..
        $mediaFiles = [];

        foreach ($files as $file) {
            $fullPath = $directoryPath . '/' . $file;

            // Ensure it's a file
            if (is_file($fullPath)) {
                $fileUrl = asset("storage/doctors/{$doctorId}/{$category}/{$file}");
                $mediaFiles[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'file_name' => $file,
                    'url' => $fileUrl,
                    'thumb' => $fileUrl, // Adjust if you generate thumbnails
                    'icon' => $fileUrl,  // Adjust if you generate icons
                    'formated_size' => round(filesize($fullPath) / 1024, 2) . ' KB', // File size in KB
                ];
            }
        }

        Log::info('Media files retrieved:', $mediaFiles);

        return response()->json($mediaFiles);
    }


    /**
     * Delete a media file using its UUID.
     */
    public function clear(Request $request): JsonResponse
    {
        $uuid = $request->get('uuid');

        Log::info("DoctorsGalleryController@clear => incoming UUID: {$uuid}");

        if ($uuid) {
            // Find the media file by UUID
            $media = $this->uploadRepository->findMediaByUUID($uuid);
            if ($media) {
                Storage::delete("public/{$media->collection_name}/{$media->file_name}");
                $media->delete();

                Log::info("DoctorsGalleryController@clear => Deleted file: {$media->file_name}");
                return $this->sendResponse(true, 'Media deleted successfully');
            }
        }

        Log::info("DoctorsGalleryController@clear => No valid UUID provided!");
        return $this->sendResponse(false, 'Error while deleting media');
    }

    public function storeCabinet(UploadRequest $request): JsonResponse
    {
        $doctorId = auth()->user()->doctor->id;

        $uuid = $request->get('uuid');
        $category = 'cabinet';    // Hard-coded folder name

        try {
            // Create "doctors/{doctorId}/cabinet" if it doesn't exist
            $storagePath = "doctors/{$doctorId}/{$category}";
            if (!Storage::exists($storagePath)) {
                Storage::makeDirectory($storagePath);
            }

            // Get the file from the request
            $file = $request->file('file');

            // Save with the original file name inside the "cabinet" folder
            $filePath = $file->storeAs($storagePath, $file->getClientOriginalName(), 'public');

            // Create a record in the database 
            $upload = $this->uploadRepository->create([
                'name' => $file->getClientOriginalName(),
                'file_name' => basename($filePath),
                'collection_name' => $category,
                'uuid' => $uuid,
                'disk' => 'public',
                'size' => $file->getSize(),
                'mime_type' => $file->getMimeType(),
                'custom_properties' => [
                    'uuid' => $uuid,
                    'user_id' => $doctorId,
                ],
            ]);

            return $this->sendResponse($uuid, "Uploaded to 'cabinet' Successfully");
        } catch (ValidatorException $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }


    public function allCabinet(Request $request): JsonResponse
    {
        $doctorId = auth()->user()->doctor->id;

        $category = 'cabinet';

        // Physical path in storage
        $directoryPath = storage_path("app/public/doctors/{$doctorId}/{$category}");

        // If the folder doesn't exist, return an empty array
        if (!is_dir($directoryPath)) {
            return response()->json([]);
        }

        // Get all files (exclude . and ..)
        $files = array_diff(scandir($directoryPath), ['.', '..']);
        $mediaFiles = [];

        foreach ($files as $file) {
            $fullPath = $directoryPath . '/' . $file;
            if (is_file($fullPath)) {
                $fileUrl = asset("storage/doctors/{$doctorId}/{$category}/{$file}");
                $mediaFiles[] = [
                    'name' => pathinfo($file, PATHINFO_FILENAME),
                    'file_name' => $file,
                    'url' => $fileUrl,
                    'thumb' => $fileUrl,  // Adjust if you create real thumbnails
                    'icon' => $fileUrl,  // Adjust if you create icons
                    'formated_size' => round(filesize($fullPath) / 1024, 2) . ' KB',
                ];
            }
        }

        return response()->json($mediaFiles);
    }
    public function clearFile(Request $request): JsonResponse
    {
        Log::info("clearFile() invoked", [
            'request_data' => $request->all()
        ]);

        $doctorId = auth()->user()->doctor->id;
        // we rename the variable so it reads the 'uuid' from the request
        $uuid = $request->input('uuid'); // rename it from 'file_name' to 'uuid'

        // Then you need to figure out the actual file name from the DB or from the
        // storage if the 'uuid' is somehow the file's name
        // If you are storing "uuid" as the actual physical filename, then do something like:
        // $fileName = $uuid;
        // or look up the DB to find the file_name by that uuid

        $fileName = $uuid; // Or do a DB query to find the real file_name
        $folder = 'cabinet';
        $directoryPath = storage_path("app/public/doctors/{$doctorId}/{$folder}");
        $fullPath = $directoryPath . '/' . $fileName;

        if (file_exists($fullPath)) {
            @unlink($fullPath);
            return response()->json([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'File not found!'
            ], 404);
        }
    }


}
