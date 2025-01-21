<?php
/*
 * File name: ConsultationRepository.php
 * Author: Ton Nom
 * Copyright (c) 2024
 */

namespace App\Repositories;

use App\Models\Consultation;
use InfyOm\Generator\Common\BaseRepository;

/**
 * Class ConsultationRepository
 * @package App\Repositories
 *
 * @method Consultation findWithoutFail($id, $columns = ['*'])
 * @method Consultation find($id, $columns = ['*'])
 * @method Consultation first($columns = ['*'])
 */
class ConsultationRepository extends BaseRepository
{
    /**
     * @var array
     */
    protected $fieldSearchable = [
        'dateConsultation',
        'raison',
        'motif',
        'patient_id',
    ];

    /**
     * Configure the Model
     **/
    public function model(): string
    {
        return Consultation::class;
    }
}
