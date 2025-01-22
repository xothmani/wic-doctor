<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Product Payment</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <script>
        async function sendPaymentRequest(event) {
            event.preventDefault(); // Prevent the default form submission

            // Gather form data
            
            
            const description = document.getElementById('description').value;
            const amount = parseFloat(document.getElementById('amount').value);
            const userId = parseInt(document.getElementById('user_id').value);
            const paymentMethodId = parseInt(document.getElementById('payment_method_id').value);

            // Construct the JSON payload
            const payload = {
		description: description,
                amount: amount,
                user_id: userId,
                payment_method_id: paymentMethodId
};
            try {
                // Send the JSON request using fetch
                const response = await fetch("{{ route('paypal.payment') }}", {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}', // Include CSRF token for Laravel
                    },
			mode: 'cors', // Enable CORS
    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                // Handle the response
                const result = await response.json();
                if (response.ok) {
                    // Redirect to the PayPal approval link
                    if (result.approval_url) {
                        window.location.href = result.approval_url;
                    } else {
                        alert('Payment successful!');
                    }
                } else {
                    alert('Error: ' + result.error);
                }
            } catch (error) {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            }
        }
    </script>
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card shadow">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>Product Payment</h4>
                    </div>
                    <div class="card-body">
                        <form onsubmit="sendPaymentRequest(event)">
                            <div class="mb-3">
                                <label for="description" class="form-label">Description</label>
                                <input type="text" class="form-control" id="description" name="description" placeholder="Enter description" required>
                            </div>
                            <div class="mb-3">
                                <label for="amount" class="form-label">Amount</label>
                                <input type="number" step="0.01" class="form-control" id="amount" name="amount" placeholder="Enter amount" required>
                            </div>
                            <div class="mb-3">
                                <label for="user_id" class="form-label">User ID</label>
                                <input type="number" class="form-control" id="user_id" name="user_id" placeholder="Enter user ID" required>
                            </div>
                            <div class="mb-3">
                                <label for="payment_method_id" class="form-label">Payment Method ID</label>
                                <input type="number" class="form-control" id="payment_method_id" name="payment_method_id" placeholder="Enter payment method ID" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Proceed to Pay</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
