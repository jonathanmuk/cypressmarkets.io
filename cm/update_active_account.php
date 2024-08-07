<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || !isset($_POST['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Invalid request']);
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = $_POST['account_id'];

// Verify that the account belongs to the user
$sql = "SELECT * FROM user_accounts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $account_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    echo json_encode(['success' => false, 'message' => 'Account not found']);
    exit;
}

// Update the active account
$_SESSION['active_account_id'] = $account_id;

// Fetch the updated account details
$sql = "SELECT * FROM user_accounts WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $account_id);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

$stmt->close();
$conn->close();

echo json_encode(['success' => true, 'account' => $account]);