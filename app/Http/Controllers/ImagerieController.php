<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ImagerieController extends Controller
{
    /**
     * Display a listing of the resource.
     */
public function index()
    {
        return view('imagerie.index');
    }

   
 public function show($specialty)
    {
        // Validation des spécialités autorisées
        $allowedSpecialties = [
            'dermato', 'cardio', 'Radiologue', 
            'gastro', 'gyneco', 'ortho',
            'ophta', 'dentist', 'oncology'
        ];
        
        if (!in_array($specialty, $allowedSpecialties)) {
            abort(404);
        }
        
        return view("imagerie.{$specialty}");
    }

    
}
