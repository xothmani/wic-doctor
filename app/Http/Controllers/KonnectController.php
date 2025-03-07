<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\PaymentSuccessMail;
use App\Models\Patient;
use App\Models\User;
use Carbon\Carbon;

class KonnectController extends Controller
{
    public function initPayment(Request $request)
    {
        Log::info('Konnect Payment Request Data: ', $request->all());

        $validatedData = $request->validate([
            'amount' => 'required|numeric|min:1',
            'description' => 'required|string|max:255',
            'appointment_id' => 'required|integer|exists:appointments,id',
            'user_id' => 'required|integer|exists:users,id',
            'start_at' => 'required|date',
            'email' => 'required|email',
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'phone' => 'required|string|max:20',
        ]);

        $paymentToken = uniqid('konnect_', true);

        // Save the payment session in the database
        DB::table('payment_sessions')->insert([
            'token' => $paymentToken,
            'appointment_id' => $validatedData['appointment_id'],
            'email' => $validatedData['email'],
            'start_at' => $validatedData['start_at'],
            'doctor_id' => auth()->id(),
            'amount' => $validatedData['amount'],
            'description' => $validatedData['description'],
            'user_id' => $validatedData['user_id'],
            'payment_method_id' => 2, // Assuming 2 represents Konnect
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $apiEndpoint = "https://wic-doctor.com:3004/init-payment";

        // Prepare payload for Konnect API
        $payload = [
            "receiverWalletId" => "67596157bf9f84ccec7e554f",
            "token" => "TND",
            "amount" => (int) $validatedData['amount'],
            "type" => "immediate",
            "description" => $validatedData['description'],
            "lifespan" => 10,
            "checkoutForm" => true,
            "addPaymentFeesToAmount" => true,
            "firstName" => $validatedData['first_name'],
            "lastName" => $validatedData['last_name'],
            "phoneNumber" => $validatedData['phone'],
            "email" => $validatedData['email'],
            "orderId" => $paymentToken,
            "webhook" => route('konnect.payment.webhook'), // Add webhook route
            "successUrl" => route('konnect.payment.success'), // Add success route
            "failUrl" => route('konnect.payment.fail'), // Add failure route
            "theme" => "light",
        ];

        try {
            $response = \Http::post($apiEndpoint, $payload);

            if ($response->successful()) {
                $responseData = $response->json();
                if (isset($responseData['payUrl'])) {
                    return response()->json(['payment_url' => $responseData['payUrl']]);
                } else {
                    Log::error("Konnect API Error: Missing payment URL");
                    return response()->json(['error' => 'Unable to generate payment link.'], 400);
                }
            } else {
                Log::error("Konnect API Error: " . $response->body());
                return response()->json(['error' => 'Payment initiation failed.'], 400);
            }
        } catch (\Exception $e) {
            Log::error("Konnect API Exception: " . $e->getMessage());
            return response()->json(['error' => 'Payment initiation failed.'], 500);
        }
    }

    public function paymentSuccess(Request $request)
    {
        Log::info('Konnect Payment Success Request Data: ', $request->all());

        $paymentToken = $request->input('token');

        // Retrieve the payment session
        $paymentSession = DB::table('payment_sessions')->where('token', $paymentToken)->first();

        if (!$paymentSession) {
            Log::error("Konnect Payment Session Not Found: " . $paymentToken);
            return redirect()->route('meet.index')->with('error', 'Payment session not found.');
        }

        // Update the appointment and payment status
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

        // Send success email
        $patient = Patient::where('user_id', $paymentSession->user_id)->first();
        if ($patient) {
            $formattedDate = Carbon::parse($paymentSession->start_at)->format('d/m/Y H:i'); // Format date and time
            $currency = 'TND';
            $paymentDetails = [
                'patient_name' => $patient->first_name . ' ' . $patient->last_name,
                'amount' => $paymentSession->tele_price_tnd,
                'currency' => $currency,
                'description' => $paymentSession->description,
                'start_at' => $formattedDate,
            ];

            Mail::to($paymentSession->email)->send(new PaymentSuccessMail($paymentDetails));
        }

        return redirect()->away('https://gateway.sandbox.konnect.network/payment-success');
    }

    public function paymentFail(Request $request)
    {
        Log::info('Konnect Payment Failure Request Data: ', $request->all());

        return redirect()->away('https://gateway.sandbox.konnect.network/payment-failure');
    }
}
