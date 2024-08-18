<?php
// create_account.php

// Enable error reporting for debugging (remove in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

// Include database configuration
require_once 'config.php';

// Check if it's a POST request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Invalid request method']);
    exit;
}

// Retrieve and sanitize input data
$user_id = $_SESSION['user_id'];
$account_type = filter_input(INPUT_POST, 'accountType', FILTER_SANITIZE_STRING);
$account_mode = filter_input(INPUT_POST, 'accountMode', FILTER_SANITIZE_STRING);
$starting_balance = filter_input(INPUT_POST, 'startingBalance', FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
$currency = filter_input(INPUT_POST, 'currency', FILTER_SANITIZE_STRING);
$account_name = filter_input(INPUT_POST, 'accountNickname', FILTER_SANITIZE_STRING);
$account_password = filter_input(INPUT_POST, 'accountPassword', FILTER_SANITIZE_STRING);

// Validate input
if (!$account_type || !$account_mode || !$starting_balance || !$currency || !$account_name || !$account_password) {
    echo json_encode(['success' => false, 'message' => 'All fields are required']);
    exit;
}

// Generate account number (you may want to implement a more sophisticated method)
$account_number = date('Ymd') . str_pad($user_id, 4, '0', STR_PAD_LEFT);

// Hash the account password
$hashed_password = password_hash($account_password, PASSWORD_DEFAULT);

// Prepare SQL statement
$sql = "INSERT INTO user_accounts (user_id, account_name, account_number, balance, opening_date, status, account_type, account_mode, currency, account_password) 
        VALUES (?, ?, ?, ?, NOW(), 'active', ?, ?, ?, ?)";

try {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issdssss", $user_id, $account_name, $account_number, $starting_balance, $account_type, $account_mode, $currency, $hashed_password);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true, 'message' => 'Account created successfully', 'account_number' => $account_number]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Failed to create account']);
    }
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
}

$stmt->close();
$conn->close();