<?php
/*
 * File name: UpdateConsultationRequest.php
 * Author: Ton Nom
 * Copyright (c) 2024
 */

namespace App\Http\Requests;

use App\Models\Consultation;
use Illuminate\Foundation\Http\FormRequest;

class UpdateConsultationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules(): array
    {
        return Consultation::$rules; // Assure-toi que les règles existent dans le modèle Consultation
    }

    /**
     * @param array $keys
     * @return array
     */
    public function all($keys = null): array
    {
        $input = parent::all();
        if (!isset($input['patient_id']) || $input['patient_id'] == 0) {
            $input['patient_id'] = null; // Ajuste selon tes besoins
        }
        return $input;
    }
}
