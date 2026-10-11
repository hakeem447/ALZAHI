<?php
session_start();
if (!isset($_SESSION['admin_id'])) { header('Location: login.php'); exit; }
require_once __DIR__ . '/../config/database.php';

function e($v) { return htmlspecialchars((string)$v, ENT_QUOTES, 'UTF-8'); }
function itemsFromJson($json) { $x = json_decode((string)$json, true); return is_array($x) ? $x : []; }
function goOrder($id, $notice = '') {
    $url = 'order-details.php?id=' . (int)$id;
    if ($notice !== '') $url .= '&notice=' . rawurlencode($notice);
    header('Location: ' . $url); exit;
}
function whatsappPhone($phone) {
    $d = preg_replace('/\D+/', '', (string)$phone);
    if (strpos($d, '00') === 0) $d = substr($d, 2);
    if (preg_match('/^0?5\d{8}$/', $d)) $d = '971' . ltrim($d, '0');
    return $d;
}
function quoteMessage($order, $items, $total) {
    $lines = ['JASIR FRESH - ORDER PRICE DETAILS', 'Order #' . $order['id'], 'Customer: ' . $order['customer_name'], 'Phone: ' . $order['customer_phone'], '', 'Order items:'];
    foreach ($items as $item) {
        $name = $item['product_name'] ?? $item['name'] ?? 'Product';
        $qty = (float)($item['quantity'] ?? 0);
        $unit = strtolower((string)($item['unit'] ?? 'kg'));
        $unitLabel = $unit === 'box' ? ($item['box_label'] ?? 'Box') : 'KG';
        $line = '- ' . $name . ' - ' . rtrim(rtrim(number_format($qty, 2, '.', ''), '0'), '.') . ' ' . $unitLabel;
        $price = (float)($item['price'] ?? 0);
        if ($price > 0) $line .= ' - AED ' . number_format($price, 2) . ' per ' . $unitLabel . ' (Line: AED ' . number_format((float)($item['line_total'] ?? ($price * $qty)), 2) . ')';
        $lines[] = $line;
    }
    if ((float)$total > 0) { $lines[] = ''; $lines[] = 'Quotation Total: AED ' . number_format((float)$total, 2); }
    $lines[] = ''; $lines[] = 'Please confirm the order details.'; $lines[] = 'Thank you - AL ZAHI Fresh';
    return implode("\n", $lines);
}

$orderId = (int)($_GET['id'] ?? $_POST['order_id'] ?? 0);
if ($orderId <= 0) exit('Invalid order ID.');
$notice = trim((string)($_GET['notice'] ?? ''));
$error = '';
$allowedStatuses = ['Pending','New','Confirmed','Processing','Completed','Cancelled'];

