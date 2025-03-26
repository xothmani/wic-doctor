<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use Illuminate\Support\Facades\DB; 
use Illuminate\Support\Facades\Log;

class PayPalAPIController extends Controller
{
    public function index()
    {
	 Log::info('PayPalController@index was accessed.');	
        return view('paypal.payment');
    }
public function paymentStatic()
{
    $productName = "Static Product"; // Static product name
    $totalAmount = 100.00; // Static amount in your preferred currency

    $provider = new PayPalClient;
    $provider->setApiCredentials(config('paypal'));

    // Ensure API credentials are correctly set
    $paypalConfig = config('paypal');
    if (empty($paypalConfig['sandbox']['client_id']) || empty($paypalConfig['sandbox']['client_secret'])) {
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
                "description" => $productName,
                "amount" => [
                    "currency_code" => config('paypal.currency'),
                    "value" => $totalAmount,
                ]
            ]
        ]
    ]);

    if (isset($response['id']) && $response['id'] != null) {
        foreach ($response['links'] as $link) {
            if ($link['rel'] === 'approve') {
                return redirect()->away($link['href']);
            }
        }
    }

    return response()->json([
        'error' => $response['message'] ?? 'Something went wrong.',
    ], 500);
}

    public function payment(Request $request)
    
	{
       // $productName = $request->product_name;
       // $price = $request->price;
       // $quantity = $request->quantity;
       // $totalAmount = $price * $quantity;
       $totalAmount = 100.00; 
        //dd(env('PAYPAL_CURRENCY'), config('paypal'), config('app.env'));

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));

        // Ensure API credentials are correctly set
        $paypalConfig = config('paypal');
        if (empty($paypalConfig['sandbox']['client_id']) || empty($paypalConfig['sandbox']['client_secret'])) {
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
                   
                    "amount" => [
                        "currency_code" => config('paypal.currency'),
                        "value" => $totalAmount,
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            foreach ($response['links'] as $link) {
                if ($link['rel'] === 'approve') {
                    return redirect()->away($link['href']);
                }
            }
        }

        return redirect()->route('paypal')->with('error', $response['message'] ?? 'Something went wrong.');
    }public function paymentCancel()
    {
        Log::info("Availability Matched");
        return redirect()->route('paypal')->with('error', 'You have canceled the transaction.');
    }

    public function paymentSuccess(Request $request)
    {
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);
        //dd($response);

        if (isset($response['status']) && $response['status'] === 'COMPLETED') {
            // Insert payment details into the database
            $purchaseUnit = $response['purchase_units'][0];
            $capture = $purchaseUnit['payments']['captures'][0];
            $amount = $capture['amount']['value']; // Retrieve the amount
            $currency = $capture['amount']['currency_code']; // Retrieve the currency
            $transactionId = $capture['id']; // Retrieve the transaction ID
            $description = $purchaseUnit['reference_id'] ?? 'Payment via PayPal'; // Optional description
        
            // Assuming $userId, $paymentMethodId, and $paymentStatusId are already defined
            $userId = auth()->id(); // Replace with actual user ID logic
            $paymentMethodId = 5; // PayPal method ID
            $paymentStatusId = 2; // Paid status ID
        
            // Insert into the database
            DB::table('payments')->insert([
                'amount' => $amount,
                'description' => $description,
                'user_id' => $userId,
                'payment_method_id' => $paymentMethodId,
                'payment_status_id' => $paymentStatusId,    
                'created_at' => now(),
                'updated_at' => now(),
            
        ]);
            return redirect()->route('paypal')->with('success', 'Transaction complete.');
        }

        return redirect()->route('paypal')->with('error', $response['message'] ?? 'Something went wrong.');
    }
}

