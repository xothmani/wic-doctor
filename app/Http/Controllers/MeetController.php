<?php

namespace App\Http\Controllers;

use App\Models\Room;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\PurchasedNumber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Illuminate\Support\Facades\Http;
use App\Mail\SendMail;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;


class MeetController extends Controller
{
    public function index()
    {
        $user_id = auth()->id();

        // Paginate rooms with patient data
        $rooms = Room::where('owner_id', $user_id)->paginate(10);

        // Fetch unique patient IDs from rooms
        $patient_user_ids = $rooms->pluck('patient_id')->unique();

        // Fetch patient data for these IDs
        $patients = Patient::whereIn('user_id', $patient_user_ids)->get()->keyBy('user_id'); // Key by user_id for easier access

        // Fetch purchased phone numbers
        $purchased_numbers = PurchasedNumber::where('user_id', $user_id)->pluck('phone_number');

        return view('meet.index', compact('rooms', 'patients', 'purchased_numbers'));
    }

    public function DirectIndex(Request $request)
    {
        \Log::info('Request Data:', $request->all());
        $patientName = $request->query('patient_name');
        $patient_id = $request->query('patient_id');
        $appointment_id = $request->query('appointment_id');
        $phone = $request->query('phone');
        $start = $request->query('start_at');
        $start_at = Carbon::parse($start)->format('Y-m-d H:i:s');
        $user_id = auth()->id();
        $rooms = Room::where('owner_id', $user_id)->get();
        $purchased_numbers = PurchasedNumber::where('user_id', $user_id)->pluck('phone_number');
        // Pass data to the view
        return view('meet.create', [
            'appointment_id' => $appointment_id,
            'patientName' => $patientName,
            'phone' => $phone,
            'patient_id' => $patient_id,
            'start_at' => $start_at,
        ], compact('rooms', 'purchased_numbers'));
    }

    
    public function createMeet(Request $request)
    {
        try {
            \Log::info("Request Data for createMeet:", $request->all());

            // Validate the request
            $validated = $request->validate([
                'appointment_id' => 'required',
                'patient_id' => 'required',
                'start_at' => 'required',
                'patient_name' => 'required',
                'phone' => 'required',
                'patient_first_name' => 'required',
                'patient_last_name' => 'required',
                'patient_Email' => 'required',
            ]);


        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::error("Validation failed: ", $e->errors());
            return back()->withErrors($e->errors())->withInput();
        }

        // Prepare data
        $appointment_id = $request->input("appointment_id");
        $patient_id = $validated['patient_id'];
        $patient_name = $validated['patient_name'];
        $patient_first_name = $validated['patient_first_name'];
        $patient_last_name = $validated['patient_last_name'];
        $patient_phone = $validated['phone'];
        $patient_Email = $validated['patient_Email'];
        $start = $validated['start_at'];
        //$start_at = Carbon::parse($start)->format('Y-m-d H:i:s');
        $start_at = Carbon::parse($start);
        $date = $start_at->format('Y-m-d');
        $time2 = $start_at->format('H:i:s');
        $time = $start_at->format('H-i-s');

        $patient_first_name = str_replace(' ', '_', $patient_first_name);
	$patient_last_name = str_replace(' ', '_', $patient_last_name);
	$room_name = "{$patient_first_name}_{$patient_last_name}_{$patient_phone}_{$date}_{$time}";
	$meet_link = "https://meet.jit.si/{$room_name}";

        $user_id = auth()->id();
        $purchased_numbers = PurchasedNumber::where('user_id', $user_id)->pluck('phone_number');
        \Log::info("Doctor's ");
        $start_at->locale('en'); // Set the locale to English
        $dayName = $start_at->dayName; // This will now give the day name in English (e.g., "saturday")
        \Log::info("appointment_id: {$appointment_id}");
        \Log::info("Doctor's : {$time}");
        try {
            // Fetch patient details


            // Create room record
            $room = Room::create([
                'room_name' => $room_name,
                'meet_link' => $meet_link,
                'owner_id' => auth()->id(),
                'appointment_id' => $appointment_id,
                'patient_id' => $patient_id,
                'date' => $date,
                'time' => $time2,
                'status' => 'Pending',
            ]);
            //$appointmentId = $paymentDetails['appointment_id']; // Assuming appointment_id is passed in payment details
            DB::table('appointments')
                ->where('id', $appointment_id)
                ->update(['appointment_status_id' => 4]);

            $doctor = Doctor::where('user_id', $user_id)->first();
            if (!$doctor) {
                return back()->with('error', 'Doctor not found.');
                \Log::info("Doctor's : {$doctor}");
            }
            $tele_price_tnd = $doctor->tele_price_tnd;
            $tele_price_eur = $doctor->tele_price_eur;
            \Log::info("Doctor's tele_price");
            // Prepare data for the modal
            $data = [
                'appointment_id' => $appointment_id,
                'meet_link' => $meet_link,
                'patient_name' => $patient_name,
                'patient_id' => $patient_id,
                'phone' => $patient_phone,
                'start_at' => $start_at,
                'patient_last_name' => $patient_last_name,
                'patient_first_name' => $patient_first_name,
                'patient_Email' => $patient_Email,
                'tele_price_tnd' => $tele_price_tnd,
                'tele_price_eur' => $tele_price_eur,
            ];

            // Return success with data for modal
            return view('meet.send_link', compact('data', 'purchased_numbers'))->with('success', 'Room created successfully!');
        } catch (\Exception $e) {
            \Log::error("Error creating room: " . $e->getMessage());

            return back()->with('error', 'Failed to create the room. Please try again.');
        }
    }




