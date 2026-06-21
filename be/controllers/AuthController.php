<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../core/Response.php';
require_once __DIR__ . '/../core/Request.php';
require_once __DIR__ . '/../core/Logger.php';
require_once __DIR__ . '/../models/User.php';
require_once __DIR__ . '/../services/AuthService.php';

header('Content-Type: application/json; charset=utf-8');

$request = new Request();
$service = new AuthService($pdo);
$action  = $request->post('action') ?? $request->get('action') ?? '';

switch ($action) {

    // ── LOGIN ──────────────────────────────────────────────────────────────
    case 'login':
        $identifier = trim($_POST['identifier'] ?? '');
        $password   = $_POST['password'] ?? '';
        $result     = $service->login($identifier, $password);
        Response::json($result);
        break;

    // ── REGISTER ───────────────────────────────────────────────────────────
    case 'register':
        $result = $service->register($_POST);
        Response::json($result);
        break;

    // ── LOGOUT ─────────────────────────────────────────────────────────────
    case 'logout':
        $result = $service->logout();
        Response::json($result);
        break;

    // ── FORGOT PASSWORD: SEND OTP ──────────────────────────────────────────
    case 'send_otp':
        $email  = trim($_POST['email'] ?? '');
        $result = $service->sendPasswordResetOtp($email);
        Response::json($result);
        break;

    // ── FORGOT PASSWORD: VERIFY OTP ───────────────────────────────────────
    case 'verify_otp':
        $email  = trim($_POST['email'] ?? $_SESSION['pwd_email'] ?? '');
        $otp    = trim($_POST['otp']   ?? '');
        $result = $service->verifyPasswordResetOtp($email, $otp);

        if ($result['success']) {
            $_SESSION['pwd_email'] = $email;
            $_SESSION['pwd_otp']   = $otp;
            $_SESSION['pwd_step']  = 3;
        }

        Response::json($result);
        break;

    // ── FORGOT PASSWORD: RESET PASSWORD ───────────────────────────────────
    case 'reset_pwd':
        $email           = trim($_SESSION['pwd_email'] ?? '');
        $otp             = trim($_SESSION['pwd_otp']   ?? '');
        $newPassword     = $_POST['new_pwd']     ?? '';
        $confirmPassword = $_POST['confirm_pwd'] ?? '';

        $result = $service->resetPassword($email, $otp, $newPassword, $confirmPassword);

        if ($result['success']) {
            unset($_SESSION['pwd_step'], $_SESSION['pwd_email'], $_SESSION['pwd_otp']);
        }

        Response::json($result);
        break;

    // ── INVALID ACTION ─────────────────────────────────────────────────────
    default:
        Response::error('Hành động không hợp lệ.', 400);
        break;
}