<?php
session_start();
include '../PHP/db_connect.php';

// --- Only allow shop owners ---
if (empty($_SESSION['shop_owner_id'])) {
    echo "<script>
        alert('You must log in first!');
        window.location.href = '../Html/index.php';
    </script>";
    exit();
}

$user_id = $_SESSION['shop_owner_id'];

// --- Fetch logged-in shop owner info ---
$stmt = $conn->prepare("SELECT * FROM shop_owners WHERE shop_owner_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$self_result = $stmt->get_result();
$self = $self_result->fetch_assoc();
$stmt->close();

// --- Fetch contact list (all customers this shop owner has chatted with) ---
$contacts = [];
$stmt = $conn->prepare("
    SELECT 
        c.customer_id AS id, 
        c.customer_name AS name, 
        c.profile_pic AS customer_profile_pic, 
        1 AS is_active,
        MAX(m.created_at) AS last_msg_time
    FROM messages m
    INNER JOIN customers c
        ON (
            (m.sender_type = 'customer' AND m.sender_id = c.customer_id)
            OR
            (m.receiver_type = 'customer' AND m.receiver_id = c.customer_id)
        )
    WHERE
        (m.sender_type = 'shop_owner' AND m.sender_id = ?)
        OR
        (m.receiver_type = 'shop_owner' AND m.receiver_id = ?)
    GROUP BY c.customer_id, c.customer_name, c.profile_pic
    ORDER BY last_msg_time DESC
");
$stmt->bind_param("ii", $user_id, $user_id);
$stmt->execute();
$contacts_result = $stmt->get_result();
while ($row = $contacts_result->fetch_assoc()) {
    $contacts[] = $row;
}
$stmt->close();

// --- Determine chat target (customer) ---
$target_id = isset($_GET['customer_id']) ? intval($_GET['customer_id']) : 0;

// --- Auto-select first contact if no chat selected ---
if (!$target_id && count($contacts) > 0) {
    header("Location: Massenger-chat-shop.php?customer_id=" . $contacts[0]['id']);
    exit();
}

// --- Fetch target customer info (if selected) ---
$target = null;
if ($target_id) {
    $stmt = $conn->prepare("SELECT *, profile_pic AS customer_profile_pic FROM customers WHERE customer_id = ?");
    $stmt->bind_param("i", $target_id);
    $stmt->execute();
    $target_result = $stmt->get_result();
    $target = $target_result->fetch_assoc();
    $stmt->close();
}

// --- Fetch chat messages (between this shop owner and the selected customer) ---
$messages = [];
if ($target_id && $target) {
    $stmt = $conn->prepare("
        SELECT * FROM messages
        WHERE
            (
                sender_type = 'shop_owner' AND sender_id = ? AND receiver_type = 'customer' AND receiver_id = ?
            )
            OR
            (
                sender_type = 'customer' AND sender_id = ? AND receiver_type = 'shop_owner' AND receiver_id = ?
            )
        ORDER BY created_at ASC
    ");
    $stmt->bind_param("iiii", $user_id, $target_id, $target_id, $user_id);
    $stmt->execute();
    $messages_result = $stmt->get_result();
    while ($row = $messages_result->fetch_assoc()) {
        $messages[] = $row;
    }
    $stmt->close();
}

// --- Handle new message POST (AJAX-friendly) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['message']) && $target_id && $target) {
    $msg = trim($_POST['message']);
    if ($msg !== '') {
        $stmt = $conn->prepare("INSERT INTO messages (sender_type, sender_id, receiver_type, receiver_id, message) VALUES ('shop_owner', ?, 'customer', ?, ?)");
        $stmt->bind_param("iis", $user_id, $target_id, $msg);
        $success = $stmt->execute();
        $stmt->close();
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => [
                'sender_type' => 'shop_owner',
                'message' => htmlspecialchars($msg),
                'created_at' => date('Y-m-d H:i:s')
            ]
        ]);
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <title>সহজ যোগান - মেসেঞ্জার (শপওনার)</title>
    <link rel="stylesheet" href="../CSS/Massenger-chat.css?v=1.0">
    <style>.contact.active { border-left: 4px solid #0084ff; }</style>
</head>
<body>
    <header>
      <div class="logo" id="logoDiv" style="cursor:pointer;">
    <img src="../Images/Logo.png" alt="Liberty Logo">
    <h2>সহজ যোগান</h2>
</div>
<script>
document.getElementById('logoDiv').onclick = function() {
    window.location.href = '../Html/ShopOwner_Home.php';
};
</script>

    </header>
    
    <div class="messenger-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <span class="profile-name"><?= htmlspecialchars($self['shop_owner_name']) ?></span>
            </div>
            <div class="sidebar-search">
                <input type="text" placeholder="Search" onkeyup="filterContacts(this.value)">
            </div>
           <ul class="contact-list" id="contact-list">
<?php foreach($contacts as $contact): ?>
<?php
    // file existence check for contact pic
    $contact_pic_path = "../uploads/" . htmlspecialchars($contact['customer_profile_pic']);
    $contact_pic_exists = !empty($contact['customer_profile_pic']) && file_exists($contact_pic_path);
?>
<li class="contact<?= ($target_id == $contact['id']) ? ' active' : '' ?>" onclick="window.location.href='Massenger-chat-shop.php?customer_id=<?= $contact['id'] ?>'">
    <?php if ($contact_pic_exists): ?>
        <img src="../uploads/<?= htmlspecialchars($contact['customer_profile_pic']) ?>" alt="<?= htmlspecialchars($contact['name']) ?>">
    <?php else: ?>
        <img src="../Images/Sample_User_Icon.png" alt="User">
    <?php endif; ?>
    <div>
        <span class="contact-name"><?= htmlspecialchars($contact['name']) ?></span>
        <span class="contact-status"><?= intval($contact['is_active']) ? 'Active now' : 'Offline' ?></span>
    </div>
</li>
<?php endforeach; ?>
</ul>
        </aside>
        <main class="chat-area">
            <?php if ($target): ?>
           <div class="chat-header">
<?php
    $target_pic_path = "../uploads/" . htmlspecialchars($target['customer_profile_pic']);
    $target_pic_exists = !empty($target['customer_profile_pic']) && file_exists($target_pic_path);
?>
<?php if ($target_pic_exists): ?>
    <img src="../uploads/<?= htmlspecialchars($target['customer_profile_pic']) ?>" alt="<?= htmlspecialchars($target['customer_name'] ?? '') ?>">
<?php else: ?>
    <img src="../Images/Sample_User_Icon.png" alt="User">
<?php endif; ?>
    <div>
        <span class="chat-name"><?= htmlspecialchars($target['customer_name'] ?? '') ?></span>
        <span class="chat-username"><?= htmlspecialchars($target['customer_email'] ?? '') ?></span>
    </div>
    <div style="margin-left:auto;padding-left:16px;color:#0084ff;font-size:14px;align-self:center;">
        ● Active now
    </div>
</div>
            <div class="chat-messages" id="chat-messages">
                <?php if (count($messages) == 0): ?>
                    <div style="color:#888;text-align:center;margin-top:15px;">কোনো মেসেজ নেই</div>
                <?php endif; ?>
                <?php foreach($messages as $msg): ?>
                    <div class="message <?= ($msg['sender_type'] === 'shop_owner' && $msg['sender_id'] == $user_id) ? 'sent' : 'received' ?>">
                        <span><?= nl2br(htmlspecialchars($msg['message'])) ?></span>
                        <div style="font-size:11px;color:#aaa;text-align:right;"><?= date('H:i', strtotime($msg['created_at'])) ?></div>
                    </div>
                <?php endforeach; ?>
            </div>
            <form class="chat-input" id="chat-form" method="post" autocomplete="off">
                <input type="text" name="message" id="message-input" placeholder="Message..." autocomplete="off">
                <button type="submit" class="icon send-btn" title="Send">&#10148;</button>
            </form>
            <?php else: ?>
            <div style="display:flex;align-items:center;justify-content:center;height:100%;color:#888;">কোনো চ্যাট নির্বাচন করুন</div>
            <?php endif; ?>
        </main>
    </div>
    <script>
    function filterContacts(val) {
        var items = document.querySelectorAll("#contact-list .contact");
        val = val.toLowerCase();
        items.forEach(function(item) {
            var name = item.querySelector('.contact-name').textContent.toLowerCase();
            item.style.display = name.includes(val) ? '' : 'none';
        });
    }
    document.getElementById('chat-form')?.addEventListener('submit', function(e){
        e.preventDefault();
        var input = document.getElementById('message-input');
        var msg = input.value.trim();
        if(msg === '') return;
        var xhr = new XMLHttpRequest();
        xhr.open('POST', window.location.href, true);
        xhr.setRequestHeader('Content-type', 'application/x-www-form-urlencoded');
        xhr.onload = function(){
            if(xhr.status === 200){
                var resp = JSON.parse(xhr.responseText);
                if(resp.success){
                    var chat = document.getElementById('chat-messages');
                    var wrapper = document.createElement('div');
                    wrapper.className = 'message sent';
                    wrapper.innerHTML = "<span>"+input.value.replace(/</g,"&lt;")+"</span><div style='font-size:11px;color:#aaa;text-align:right;'>" + (new Date()).toLocaleTimeString().slice(0,5) + "</div>";
                    chat.appendChild(wrapper);
                    chat.scrollTop = chat.scrollHeight;
                    input.value = '';
                }
            }
        };
        xhr.send('message=' + encodeURIComponent(msg));
    });
    window.onload = function(){
        var chat = document.getElementById('chat-messages');
        if(chat) chat.scrollTop = chat.scrollHeight;
    };
    </script>
</body>
</html>