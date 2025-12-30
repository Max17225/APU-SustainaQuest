<?php
// 1. SESSION HANDLING
session_start();

// 2. INCLUDES & SETTINGS
$path = "../";
$page_css = "shop.css"; 
require_once '../includes/db_connect.php';
require_once '../includes/header.php';
require_once 'user_functions.php'; 

// ================= FETCH DATA (USING FUNCTIONS FROM user_functions.php) =================
$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;
$current_tab = isset($_GET['tab']) ? $_GET['tab'] : 'permanent';

// 1. Get User Points 
$user_points = get_user_points($conn, $user_id);

// 2. Fetch Data based on Tab 
$items_list = [];
$result = null;

if ($current_tab == 'history' && $user_id) {
    $result = get_redemption_history($conn, $user_id);
} else {
    $items_list = get_shop_items($conn, $current_tab);
}
?>

<div id="shop-page">
    <div class="top-bar">
        <div class="tabs">
            <a href="?tab=permanent" class="tab-link <?php echo ($current_tab == 'permanent') ? 'active' : ''; ?>">Permanent</a>
            <a href="?tab=limited" class="tab-link <?php echo ($current_tab == 'limited') ? 'active' : ''; ?>">Limited-Time</a>
            <?php if ($user_id): ?>
                <a href="?tab=history" class="tab-link <?php echo ($current_tab == 'history') ? 'active' : ''; ?>">History</a>
            <?php endif; ?>
        </div>
        <div class="points">
            <?php if ($user_id): ?>
                <span>Green Points:</span> <strong><?php echo number_format($user_points); ?></strong>
            <?php else: ?>
                <a href="../auth_page/login.php" style="color:inherit; text-decoration:none;">Login to View Points</a>
            <?php endif; ?>
        </div>
    </div>

    <?php if ($current_tab != 'history'): ?>
        <div class="grid">
            <?php for ($i = 0; $i < 8; $i++): 
                $item = isset($items_list[$i]) ? $items_list[$i] : null;
            ?>
                <?php if ($item): 
                    $in_stock = $item['quantity'] > 0;
                    $can_afford = ($user_points >= $item['pointCost']);
                    

                    $btn_text = "Redeem";
                    $btn_class = "btn-redeem";
                    $btn_attr = ""; 
                    if (!$in_stock) {
                        $btn_text = "Sold Out";
                        $btn_class = "btn-disabled";
                        $btn_attr = "disabled";
                    } elseif (!$can_afford) {
                        $btn_text = "Not Enough";
                        $btn_class = "btn-locked"; 
                        $btn_attr = "disabled"; 
                    }

                    $card_class = "card";
                    if (!$in_stock) $card_class .= " sold-out";
                    $img_src = ($item['itemPictureURL']) ? $path . $item['itemPictureURL'] : "";
                ?>
                    <div class="<?php echo $card_class; ?>">
                        <?php if (!$in_stock): ?><div class="sold-badge">SOLD OUT</div><?php endif; ?>
                        
                        <div class="item-pic">
                            <?php if ($img_src): ?><img src="<?php echo $img_src; ?>" alt="item"><?php else: ?><span><?php echo htmlspecialchars($item['itemName']); ?></span><?php endif; ?>
                        </div>

                        <div class="card-content">
                            <h4><?php echo htmlspecialchars($item['itemName']); ?></h4>
                            
                            <div class="meta-row">
                                <span class="quantity-pill" style="font-size:0.75rem;">Stock: <?php echo $item['quantity']; ?></span>
                                
                                <?php if ($user_id && $in_stock): ?>
                                    <div class="qty-control">
                                        <button type="button" class="qty-btn" onclick="decreaseQty(<?php echo $item['itemId']; ?>)">-</button>
                                        <input type="number" id="qty_input_<?php echo $item['itemId']; ?>" class="qty-input-inline" value="1" min="1" max="<?php echo $item['quantity']; ?>" readonly>
                                        <button type="button" class="qty-btn" onclick="increaseQty(<?php echo $item['itemId']; ?>, <?php echo $item['quantity']; ?>)">+</button>
                                    </div>
                                <?php else: ?>
                                    <span></span> <?php endif; ?>

                                <span class="points-text"><?php echo $item['pointCost']; ?> PTS</span>
                            </div>

                            <div class="buttons">
                                <button type="button" class="btn-view" onclick="openModal('<?php echo addslashes($item['itemName']); ?>', '<?php echo addslashes($item['itemDesc']); ?>', '<?php echo $img_src; ?>')">Details</button>
                                
                                <?php if ($user_id): ?>
                                    <form action="redeem_process.php" method="POST" style="flex:1;" 
                                          onsubmit="return openConfirmModal(event, this, '<?php echo addslashes($item['itemName']); ?>', <?php echo $item['pointCost']; ?>, <?php echo $item['itemId']; ?>);">
                                        <input type="hidden" name="item_id" value="<?php echo $item['itemId']; ?>">
                                        <input type="hidden" name="quantity" id="form_qty_<?php echo $item['itemId']; ?>" value="1">
                                        
                                        <button type="submit" class="<?php echo $btn_class; ?>" <?php echo $btn_attr; ?>>
                                            <?php echo $btn_text; ?>
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <button class="btn-redeem" onclick="window.location.href='../auth_page/login.php'">Login</button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="card empty-slot">
                        <div class="item-pic empty-pic"><span>Empty</span></div>
                        <div class="card-content">
                            <h4 style="color:#ccc;">Coming Soon</h4>
                            <div class="buttons"><button disabled class="btn-disabled">Redeem</button></div>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endfor; ?>
        </div>

    <?php elseif ($current_tab == 'history'): ?>
        <?php if ($user_id): ?>
            <div style="background:white; padding:20px; border:1px solid #ddd; border-radius:12px;" class="history-wrapper">
                <table class="history-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Date</th>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Total Cost</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($result && $result->num_rows > 0): ?>
                            <?php while ($row = $result->fetch_assoc()): 
                                $qty = $row['redempQuantity'];
                                $total_history_cost = $row['pointCost'] * $qty;
                                $status_text = ($row['redempStatus'] == 1) ? "Redeemed" : "Pending";
                                $badge_class = ($row['redempStatus'] == 1) ? "badge-redeemed" : "badge-pending";
                            ?>
                                <tr>
                                    <td style="color:#888;">#<?php echo $row['redemptionId']; ?></td>
                                    <td><?php echo date("Y-m-d", strtotime($row['redempDate'])); ?></td>
                                    <td>
                                        <div style="display:flex; align-items:center; gap:10px;">
                                            <?php if($row['itemPictureURL']): ?>
                                                <img src="<?php echo $path.$row['itemPictureURL']; ?>" style="width:30px; height:30px; object-fit:contain; border:1px solid #eee; border-radius:4px;">
                                            <?php endif; ?>
                                            <span><?php echo htmlspecialchars($row['itemName']); ?></span>
                                        </div>
                                    </td>
                                    <td style="font-weight:bold;"><?php echo $qty; ?></td>
                                    <td style="color:#c0392b;">-<?php echo number_format($total_history_cost); ?></td>
                                    <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status_text; ?></span></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr><td colspan="6" style="padding:20px; text-align:center; color:#888;">No history found.</td></tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    <?php endif; ?>
