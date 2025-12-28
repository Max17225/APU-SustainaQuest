<?php
session_start();
require_once '../includes/db_connect.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

if ($item_id <= 0 || $quantity <= 0) {
    header("Location: shop.php?status=error&msg=Invalid Request");
    exit();
}

$conn->begin_transaction();

try {
    // A. Fetch User Data
    $stmtUser = $conn->prepare("SELECT greenPoints FROM users WHERE userId = ? FOR UPDATE");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();

    // B. Fetch Item Data
    $stmtItem = $conn->prepare("SELECT itemName, pointCost, quantity FROM items WHERE itemId = ? FOR UPDATE");
    $stmtItem->bind_param("i", $item_id);
    $stmtItem->execute();
    $itemData = $stmtItem->get_result()->fetch_assoc();
    $stmtItem->close();

    // C. Validations
    if (!$userData || !$itemData) { throw new Exception("Data not found."); }
    if ($itemData['quantity'] < $quantity) { throw new Exception("Not enough stock available."); }
    
    $total_cost = $itemData['pointCost'] * $quantity;
    if ($userData['greenPoints'] < $total_cost) {
        throw new Exception("You need " . number_format($total_cost) . " points.");
    }

    // D. Process Trade
    // 1. Deduct Points
    $newPoints = $userData['greenPoints'] - $total_cost;
    $updateUser = $conn->prepare("UPDATE users SET greenPoints = ? WHERE userId = ?");
    $updateUser->bind_param("ii", $newPoints, $user_id);
    $updateUser->execute();
    $updateUser->close();

    // 2. Decrease Stock
    $newStock = $itemData['quantity'] - $quantity;
    $updateItem = $conn->prepare("UPDATE items SET quantity = ? WHERE itemId = ?");
    $updateItem->bind_param("ii", $newStock, $item_id);
    $updateItem->execute();
    $updateItem->close();

    // 3. Insert History 
    $insertHistory = $conn->prepare("INSERT INTO redemptions (userId, itemId, redempDate, redempQuantity, redempStatus) VALUES (?, ?, NOW(), ?, 1)");
    $insertHistory->bind_param("iii", $user_id, $item_id, $quantity);
    $insertHistory->execute();
    $insertHistory->close();

    $conn->commit();

    $itemName = urlencode($itemData['itemName']);
    header("Location: shop.php?status=success&item=$itemName&qty=$quantity");
    exit();

} catch (Exception $e) {
    $conn->rollback();
    $errorMsg = urlencode($e->getMessage());
    header("Location: shop.php?status=error&msg=$errorMsg");
    exit();
}
?>