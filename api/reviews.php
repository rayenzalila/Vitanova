<?php
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';
require_once '../includes/messages.php';
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 405);
if (!isLoggedIn()) jsonResponse(false, MSG_REVIEW_NOT_LOGGED_IN, [], 401);

$productId = (int)($_POST['product_id'] ?? 0);
$rating    = (int)($_POST['rating'] ?? 0);
$comment   = trim($_POST['comment'] ?? '');
$userId    = $_SESSION['user_id'];

if (!$productId) jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 400);
if (!$rating || $rating < 1 || $rating > 5) jsonResponse(false, MSG_REVIEW_RATING_EMPTY, [], 422);
if (!$comment) jsonResponse(false, MSG_REVIEW_COMMENT_EMPTY, [], 422);
if (mb_strlen($comment) < 10) jsonResponse(false, MSG_REVIEW_COMMENT_SHORT, [], 422);

try {
    $db = getDB();
    $chk = $db->prepare("SELECT id FROM reviews WHERE product_id = ? AND user_id = ?");
    $chk->execute([$productId, $userId]);
    if ($chk->fetch()) jsonResponse(false, MSG_REVIEW_ALREADY_SUBMITTED, [], 409);

    $chkP = $db->prepare("SELECT id FROM products WHERE id = ?");
    $chkP->execute([$productId]);
    if (!$chkP->fetch()) jsonResponse(false, MSG_GENERIC_SERVER_ERROR, [], 404);

    $stmt = $db->prepare("INSERT INTO reviews (product_id, user_id, rating, comment) VALUES (?,?,?,?)");
    $stmt->execute([$productId, $userId, $rating, htmlspecialchars($comment)]);
    jsonResponse(true, MSG_REVIEW_SUCCESS);
} catch (Exception $e) {
    jsonResponse(false, MSG_REVIEW_SERVER_ERROR, [], 500);
}
