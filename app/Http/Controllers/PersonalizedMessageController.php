<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Doctor;
use App\Models\Patient;

use App\Models\PersonalizedMessage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;

class PersonalizedMessageController extends Controller
{

    public function history($patientId)
    {
        $doctor = Doctor::where('user_id', auth()->id())->firstOrFail();
    
        $messages = PersonalizedMessage::where('doctor_id', $doctor->id)
            ->where('patient_id', $patientId)
            ->orderBy('created_at', 'desc')
            ->get();
    
        // Ne retourne PAS une page entière mais une vue partielle
        return view('patients.history', compact('messages'));
    }
        public function store(Request $request)
    {
        // Validation du message envoyé
        $validatedData = $request->validate([
            'message' => 'required|string|max:1600', // Assurez-vous de valider un message de taille raisonnable
            'patient_id' => 'required|integer|exists:patients,id', // Vérifier si le patient existe dans la base
        ]);

        // Récupérer le docteur connecté
        $doctor = Doctor::where('user_id', auth()->user()->id)->first();

        if (!$doctor) {
            return redirect()->back()->with('error', 'Aucun docteur trouvé pour cet utilisateur.');
        }

        // Enregistrement du message personnalisé dans la base de données
        $personalizedMessage = new PersonalizedMessage();
        $personalizedMessage->doctor_id = $doctor->id; // Utilise l'id du docteur
        $personalizedMessage->patient_id = $request->input('patient_id');
        $personalizedMessage->message = $request->input('message');
        $personalizedMessage->save(); // `created_at` est automatiquement défini ici

        // Récupérer le message exactement tel qu'il a été saisi
        $message = $personalizedMessage->message;

        // Récupérer les informations du patient
        $patient = $personalizedMessage->patient;

        // Appeler la fonction pour envoyer un SMS
        $this->sendSmsToPatient($patient, $message, $doctor);

        // Retourner une réponse ou rediriger
        return redirect()->route('patients.index')->with('success', 'Message personnalisé envoyé avec succès.');
    }

    private function sendSmsToPatient(Patient $patient, string $message, string $shortUrl): void
{
    // Récupérer les informations du médecin authentifié
    $doctorId = auth()->user()->getDoctorId();
    $doctor = Doctor::find($doctorId);

    // Vérifier si le médecin existe
    if (!$doctor) {
        Log::error("Médecin non trouvé pour l'envoi du SMS");
        return;
    }

    // Récupérer les données nécessaires
    $numFrance = $doctor->num_france;
    $api = $doctor->api_key;
    $to = $patient->phone_number;
    $alphasender = 'Wic doctor';



    // Envoi du message en fonction du numéro de téléphone
    if (Str::startsWith($to, '+33')) {
        // Envoi via le service SMS France
        $smsSuccess = $this->sendsms($api, $numFrance, $to, $message, $alphasender);
    
        if ($smsSuccess) {
            Log::info("SMS envoyé avec succès à $to");
    
            // Déterminer le nombre de SMS nécessaires
            $messageLength = strlen($message);
            $smsCount = ceil($messageLength / 160); // 1 SMS par 160 caractères
    
            // Incrémentation du pack SMS perso
            $doctor->increment('pack_sms_perso', $smsCount);
        } else {
            Log::error("Échec de l'envoi du SMS à $to");
        }
    } elseif (Str::startsWith($to, '+216')) {
        // Envoi via l'API Tunisie
        $this->sendSmsToTunisia($to, $message);
    } else {
        Log::warning("Code pays non pris en charge pour le numéro : $to");
    }
    
}

private function sendSmsToTunisia($to, $message)
{
    $doctorId = auth()->user()->getDoctorId();
    $doctor = Doctor::find($doctorId);

    // Suppression du caractère "+" pour le service Tunisie
    $to = str_replace('+', '', $to);

    // Appel API Tunisie
    $response = Http::post('https://wic-doctor.com:3004/send-sms-vats', [
        'gsm' => $to,
        'message' => $message
    ]);

    if ($response->successful() && $response->json('success') === true) {
        Log::info("SMS Tunisie envoyé avec succès à $to");

        // Déterminer le nombre de SMS nécessaires
        $messageLength = strlen($message);
        $smsCount = ceil($messageLength / 160); // 1 SMS par 160 caractères

        // Incrémentation du pack SMS perso selon le nombre de SMS envoyés
        $doctor->increment('pack_sms_perso', $smsCount);
    } else {
        Log::error("Échec de l'envoi du SMS Tunisie à $to : " . $response->body());
    }
}


private function sendsmsToFrance($api_key, $from, $to, $message, $alphasender = 'WIC DOCTOR')
{
    $url = 'https://dashboard.wic-sms.com/apis/smscontact/';

    // Supprimer le "+" au début si présent
    if (strpos($to, '+') === 0) {
        $to = substr($to, 1); // Supprime le premier caractère '+'
    }

    $fields = [
        'apikey' => $api_key,
        'from' => $from,
        'to' => $to,
        'message' => $message,
        'alphasender' => $alphasender,
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
    curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);

    $result = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    Log::info("HTTP Code: $httpCode");
        Log::info("API Response: $result");
    
        $response = json_decode($result, true);
    
        if (isset($response['status']) && $response['status'] === "0") {
            Log::info("SMS envoyé avec succès à $to : $message from: $from avec api key: $api_key");
            return true; //  succès
        } else {
            Log::error("Échec de l'envoi du SMS. Réponse de l'API : " . $result);
            return false; //  échec
        }
    }


}
