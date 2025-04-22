<?php

    namespace App\Http\Controllers;

    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Storage;
    use Illuminate\Support\Facades\Http;
    use Illuminate\Support\Facades\Validator;
    class HelpDeskController extends Controller
    {
        public function index()
        {
            return view('helpdesk.index');
        }

        
 
    }
