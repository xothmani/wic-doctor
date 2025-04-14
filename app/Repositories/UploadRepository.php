<?php
/*
 * File name: UploadRepository.php
 * Last modified: 2021.05.31 at 16:24:41
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Repositories;

use App\Models\Media;
use App\Models\Upload;
use Exception;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Str;
use InfyOm\Generator\Common\BaseRepository;
use Illuminate\Support\Facades\Log;

/**
 * Class UploadRepository
 * @package App\Repositories
 * @version June 12, 2018, 11:30 am UTC
 *
 * @method Upload findWithoutFail($id, $columns = ['*'])
 * @method Upload find($id, $columns = ['*'])
 * @method Upload first($columns = ['*'])
 */
class UploadRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [

    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Upload::class;
    }

    /**
     * @param $uuid
     * @throws Exception
     */
    public function clear($uuid): ?bool
    {
        $uploadModel = $this->getByUuid($uuid);
        return $uploadModel->delete();
    }

    /**
     * @param $uuids
     * @throws Exception
     */
    /*public function clearWhereIn($uuids): ?bool
    {
        $data = Upload::query()->whereIn('uuid', $uuids)->get();
        Log::info("Found uploads to delete: " . $data->toJson());


        try{
            return Upload::query()->whereIn('uuid', $uuids)->delete();
        }catch(Exception $e){
            Log::info("Error delete images by uuids: " . $e->getMessage());
        }
        
    }*/

    public function clearWhereIn($uuids): ?bool
    {
        $data = Media::query()->whereIn('uuid', $uuids)->get();
        Log::info("Found media to delete: " . $data->toJson());

        try {
            return Media::query()->whereIn('uuid', $uuids)->delete(); // ou ->forceDelete() si soft deletes
        } catch (Exception $e) {
            Log::info("Error deleting media by uuids: " . $e->getMessage());
            return false;
        }
    }

    /** public function getByUuid($uuid = '')
     {
         $uploadModel = Upload::query()->where('uuid', $uuid)->first();
         return $uploadModel;
     }**/
    public function getByUuid($uuid = '')
    {
        $uploadModel = Upload::query()->where('uuid', $uuid)->first();
        if (!$uploadModel) {
            \Log::error("Upload not found for UUID: {$uuid}");
        }
        return $uploadModel;
    }

    /**
     * clear all uploaded cache
     */
    public function clearAll()
    {
        Upload::query()->where('id', '>', 0)->delete();
        Media::query()->where('model_type', '=', 'App\Models\Upload')->delete();
    }

    /**
     * @return Builder[]|Collection
     */
    public function allMedia($collection = null)
    {
        $medias = Media::query()->where('model_type', '=', 'App\Models\Upload');
        if ($collection) {
            $medias = $medias->where('collection_name', $collection);
        }
        $medias = $medias->orderBy('id', 'desc')->get();
        return $medias;
    }
    public function allMediacat($collection = null)
    {
        // Log the incoming collection for debugging
        Log::info("Fetching media for collection: " . ($collection ?? 'all collections'));

        // Query the Media model
        $medias = Media::query()->where('model_type', 'App\Models\Upload');

        // If a collection is specified, filter by it
        if ($collection) {
            $medias->where('collection_name', $collection);
        }

        // Order by descending ID and fetch the results
        $medias = $medias->orderBy('id', 'desc')->get();

        // Log the fetched results
        Log::info("Media fetched: ", $medias->toArray());

        return $medias;
    }


    public function collectionsNames()
    {
        $medias = Media::all('collection_name')->pluck('collection_name', 'collection_name')->map(function ($c) {
            return [
                'value' => $c,
                'title' => Str::title(preg_replace('/_/', ' ', $c))
            ];
        })->unique();
        unset($medias['default']);
        $medias->prepend(['value' => 'default', 'title' => 'Default'], 'default');
        return $medias;
    }

}