// Load products for the quotation editor's Add Product control.
$products = [];
$pResult = $conn->query("SELECT id, name, category, unit, price, box_label, status FROM products ORDER BY name ASC");
if ($pResult) { while ($p = $pResult->fetch_assoc()) { $products[] = $p; } $pResult->free(); }

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = (string)($_POST['action'] ?? '');
    if ($action === 'save_quote') {
        $stmt = $conn->prepare('SELECT id FROM orders WHERE id = ? LIMIT 1');
        if (!$stmt) { $error = 'Unable to load order.'; }
        else {
            $stmt->bind_param('i', $orderId); $stmt->execute(); $exists = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if (!$exists) $error = 'Order not found.';
        }
        $ids = $_POST['product_id'] ?? []; $names = $_POST['item_name'] ?? []; $units = $_POST['unit'] ?? [];
        $labels = $_POST['box_label'] ?? []; $qtys = $_POST['quantity'] ?? []; $prices = $_POST['price'] ?? []; $removed = $_POST['remove_item'] ?? [];
        $quotedItems = []; $total = 0.0;
        if ($error === '') {
            foreach ($names as $i => $rawName) {
                if (isset($removed[$i])) continue;
                $name = trim((string)$rawName);
                $qtyText = trim((string)($qtys[$i] ?? ''));
                $priceText = trim((string)($prices[$i] ?? '0'));
                $unit = strtolower((string)($units[$i] ?? 'kg')) === 'box' ? 'box' : 'kg';
                $boxLabel = trim((string)($labels[$i] ?? 'Box'));
                if ($name === '') { $error = 'Each item needs a product name, or select Remove for that row.'; break; }
                if ($qtyText === '' || !is_numeric($qtyText) || (float)$qtyText <= 0 || (float)$qtyText > 1000000) { $error = 'Enter a valid quantity greater than zero for ' . $name . '.'; break; }
                if ($priceText === '' || !is_numeric($priceText) || (float)$priceText < 0 || (float)$priceText > 99999999) { $error = 'Enter a valid price (0 or higher) for ' . $name . '.'; break; }
                $qty = (float)$qtyText; $price = (float)$priceText; $lineTotal = round($qty * $price, 2); $total += $lineTotal;
                $quotedItems[] = ['product_id'=>(int)($ids[$i] ?? 0), 'product_name'=>$name, 'category'=>'', 'unit'=>$unit, 'box_label'=>$unit === 'box' ? ($boxLabel ?: 'Box') : '', 'quantity'=>$qty, 'price'=>$price, 'line_total'=>$lineTotal];
            }
            if ($error === '' && count($quotedItems) === 0) $error = 'Quotation must contain at least one item.';
        }
        if ($error === '') {
            $json = json_encode($quotedItems, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
            if ($json === false) $error = 'Unable to prepare quotation data.';
            else {
                $stmt = $conn->prepare('UPDATE orders SET quoted_items_json = ?, quoted_total = ?, quote_sent_at = NULL WHERE id = ?');
                if (!$stmt) $error = 'Unable to prepare quotation update.';
                else { $stmt->bind_param('sdi', $json, $total, $orderId); if ($stmt->execute()) { $stmt->close(); goOrder($orderId, 'Quotation changes saved successfully.'); } else { $error = 'Unable to save quotation changes.'; $stmt->close(); } }
            }
        }
    } elseif ($action === 'update_status') {
        $newStatus = trim((string)($_POST['status'] ?? ''));
        if (!in_array($newStatus, $allowedStatuses, true)) $error = 'Please select a valid order status.';
        else { $stmt = $conn->prepare('UPDATE orders SET status = ? WHERE id = ?'); if (!$stmt) $error = 'Unable to update status.'; else { $stmt->bind_param('si', $newStatus, $orderId); if ($stmt->execute()) { $stmt->close(); goOrder($orderId, 'Order status updated.'); } else { $error = 'Unable to update status.'; $stmt->close(); } } }
    } elseif ($action === 'send_email') {
        $stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
        if (!$stmt) $error = 'Unable to load order for email.';
        else {
            $stmt->bind_param('i', $orderId); $stmt->execute(); $emailOrder = $stmt->get_result()->fetch_assoc(); $stmt->close();
            if (!$emailOrder) $error = 'Order not found.';
            else {
                $emailItems = itemsFromJson($emailOrder['quoted_items_json'] ?? '');
                if (!$emailItems) $emailItems = itemsFromJson($emailOrder['items_json'] ?? '');
                if (!$emailItems) $error = 'No order items were found.';
                else {
                    $message = quoteMessage($emailOrder, $emailItems, (float)($emailOrder['quoted_total'] ?? 0));
                    try {
                        require_once __DIR__ . '/../config/send-order-email.php';
                        $cfg = require __DIR__ . '/../config/email.php';
                        $to = trim((string)($cfg['admin_email'] ?? ''));
                        if (!filter_var($to, FILTER_VALIDATE_EMAIL)) throw new Exception('Set a valid admin_email in config/email.php.');
                        $result = sendJasirEmail($to, (string)($cfg['admin_name'] ?? ' AL ZAHI Fresh'), 'Order #' . $orderId . ' - ' . $emailOrder['customer_name'], nl2br(e($message)), $message);
                        if (!empty($result['success'])) {
                            $u = $conn->prepare('UPDATE orders SET quote_sent_at = NOW() WHERE id = ?');
                            if ($u) { $u->bind_param('i', $orderId); $u->execute(); $u->close(); }
                            goOrder($orderId, 'Order email sent successfully.');
                        }
                        $error = $result['message'] ?? 'Email could not be sent.';
                    } catch (Throwable $ex) { error_log(' AL ZAHI Fresh order email: ' . $ex->getMessage()); $error = 'Email could not be sent. Check config/email.php and SMTP settings.'; }
                }
            }
        }
    }
}

