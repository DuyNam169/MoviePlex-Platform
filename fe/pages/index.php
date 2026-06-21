<?php
session_start();
if (empty($_SESSION['user_id'])) {
    header('Location: /fe/pages/login.php');
    exit;
}

$role = $_SESSION['user_role'] ?? 'user';

if ($role === 'admin' || $role === 'admin_monitor') {
    header('Location: /fe/admin/index.php');
    exit;
} elseif ($role === 'staff') {
    header('Location: /fe/pages/staff.php');
    exit;
} else {
    header('Location: /fe/pages/home.php');
    exit;
}
