<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientFileLog;
use App\Models\PatientFileUser;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class PatientFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.membership'])->except(['apiIndex', 'apiShow', 'apiDownload', 'apiDestroy', 'apiGiveAccess', 'apiUpload']);
    }

    // Web Routes
    public function index(Patient $patient = null)
    {
        $doctor = Auth::user()->doctor;

        if ($patient) {
            if (!$this->hasFileAccess($patient, Auth::user())) {
                abort(403, 'Unauthorized access to patient files.');
            }

            $files = $patient->files()->with('uploader')->get();
            $patient->load('doctors.specialities', 'doctors.user');
            $allDoctors = Doctor::with('specialities', 'user')->get();

            return view('patient_files.index', compact('patient', 'files', 'allDoctors'));
        }

        $myPatients = $doctor ? $doctor->patients()->with('user')->get() : [];
        return view('patient_files.select_patient', compact('myPatients'));
    }

    public function show(Patient $patient, PatientFile $file)
    {
        if (!$this->hasFileAccess($patient, Auth::user(), $file)) {
            abort(403, 'Unauthorized access to patient files.');
        }

        if ($file->patient_id !== $patient->id) {
            abort(404, 'File not found.');
        }

        return view('patient_files.show', compact('patient', 'file'));
    }

    public function create(Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        return view('patient_files.create', compact('patient'));
    }

    public function store(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2|max:102400',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());
        $encryptedContent = Crypt::encrypt($fileContent);
        $filePath = "patient_files/{$patient->id}/" . time() . '_' . $fileName;

        Storage::disk('patient_files')->put($filePath, $encryptedContent);

        $patientFile = PatientFile::create([
            'patient_id' => $patient->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        // Grant access to the uploader (doctor)
        PatientFileUser::create([
            'patient_file_id' => $patientFile->id,
            'patient_id' => $patient->id,
            'user_id' => Auth::id(),
            'expiration_date' => null,
        ]);

        PatientFileLog::create([
            'patient_file_id' => $patientFile->id,
            'user_id' => Auth::id(),
            'action' => 'upload',
        ]);

        return redirect()->route('patient_files.index', $patient)
            ->with('success', 'File uploaded successfully.');
    }

    public function download(Patient $patient, PatientFile $file)
    {
        if (!$this->hasFileAccess($patient, Auth::user(), $file)) {
            abort(403, 'Unauthorized access to patient files.');
        }

        if ($file->patient_id !== $patient->id) {
            abort(404, 'File not found.');
        }

        PatientFileLog::create([
            'patient_file_id' => $file->id,
            'user_id' => Auth::id(),
            'action' => 'download',
        ]);

        $encryptedContent = Storage::disk('patient_files')->get($file->file_path);
        $decryptedContent = Crypt::decrypt($encryptedContent);

        return response($decryptedContent)
            ->header('Content-Type', $file->file_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
    }

    public function destroy(Patient $patient, PatientFile $file)
    {
        if (!$this->hasFileAccess($patient, Auth::user(), $file)) {
            abort(403, 'Unauthorized access to patient files.');
        }

        if ($file->patient_id !== $patient->id) {
            abort(404, 'File not found.');
        }

        PatientFileLog::create([
            'patient_file_id' => $file->id,
            'user_id' => Auth::id(),
            'action' => 'delete',
        ]);

        Storage::disk('patient_files')->delete($file->file_path);
        $file->delete();

        return redirect()->route('patient_files.index', $patient)
            ->with('success', 'File deleted successfully.');
    }

    public function assignAccess(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient.');
        }

        if (!auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctor->id)) {
            abort(403, 'Unauthorized to assign file access.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'patient_file_id' => 'required|exists:patient_files,id',
            'expiration_date' => 'nullable|date|after:now',
        ]);

        $patientFile = PatientFile::findOrFail($request->patient_file_id);
        if ($patientFile->patient_id !== $patient->id) {
            abort(404, 'File not found.');
        }

        $user = \App\Models\User::findOrFail($request->user_id);
        \Log::info('Assigning file access; user: ' . $user->name . ' ' . $user->id . ' File: ' . $patientFile->file_name . ' Patient: ' . $patient->name);

        if (
            PatientFileUser::where('patient_file_id', $patientFile->id)
                ->where('user_id', $user->id)
                ->exists()
        ) {
            \Log::info('Access already granted; user: ' . $user->name . ' File: ' . $patientFile->file_name);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', trans('lang.file_access_already_granted'));
        }

        PatientFileUser::create([
            'patient_file_id' => $patientFile->id,
            'patient_id' => $patient->id,
            'user_id' => $user->id,
            'expiration_date' => $request->expiration_date,
        ]);

        // If the user is a doctor, maintain the doctor_patients relationship
        $doctor = Doctor::where('user_id', $user->id)->first();
        if ($doctor && !$patient->doctors()->where('doctors.id', $doctor->id)->exists()) {
            $patient->doctors()->attach($doctor);
            \Log::info('Doctor-patient relationship created; doctor: ' . $doctor->name . ' Patient: ' . $patient->name);
        }

        return redirect()->route('patient_files.index', $patient)
            ->with('success', trans('lang.file_access_assigned_success'));
    }

    // API Routes
    // index test: passed successfully
    public function apiIndex(Patient $patient)
    {
        $files = $patient->files()->with('uploader')->get()->map(function ($file) {
            return $file->makeHidden(['uploader']);
        });
        return response()->json(['files' => $files], 200);
    }

    // show test: passed successfully
    public function apiShow(Patient $patient, PatientFile $file)
    {
        if ($file->patient_id !== $patient->id) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        return response()->json(['file' => $file->makeHidden(['uploader'])], 200);
    }

    // download test: passed successfully
    public function apiDownload(Patient $patient, PatientFile $file, Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($file->patient_id !== $patient->id) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        PatientFileLog::create([
            'patient_file_id' => $file->id,
            'user_id' => $request->user_id,
            'action' => 'download',
        ]);

        $encryptedContent = Storage::disk('patient_files')->get($file->file_path);
        $decryptedContent = Crypt::decrypt($encryptedContent);

        return response($decryptedContent)
            ->header('Content-Type', $file->file_type)
            ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
    }

    public function apiDestroy(Patient $patient, PatientFile $file, Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        if ($file->patient_id !== $patient->id) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        PatientFileLog::create([
            'patient_file_id' => $file->id,
            'user_id' => $request->user_id,
            'action' => 'delete',
        ]);

        Storage::disk('patient_files')->delete($file->file_path);
        $file->delete();

        return response()->json(['message' => 'File deleted successfully.'], 200);
    }

    public function apiGiveAccess(Request $request, Patient $patient, PatientFile $file)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'to_user_id' => 'required|exists:users,id',
            'expire_duration' => 'nullable|integer|min:1', // Duration in days
        ]);

        if ($file->patient_id !== $patient->id) {
            return response()->json(['error' => 'File not found.'], 404);
        }

        $toUser = \App\Models\User::findOrFail($request->to_user_id);
        \Log::info('Assigning file access; to_user: ' . $toUser->name . ' ' . $toUser->id . ' File: ' . $file->file_name . ' Patient: ' . $patient->name);

        if (
            PatientFileUser::where('patient_file_id', $file->id)
                ->where('user_id', $toUser->id)
                ->exists()
        ) {
            \Log::info('Access already granted; to_user: ' . $toUser->name . ' File: ' . $file->file_name);
            return response()->json(['error' => trans('lang.file_access_already_granted')], 400);
        }

        $expirationDate = $request->expire_duration ? now()->addDays($request->expire_duration) : null;

        PatientFileUser::create([
            'patient_file_id' => $file->id,
            'patient_id' => $patient->id,
            'user_id' => $toUser->id,
            'expiration_date' => $expirationDate,
        ]);

        // If the to_user is a doctor, maintain the doctor_patients relationship
        $doctor = Doctor::where('user_id', $toUser->id)->first();
        if ($doctor && !$patient->doctors()->where('doctors.id', $doctor->id)->exists()) {
            $patient->doctors()->attach($doctor);
            \Log::info('Doctor-patient relationship created; doctor: ' . $doctor->name . ' Patient: ' . $patient->name);
        }

        return response()->json(['message' => trans('lang.file_access_assigned_success')], 200);
    }

    
    public function apiUpload(Request $request, Patient $patient)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2|max:102400',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());
        $encryptedContent = Crypt::encrypt($fileContent);
        $filePath = "patient_files/{$patient->id}/" . time() . '_' . $fileName;

        Storage::disk('patient_files')->put($filePath, $encryptedContent);

        $patientFile = PatientFile::create([
            'patient_id' => $patient->id,
            'uploaded_by' => $request->user_id,
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        PatientFileUser::create([
            'patient_file_id' => $patientFile->id,
            'patient_id' => $patient->id,
            'user_id' => $request->user_id,
            'expiration_date' => null,
        ]);

        PatientFileLog::create([
            'patient_file_id' => $patientFile->id,
            'user_id' => $request->user_id,
            'action' => 'upload',
        ]);

        return response()->json(['message' => 'File uploaded successfully.', 'file' => $patientFile], 200);
    }

    // Helper method to check file access
    protected function hasFileAccess(Patient $patient, $user, PatientFile $file = null)
    {
        $doctor = $user->doctor;

        // Check doctor-patient relationship
        if ($doctor && $doctor->patients()->where('patient_id', $patient->id)->exists()) {
            return true;
        }

        // Check specific file access in patient_file_users
        $query = PatientFileUser::where('patient_id', $patient->id)
            ->where('user_id', $user->id)
            ->whereNull('expiration_date')
            ->orWhere('expiration_date', '>', now());

        if ($file) {
            $query->where('patient_file_id', $file->id);
        }

        return $query->exists();
    }
}