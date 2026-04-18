<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 405);

$name    = trim($_POST['name'] ?? '');
$email   = trim($_POST['email'] ?? '');
$phone   = trim($_POST['phone'] ?? '');
$address = trim($_POST['address'] ?? '');
$city    = trim($_POST['city'] ?? '');
$postal  = trim($_POST['postal_code'] ?? '');
$items   = json_decode($_POST['cart_items'] ?? '[]', true);

if (!$name)    jsonResponse(false, MSG_CHECKOUT_NAME_EMPTY, [], 422);
if (!$email)   jsonResponse(false, MSG_CHECKOUT_EMAIL_EMPTY, [], 422);
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) jsonResponse(false, MSG_CHECKOUT_EMAIL_INVALID, [], 422);
if (!$phone)   jsonResponse(false, MSG_CHECKOUT_PHONE_EMPTY, [], 422);
if (!preg_match('/^(?:\+216|00216)?[234579]\d{7}$/', preg_replace('/[\s\-.]+/', '', $phone))) jsonResponse(false, MSG_CHECKOUT_PHONE_INVALID, [], 422);
if (!$address) jsonResponse(false, MSG_CHECKOUT_ADDRESS_EMPTY, [], 422);
if (!$city)    jsonResponse(false, MSG_CHECKOUT_CITY_EMPTY, [], 422);
if (!$postal || !preg_match('/^[1-9]\d{3}$/', preg_replace('/[\s\-.]+/', '', $postal))) jsonResponse(false, MSG_CHECKOUT_POSTAL_INVALID, [], 422);
if (empty($items)) jsonResponse(false, MSG_CHECKOUT_CART_EMPTY, [], 422);

try {
    $db       = getDB();
    $subtotal = array_sum(array_map(fn($i) => (float)$i['price'] * (int)$i['quantity'], $items));
    $shipping = $subtotal >= 50 ? 0 : 4.90;
    $total    = $subtotal + $shipping;

    $stmt = $db->prepare("INSERT INTO orders (user_id,customer_name,email,phone,address,city,postal_code,items,subtotal,total,payment_method) VALUES (?,?,?,?,?,?,?,?,?,?,'livraison')");
    $stmt->execute([
        isLoggedIn() ? $_SESSION['user_id'] : null,
        htmlspecialchars($name), $email, htmlspecialchars($phone),
        htmlspecialchars($address), htmlspecialchars($city), $postal,
        json_encode($items), $subtotal, $total
    ]);
    jsonResponse(true, MSG_ORDER_SUCCESS, ['order_id' => $db->lastInsertId()]);
} catch (Exception $e) {
    jsonResponse(false, MSG_ORDER_SERVER_ERROR, [], 500);
}
