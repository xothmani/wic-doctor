<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientFileLog;
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
        // Check if the authenticated doctor is associated with the patient
        $doctor = Auth::user()->doctor;
        if (!$doctor || !$doctor->patients()->where('patient_id', $patient->id)->exists()) {
            abort(403, 'Unauthorized access to patient files.');
        }

        $files = $patient->files()->with('uploader')->get();
        return view('patient_files.index', compact('patient', 'files'));
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
            'file' => 'required|file|mimes:pdf,doc,docx,jpg,png|max:20480',
            'description' => 'nullable|string|max:255',
        ]);

        $file = $request->file('file');
        $fileName = $file->getClientOriginalName();
        $fileContent = file_get_contents($file->getRealPath());
        $encryptedContent = Crypt::encrypt($fileContent);
        $filePath = "patient_files/{$patient->id}/" . time() . '_' . $fileName;

        Storage::disk('patient_files')->put($filePath, $encryptedContent);

        $f = PatientFile::create([
            'patient_id' => $patient->id,
            'uploaded_by' => Auth::id(),
            'file_name' => $fileName,
            'file_path' => $filePath,
            'file_type' => $file->getClientMimeType(),
            'file_size' => $file->getSize(),
            'description' => $request->description,
        ]);

        PatientFileLog::create([
            'patient_file_id' => $f->id,
            'user_id' => Auth::id(),
            'action' => 'upload',
        ]);

        return redirect()->route('patient_files.index', $patient)
            ->with('success', 'File uploaded successfully.');
    }

    // In download method
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
}