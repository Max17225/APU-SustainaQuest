<?php
// ============================================================
// SHOP REDEMPTION PROCESSOR (SERVER-SIDE)
// ============================================================
// This file handles:
// - User authentication check
// - Validating item selection and quantity
// - Database Transaction handling 
// - Row locking (FOR UPDATE) to prevent race conditions
// - Verifying User Points and Item Stock levels
// - Processing the trade (Deduct Points, Reduce Stock, Log History)
// ============================================================

session_start();
require_once '../includes/db_connect.php';

// ============================================================
// 1. AUTHENTICATION CHECK
// ------------------------------------------------------------
// Ensure the user is logged in before allowing any transaction
// ============================================================
if (!isset($_SESSION['user_id'])) {
    header("Location: ../auth_page/login.php");
    exit();
}

$user_id = $_SESSION['user_id'];
$item_id = isset($_POST['item_id']) ? intval($_POST['item_id']) : 0;
$quantity = isset($_POST['quantity']) ? intval($_POST['quantity']) : 1;

// ============================================================
// 2. INPUT VALIDATION
// ------------------------------------------------------------
// Check for valid item ID and positive quantity
// ============================================================
if ($item_id <= 0 || $quantity <= 0) {
    header("Location: shop.php?status=error&msg=Invalid Request");
    exit();
}

// ============================================================
// 3. START DATABASE TRANSACTION
// ------------------------------------------------------------
// We use a transaction to ensure that points deduction, stock
// reduction, and history logging happen atomically.
// If any step fails, we ROLLBACK to prevent data corruption.
// ============================================================
$conn->begin_transaction();

try {
    // ========================================================
    // A. FETCH USER DATA (WITH LOCK)
    // --------------------------------------------------------
    // 'FOR UPDATE' locks the user row to prevent points from
    // being spent simultaneously by another request.
    // ========================================================
    $stmtUser = $conn->prepare("SELECT greenPoints FROM users WHERE userId = ? FOR UPDATE");
    $stmtUser->bind_param("i", $user_id);
    $stmtUser->execute();
    $userData = $stmtUser->get_result()->fetch_assoc();
    $stmtUser->close();

    // ========================================================
    // B. FETCH ITEM DATA (WITH LOCK)
    // --------------------------------------------------------
    // Locks the item row to prevent overselling stock if multiple
    // users redeem the last item at the exact same moment.
    // ========================================================
    $stmtItem = $conn->prepare("SELECT itemName, pointCost, quantity FROM items WHERE itemId = ? FOR UPDATE");
    $stmtItem->bind_param("i", $item_id);
    $stmtItem->execute();
    $itemData = $stmtItem->get_result()->fetch_assoc();
    $stmtItem->close();

    // ========================================================
    // C. LOGICAL VALIDATIONS
    // --------------------------------------------------------
    // Check existance, stock availability, and user affordability
    // ========================================================
    if (!$userData || !$itemData) { throw new Exception("Data not found."); }
    if ($itemData['quantity'] < $quantity) { throw new Exception("Not enough stock available."); }
    
    $total_cost = $itemData['pointCost'] * $quantity;
    if ($userData['greenPoints'] < $total_cost) {
        throw new Exception("You need " . number_format($total_cost) . " points.");
    }

    // ========================================================
    // D. PROCESS TRADE EXECUTION
    // ========================================================
    
    // --------------------------------------------------------
    // 1. Deduct Points from User
    // --------------------------------------------------------
    $newPoints = $userData['greenPoints'] - $total_cost;
    $updateUser = $conn->prepare("UPDATE users SET greenPoints = ? WHERE userId = ?");
    $updateUser->bind_param("ii", $newPoints, $user_id);
    $updateUser->execute();
    $updateUser->close();

    // --------------------------------------------------------
    // 2. Decrease Stock from Inventory
    // --------------------------------------------------------
    $newStock = $itemData['quantity'] - $quantity;
    $updateItem = $conn->prepare("UPDATE items SET quantity = ? WHERE itemId = ?");
    $updateItem->bind_param("ii", $newStock, $item_id);
    $updateItem->execute();
    $updateItem->close();

    // --------------------------------------------------------
    // 3. Insert Redemption History Log
    // --------------------------------------------------------
    $insertHistory = $conn->prepare("INSERT INTO redemptions (userId, itemId, redempDate, redempQuantity, redempStatus) VALUES (?, ?, NOW(), ?, 1)");
    $insertHistory->bind_param("iii", $user_id, $item_id, $quantity);
    $insertHistory->execute();
    $insertHistory->close();

    // ========================================================
    // 4. COMMIT TRANSACTION
    // --------------------------------------------------------
    // All steps succeeded. Save changes permanently.
    // ========================================================
    $conn->commit();

    $itemName = urlencode($itemData['itemName']);
    header("Location: shop.php?status=success&item=$itemName&qty=$quantity");
    exit();

} catch (Exception $e) {
    // ========================================================
    // ERROR HANDLING & ROLLBACK
    // --------------------------------------------------------
    // If any exception occurs, undo all changes in this transaction
    // ========================================================
    $conn->rollback();
    $errorMsg = urlencode($e->getMessage());
    header("Location: shop.php?status=error&msg=$errorMsg");
    exit();
}
?>