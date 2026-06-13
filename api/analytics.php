<?php
/**
 * Vitanova — Analytics API
 * Handles data requests for the admin dashboard
 */
require_once '../includes/db.php';
require_once '../includes/auth.php';
require_once '../includes/functions.php';

// Protection admin
if (!isAdmin()) {
    header('HTTP/1.1 403 Forbidden');
    echo json_encode(['error' => 'Unauthorized']);
    exit;
}

$db = getDB();
$endpoint = $_GET['endpoint'] ?? '';
header('Content-Type: application/json');

try {
    $timeframe = $_GET['timeframe'] ?? 'daily';
    $format = match($timeframe) {
        'hourly'   => '%Y-%m-%d %H:00',
        'weekly'   => '%X-W%V',
        'monthly'  => '%Y-%m',
        'annually' => '%Y',
        default    => '%Y-%m-%d'
    };

    switch ($endpoint) {
        case 'users':
            $total = $db->query("SELECT COUNT(*) FROM users WHERE role='client'")->fetchColumn();
            $signups = $db->query("SELECT DATE_FORMAT(created_at, '$format') as date, COUNT(*) as count FROM users WHERE role='client' GROUP BY date ORDER BY date ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode(['total' => (int)$total, 'signups' => $signups]);
            break;
            
        case 'orders':
            $total = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
            $revenue = $db->query("SELECT SUM(total) FROM orders WHERE status != 'annulee'")->fetchColumn() ?? 0;
            $orders_time = $db->query("SELECT DATE_FORMAT(created_at, '$format') as date, COUNT(*) as count, SUM(total) as revenue FROM orders GROUP BY date ORDER BY date ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

            $status_breakdown = $db->query("SELECT status, COUNT(*) as count FROM orders GROUP BY status")->fetchAll(PDO::FETCH_ASSOC);
            $recent = $db->query("SELECT id, customer_name, total, status, created_at FROM orders ORDER BY created_at DESC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            echo json_encode([
                'total' => (int)$total, 
                'revenue' => (float)$revenue, 
                'orders_over_time' => $orders_time,
                'status_breakdown' => $status_breakdown,
                'recent' => $recent
            ]);
            break;
            
        case 'products':
            $stock = $db->query("SELECT name, stock FROM products ORDER BY stock ASC LIMIT 10")->fetchAll(PDO::FETCH_ASSOC);
            
            // Aggregate Top Sellers from JSON items
            $orderItems = $db->query("SELECT items FROM orders WHERE status != 'annulee'")->fetchAll(PDO::FETCH_COLUMN);
            $productSales = [];
            foreach ($orderItems as $json) {
                $items = json_decode($json, true);
                if (is_array($items)) {
                    foreach ($items as $item) {
                        $name = $item['name'] ?? 'Inconnu';
                        $qty = $item['quantity'] ?? 1;
                        $productSales[$name] = ($productSales[$name] ?? 0) + $qty;
                    }
                }
            }
            arsort($productSales);
            $topSellers = [];
            foreach (array_slice($productSales, 0, 5, true) as $name => $count) {
                $topSellers[] = ['name' => $name, 'count' => $count];
            }
            
            echo json_encode(['stock_levels' => $stock, 'top_sellers' => $topSellers]);
            break;
            
        case 'reviews':
            $avg = $db->query("SELECT AVG(rating) FROM reviews")->fetchColumn() ?? 0;
            $count = $db->query("SELECT COUNT(*) FROM reviews")->fetchColumn();
            $trend = $db->query("SELECT DATE_FORMAT(created_at, '$format') as date, AVG(rating) as avg_rating FROM reviews GROUP BY date ORDER BY date ASC LIMIT 50")->fetchAll(PDO::FETCH_ASSOC);

            echo json_encode(['avg_rating' => round((float)$avg, 1), 'total_reviews' => (int)$count, 'trend' => $trend]);
            break;
            
        case 'messages':
            $total = $db->query("SELECT COUNT(*) FROM messages")->fetchColumn();
            $unread = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0")->fetchColumn();
            echo json_encode(['total' => (int)$total, 'unread' => (int)$unread]);
            break;
            
        default:
            echo json_encode(['error' => 'Invalid endpoint']);
    }
} catch (Exception $e) {
    header('HTTP/1.1 500 Internal Server Error');
    echo json_encode(['error' => $e->getMessage()]);
}
