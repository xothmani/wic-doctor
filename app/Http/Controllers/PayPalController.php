<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Crypt;
use App\Mail\PaymentSuccessMail;
use Illuminate\Support\Facades\Mail;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class PayPalController extends Controller
{
    public function index()
    {
        return view('paypal.form');
    }

    public function payment(Request $request)
    {
        Log::info('Payment Request Data: ', $request->all());
        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'user_id' => 'required|integer|exists:users,id', // Ensure user_id exists in users table
            'payment_method_id' => 'required|integer',
        ]);

        $amount = $validatedData['amount'];
        $description = $validatedData['description'];
        //dd(env('PAYPAL_CURRENCY'), config('paypal'), config('app.env'));

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        // Ensure API credentials are correctly set
        $paypalConfig = config('paypal');
        if (empty($paypalConfig['live']['client_id']) || empty($paypalConfig['live']['client_secret'])) {
            return response()->json(['error' => 'PayPal API credentials are missing.'], 500);
        }

        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('paypal.payment.success'),
                "cancel_url" => route('paypal.payment.cancel'),
            ],
            "purchase_units" => [
                [
                    "description" => $description,
                    "amount" => [
                        "currency_code" => config('paypal.currency'),
                        "value" => $amount,
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    session([
                        'payment_details' => $validatedData,
                    ]);
                    return response()->json(['approval_url' => $link['href']]);
                }
            }
        }

        return redirect()->route('paypal')->with('error', $response['message'] ?? 'Something went wrong.');
    }
    public function paymentCancel()
    {
        Log::info("paymentCanceled");
        return redirect()->route('paypal')->with('error', 'You have canceled the transaction.');
    }

    public function paymentSuccess(Request $request)
    {
        //\Log::info("Request Data for succcccess:", $request->all());
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        //\Log::info("PayPal Response: ", $response);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            // Check if the response contains the invoice_id
            $purchaseUnit = $response['purchase_units'][0] ?? null;
            //\Log::info("PayPal Response: ", $purchaseUnit);
            if ($purchaseUnit && isset($purchaseUnit['reference_id'])) {
                $customPaymentToken = $purchaseUnit['reference_id']; // Retrieve the token
            } else {
                \Log::error("Reference ID missing in PayPal response: " . json_encode($response));
                return response()->json(['error' => 'Reference ID not found in PayPal response.'], 400);
            }
            // Retrieve the payment session using the token
            $paymentSession = DB::table('payment_sessions')
                ->where('token', $customPaymentToken)
                ->first();

            if (!$paymentSession) {
                \Log::error("Payment session not found for token: " . $customPaymentToken);
                return response()->json(['error' => 'Payment session not found.'], 404);
            }

            // Update appointment status
            DB::table('appointments')
                ->where('id', $paymentSession->appointment_id)
                ->update([
                    'appointment_status_id' => 9,
                ]);

            // Insert payment record
            $paymentId = DB::table('payments')->insertGetId([
            'amount' => $paymentSession->tele_price_tnd,
            'description' => $paymentSession->description,
            'user_id' => $paymentSession->user_id,
            'payment_method_id' => 13, // Konnect
            'payment_status_id' => 2, // Completed
            'appointment_id' => $paymentSession->appointment_id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
	DB::table('rooms')
            ->where('appointment_id', $paymentSession->appointment_id)
            ->update([
                'payments_id' => $paymentId,
                'updated_at' => now(),
            ]);

	$patient_id = $paymentSession->user_id;

            $user = User::find($paymentSession->user_id);
            $phoneNumber = $user->phone;

            // Fetch patient details using the Patient model
            $patient = Patient::where('user_id', $patient_id)->first();
            if (!$patient) {
                \Log::error("Patient not found for ID: " . $patient_id);
                return response()->json(['error' => 'Patient not found.'], 404);
            }
            $patientName = $patient->first_name . ' ' . $patient->last_name;
            $patientEmail = $paymentSession->email;
	    $currency = 'EUR';
	    $formattedDate = Carbon::parse($paymentSession->start_at)->format('d/m/Y H:i'); // Format date and time
            $paymentDetails = [
                'patient_name' => $patientName,
		'currency' => $currency,
                'amount' => $paymentSession->tele_price_eur,
                'description' => $paymentSession->description,
                'start_at' => $formattedDate,
            ];

            Mail::to($patientEmail)->send(new PaymentSuccessMail($paymentDetails));
	   // $this->sendBySMS($phoneNumber, $patientName);
              return view('paypal.payment_success');

        }

            return view('paypal.error');

    }
	protected function sendBySMS($phoneNumber, $patientName)
{
    \Log::info("Attempting to send SMS to $phoneNumber...");

    // Message content for payment confirmation
    $message = "Bonjour $patientName,\n";
    $message .= "Votre paiement pour votre téléconsultation avec Wic-Doctor a été confirmé.\n";
    $message .= "Veuillez consulter votre email pour plus de détails.\n";
    $message .= "Merci de votre confiance !";

    $api_key = 'INS757364498';
    $from = '33743134488'; // Replace with your authorized sender ID or number
    $to = $phoneNumber;
    $alphasender = 'Wic-Doctor';

    // Debug log to check the complete message before sending
    \Log::info("Message content: $message");

    // Send SMS using the `sendsms` method
    $smsResult = $this->sendsms($api_key, $from, $to, $message, $alphasender);

    if ($smsResult) {
        \Log::info("SMS sent successfully to $phoneNumber with message: $message");
    } else {
        \Log::error("Failed to send SMS to $phoneNumber.");
    }
}

private function sendsms($api_key, $from, $to, $message, $alphasender = 'Wic-Doctor')
{
    $url = 'https://wicsms.com/apis/smscontact/';

    // Remove "+" at the beginning if present
    if (strpos($to, '+') === 0) {
        $to = substr($to, 1); // Remove the first "+" character
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

    \Log::info("HTTP Code: $httpCode");
    \Log::info("API Response: $result");

    // Parse the API response
    $response = json_decode($result, true);

    if (isset($response['status']) && $response['status'] === "0") {
        \Log::info("SMS successfully sent to $to: $message");
        return true;
    } else {
        \Log::error("Failed to send SMS. API Response: " . $result);
        return false;
    }
}

}