</div>

<div id="confirmModal" class="modal">
    <div class="modal-content">
        <span onclick="closeConfirmModal()" class="close-btn">&times;</span>
        <div style="text-align: center; margin-bottom: 15px;">
            <img src="https://cdn-icons-png.flaticon.com/512/1067/1067566.png" style="width: 60px;">
        </div>
        <h3 style="margin-top: 0; color: #2c3e50;">Confirm Redemption</h3>
        <p style="color: #666; font-size: 0.95rem;">
            Redeeming: <b id="confirmItemName" style="color: #264633;">Item</b><br>
            Quantity: <b id="confirmQty">1</b>
        </p>
        <div style="background:#f4f9f5; padding:10px; border-radius:8px; margin-bottom:20px;">
            Total Cost: <b id="confirmTotalCost" style="color:#c0392b;">0</b> Points
        </div>
        <div style="display: flex; gap: 10px;">
            <button onclick="closeConfirmModal()" style="flex: 1; padding: 10px; border: 1px solid #ddd; background: white; border-radius: 8px; cursor: pointer;">Cancel</button>
            <button onclick="processRedemption()" style="flex: 1; padding: 10px; border: none; background: #264633; color: white; border-radius: 8px; cursor: pointer;">Confirm</button>
        </div>
    </div>
