<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientFileLog;
use App\Models\PatientFileUser;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PatientFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.membership'])->except(['apiIndex', 'apiShow', 'apiDownload', 'apiDestroy', 'apiGiveAccess', 'apiUpload', 'apiRevokeAccess']);
        Log::info('PatientFileController initialized', ['user_id' => Auth::id()]);
    }

    // Web Routes
    public function index(Patient $patient = null)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        Log::info('Accessing patient files index', [
            'user_id' => $user->id,
            'patient_id' => $patient?->id
        ]);

        if ($patient) {
            if (!$this->hasFileAccess($patient, $user)) {
                Log::warning('Unauthorized access attempt to patient files', [
                    'user_id' => $user->id,
                    'patient_id' => $patient->id
                ]);
                abort(403, 'Unauthorized access to patient files.');
            }

            // Retrieve only files the user has access to
            $files = PatientFile::where('patient_id', $patient->id)
                ->where(function ($query) use ($user, $patient) {
                    // If user is the patient, include all their files
                    if ($patient->user_id === $user->id) {
                        return;
                    }
                    // If user is a doctor with a relationship, include files they have access to
                    if ($user->doctor && $user->doctor->patients()->where('patient_id', $patient->id)->exists()) {
                        $query->whereExists(function ($subQuery) use ($user) {
                            $subQuery->select(\DB::raw(1))
                                ->from('patient_file_users')
                                ->whereColumn('patient_file_users.patient_file_id', 'patient_files.id')
                                ->where('patient_file_users.user_id', $user->id)
                                ->where(function ($q) {
                                    $q->whereNull('patient_file_users.expiration_date')
                                        ->orWhere('patient_file_users.expiration_date', '>', now());
                                });
                        });
                    }
                })
                ->with('uploader')
                ->orderBy('created_at', 'desc')
                ->get();

            $patient->load('doctors.specialities', 'doctors.user');
            $allDoctors = Doctor::with('specialities', 'user')->get();
            $allUsers = User::whereNotNull('name')->whereNotNull('email')->get(); // Add this

            Log::info('Successfully retrieved patient files', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_count' => $files->count()
            ]);

            return view('patient_files.index', compact('patient', 'files', 'allDoctors', 'allUsers')); // Update to include allUsers
        }

        $myPatients = $doctor ? $doctor->patients()->with('user')->get() : [];
        Log::info('Viewing patient selection page', [
            'user_id' => $user->id,
            'patient_count' => $myPatients->count()
        ]);

        return view('patient_files.select_patient', compact('myPatients'));
    }

    public function show(Patient $patient, PatientFile $file)
    {
        $user = Auth::user();
        Log::info('Accessing patient file details', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('Unauthorized access attempt to patient file', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(403, 'Unauthorized access to patient file.');
        }

        $file->load('uploader');
        $patient->load('doctors.specialities', 'doctors.user');
        $allUsers = User::whereNotNull('name')->whereNotNull('email')->get(); // Add this

        Log::info('Successfully retrieved patient file details', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        return view('patient_files.show', compact('patient', 'file', 'allUsers')); // Include allUsers
    }

    public function create(Patient $patient)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        Log::info('Accessing file creation page', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$doctor || !$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized attempt to access file creation', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized access to patient files.');
        }

        return view('patient_files.create', compact('patient'));
    }

    public function store(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        Log::info('Attempting to upload file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$doctor || !$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized file upload attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized access to patient files.');
        }

        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2,zip,application/octet-stream',
                    'max:102400',
                ],
                'description' => 'nullable|string|max:255',
            ], [
                'file.required' => trans('lang.file_required'),
                'file.mimes' => trans('lang.invalid_file_type'),
                'file.max' => trans('lang.file_too_large', ['max' => '100MB']), // Update to reflect actual limit
            ]);

            $file = $request->file('file');
            $file = $request->file('file');
            Log::info('Detected MIME type', [
                'file_name' => $file->getClientOriginalName(),
                'mime_type' => $file->getClientMimeType(),
            ]);
            $fileName = $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());
            $encryptedContent = Crypt::encrypt($fileContent);
            $filePath = "{$patient->id}/" . time() . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            // // Check if storage is writable
            // if (!Storage::disk('patient_files')->getDriver()->getAdapter()->getPathPrefix()) {
            //     throw new \Exception('Storage disk is not properly configured.');
            // }

            Storage::disk('patient_files')->put($filePath, $encryptedContent);

            $patientFile = PatientFile::create([
                'patient_id' => $patient->id,
                'uploaded_by' => $user->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);

            PatientFileUser::create([
                'patient_file_id' => $patientFile->id,
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'expiration_date' => null,
            ]);

            PatientFileLog::create([
                'patient_file_id' => $patientFile->id,
                'user_id' => $user->id,
                'action' => 'upload',
            ]);

            Log::info('File uploaded successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $patientFile->id,
                'file_name' => $fileName
            ]);

            return redirect()->route('patient_files.index', $patient)
                ->with('success', trans('lang.file_uploaded_successfully'));
        } catch (ValidationException $e) {
            Log::error('File upload validation failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'errors' => $e->errors()
            ]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', trans('lang.file_upload_failed_validation'));
        } catch (\Illuminate\Contracts\Encryption\EncryptException $e) {
            Log::error('File encryption failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', trans('lang.file_upload_failed_encryption'));
        } catch (\Illuminate\Contracts\Filesystem\FileNotFoundException $e) {
            Log::error('File not found during upload', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', trans('lang.file_upload_failed_missing'));
        } catch (\Exception $e) {
            Log::error('File upload failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', trans('lang.file_upload_failed_generic', ['error' => $e->getMessage()]));
        }
    }

    public function download(Patient $patient, PatientFile $file)
    {
        $user = Auth::user();
        Log::info('Attempting to download file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('Unauthorized file download attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(403, 'Unauthorized access to patient files.');
        }

        if ($file->patient_id !== $patient->id) {
            Log::error('File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(404, 'File not found.');
        }

        try {
            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'download',
            ]);

            $encryptedContent = Storage::disk('patient_files')->get($file->file_path);
            $decryptedContent = Crypt::decrypt($encryptedContent);

            Log::info('File downloaded successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response($decryptedContent)
                ->header('Content-Type', $file->file_type)
                ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
        } catch (\Exception $e) {
            Log::error('File download failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            abort(500, 'File download failed.');
        }
    }

    public function destroy(Patient $patient, PatientFile $file)
    {
        $user = Auth::user();
        Log::info('Attempting to delete file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('Unauthorized file deletion attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(403, 'Unauthorized access to patient files.');
        }

        if ($file->patient_id !== $patient->id) {
            Log::error('File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(404, 'File not found.');
        }

        try {
            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'delete',
            ]);

            Storage::disk('patient_files')->delete($file->file_path);
            $file->delete();

            Log::info('File deleted successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return redirect()->route('patient_files.index', $patient)
                ->with('success', 'File deleted successfully.');
        } catch (\Exception $e) {
            Log::error('File deletion failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', 'File deletion failed.');
        }
    }

    public function assignAccess(Request $request, Patient $patient)
    {
        $user = Auth::user();
        $doctor = $user->doctor;
        Log::info('Attempting to assign file access', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$doctor || !$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized attempt to assign file access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized access to patient.');
        }

        if (!auth()->user()->hasPermissionInContext('patient_files.assign_access', $doctor->id)) {
            Log::warning('Missing permission to assign file access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized to assign file access.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
            'patient_file_id' => 'required|exists:patient_files,id',
            'expiration_date' => 'nullable|date|after:now',
        ]);

        $patientFile = PatientFile::findOrFail($request->patient_file_id);
        if ($patientFile->patient_id !== $patient->id) {
            Log::error('File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $request->patient_file_id
            ]);
            abort(404, 'File not found.');
        }

        $toUser = User::findOrFail($request->user_id);
        Log::info('Assigning file access', [
            'user_id' => $user->id,
            'to_user_id' => $toUser->id,
            'patient_id' => $patient->id,
            'file_id' => $patientFile->id,
            'file_name' => $patientFile->file_name
        ]);

        if (
            PatientFileUser::where('patient_file_id', $patientFile->id)
                ->where('user_id', $toUser->id)
                ->exists()
        ) {
            Log::info('Access already granted', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'file_id' => $patientFile->id,
                'file_name' => $patientFile->file_name
            ]);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', trans('lang.file_access_already_granted'));
        }

        try {
            PatientFileUser::create([
                'patient_file_id' => $patientFile->id,
                'patient_id' => $patient->id,
                'user_id' => $toUser->id,
                'expiration_date' => $request->expiration_date,
            ]);

            $doctor = Doctor::where('user_id', $toUser->id)->first();
            if ($doctor && !$patient->doctors()->where('doctors.id', $doctor->id)->exists()) {
                $patient->doctors()->attach($doctor);
                Log::info('Doctor-patient relationship created', [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id
                ]);
            }

            PatientFileLog::create([
                'patient_file_id' => $patientFile->id,
                'user_id' => $user->id,
                'action' => 'assign_access',
            ]);

            Log::info('File access assigned successfully', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $patientFile->id
            ]);

            return redirect()->route('patient_files.index', $patient)
                ->with('success', trans('lang.file_access_assigned_success'));
        } catch (\Exception $e) {
            Log::error('File access assignment failed', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $patientFile->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', 'File access assignment failed.');
        }
    }

    public function revokeAccess(Request $request, Patient $patient, PatientFile $file)
    {
        $user = Auth::user();
        Log::info('Attempting to revoke file access', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('Unauthorized attempt to revoke file access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(403, 'Unauthorized to revoke file access.');
        }

        $request->validate([
            'user_id' => 'required|exists:users,id',
        ]);

        $toUser = User::findOrFail($request->user_id);
        if ($user->id === $toUser->id) {
            Log::warning('Attempt to revoke own access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', 'Cannot revoke your own access.');
        }

        if ($file->patient_id !== $patient->id) {
            Log::error('File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            abort(404, 'File not found.');
        }

        try {
            $fileAccess = PatientFileUser::where('patient_file_id', $file->id)
                ->where('user_id', $toUser->id)
                ->first();

            if (!$fileAccess) {
                Log::info('No access found to revoke', [
                    'user_id' => $user->id,
                    'to_user_id' => $toUser->id,
                    'patient_id' => $patient->id,
                    'file_id' => $file->id
                ]);
                return redirect()->route('patient_files.index', $patient)
                    ->with('error', 'No access found to revoke.');
            }

            $fileAccess->delete();

            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'revoke_access',
            ]);

            Log::info('File access revoked successfully', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return redirect()->route('patient_files.index', $patient)
                ->with('success', 'File access revoked successfully.');
        } catch (\Exception $e) {
            Log::error('File access revocation failed', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('patient_files.index', $patient)
                ->with('error', 'File access revocation failed.');
        }
    }

    // API Routes
    public function apiIndex(Patient $patient, Request $request)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header');
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        Log::info('API: Accessing patient files index', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('API: Unauthorized access attempt to patient files', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Unauthorized access to patient files.'], 403);
        }

        // Retrieve only files the user has access to
        $files = PatientFile::where('patient_id', $patient->id)
            ->where(function ($query) use ($user, $patient) {
                // If user is the patient, include all their files
                if ($patient->user_id === $user->id) {
                    return;
                }
                // If user is a doctor with a relationship, include files they have access to
                if ($user->doctor && $user->doctor->patients()->where('patient_id', $patient->id)->exists()) {
                    $query->whereExists(function ($subQuery) use ($user) {
                        $subQuery->select(\DB::raw(1))
                            ->from('patient_file_users')
                            ->whereColumn('patient_file_users.patient_file_id', 'patient_files.id')
                            ->where('patient_file_users.user_id', $user->id)
                            ->where(function ($q) {
                                $q->whereNull('patient_file_users.expiration_date')
                                    ->orWhere('patient_file_users.expiration_date', '>', now());
                            });
                    });
                }
            })
            ->with('uploader')
            ->get()
            ->map(function ($file) {
                return $file->makeHidden(['uploader']);
            });

        Log::info('API: Successfully retrieved patient files', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_count' => $files->count()
        ]);

        return response()->json(['files' => $files], 200);
    }

    public function apiShow(Patient $patient, PatientFile $file, Request $request)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        Log::info('API: Attempting to access patient file show', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('API: Unauthorized access attempt to patient file', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Unauthorized access to patient files.'], 403);
        }

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        return response()->json(['file' => $file->makeHidden(['uploader'])], 200);
    }

    public function apiDownload(Patient $patient, PatientFile $file, Request $request)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        Log::info('API: Attempting to download file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('API: Unauthorized file download attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to download file.'], 403);
        }

        try {
            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'download',
            ]);

            $encryptedContent = Storage::disk('patient_files')->get($file->file_path);
            $decryptedContent = Crypt::decrypt($encryptedContent);

            Log::info('API: File downloaded successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response($decryptedContent)
                ->header('Content-Type', $file->file_type)
                ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
        } catch (\Exception $e) {
            Log::error('API: File download failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'File download failed.'], 500);
        }
    }

    public function apiDestroy(Patient $patient, PatientFile $file, Request $request)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        Log::info('API: Attempting to delete file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        if (!$this->hasFileAccess($patient, $user, $file) && $user->id !== $file->uploaded_by) {
            Log::warning('API: Unauthorized file deletion attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to delete file.'], 403);
        }

        try {
            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'delete',
            ]);

            Storage::disk('patient_files')->delete($file->file_path);
            $file->delete();

            Log::info('API: File deleted successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response()->json(['message' => 'File deleted successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('API: File deletion failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'File deletion failed.'], 500);
        }
    }

    public function apiGiveAccess(Request $request, Patient $patient, PatientFile $file)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
            'expiration_date' => 'nullable|date|after:now',
        ]);

        Log::info('API: Attempting to assign file access', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('API: Unauthorized attempt to assign file access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to assign file access.'], 403);
        }

        $toUser = User::findOrFail($request->to_user_id);
        Log::info('API: Assigning file access', [
            'user_id' => $user->id,
            'to_user_id' => $toUser->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id,
            'file_name' => $file->file_name
        ]);

        if (
            PatientFileUser::where('patient_file_id', $file->id)
                ->where('user_id', $toUser->id)
                ->exists()
        ) {
            Log::info('API: Access already granted', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);
            return response()->json(['error' => trans('lang.file_access_already_granted')], 400);
        }

        try {
            PatientFileUser::create([
                'patient_file_id' => $file->id,
                'patient_id' => $patient->id,
                'user_id' => $toUser->id,
                'expiration_date' => $request->expiration_date,
            ]);

            $doctor = Doctor::where('user_id', $toUser->id)->first();
            if ($doctor && !$patient->doctors()->where('doctors.id', $doctor->id)->exists()) {
                $patient->doctors()->attach($doctor);
                Log::info('API: Doctor-patient relationship created', [
                    'user_id' => $user->id,
                    'doctor_id' => $doctor->id,
                    'patient_id' => $patient->id
                ]);
            }

            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'assign_access'
            ]);

            Log::info('API: File access assigned successfully', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);

            return response()->json(['message' => trans('lang.file_access_assigned_success')], 200);
        } catch (\Exception $e) {
            Log::error('API: File access assignment failed', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'File access assignment failed.'], 500);
        }
    }

    public function apiUpload(Request $request, Patient $patient)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        \Log::info('API: Attempting to upload file', [
            'user_id' => $userId,
            'patient_id' => $patient->id
        ]);

        $user = User::findOrFail($userId);

        \Log::info('found user', [
            'user_id' => $user->email
        ]);

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2|max:102400',
            'description' => 'nullable|string|max:255',
        ]);

        $user = User::findOrFail($userId);
        Log::info('API: Attempting to upload file', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('API: Unauthorized file upload attempt', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Unauthorized to upload file.'], 403);
        }

        try {
            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());
            $encryptedContent = Crypt::encrypt($fileContent);
            $filePath = "{$patient->id}/" . time() . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            Storage::disk('patient_files')->put($filePath, $encryptedContent);

            $patientFile = PatientFile::create([
                'patient_id' => $patient->id,
                'uploaded_by' => $user->id,
                'file_name' => $fileName,
                'file_path' => $filePath,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
                'description' => $request->description,
            ]);

            PatientFileUser::create([
                'patient_file_id' => $patientFile->id,
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'expiration_date' => null,
            ]);

            PatientFileLog::create([
                'patient_file_id' => $patientFile->id,
                'user_id' => $user->id,
                'action' => 'upload',
            ]);

            Log::info('API: File uploaded successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $patientFile->id,
                'file_name' => $fileName
            ]);

            return response()->json(['message' => 'File uploaded successfully.', 'file' => $patientFile], 200);
        } catch (\Exception $e) {
            Log::error('API: File upload failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'File upload failed.'], 500);
        }
    }

    public function apiRevokeAccess(Request $request, Patient $patient, PatientFile $file)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        $user = User::findOrFail($userId);
        $request->validate([
            'to_user_id' => 'required|exists:users,id',
        ]);

        Log::info('API: Attempting to revoke file access', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_id' => $file->id
        ]);

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        if (!$this->hasFileAccess($patient, $user, $file)) {
            Log::warning('API: Unauthorized attempt to revoke file access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to revoke file access.'], 403);
        }

        $toUser = User::findOrFail($request->to_user_id);
        if ($user->id === $toUser->id) {
            Log::warning('API: Attempt to revoke own access', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Cannot revoke your own access.'], 400);
        }

        try {
            $fileAccess = PatientFileUser::where('patient_file_id', $file->id)
                ->where('user_id', $toUser->id)
                ->first();

            if (!$fileAccess) {
                Log::info('API: No access found to revoke', [
                    'user_id' => $user->id,
                    'to_user_id' => $toUser->id,
                    'patient_id' => $patient->id,
                    'file_id' => $file->id
                ]);
                return response()->json(['error' => 'No access found to revoke.'], 400);
            }

            $fileAccess->delete();

            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'revoke_access',
            ]);

            Log::info('API: File access revoked successfully', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response()->json(['message' => 'File access revoked successfully.'], 200);
        } catch (\Exception $e) {
            Log::error('API: File access revocation failed', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'File access revocation failed.'], 500);
        }
    }

    // Helper method to check file access
    protected function hasFileAccess(Patient $patient, $userOrId, PatientFile $file = null)
    {
        $user = is_numeric($userOrId) ? User::findOrFail($userOrId) : $userOrId;

        // If requester is the patient: grant access
        if ($user && $user->id === $patient->user_id) {
            return true;
        }

        // If requester is a doctor
        $doctor = $user->doctor;

        // Check doctor-patient relationship
        if ($doctor && $doctor->patients()->where('patient_id', $patient->id)->exists()) {

            if (!$file) {
                return true;
            }

            // Check specific file access in patient_file_users
            $query = PatientFileUser::where('patient_id', $patient->id)
                ->where('user_id', $user->id)
                ->where(function ($q) {
                    $q->whereNull('expiration_date')
                        ->orWhere('expiration_date', '>', now());
                });

            if ($file) {
                $query->where('patient_file_id', $file->id);
            }

            return $query->exists();
        }

        return false;
    }
}