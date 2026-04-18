<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 405);
}

$input  = json_decode(file_get_contents('php://input'), true) ?? [];
$action = $input['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'sync':
        $items = $input['items'] ?? [];
        $_SESSION['cart'] = is_array($items) ? $items : [];
        jsonResponse(true, 'Panier synchronisé.');

    case 'add':
        $id  = (int)($input['product_id'] ?? 0);
        $qty = max(1, (int)($input['quantity'] ?? 1));
        if (!$id) jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 400);
        try {
            $db   = getDB();
            $stmt = $db->prepare("SELECT id,name,price,stock,category,slug FROM products WHERE id = ?");
            $stmt->execute([$id]);
            $p = $stmt->fetch();
            if (!$p) jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 404);
            if ($p['stock'] < 1) jsonResponse(false, MSG_CART_OUT_OF_STOCK);
            if ($qty > $p['stock']) jsonResponse(false, MSG_CART_QUANTITY_EXCEEDED);
            $cart = $_SESSION['cart'] ?? [];
            $idx  = array_search($id, array_column($cart, 'id'));
            if ($idx !== false) {
                $newQty = $cart[$idx]['quantity'] + $qty;
                if ($newQty > $p['stock']) jsonResponse(false, MSG_CART_QUANTITY_EXCEEDED);
                $cart[$idx]['quantity'] = $newQty;
            } else {
                $cart[] = ['id'=>$p['id'],'name'=>$p['name'],'price'=>(float)$p['price'],
                           'quantity'=>$qty,'category'=>$p['category'],'slug'=>$p['slug'],'image'=>''];
            }
            $_SESSION['cart'] = $cart;
            jsonResponse(true, MSG_CART_ITEM_ADDED, ['cart_count' => array_sum(array_column($cart, 'quantity'))]);
        } catch (Exception $e) { jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 500); }

    case 'remove':
        $id   = (int)($input['product_id'] ?? 0);
        $cart = array_filter($_SESSION['cart'] ?? [], fn($i) => $i['id'] !== $id);
        $_SESSION['cart'] = array_values($cart);
        jsonResponse(true, MSG_CART_ITEM_REMOVED, ['cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))]);

    case 'update':
        $id  = (int)($input['product_id'] ?? 0);
        $qty = (int)($input['quantity'] ?? 0);
        $cart = $_SESSION['cart'] ?? [];
        foreach ($cart as &$item) {
            if ($item['id'] === $id) {
                if ($qty <= 0) { $cart = array_filter($cart, fn($i) => $i['id'] !== $id); break; }
                $item['quantity'] = $qty; break;
            }
        }
        $_SESSION['cart'] = array_values($cart);
        jsonResponse(true, MSG_CART_UPDATED, ['cart_count' => array_sum(array_column($_SESSION['cart'], 'quantity'))]);

    default:
        jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 400);
}
