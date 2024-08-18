<?php
header('Content-Type: application/json');
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['success' => false, 'message' => 'User not logged in']);
    exit;
}

if (!isset($_GET['account_id'])) {
    echo json_encode(['success' => false, 'message' => 'Account ID not provided']);
    exit;
}

$user_id = $_SESSION['user_id'];
$account_id = $_GET['account_id'];

$sql = "SELECT * FROM user_accounts WHERE id = ? AND user_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $account_id, $user_id);
$stmt->execute();
$result = $stmt->get_result();
$account = $result->fetch_assoc();

if ($account) {
    echo json_encode(['success' => true, 'account' => $account]);
} else {
    echo json_encode(['success' => false, 'message' => 'Account not found']);
}

$stmt->close();
$conn->close();
?>