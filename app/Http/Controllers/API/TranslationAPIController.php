<?php
/*
 * File name: TranslationAPIController.php
 * Last modified: 2024.03.14 at 19:05:03
 * Author: SmarterVision - https://codecanyon.net/user/smartervision
 * Copyright (c) 2024
 */

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Log;

class TranslationAPIController extends Controller
{

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    function supportedLocales(Request $request)
    {
        try {
            if (($request->segment(2) == 'clinic_owner')) {
                $file = "clinic_owner_app.json";
            }else if (($request->segment(2) == 'doctor')) {
                $file = "doctor_app.json";
            }
            else {
                $file = "customer_app.json";
            }
            $dir = base_path("resources/lang/");
            $locales = array_diff(scandir($dir), array('..', '.'));
            $supportedLocales = [];
            foreach ($locales as $locale) {
                if (file_exists(base_path("resources/lang/$locale/$file"))) {
                    $supportedLocales[] = $locale;
                }
            }
        } catch (Exception $exception) {
            return $this->sendError($exception->getMessage());
        }
        return $this->sendResponse($supportedLocales, 'Supported Locales retrieved successfully');
    }

    function translations(Request $request)
    {
        Log::info("TranslationAPIController", ["request" => $request->all()]);

        if (auth()->check()) {
            Log::info("Utilisateur connecté", ["id" => auth()->id()]);
        }


        try {
            $this->validate($request, [
                'locale' => 'required|string:10',
            ]);


            // 1. Mise à jour directe du champ locale_mobile de l'utilisateur connecté
            if (auth()->check()) {
                $user = auth()->user();
                $user->locale_mobile = $request->locale;
                $user->save(); // ✅ Sauvegarder la mise à jour
            }

            if (($request->segment(2) == 'clinic_owner')) {
                $file = "clinic_owner_app.json";
            }else if (($request->segment(2) == 'doctor')) {
                $file = "doctor_app.json";
            }
            else {
                $file = "customer_app.json";
            }
            $locale = $request->get('locale', 'en');
            $translation = json_decode(
                file_get_contents(base_path("resources/lang/$locale/$file")), true
            );
        } catch (ValidationException | Exception $exception) {
            return $this->sendError("Translation Not Found");
        }
        return $this->sendResponse($translation, 'Translation retrieved successfully');
    }



    public function setUserLocale(Request $request)
    {
        $user = auth()->user();

        if (!$user) {
            return response()->json(false);
        }


        $locale = $request->get('locale');
        if (!in_array($locale, ['en', 'fr', 'ar'])) {
            return response()->json(false);
        }

        try {
            $user->locale_mobile = $locale;
            $user->save();
            return response()->json(true);
        } catch (\Exception $e) {
            return response()->json(false);
        }
    }
}
