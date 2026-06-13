<?php
require_once 'includes/db.php';
$db = getDB();

$password = 'admin123';
$hash = password_hash($password, PASSWORD_DEFAULT);

try {
    // 1. Check if an admin exists
    $stmt = $db->query("SELECT * FROM users WHERE role = 'admin'");
    $admins = $stmt->fetchAll();

    if (count($admins) === 0) {
        // Create an admin if none exists
        $stmt = $db->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
        $stmt->execute(['Administrateur', 'admin@vitanova.com', $hash, 'admin']);
        echo "<h1>Admin Account Created!</h1>";
        echo "<p>No admin was found in the database, so I created one for you.</p>";
        echo "<p><strong>Email:</strong> admin@vitanova.com</p>";
        echo "<p><strong>Password:</strong> admin123</p>";
    } else {
        // Reset password for all existing admins
        $stmt = $db->prepare("UPDATE users SET password = ? WHERE role = 'admin'");
        $stmt->execute([$hash]);
        echo "<h1>Admin Password Reset!</h1>";
        echo "<p>The password for the following admin accounts has been reset to: <strong>admin123</strong></p>";
        echo "<ul>";
        foreach ($admins as $admin) {
            echo "<li>" . htmlspecialchars($admin['email']) . "</li>";
        }
        echo "</ul>";
    }

    echo "<p style='color:red'><strong>Please delete this file (reset_pwd.php) after use!</strong></p>";

} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}