$stmt = $conn->prepare('SELECT * FROM orders WHERE id = ? LIMIT 1');
if (!$stmt) exit('Unable to prepare order query.');
$stmt->bind_param('i', $orderId); $stmt->execute(); $order = $stmt->get_result()->fetch_assoc(); $stmt->close();
if (!$order) exit('Order not found.');
$originalItems = itemsFromJson($order['items_json'] ?? '');
$savedQuote = itemsFromJson($order['quoted_items_json'] ?? '');
$hasSavedQuote = count($savedQuote) > 0;
$editorItems = $hasSavedQuote ? $savedQuote : $originalItems;
$quotedTotal = (float)($order['quoted_total'] ?? 0);
$waPhone = whatsappPhone($order['customer_phone'] ?? '');
$waMessage = quoteMessage($order, $hasSavedQuote ? $savedQuote : $originalItems, $hasSavedQuote ? $quotedTotal : 0);
$waUrl = $waPhone !== '' ? 'https://wa.me/' . $waPhone . '?text=' . rawurlencode($waMessage) : '';
$history = [];
$phone = (string)($order['customer_phone'] ?? '');
$stmt = $conn->prepare('SELECT id, customer_name, customer_phone, item_count, status, created_at FROM orders WHERE customer_phone = ? ORDER BY created_at DESC, id DESC');
if ($stmt) { $stmt->bind_param('s', $phone); $stmt->execute(); $res = $stmt->get_result(); while ($r = $res->fetch_assoc()) $history[] = $r; $stmt->close(); }
$currentStatus = (string)($order['status'] ?? 'Pending');
$productOptions = [];
foreach ($products as $p) { $productOptions[] = ['id'=>(int)$p['id'], 'name'=>(string)$p['name'], 'category'=>(string)($p['category'] ?? ''), 'unit'=>(string)($p['unit'] ?? 'kg'), 'price'=>(float)($p['price'] ?? 0), 'box_label'=>(string)($p['box_label'] ?? 'Box')]; }
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Order #<?= e($orderId) ?> | J AL ZAHI FresH ADMIN</title>
<style>
*{box-sizing:border-box}body{margin:0;padding:24px;background:#f4f7f5;color:#20382e;font-family:Arial,Helvetica,sans-serif}.page{width:100%;max-width:1100px;margin:0 auto}.topbar{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap;margin-bottom:22px}h1{margin:0;font-size:27px}h2{margin:0 0 16px;font-size:19px}.muted{color:#6a7a72;font-size:13px;line-height:1.6}.card{background:#fff;border:1px solid #e0e9e3;border-radius:15px;padding:22px;margin-bottom:20px;box-shadow:0 5px 20px rgba(20,55,39,.04)}.details-grid{display:grid;grid-template-columns:repeat(2,minmax(0,1fr));gap:16px}.detail-label{display:block;margin-bottom:6px;color:#708078;font-size:12px}.detail-value{overflow-wrap:anywhere;font-weight:700}.status-form,.action-row,.edit-tools{display:flex;gap:10px;flex-wrap:wrap;align-items:center}input,select{width:100%;padding:10px 11px;border:1px solid #d8e3dc;border-radius:9px;font:inherit;background:#fff;color:#20382e}.status-form select{width:auto;min-width:160px}button,.btn{display:inline-flex;align-items:center;justify-content:center;gap:7px;padding:11px 15px;border:0;border-radius:9px;background:#1c8056;color:#fff;font-size:13px;font-weight:700;text-decoration:none;cursor:pointer;font-family:inherit}button:hover,.btn:hover{filter:brightness(.94)}.btn-secondary{background:#e9f0ec;color:#244735}.btn-whatsapp{background:#177c51}.btn-email{background:#304f72}.btn-edit{background:#315477}.btn-danger{background:#a83a32}.btn-add{background:#e7f3ec;color:#205e3d;border:1px solid #cfe4d7}.alert{padding:13px 15px;margin-bottom:18px;border-radius:10px;background:#e7f5ec;color:#205e3d;overflow-wrap:anywhere}.alert-error{background:#fff0ef;color:#a22b24}.table-wrap{width:100%;overflow-x:auto}table{width:100%;border-collapse:collapse;min-width:700px}th,td{padding:12px 10px;border-bottom:1px solid #e8eee9;text-align:left;vertical-align:middle;font-size:13px}th{color:#687a70;background:#f8faf8;font-size:12px}.product-name{font-weight:700}.price-input{width:125px}.qty-input{width:90px}.name-input{min-width:165px}.unit-select{min-width:90px}.price-help{margin:0 0 14px;color:#687a70;font-size:13px;line-height:1.6}.quote-total{display:flex;justify-content:flex-end;align-items:center;gap:16px;margin:18px 0;font-size:15px;font-weight:700}.total-value{font-size:22px}.status-pill{display:inline-block;padding:5px 9px;border-radius:30px;background:#edf2ef;color:#315642;font-size:11px;font-weight:700}.edit-only{display:none}.editing .edit-only{display:table-cell}.editing .static-value{display:none}.editing .edit-hint{display:block}.edit-hint{display:none}.small-note{font-size:12px;color:#6a7a72;margin-top:10px}.history-table td,.history-table th{white-space:nowrap}.row-remove{white-space:nowrap}.empty{color:#687a70;padding:12px 0}.hidden{display:none!important}@media(max-width:650px){body{padding:12px}h1{font-size:22px}.card{padding:15px;border-radius:12px}.details-grid{grid-template-columns:1fr}.status-form select{width:100%}.status-form button,.action-row .btn,.action-row button{width:100%}.price-input{width:100px}.quote-total{justify-content:space-between}}
</style></head><body><div class="page">
<div class="topbar"><div><h1>Order #<?= e($orderId) ?></h1><p class="muted"> AL ZAHI Fresh · Order details and customer history</p></div><a class="btn btn-secondary" href="orders.php">Back to Orders</a></div>
<?php if ($notice !== ''): ?><div class="alert"><?= e($notice) ?></div><?php endif; ?>
<?php if ($error !== ''): ?><div class="alert alert-error"><?= e($error) ?></div><?php endif; ?>
<section class="card"><h2>Customer Details</h2><div class="details-grid">
<div><span class="detail-label">Customer Name</span><div class="detail-value"><?= e($order['customer_name'] ?? '') ?></div></div>
<div><span class="detail-label">Phone Number</span><div class="detail-value"><?= e($order['customer_phone'] ?? '') ?></div></div>
<div><span class="detail-label">Order Date</span><div class="detail-value"><?= e($order['created_at'] ?? '') ?></div></div>
<div><span class="detail-label">Current Status</span><div class="detail-value"><span class="status-pill"><?= e($currentStatus) ?></span></div></div>
</div></section>
<section class="card"><h2>Update Order Status</h2><form method="POST" class="status-form"><input type="hidden" name="order_id" value="<?= $orderId ?>"><input type="hidden" name="action" value="update_status"><select name="status" required><?php foreach($allowedStatuses as $st): ?><option value="<?= e($st) ?>" <?= $currentStatus===$st?'selected':'' ?>><?= e($st) ?></option><?php endforeach; ?></select><button type="submit">Update Status</button></form></section>
<section class="card" id="quotationCard"><h2>Order Items & Price Quotation</h2>
<p class="price-help"> </p>
<form method="POST" id="quoteForm"><input type="hidden" name="order_id" value="<?= $orderId ?>"><input type="hidden" name="action" value="save_quote">
<div class="edit-tools" style="margin-bottom:14px"><button type="button" class="btn-edit" id="editBtn">Edit</button><button type="button" class="btn-add edit-only" id="addProductBtn">+ Add Product</button><span class="small-note" id="editStatus"></span></div>
<div class="table-wrap"><table id="quoteTable"><thead><tr><th>Product</th><th>Unit</th><th>Quantity</th><th>Price per Unit (AED)</th><th>Line Total (AED)</th><th class="edit-only">Action</th></tr></thead><tbody>
<?php if ($editorItems): foreach($editorItems as $i=>$item): $name=(string)($item['product_name']??$item['name']??'Product'); $unit=strtolower((string)($item['unit']??'kg'))==='box'?'box':'kg'; $qty=(float)($item['quantity']??1); $price=(float)($item['price']??0); $pid=(int)($item['product_id']??0); $bl=(string)($item['box_label']??'Box'); ?>
<tr class="quote-row"><td><span class="static-value product-name"><?= e($name) ?></span><input class="name-input edit-only" name="item_name[]" value="<?= e($name) ?>" required><input type="hidden" name="product_id[]" value="<?= $pid ?>"></td>
<td><span class="static-value"><?= e($unit==='box'?($bl?:'Box'):'KG') ?></span><select class="unit-select edit-only" name="unit[]"><option value="kg" <?= $unit==='kg'?'selected':'' ?>>KG</option><option value="box" <?= $unit==='box'?'selected':'' ?>>Box</option></select><input type="hidden" class="box-label" name="box_label[]" value="<?= e($bl) ?>"></td>
<td><span class="static-value"><?= e(rtrim(rtrim(number_format($qty,2,'.',''),'0'),'.')) ?></span><input class="qty-input edit-only quantity-input" type="number" name="quantity[]" min="0.01" max="1000000" step="0.01" value="<?= e(number_format($qty,2,'.','')) ?>" required></td>
<td><span class="static-value"><?= $price>0?'AED '.e(number_format($price,2)):'—' ?></span><input class="price-input edit-only price-field" type="number" name="price[]" min="0" max="99999999" step="0.01" value="<?= e(number_format($price,2,'.','')) ?>" required></td>
<td><strong class="line-total"><?= e(number_format($qty*$price,2)) ?></strong></td><td class="edit-only row-remove"><button type="button" class="btn-danger remove-row">Remove</button></td></tr>
<?php endforeach; else: ?><tr><td colspan="6">No items were found in this order.</td></tr><?php endif; ?>
</tbody></table></div>
<div class="quote-total"><span>Quotation Total</span><span class="total-value">AED <span id="quoteTotal"><?= e(number_format($quotedTotal,2)) ?></span></span></div>
<div class="action-row"><button type="submit">Save Prices</button><button type="button" class="btn-secondary" id="cancelEditBtn" style="display:none">Cancel Edit</button></div>
<p class="small-note">Price means price per selected unit (KG or Box). Items removed here are removed from this quotation only, not the original customer order.</p>
</form></section>
<section class="card"><h2>Send Order Details</h2><p class="price-help"></p><div class="action-row">
<?php if($waUrl!==''): ?><a class="btn btn-whatsapp" href="<?= e($waUrl) ?>" target="_blank" rel="noopener noreferrer">Reply on WhatsApp</a><?php else: ?><span class="muted">A valid customer phone number is required for WhatsApp.</span><?php endif; ?>
<form method="POST" onsubmit="return confirm('Send this order summary to the email address configured in config/email.php?');"><input type="hidden" name="order_id" value="<?= $orderId ?>"><input type="hidden" name="action" value="send_email"><button type="submit" class="btn-email">Send Order Email</button></form></div><p class="muted" style="margin-top:14px"></p></section>
<section class="card"><h2>Customer Order History</h2><p class="muted">Latest order appears first. This history matches the customer's saved phone number.</p><?php if($history): ?><div class="table-wrap"><table class="history-table"><thead><tr><th>Order</th><th>Date</th><th>Items</th><th>Status</th><th>Action</th></tr></thead><tbody><?php foreach($history as $h): ?><tr><td>#<?= (int)$h['id'] ?></td><td><?= e($h['created_at']??'') ?></td><td><?= (int)($h['item_count']??0) ?></td><td><span class="status-pill"><?= e($h['status']??'') ?></span></td><td><?php if((int)$h['id']===$orderId): ?><strong>Current Order</strong><?php else: ?><a class="btn btn-secondary" href="order-details.php?id=<?= (int)$h['id'] ?>">View Order</a><?php endif; ?></td></tr><?php endforeach; ?></tbody></table></div><?php else: ?><p class="empty">No previous orders were found for this customer.</p><?php endif; ?></section>
</div>
<script>
const products = <?= json_encode($productOptions, JSON_UNESCAPED_UNICODE|JSON_UNESCAPED_SLASHES) ?: '[]' ?>;
const form = document.getElementById('quoteForm'); const tableBody = document.querySelector('#quoteTable tbody'); const editBtn = document.getElementById('editBtn'); const cancelBtn = document.getElementById('cancelEditBtn'); const addBtn = document.getElementById('addProductBtn'); const editStatus = document.getElementById('editStatus'); let editing = false; let initialForm = '';
function money(n){return (Number(n)||0).toFixed(2)}
function recalc(){let total=0; document.querySelectorAll('.quote-row').forEach(row=>{const q=Number(row.querySelector('.quantity-input')?.value)||Number(row.dataset.qty)||0;const p=Number(row.querySelector('.price-field')?.value)||0;const line=q*p;const out=row.querySelector('.line-total');if(out)out.textContent=money(line);total+=line;});document.getElementById('quoteTotal').textContent=money(total);}
function setEditing(on){editing=on;document.getElementById('quotationCard').classList.toggle('editing',on);editBtn.textContent=on?'Editing…':'Edit';editBtn.disabled=on;cancelBtn.style.display=on?'inline-flex':'none';editStatus.textContent=on?'Editing mode: add/remove products or change quantity and prices.':'Read-only mode. Press Edit to make changes.';}
function attachRow(row){row.querySelectorAll('.quantity-input,.price-field').forEach(el=>el.addEventListener('input',recalc));const unit=row.querySelector('.unit-select');if(unit)unit.addEventListener('change',()=>{const label=row.querySelector('.box-label');if(label && unit.value==='box' && !label.value)label.value='Box';recalc();});const remove=row.querySelector('.remove-row');if(remove)remove.addEventListener('click',()=>{if(confirm('Remove this item from the quotation?')){row.remove();recalc();}});}
function makeRow(product){const tr=document.createElement('tr');tr.className='quote-row';const unit=(product.unit||'kg').toLowerCase()==='box'?'box':'kg';const price=Number(product.price)||0;tr.innerHTML=`<td><span class="static-value product-name"></span><input class="name-input edit-only" name="item_name[]" required><input type="hidden" name="product_id[]"></td><td><span class="static-value"></span><select class="unit-select edit-only" name="unit[]"><option value="kg">KG</option><option value="box">Box</option></select><input type="hidden" class="box-label" name="box_label[]" value="Box"></td><td><span class="static-value">1</span><input class="qty-input edit-only quantity-input" type="number" name="quantity[]" min="0.01" max="1000000" step="0.01" value="1" required></td><td><span class="static-value">—</span><input class="price-input edit-only price-field" type="number" name="price[]" min="0" max="99999999" step="0.01" value="${money(price)}" required></td><td><strong class="line-total">${money(price)}</strong></td><td class="edit-only row-remove"><button type="button" class="btn-danger remove-row">Remove</button></td>`;tr.querySelector('.product-name').textContent=product.name;tr.querySelector('[name="item_name[]"]').value=product.name;tr.querySelector('[name="product_id[]"]').value=product.id;tr.querySelector('.static-value:nth-child(1)');const spans=tr.querySelectorAll('td:nth-child(2) .static-value');if(spans[0])spans[0].textContent=unit==='box'?(product.box_label||'Box'):'KG';tr.querySelector('.unit-select').value=unit;tr.querySelector('.box-label').value=product.box_label||'Box';attachRow(tr);tableBody.appendChild(tr);recalc();}
editBtn.addEventListener('click',()=>{initialForm=form.innerHTML;setEditing(true);document.querySelectorAll('.quote-row').forEach(attachRow);recalc();});
addBtn.addEventListener('click',()=>{if(!editing)return;if(!products.length){alert('No products found in the products table. Add products in the admin Products page first.');return;}const choice=prompt('Type the product number to add:\n'+products.map((p,i)=>(i+1)+'. '+p.name+' ('+(p.category||'')+')').join('\n'));if(choice===null)return;const idx=Number(choice)-1;if(!Number.isInteger(idx)||idx<0||idx>=products.length){alert('Invalid product number.');return;}makeRow(products[idx]);});
cancelBtn.addEventListener('click',()=>{if(confirm('Discard unsaved edits? Reload the saved quotation?'))location.reload();});
document.querySelectorAll('.quote-row').forEach(attachRow);recalc();
</script></body></html>
