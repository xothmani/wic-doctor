<?php
/*
 * File name: UploadController.php
 * Last modified: 2021.06.10 at 20:38:02
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers;

use App\Http\Requests\UploadRequest;
use App\Repositories\UploadRepository;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Prettus\Validator\Exceptions\ValidatorException;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Illuminate\Support\Facades\Log;


class UploadController extends Controller
{
    private UploadRepository $uploadRepository;

    /**
     * UploadController constructor.
     * @param UploadRepository $uploadRepository
     */
    public function __construct(UploadRepository $uploadRepository)
    {
        parent::__construct();
        $this->uploadRepository = $uploadRepository;
    }

    public function index(): View
    {
        return view('medias.index');
    }

    /**
     * Get images paths
     * @param $id
     * @param $conversion
     * @param null $filename
     * @return BinaryFileResponse
     */
    public function storage($id, $conversion, $filename = null): BinaryFileResponse
    {
        $array = explode('.', $conversion . $filename);
        $extension = strtolower(end($array));
        if (isset($filename)) {
            return response()->file(storage_path('app/public/' . $id . '/' . $conversion . '/' . $filename));
        } else {
            $filename = $conversion;
            return response()->file(storage_path('app/public/' . $id . '/' . $filename));
        }

    }

    public function collectionsNames(UploadRequest $request): JsonResponse
    {
        $allMedias = $this->uploadRepository->collectionsNames();
        return $this->sendResponse($allMedias, 'Get Collections Successfully');
    }

    /**
     * Store a newly created Upload in storage.
     * @param UploadRequest $request
     * @return JsonResponse
     */
    public function store(UploadRequest $request): JsonResponse
    {
        $input = $request->all();
        try {
            $upload = $this->uploadRepository->create($input);
            $upload->addMedia($input['file'])
                ->withCustomProperties(['uuid' => $input['uuid'], 'user_id' => auth()->id()])
                ->toMediaCollection($input['field']);
            return $this->sendResponse($input['uuid'], "Uploaded Successfully");
        } catch (ValidatorException $e) {
            return $this->sendResponse(false, $e->getMessage());
        }
    }

    public function all(UploadRequest $request, $collection = null)
    {
        $allMedias = $this->uploadRepository->allMedia($collection);

        if (!auth()->user()->hasRole('admin')) {
            $allMedias = $allMedias->filter(function ($element) {
                return isset($element['custom_properties']['user_id'])
                    && $element['custom_properties']['user_id'] == auth()->id();
            });
        }

        // Convert to a plain array, then JSON-encode that
        $allMediasArray = $allMedias->toArray();
        $json = json_encode($allMediasArray);

        // Log both so you see exactly what’s returned
        Log::info('All medias collection:', $allMediasArray);
        Log::info('All medias JSON: ' . $json);

        // Return the JSON string 
        return $json;
    }

    /**
     * clear cache from Upload table
     * @throws \Exception
     */
    public function clear(UploadRequest $request): JsonResponse
    {
        $input = $request->all();
        if ($input['uuid']) {
            $result = $this->uploadRepository->clear($input['uuid']);
            return $this->sendResponse($result, 'Media deleted successfully');
        }
        return $this->sendResponse(false, 'Error will delete media');

    }

    /**
     * clear all cache
     * @return RedirectResponse
     */
    public function clearAll(): RedirectResponse
    {
        $this->uploadRepository->clearAll();
        return redirect()->back();
    }
}
