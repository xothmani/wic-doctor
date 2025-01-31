<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\DataTables\NewsLatterDataTable;
use App\Models\NewsLatter;

class NewsLatterController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(NewsLatterDataTable $dataTable)
    {
        return $dataTable->render('newsletters.index'); // Vue à personnaliser
    }}