    /*public function sendMeetingInfo(Request $request)
    {
        \Log::info("Request Data for sendMeetingInfo:", $request->all());

        $apiEndpoint = "https://wic-doctor.com:3004/init-payment";
        $smsServiceEnabled = true; // Use this to toggle SMS sending for testing

        // Prepare request payload for API
        $payload = [
            "receiverWalletId" => "67596157bf9f84ccec7e554f",
            "token" => "TND",
            "amount" => (int) $request->input('prix'),
            "type" => "immediate",
            "description" => "description",
            "lifespan" => 10,
            "checkoutForm" => true,
            "addPaymentFeesToAmount" => true,
            "firstName" => $request->input('patient_first_name'),
            "lastName" => $request->input('patient_last_name'),
            "phoneNumber" => $request->input('phone'),
            "email" => $request->input('email'),
            "patient_id" => $request->input('patient_id'),
            "orderId" => "ad553df2fdfd22",
            "webhook" => $apiEndpoint,
            "silentWebhook" => true,
            "successUrl" => "https://gateway.sandbox.konnect.network/payment-success",
            "failUrl" => "https://gateway.sandbox.konnect.network/payment-failure",
            "theme" => "light",
        ];
        try {
            // Make POST request to API
            $response = Http::post($apiEndpoint, $payload);

            // Check for successful response
            if ($response->successful()) {
                $responseData = $response->json();

                // Log the full response
                \Log::info("API Response: " . json_encode($responseData));

                // Log the payment link or response
                if (isset($responseData['payUrl'])) {
                    $apiPaymentLink = $responseData['payUrl'];
                    \Log::info("Payment Link: " . $apiPaymentLink);
                } else {
                    \Log::warning("Payment link missing in API response: " . json_encode($responseData));
                }
            } else {
                // Log error details if the request failed
                \Log::error("API Call Failed. Status: {$response->status()}, Body: " . $response->body());
            }
        } catch (\Exception $e) {
            // Log exception details
            \Log::error("Error occurred while calling the API: " . $e->getMessage());
        }
        try {

            // Generate PayPal payment link
            $paypalPayload = [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route(' '),
                    "cancel_url" => route('paypal.payment.cancel'),
                ],
                "purchase_units" => [
                    [
                        "description" => "Payment for Room",
                        "amount" => [
                            "currency_code" => "EUR",
                            "value" => $request->input('prix'), // Use the price from the request
                        ]
                    ]
                ]
            ];

            $paypalProvider = new \Srmklive\PayPal\Services\PayPal;
            $paypalProvider->setApiCredentials(config('paypal'));
            $paypalToken = $paypalProvider->getAccessToken();

            $paypalResponse = $paypalProvider->createOrder($paypalPayload);

            if (isset($paypalResponse['links'])) {
                foreach ($paypalResponse['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $paypalLink = $link['href'];
                        // Encrypt and store payment details in the session
                        session([
                            'payment_details' => Crypt::encrypt([
                                'amount' => $request->input('prix'),
                                'description' => "Payment Room for Appointment_id :" . $request->input('appointment_id'),
                                'user_id' => $request->input('patient_id'),
                                'appointment_id' => $request->input('appointment_id'),
                                'payment_method_id' => 2, // Example payment method ID
                            ])
                        ]);
                    }
                }
            }

            Log::info("PayPal Link: {$paypalLink}");
            $doctor = Doctor::where('user_id', auth()->id())->first();
            $doctorName = $doctor ? $doctor->name : 'Non spécifié';
            // Get date and time
            $date = Carbon::parse($request->input('start_at'))->format('Y-m-d');
            $time = Carbon::parse($request->input('start_at'))->format('H:i');
            // Send SMS with links
            $emailSuccess = false;
            if ($smsServiceEnabled && $apiPaymentLink && $paypalLink) {
                $roomName = $request->input('meet_link');

                $emailSuccess = $this->sendByEmail(
                    $request->input('email'),
                    $apiPaymentLink,
                    $paypalLink,
                    $request->input('patient_first_name'),
                    $date,
                    $time,
                    $doctorName
                );
                //$this->sendBySMS($request->input('phone'), $request->input('patient_first_name'));
            }

        } catch (\Exception $e) {
            Log::error("Error occurred: " . $e->getMessage());
            return redirect()->route('meet.index')->with('error', 'Échec de l\'envoi de l\'email. Veuillez réessayer.');
        }
        if ($emailSuccess) {
            return redirect()->route('meet.index')->with('success', 'Informations sur la réunion et liens de paiement envoyés avec succès par email.');
        } else {
            return redirect()->route('meet.index')->with('error', 'Échec de l\'envoi de l\'email. Veuillez réessayer.');
        }

    }*/
    /*public function sendMeetingInfo(Request $request)
    {
        \Log::info("Request Data for sendMeetingInfo:", $request->all());

        $paymentToken = Str::uuid();
        $user_id = auth()->id();
        //$packId = 2; // Specify the pack ID here

        // Save payment details in the database
        DB::table('payment_sessions')->insert([
            'token' => $paymentToken,
            'appointment_id' => $request->input('appointment_id'),
            'email' => $request->input('email'),
            'start_at' => $request->input('start_at'),
            'doctor_id' => $user_id,
            'tele_price_tnd' => $request->input('tele_price_tnd'),
            'tele_price_eur' => $request->input('tele_price_eur'),
            'description' => $request->input('meet_link'),
            'user_id' => $request->input('patient_id'),
            'payment_method_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $apiEndpoint = "https://wic-doctor.com:3004/init-payment";
        $smsServiceEnabled = true; // Use this to toggle SMS sending for testing

        \Log::info('Generated Payment Token: ' . $paymentToken);

        // Prepare request payload for API
        $successUrl = route('konnect.payment.success', [
            'token' => (string) $paymentToken, // Explicitly cast to string
            'patient_id' => $request->input('patient_id'),
        ]);

        \Log::info('Success URL: ' . $successUrl);
        $failUrl = route('konnect.payment.fail', [
            'appointment_id' => $request->input('appointment_id'),
            'patient_id' => $request->input('patient_id'),
        ]);

        $payload = [
            "receiverWalletId" => "67596157bf9f84ccec7e554f",
            "token" => "TND",
            "amount" => (int) $request->input('tele_price_tnd'),
            "type" => "immediate",
            "description" => "description",
            "lifespan" => 10,
            "checkoutForm" => true,
            "addPaymentFeesToAmount" => true,
            "firstName" => $request->input('patient_first_name'),
            "lastName" => $request->input('patient_last_name'),
            "phoneNumber" => $request->input('phone'),
            "email" => $request->input('email'),
            "patient_id" => $request->input('patient_id'),
            "orderId" => "order_" . $request->input('appointment_id'),
            "webhook" => $apiEndpoint,
            "silentWebhook" => true,
            "successUrl" => $successUrl, // Dynamic success URL
            "failUrl" => $failUrl,       // Dynamic failure URL
            "theme" => "light",
        ];
        Log::info('Konnect Payload: ', $payload);

        try {
            // Make POST request to API
            $response = Http::post($apiEndpoint, $payload);

            // Check for successful response
            if ($response->successful()) {
                $responseData = $response->json();

                // Log the full response
                \Log::info("API Response: " . json_encode($responseData));

                // Log the payment link or response
                if (isset($responseData['payUrl'])) {
                    $apiPaymentLink = $responseData['payUrl'];
                    \Log::info("Payment Link: " . $apiPaymentLink);
                } else {
                    \Log::warning("Payment link missing in API response: " . json_encode($responseData));
                }
            } else {
                // Log error details if the request failed
                \Log::error("API Call Failed. Status: {$response->status()}, Body: " . $response->body());
            }
        } catch (\Exception $e) {
            // Log exception details
            \Log::error("Error occurred while calling the API: " . $e->getMessage());
        }
        try {

            // Generate PayPal payment link
            $paypalPayload = [
                "intent" => "CAPTURE",
                "application_context" => [
                    "return_url" => route('paypal.payment.success'),
                    "cancel_url" => route('paypal.payment.cancel'),
                ],
                "purchase_units" => [
                    [
                        "reference_id" => $paymentToken, // Use the token here
                        "description" => "Payment for Appointment",
                        "amount" => [
                            "currency_code" => "EUR",
                            "value" => $request->input('tele_price_eur'),
                        ],
                    ]
                ]
            ];


            $paypalProvider = new \Srmklive\PayPal\Services\PayPal;
            $paypalProvider->setApiCredentials(config('paypal'));
            $paypalToken = $paypalProvider->getAccessToken();

            $paypalResponse = $paypalProvider->createOrder($paypalPayload);

            if (isset($paypalResponse['links'])) {
                foreach ($paypalResponse['links'] as $link) {
                    if ($link['rel'] === 'approve') {
                        $paypalLink = $link['href'];
                        // Encrypt and store payment details in the session

                    }
                }
            }

            Log::info("PayPal Link: {$paypalLink}");
            $doctor = Doctor::where('user_id', auth()->id())->first();
            $doctorName = $doctor ? $doctor->name : 'Non spécifié';
            // Get date and time
            $date = Carbon::parse($request->input('start_at'))->format('Y-m-d');
            $time = Carbon::parse($request->input('start_at'))->format('H:i');
            // Send SMS with links
            $emailSuccess = false;
            if ($smsServiceEnabled && $apiPaymentLink && $paypalLink) {
                $roomName = $request->input('meet_link');

                $emailSuccess = $this->sendByEmail(
                    $request->input('email'),
                    $apiPaymentLink,
                    $paypalLink,
                    $request->input('patient_first_name'),
                    $date,
                    $time,
                    $doctorName
                );
                $this->sendBySMS($request->input('phone'), $request->input('patient_first_name'));
            }

        } catch (\Exception $e) {
            Log::error("Error occurred: " . $e->getMessage());
            return redirect()->route('meet.index')->with('error', 'Échec de l\'envoi de l\'email. Veuillez réessayer.');
        }
        if ($emailSuccess) {
            return redirect()->route('meet.index')->with('success', 'Informations sur la réunion et liens de paiement envoyés avec succès par email.');
        } else {
            return redirect()->route('meet.index')->with('error', 'Échec de l\'envoi de l\'email. Veuillez réessayer.');
        }

    }*/
    public function sendMeetingInfo(Request $request)
    {
        \Log::info("Request Data for sendMeetingInfo:", $request->all());

        $paymentToken = Str::uuid();
        $user_id = auth()->id();

        // Save payment details in the database
        DB::table('payment_sessions')->insert([
            'token' => $paymentToken,
            'appointment_id' => $request->input('appointment_id'),
            'email' => $request->input('email'),
            'start_at' => $request->input('start_at'),
            'doctor_id' => $user_id,
            'tele_price_tnd' => $request->input('tele_price_tnd'),
            'tele_price_eur' => $request->input('tele_price_eur'),
            'description' => $request->input('meet_link'),
            'user_id' => $request->input('patient_id'),
            'payment_method_id' => 2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $apiEndpoint = "https://wic-doctor.com:3004/init-payment";
        $smsServiceEnabled = true; // Use this to toggle SMS sending for testing

        \Log::info('Generated Payment Token: ' . $paymentToken);

        // Prepare Konnect payment link only if tele_price_tnd is valid
        $apiPaymentLink = null;
        if (!empty($request->input('tele_price_tnd')) && $request->input('tele_price_tnd') > 0) {
            $successUrl = route('konnect.payment.success', [
                'token' => (string) $paymentToken, // Explicitly cast to string
                'patient_id' => $request->input('patient_id'),
            ]);

            \Log::info('Success URL: ' . $successUrl);
            $failUrl = route('konnect.payment.fail', [
                'appointment_id' => $request->input('appointment_id'),
                'patient_id' => $request->input('patient_id'),
            ]);

            $payload = [
                "receiverWalletId" => "67596157bf9f84ccec7e554f",
                "token" => "TND",
                "amount" => (int) $request->input('tele_price_tnd') * 1000,
                "type" => "immediate",
                "description" => "description",
                "lifespan" => 10,
                "checkoutForm" => true,
                "addPaymentFeesToAmount" => true,
                "firstName" => $request->input('patient_first_name'),
                "lastName" => $request->input('patient_last_name'),
                "phoneNumber" => $request->input('phone'),
                "email" => $request->input('email'),
                "patient_id" => $request->input('patient_id'),
                "orderId" => "order_" . $request->input('appointment_id'),
                "webhook" => $apiEndpoint,
                "silentWebhook" => true,
                "successUrl" => $successUrl, // Dynamic success URL
                "failUrl" => $failUrl,       // Dynamic failure URL
                "theme" => "light",
            ];
            Log::info('Konnect Payload: ', $payload);

            try {
                $response = Http::post($apiEndpoint, $payload);

                if ($response->successful()) {
                    $responseData = $response->json();
                    \Log::info("API Response: " . json_encode($responseData));
                    if (isset($responseData['payUrl'])) {
                        $apiPaymentLink = $responseData['payUrl'];
                        \Log::info("Payment Link: " . $apiPaymentLink);
                    } else {
                        \Log::warning("Payment link missing in API response: " . json_encode($responseData));
                    }
                } else {
                    \Log::error("API Call Failed. Status: {$response->status()}, Body: " . $response->body());
                }
            } catch (\Exception $e) {
                \Log::error("Error occurred while calling the API: " . $e->getMessage());
            }
        }

        // Prepare PayPal payment link only if tele_price_eur is valid
        $paypalLink = null;
        if (!empty($request->input('tele_price_eur')) && $request->input('tele_price_eur') > 0) {
            try {
                $paypalPayload = [
                    "intent" => "CAPTURE",
                    "application_context" => [
                        "return_url" => route('paypal.payment.success'),
                        "cancel_url" => route('paypal.payment.cancel'),
                    ],
                    "purchase_units" => [
                        [
                            "reference_id" => $paymentToken, // Use the token here
                            "description" => "Payment for Appointment",
                            "amount" => [
                                "currency_code" => "EUR",
                                "value" => $request->input('tele_price_eur'),
                            ],
                        ]
                    ]
                ];

                $paypalProvider = new \Srmklive\PayPal\Services\PayPal;
                $paypalProvider->setApiCredentials(config('paypal'));
                $paypalToken = $paypalProvider->getAccessToken();

                $paypalResponse = $paypalProvider->createOrder($paypalPayload);

                if (isset($paypalResponse['links'])) {
                    foreach ($paypalResponse['links'] as $link) {
                        if ($link['rel'] === 'approve') {
                            $paypalLink = $link['href'];
                        }
                    }
                }

                Log::info("PayPal Link: {$paypalLink}");
            } catch (\Exception $e) {
                Log::error("Error occurred while generating PayPal link: " . $e->getMessage());
            }
        }

        $emailSuccess = false;
        if ($apiPaymentLink || $paypalLink) {
            try {
                $doctor = Doctor::where('user_id', auth()->id())->first();
                $doctorName = $doctor ? $doctor->name : 'Non spécifié';
                $date = Carbon::parse($request->input('start_at'))->format('Y-m-d');
                $time = Carbon::parse($request->input('start_at'))->format('H:i');

                $emailSuccess = $this->sendByEmail(
                    $request->input('email'),
                    $apiPaymentLink,
                    $paypalLink,
                    $request->input('patient_first_name'),
                    $date,
                    $time,
                    $doctorName,
                    $request->input('tele_price_tnd'),
                    $request->input('tele_price_eur')
                );
                // $this->sendBySMS($request->input('phone'), $request->input('patient_first_name'));
            } catch (\Exception $e) {
                Log::error("Error occurred: " . $e->getMessage());
            }
        }

        if ($emailSuccess) {
            return redirect()->route('meet.index')->with('success', 'Informations sur la réunion et liens de paiement envoyés avec succès par email.');
        } else {
            return redirect()->route('meet.index')->with('error', 'Échec de l\'envoi de l\'email. Veuillez réessayer.');
        }
    }



    /*protected function sendByEmail($email, $apiPaymentLink, $paypalLink, $patientName)
    {
        Log::info("Starting to send email to: {$email}");
        Log::info("Starting to send email to: {$apiPaymentLink}");
        Log::info("Starting to send email to: {$paypalLink}");
        Log::info("Starting to send email to: {$patientName}");
        // Prepare email details
        $details = [
            'subject' => 'Payment Information',
            'patient_name' => $patientName,
            'apiPaymentLink' => $apiPaymentLink,
            'paypalLink' => $paypalLink,
        ];

        // Send email using Laravel's Mail facade
        Mail::to($email)->send(new SendMail($details));

        Log::info("Payment links sent to {$email} via email.");
    }*/
    public function sendByEmail($email, $apiPaymentLink, $paypalLink, $patientName, $date, $time, $doctorName, $telePriceTnd, $telePriceEur)
    {
        try {
            // Log the initiation of the email sending process
            Log::info("Starting to send email to: {$email}");

            // Prepare email details
            $details = [
                'subject' => 'Wic-Doctor Paiement - Confirmez votre Rendez-vous',
                'title' => 'Wic-Doctor Payment Téléconsultation',
                'patient_name' => $patientName,
                'date' => $date,
                'time' => $time,
                'doctor_name' => $doctorName,
                'apiPaymentLink' => $apiPaymentLink,
                'paypalLink' => $paypalLink,
                'tele_price_tnd' => $telePriceTnd > 0 ? $telePriceTnd : null,
                'tele_price_eur' => $telePriceEur > 0 ? $telePriceEur : null,
            ];



            // Log the email details before sending
            Log::info("Email details prepared:", $details);

            // Send the email
            Mail::to($email)->send(new SendMail($details));

            // Log success message
            Log::info("Email successfully sent to: {$email}");
            return true;
        } catch (\Exception $e) {
            // Log the error if email sending fails
            Log::error("Failed to send email to {$email}. Error: {$e->getMessage()}");
            return false;
        }
    }


    protected function sendBySMS($phoneNumber, $patientName)
    {

        \Log::info("SMS sent to $phoneNumber with message:");
        // Envoi du mot de passe par SMS
        $message = "Bonjour $patientName,\n";
        $message .= "Veuillez consulter votre email pour confirmer le paiement de votre téléconsultation avec Wic-Doctor.\n";
        $message .= "Merci de votre confiance.";

        $api_key = 'INS757364498';
        $from = '33743134488'; // Remplacez par votre nom d'expéditeur ou numéro autorisé
        $to = $phoneNumber;
        $alphasender = 'Wic doctor';


        // Debug log to check the complete message before sending
        \Log::info("Message content: $message");

        $smsResult = $this->sendsms($api_key, $from, $to, $message, $alphasender);


        // Enregistrez un flag pour afficher le modal 
    }
    private function sendsms($api_key, $from, $to, $message, $alphasender = 'wic doctor')
    {
        $url = 'https://wicsms.com/apis/smscontact/';

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

        // Analyse de la réponse
        $response = json_decode($result, true);
        if (isset($response['status']) && $response['status'] === "0") {
            Log::info("SMS envoyé avec succès teste  à $to : $message");

        } else {
            Log::error("Échec de l'envoi du SMS. Réponse de l'API : " . $result);


        }

        return $result;
    }
}
