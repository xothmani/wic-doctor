<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\PatientFile;
use App\Models\PatientFileLog;
use App\Models\PatientFileUser;
use App\Models\Doctor;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\URL;
use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;

class PatientFileController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'check.membership'])->except(['apiIndex', 'apiShow', 'apiDownload', 'apiDestroy', 'apiGiveAccess', 'apiUpload', 'apiRevokeAccess', 'apiGetAssignedUsers', 'apiGenerateFileQrCode', 'apiDownloadExternal', 'publicUpload', 'apiGeneratePublicUploadLink']);
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

    // Si un patient spécifique est demandé
    if ($patient) {
        // Vérification des droits d'accès
        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized access attempt to patient files', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized access to patient files.');
        }

        // Récupération des fichiers avec pagination
        $files = PatientFile::where('patient_id', $patient->id)
            ->where(function ($query) use ($user, $patient) {
                if ($patient->user_id === $user->id) {
                    return;
                }
                
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
            ->paginate(4);

        $patient->load('doctors.specialities', 'doctors.user');
        $allDoctors = Doctor::with('specialities', 'user')->get();
        $allUsers = User::whereNotNull('name')->whereNotNull('email')->get();

        return view('patient_files.index', compact('patient', 'files', 'allDoctors', 'allUsers'));
    }

    // Si aucun patient spécifique - page de sélection
    $searchTerm = request()->input('search');
    
    $query = $doctor 
        ? $doctor->patients()->with('user')
        : Patient::query()->whereNull('id');

    if ($searchTerm) {
        $query->where(function($q) use ($searchTerm) {
            $q->where('first_name', 'like', "%{$searchTerm}%")
              ->orWhere('last_name', 'like', "%{$searchTerm}%")
              ->orWhereHas('user', function($userQuery) use ($searchTerm) {
                  $userQuery->where('email', 'like', "%{$searchTerm}%");
              });
        });
    }

    $myPatients = $query->paginate(4)
                       ->appends(request()->query());

    Log::info('Viewing patient selection page', [
        'user_id' => $user->id,
        'patient_count' => $myPatients->total(),
        'search_term' => $searchTerm
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

        return view(view: 'patient_files.show', data: compact('patient', 'file', 'allUsers')); // Include allUsers
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
                return redirect()->route('patient_files.show', ['patient' => $patient->id, 'file' => $file->id])
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

            return redirect()->route('patient_files.show', ['patient' => $patient->id, 'file' => $file->id])
                ->with('success', 'File access revoked successfully.');
        } catch (\Exception $e) {
            Log::error('File access revocation failed', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->route('patient_files.show', ['patient' => $patient->id, 'file' => $file->id])
                ->with('error', 'File access revocation failed.');
        }
    }

    public function getAssignedUsers($patientId, $fileId)
    {
        $assignedUserIds = PatientFileUser::where('patient_file_id', $fileId)
            ->where('patient_id', $patientId)
            ->where(function ($query) {
                $query->whereNull('expiration_date')
                    ->orWhere('expiration_date', '>', now());
            })
            ->pluck('user_id')
            ->toArray();

        return response()->json(['user_ids' => $assignedUserIds]);
    }

    public function generatePublicUploadLink(Request $request, Patient $patient)
    {
        Log::info('Attempting to generate public upload link');
        $user = Auth::user();

        Log::info('Attempting to generate public upload link', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized attempt to generate public upload link', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized to generate public upload link.');
        }

        try {
            // Generate a signed URL valid for 24 hours
            $uploadUrl = URL::temporarySignedRoute(
                'patient_files.public_upload',
                now()->addHours(24),
                ['patient' => $patient->id, 'user' => $user->id]
            );

            // Generate QR code for the upload URL
            $qrCode = QrCode::create($uploadUrl)->setSize(300);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = base64_encode($result->getString());

            Log::info('Public upload link and QR code generated successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'upload_url' => $uploadUrl
            ]);

            return response()->json([
                'message' => 'Public upload link generated successfully.',
                'upload_url' => $uploadUrl,
                'qr_code' => 'data:image/png;base64,' . $qrCodeBase64
            ], 200);
        } catch (\Exception $e) {
            Log::error('Public upload link generation failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to generate public upload link.'], 500);
        }
    }

    public function publicUpload(Request $request, Patient $patient, User $user)
    {
        Log::info('Accessing public upload page', [
            'patient_id' => $patient->id,
            'user_id' => $user->id
        ]);

        // Verify the signed URL
        if (!$request->hasValidSignature()) {
            Log::warning('Invalid or expired public upload link', [
                'patient_id' => $patient->id,
                'user_id' => $user->id
            ]);
            abort(403, 'Invalid or expired upload link.');
        }

        // Verify user-patient relationship
        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('User does not have access to patient', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized access to patient.');
        }

        if ($request->isMethod('get')) {
            // Show the public upload form
            return view('patient_files.public_upload', compact('patient', 'user'));
        }

        // Handle file upload
        try {
            $request->validate([
                'file' => [
                    'required',
                    'file',
                    'mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2',
                    'max:102400',
                ],
                'description' => 'nullable|string|max:255',
            ], [
                'file.required' => trans('lang.file_required'),
                'file.mimes' => trans('lang.invalid_file_type'),
                'file.max' => trans('lang.file_too_large', ['max' => '100MB']),
            ]);

            $file = $request->file('file');
            $fileName = $file->getClientOriginalName();
            $fileContent = file_get_contents($file->getRealPath());
            $encryptedContent = Crypt::encrypt($fileContent);
            $filePath = "{$patient->id}/" . time() . '_' . Str::slug(pathinfo($fileName, PATHINFO_FILENAME)) . '.' . $file->getClientOriginalExtension();

            Storage::disk('patient_files')->put($filePath, $encryptedContent);

            $patientFile = PatientFile::create([
                'patient_id' => $patient->id,
                'uploaded_by' => $user->id, // Set uploader as the user who generated the link
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

            Log::info('Public file uploaded successfully', [
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'file_id' => $patientFile->id,
                'file_name' => $fileName
            ]);

            return redirect()->back()->with('success', trans('lang.file_uploaded_successfully'));
        } catch (ValidationException $e) {
            Log::error('Public file upload validation failed', [
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'errors' => $e->errors()
            ]);
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput()
                ->with('error', trans('lang.file_upload_failed_validation'));
        } catch (\Exception $e) {
            Log::error('Public file upload failed', [
                'patient_id' => $patient->id,
                'user_id' => $user->id,
                'error' => $e->getMessage()
            ]);
            return redirect()->back()
                ->with('error', trans('lang.file_upload_failed_generic', ['error' => $e->getMessage()]));
        }
    }

    // API Routes
    public function apiIndex(Patient $patient, Request $request)
    {
        Log::info('API: Accessing patient files index -> apiIndex function');
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header');
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

        $perPage = $request->query('per_page', 10);
        $page = $request->query('page', 1);

        if (!is_numeric($perPage) || $perPage < 1 || $perPage > 100) {
            return response()->json(['error' => 'Invalid per_page value. Must be between 1 and 100.'], 400);
        }
        if (!is_numeric($page) || $page < 1) {
            return response()->json(['error' => 'Invalid page value. Must be at least 1.'], 400);
        }

        $query = PatientFile::where('patient_id', $patient->id)
            ->where(function ($query) use ($user, $patient) {
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
            ->with('uploader');

        // Apply pagination
        $files = $query->paginate($perPage, ['*'], 'page', $page)
            ->through(function ($file) {
                return [
                    'id' => $file->id,
                    'patient_id' => $file->patient_id,
                    'uploaded_by' => $file->uploader->id, 
                    'uploaded_by_user' => $file->uploader,// user
                    'file_name' => $file->file_name,
                    'file_path' => $file->file_path,
                    'file_type' => $file->file_type,
                    'file_size' => $file->file_size,
                    'description' => $file->description,
                    'created_at' => $file->created_at,
                    'updated_at' => $file->updated_at,
                ];
            });

        Log::info('API: Successfully retrieved patient files', [
            'user_id' => $user->id,
            'patient_id' => $patient->id,
            'file_count' => $files->total(),
            'page' => $files->currentPage(),
            'per_page' => $files->perPage()
        ]);

        return response()->json([
            'files' => $files->items(),
            'pagination' => [
                'current_page' => $files->currentPage(),
                'last_page' => $files->lastPage(),
                'per_page' => $files->perPage(),
                'total' => $files->total()
            ]
        ], 200);
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

    public function apiDownloadExternal(Patient $patient, PatientFile $file)
    {

        if ($file->patient_id !== $patient->id) {
            Log::error('API: File not found for patient', [
                'user_id' => $patient->user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'File not found.'], 404);
        }

        try {
            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $patient->user->id,
                'action' => 'download',
            ]);

            $encryptedContent = Storage::disk('patient_files')->get($file->file_path);
            $decryptedContent = Crypt::decrypt($encryptedContent);

            Log::info('API: File downloaded successfully', [
                'user_id' => $patient->user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response($decryptedContent)
                ->header('Content-Type', $file->file_type)
                ->header('Content-Disposition', 'attachment; filename="' . $file->file_name . '"');
        } catch (\Exception $e) {
            Log::error('API: File download failed', [
                'user_id' => $patient->user->id,
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

        if ($toUser->id === $user->id) {
            Log::warning('API: Attempt to assign own access', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Cannot assign your own access.'], 400);
        }

        if ($toUser->id === $patient->user->id) {
            Log::warning('API: Attempt to assign patient user access on own file', [
                'user_id' => $user->id,
                'to_user_id' => $toUser->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Cannot assign patient user access on own file.'], 400);
        }

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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }

        \Log::info('found user', [
            'user_id' => $user->email
        ]);

        $request->validate([
            'file' => 'required|file|mimes:pdf,doc,docx,xls,xlsx,ppt,pptx,txt,csv,xml,hl7,dcm,nii,ecg,jpg,jpeg,png,gif,webp,svg,bmp,tiff,mp3,wav,aac,ogg,mp4,mkv,avi,mov,wmv,flv,zip,rar,7z,tar,gz,bz2|max:102400',
            'description' => 'nullable|string|max:255',
        ]);

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
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

    public function apiGetAssignedUsers(Patient $patient, PatientFile $file, Request $request)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }
        Log::info('API: Attempting to access patient file show', [
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
            Log::warning('API: Unauthorized attempt to access patient file show', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to access file.'], 403);
        }

        try {
            $assignedUsers = PatientFileUser::where('patient_file_id', $file->id)
                ->where(function ($q) {
                    $q->whereNull('expiration_date')
                        ->orWhere('expiration_date', '>', now());
                })
                ->where('user_id', '!=', $userId) 
                ->with('user')
                ->get();

            return response()->json(['assigned_users' => $assignedUsers], 200);
        } catch (\Exception $e) {
            Log::error('API: Error getting assigned users', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Error getting assigned users.'], 500);
        }
    }

    public function apiGenerateFileQrCode(Request $request, Patient $patient, PatientFile $file)
    {
        $userId = $request->header('X-User-ID');
        if (!$userId || !is_numeric($userId)) {
            Log::warning('API: Invalid or missing user_id in header', [
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Invalid or missing user_id in header.'], 400);
        }

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }

        Log::info('API: Attempting to generate QR code for file', [
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
            Log::warning('API: Unauthorized attempt to generate QR code', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id
            ]);
            return response()->json(['error' => 'Unauthorized to generate QR code.'], 403);
        }

        try {
            $downloadUrl = URL::temporarySignedRoute(
                'api.patient_files.api_download',
                now()->addHours(24),
                ['patient' => $patient->id, 'file' => $file->id]
            );

            $qrCode = QrCode::create($downloadUrl)->setSize(300);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = base64_encode($result->getString());

            PatientFileLog::create([
                'patient_file_id' => $file->id,
                'user_id' => $user->id,
                'action' => 'generate_qr_code',
            ]);

            Log::info('API: QR code generated successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'file_name' => $file->file_name
            ]);

            return response()->json([
                'message' => 'QR code generated successfully.',
                'qr_code' => 'data:image/png;base64,' . $qrCodeBase64,
                'download_url' => $downloadUrl
            ], 200);
        } catch (\Exception $e) {
            Log::error('API: QR code generation failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'file_id' => $file->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'QR code generation failed.'], 500);
        }
    }

    public function apiGeneratePublicUploadLink(Request $request, Patient $patient)
    {

        Log::info('Attempting to generate public upload link');
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

        try {
            $user = User::findOrFail($userId);
        } catch (ModelNotFoundException $e) {
            Log::error('API: User not found', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'User not found.'], 404);
        } catch (\Exception $e) {
            Log::error('API: Error retrieving user', [
                'user_id' => $userId,
                'patient_id' => $patient->id
            ]);
            return response()->json(['error' => 'Error retrieving user.'], 500);
        }

        \Log::info('found user', [
            'user_id' => $user->email
        ]);

        Log::info('Attempting to generate public upload link', [
            'user_id' => $user->id,
            'patient_id' => $patient->id
        ]);

        if (!$this->hasFileAccess($patient, $user)) {
            Log::warning('Unauthorized attempt to generate public upload link', [
                'user_id' => $user->id,
                'patient_id' => $patient->id
            ]);
            abort(403, 'Unauthorized to generate public upload link.');
        }

        try {
            // Generate a signed URL valid for 24 hours
            $uploadUrl = URL::temporarySignedRoute(
                'patient_files.public_upload',
                now()->addHours(24),
                ['patient' => $patient->id, 'user' => $user->id]
            );

            // Generate QR code for the upload URL
            $qrCode = QrCode::create($uploadUrl)->setSize(300);
            $writer = new PngWriter();
            $result = $writer->write($qrCode);
            $qrCodeBase64 = base64_encode($result->getString());

            Log::info('Public upload link and QR code generated successfully', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'upload_url' => $uploadUrl
            ]);

            return response()->json([
                'message' => 'Public upload link generated successfully.',
                'upload_url' => $uploadUrl,
                'qr_code' => 'data:image/png;base64,' . $qrCodeBase64
            ], 200);
        } catch (\Exception $e) {
            Log::error('Public upload link generation failed', [
                'user_id' => $user->id,
                'patient_id' => $patient->id,
                'error' => $e->getMessage()
            ]);
            return response()->json(['error' => 'Failed to generate public upload link.'], 500);
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