</div>

<div id="detailModal" class="modal">
    <div class="modal-content">
        <span onclick="closeModal()" class="close-btn">&times;</span>
        <div style="text-align:center; margin-bottom:15px; background:#f4f9f5; padding:20px;">
            <img id="modalImg" src="" style="max-width:150px; display:none;">
        </div>
        <h2 id="modalTitle" style="margin-top:0;">Title</h2>
        <p id="modalDesc" style="color:#555;">Description</p>
    </div>
</div>

<script>
// ==========================================
// 1. QUANTITY BUTTONS LOGIC
// ==========================================
function increaseQty(id, maxStock) {
    const input = document.getElementById('qty_input_' + id);
    let val = parseInt(input.value);
    if(val < maxStock) {
        input.value = val + 1;
    }
}

function decreaseQty(id) {
    const input = document.getElementById('qty_input_' + id);
    let val = parseInt(input.value);
    if(val > 1) {
        input.value = val - 1;
    }
}

// ==========================================
// 2. MODAL LOGIC
// ==========================================
function openModal(title, desc, imgSrc) {
    document.getElementById('modalTitle').innerHTML = title;
    document.getElementById('modalDesc').innerHTML = desc;
    const imgElement = document.getElementById('modalImg');
    if (imgSrc) { imgElement.src = imgSrc; imgElement.style.display = 'inline-block'; } 
    else { imgElement.style.display = 'none'; }
    document.getElementById('detailModal').style.display = 'block';
}
function closeModal() { document.getElementById('detailModal').style.display = 'none'; }

// ==========================================
// 3. CONFIRMATION LOGIC
// ==========================================
let targetForm = null;
function openConfirmModal(event, form, itemName, itemCost, itemId) {
    event.preventDefault();
    targetForm = form;
    
    // Get visible quantity value
    const quantity = document.getElementById('qty_input_' + itemId).value;
    // Set hidden form input
    document.getElementById('form_qty_' + itemId).value = quantity;
    
    const totalCost = itemCost * quantity;
    
    document.getElementById('confirmItemName').innerText = itemName;
    document.getElementById('confirmQty').innerText = quantity;
    document.getElementById('confirmTotalCost').innerText = totalCost.toLocaleString();
    document.getElementById('confirmModal').style.display = 'block';
    return false;
}
function processRedemption() { if (targetForm) targetForm.submit(); closeConfirmModal(); }
function closeConfirmModal() { document.getElementById('confirmModal').style.display = 'none'; targetForm = null; }

window.onclick = function(e) { 
    if (e.target == document.getElementById('detailModal')) closeModal(); 
    if (e.target == document.getElementById('confirmModal')) closeConfirmModal(); 
}

// ==========================================
// 4. STATUS & NOTIFICATIONS
// ==========================================
document.addEventListener("DOMContentLoaded", function() {
    const urlParams = new URLSearchParams(window.location.search);
    const status = urlParams.get('status');
    const item = urlParams.get('item');
    const qty = urlParams.get('qty');
    const msg = urlParams.get('msg');

    if (status === 'success') {
        openModal(
            'Redemption Successful!', 
            'You redeemed <b>' + (qty ? qty : '1') + 'x ' + (item ? decodeURIComponent(item) : 'item') + '</b>.<br>Status: <b>Redeemed</b>',
            'https://cdn-icons-png.flaticon.com/512/148/148767.png'
        );
        window.history.replaceState({}, document.title, window.location.pathname + "?tab=" + (urlParams.get('tab') || 'permanent'));
    } else if (status === 'error') {
        openModal('Transaction Failed', msg ? decodeURIComponent(msg) : 'Error', 'https://cdn-icons-png.flaticon.com/512/190/190406.png');
        window.history.replaceState({}, document.title, window.location.pathname + "?tab=" + (urlParams.get('tab') || 'permanent'));
    }
});
</script>
<?php include '../includes/footer.php'; ?>