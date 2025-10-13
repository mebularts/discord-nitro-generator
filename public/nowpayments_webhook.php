<?php
require __DIR__ . '/../bot/bootstrap.php';

use App\Models\PaymentRepository;
use App\Models\UserRepository;
use App\Support\Config;

$rawBody = file_get_contents('php://input');
$data = json_decode($rawBody, true);

if (!is_array($data)) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid payload']);
    exit;
}

$ipnSecret = Config::get('payments.nowpayments.ipn_secret');
$signature = $_SERVER['HTTP_X_NOWPAYMENTS_SIG'] ?? '';
if ($ipnSecret) {
    if ($signature === '') {
        http_response_code(401);
        echo json_encode(['error' => 'Missing signature']);
        exit;
    }

    $calculated = hash_hmac('sha512', $rawBody, $ipnSecret);
    if (!hash_equals($signature, $calculated)) {
        http_response_code(401);
        echo json_encode(['error' => 'Invalid signature']);
        exit;
    }
}

$orderId = $data['order_id'] ?? null;
if (!$orderId) {
    http_response_code(422);
    echo json_encode(['error' => 'Missing order_id']);
    exit;
}

$payments = new PaymentRepository();
$users = new UserRepository();
$payment = $payments->findByOrderId((string) $orderId);
if (!$payment) {
    http_response_code(404);
    echo json_encode(['error' => 'Payment not found']);
    exit;
}

$status = strtolower((string) ($data['payment_status'] ?? ''));
$payload = $payment['payload'] ? json_decode($payment['payload'], true) : [];
$payload['nowpayments'] = $data;

if (in_array($status, ['finished', 'confirmed'])) {
    $payments->update((int) $payment['id'], [
        'status' => 'completed',
        'amount' => (float) ($data['pay_amount'] ?? $payment['amount']),
        'payload' => json_encode($payload),
    ]);
    $users->incrementBalance((int) $payment['user_id'], (float) ($data['pay_amount'] ?? $payment['amount']));
} elseif (in_array($status, ['failed', 'expired', 'cancelled'])) {
    $payments->update((int) $payment['id'], [
        'status' => 'failed',
        'payload' => json_encode($payload),
    ]);
} else {
    $payments->update((int) $payment['id'], [
        'payload' => json_encode($payload),
    ]);
}

echo json_encode(['status' => 'ok']);
