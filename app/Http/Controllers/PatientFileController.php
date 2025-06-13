<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientFileLog;
use App\Models\Doctor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;

class PatientFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.membership']);
    }

    // List files for a patient
    public function index(Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        $files = $patient->files()->with('uploader')->get();
        $patient->load('doctors.specialities', 'doctors.user');

        $allDoctors = Doctor::with('specialities', 'user')->get();
        return view('patient_files.index', compact('patient', 'files', 'allDoctors'));
    }

    // Show a specific file
    public function show(Patient $patient, PatientFile $file)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        if (!auth()->user()->hasPermissionInContext('patient_files.show', $doctor->id)) {
            abort(403, 'Unauthorized to view file details.');
        }

        if ($file->patient_id !== $patient->id) {
            abort(404, 'File not found.');
        }

        return view('patient_files.show', compact('patient', 'file'));
    }

    // Show upload form
    public function create(Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        return view('patient_files.create', compact('patient'));
    }

    // Store a new file
    public function store(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,jpeg,png,gif,webp,svg,txt,csv,xls,xlsx,ppt,pptx,zip,rar,7z,tar,gz,bz2,xml,hl7,dcm,nii,ecg|max:20480',
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

        PatientFileLog::create([
            'patient_file_id' => $patientFile->id,
            'user_id' => Auth::id(),
            'action' => 'upload',
        ]);

        return redirect()->route('patient_files.index', $patient)
            ->with('success', 'File uploaded successfully.');
    }

    // Download a file
    public function download(Patient $patient, PatientFile $file)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
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

    // Delete a file
    public function destroy(Patient $patient, PatientFile $file)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
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

    // Assign a doctor to the patient
    public function assignDoctor(Request $request, Patient $patient)
    {
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient.');
        }

        if (!auth()->user()->hasPermissionInContext('patient_files.assign_doctor', $doctor->id)) {
            abort(403, 'Unauthorized to assign doctors.');
        }

        $request->validate([
            'doctor_id' => 'required|exists:doctors,id',
        ]);

        $selectedDoctor = Doctor::findOrFail($request->doctor_id);
        \Log::info('Associate; doctor: ' . $selectedDoctor->name . ' ' . $selectedDoctor->id . '  Patient: ' . $patient->name . ' ' . $patient->id);

        // Check if the doctor is already associated
        if ($patient->doctors()->where('doctors.id', $selectedDoctor->id)->exists()) {
            \Log::info('Already associated; doctor: ' . $selectedDoctor->name . ' ' . $selectedDoctor->id . '  Patient: ' . $patient->name . ' ' . $patient->id);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', trans('lang.doctor_already_associated'));
        }

        // Associate the doctor with the patient (add ids to table doctor_patients)
        $patient->doctors()->attach($selectedDoctor);

        return redirect()->route('patient_files.index', $patient)
            ->with('success', trans('lang.doctor_assigned_success'));
    }
}