<?php
session_start();
include "db_conn.php";

// 1. Kunin ang amount na pinasa ng JavaScript
$final_amount = $_POST['amount'] ?? 0;

if ($final_amount < 100) {
    die("Error: Ang minimum payment sa PayMongo ay ₱100.00. Ang total mo ay ₱$final_amount lang.");
}

$amount_in_cents = (int)round($final_amount * 100);

// 2. API SETUP (TEST KEY LANG DAPAT)
$secret_key = 'sk_test_fVcyhj1SkJdEui3WVKLyF67n'; 

$payload = [
    'data' => [
        'attributes' => [
            'send_email_receipt' => true,
            'show_description' => true,
            'description' => 'Food Order - Ate Kabayan',
            'line_items' => [
                [
                    'currency' => 'PHP',
                    'amount' => $amount_in_cents,
                    'description' => 'Order Total',
                    'name' => 'Meals',
                    'quantity' => 1
                ]
            ],
            'payment_method_types' => ['gcash', 'paymaya', 'card'],
            'success_url' => 'http://localhost/updated/success.php', 
            'cancel_url' => 'http://localhost/updated/Checkout.php'
        ]
    ]
];

$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, 'https://api.paymongo.com/v1/checkout_sessions');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // Fix para sa Localhost
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Authorization: Basic ' . base64_encode($secret_key . ':')
]);

$response = curl_exec($ch);
$result = json_decode($response, true);
curl_close($ch);

if (isset($result['data']['attributes']['checkout_url'])) {
    header("Location: " . $result['data']['attributes']['checkout_url']);
    exit();
} else {
    echo "<h1>PayMongo API Error:</h1>";
    echo "<pre>";
    print_r($result); // Dito mo makikita kung bakit rejected ang request
    echo "</pre>";
}
?>