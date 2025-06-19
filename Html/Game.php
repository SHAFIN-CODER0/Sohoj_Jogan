<?php
session_start();
include '../PHP/db_connect.php';

// Check if the user is logged in
if (!isset($_SESSION['customer_email'])) {
    echo "<script>
        alert('You must log in first!');
        window.location.href = '../Html/index.html';
    </script>";
    exit();
}

// ----------- Bangla Number Conversion Function -----------
function bn_number($number) {
    $bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return strtr($number, ['0'=>$bn_digits[0],'1'=>$bn_digits[1],'2'=>$bn_digits[2],'3'=>$bn_digits[3],'4'=>$bn_digits[4],'5'=>$bn_digits[5],'6'=>$bn_digits[6],'7'=>$bn_digits[7],'8'=>$bn_digits[8],'9'=>$bn_digits[9]]);
}

// ----------- FETCH COINS FOR INITIAL PAGE LOAD -----------
$customer_coins = 0;
$email = $_SESSION['customer_email'];
$sql_init = "SELECT customer_coins FROM customers WHERE customer_email = ?";
$stmt_init = $conn->prepare($sql_init);
$stmt_init->bind_param('s', $email);
$stmt_init->execute();
$stmt_init->bind_result($customer_coins);
$stmt_init->fetch();
$stmt_init->close();

// ----------- AJAX COIN UPDATE HANDLER -----------
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_coin') {
    header('Content-Type: application/json');
    $email = $_SESSION['customer_email'];
    // Increment the coin for this user
    $sql = "UPDATE customers SET customer_coins = customer_coins + 1 WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        // Now fetch the new coin count and return it
        $sql2 = "SELECT customer_coins FROM customers WHERE customer_email = ?";
        $stmt2 = $conn->prepare($sql2);
        $stmt2->bind_param('s', $email);
        $stmt2->execute();
        $stmt2->bind_result($coins);
        $stmt2->fetch();
        $stmt2->close();
        // Return both English and Bangla numerals
        echo json_encode([
            'success' => true,
            'coins' => $coins,
            'coins_bn' => bn_number($coins)
        ]);
    } else {
        echo json_encode(['success' => false, 'msg' => 'Update failed']);
    }
    exit();
}

// ----------- AJAX GET COIN COUNT HANDLER -----------
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['action']) && $_GET['action'] === 'get_coins') {
    header('Content-Type: application/json');
    $email = $_SESSION['customer_email'];
    $sql = "SELECT customer_coins FROM customers WHERE customer_email = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $stmt->bind_result($coins);
    $stmt->fetch();
    $stmt->close();

    // Return both English and Bangla numerals
    echo json_encode([
        'success' => true,
        'coins' => $coins,
        'coins_bn' => bn_number($coins)
    ]);
    exit();
}
?>

<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>সহজ যোগান (Sohaj Jogan) - Game Zone</title>
    <link rel="stylesheet" href="../CSS/Game.css?v=1">
</head>
<body>

<header>
    <div class="logo">
        <a href="../Html/Customer_Home.php">
    <img src="../Images/Logo.png" alt="Sohaj Jogan Logo">
</a>
        <h2>সহজ যোগান</h2>
    </div>
    <div class="icons">
        <div class="coin-balance">
            <img src="../Images/coin-icon.png" alt="Coins" class="coin-icon">
            <span id="coinCount">০</span> 
        </div>
    </div>
</header>

<main class="game-dashboard-vertical">
<section class="main-game-box">
    <div id="gameCanvas" style="position:relative; width:100%; max-width:520px; margin:auto;">
        <canvas id="runnerCanvas" width="600" height="350" style="background: #e3f2fd; display:block; margin:auto; border-radius:8px;"></canvas>
    </div>
    <button id="startBtn">গেম শুরু করুন</button>
<button id="restartBtn">গেম পুনরায় শুরু করুন</button>
</section>

<script>
let creditedCoins = 0; // How many coins have already been credited

// Bangla number convert for JS
function bnNumber(number) {
    const bn_digits = ['০','১','২','৩','৪','৫','৬','৭','৮','৯'];
    return String(number).replace(/\d/g, d => bn_digits[d]);
}

function fetchCoins() {
    fetch('?action=get_coins')
      .then(res => res.json())
      .then(data => {
        if (data.success) {
            document.getElementById('coinCount').innerText = data.coins_bn || bnNumber(data.coins);
        }
      });
}
window.addEventListener('DOMContentLoaded', fetchCoins);

const canvas = document.getElementById('runnerCanvas');
canvas.width = 600;
canvas.height = 350;
const ctx = canvas.getContext('2d');
let gameActive = false, avatar, ground, products, gravity, score, animationId;
let clouds = [], trees = [], buildings = [];
let rainDrops = [], raining = false, rainTimer = 0;
let skyOffset = 0, skySpeed = 0.5;
let highestScore = +localStorage.getItem("sohaj-runner-highscore") || 0;
const bgImg = new Image();
bgImg.src = "../Images/game.jpg";
let bgReady = false;
bgImg.onload = () => { bgReady = true; };
let bgX = 0;
const BG_SCROLL_SPEED = 1.2;

function resetGame() {
    avatar = {x: 60, y: 270, w: 40, h: 60, vy: 0};
    ground = {y: canvas.height - 30, h: 30};
    products = [];
    gravity = 0.7;
    score = 0;
    skyOffset = 0;
    bgX = 0;
    clouds = Array.from({length:3},(_,i)=>({
        x: Math.random()*canvas.width, y: Math.random()*40, r: 25+Math.random()*15, speed: skySpeed*(0.6+Math.random()*0.7)
    }));
    trees = Array.from({length:3},()=>({x: 100+Math.random()*650, y: ground.y-45, h: 45+Math.random()*20}));
    buildings = Array.from({length:2},()=>({x: 200+Math.random()*550, y: ground.y-80, w: 40+Math.random()*25, h: 80+Math.random()*40}));
    rainDrops = [];
    raining = false;
    rainTimer = 0;
    creditedCoins = 0; // Reset the credited coins counter
    drawBackground();
    drawAvatar();
    drawScore();
    document.getElementById('restartBtn').style.display = "none";
}

function drawBackground() {
    if (bgReady) {
        let imgW = bgImg.width;
        let imgH = bgImg.height;
        let scale = canvas.height / imgH;
        let drawW = imgW * scale;
        bgX -= BG_SCROLL_SPEED;
        if (bgX <= -drawW) bgX += drawW;
        ctx.drawImage(bgImg, bgX, 0, drawW, canvas.height);
        ctx.drawImage(bgImg, bgX + drawW, 0, drawW, canvas.height);
        if (raining) {
            ctx.fillStyle = "rgba(96, 125, 139, 0.5)";
            ctx.fillRect(0, 0, canvas.width, canvas.height);
        }
    } else {
        ctx.fillStyle = raining ? "#607d8b" : "#b3e5fc";
        ctx.fillRect(0,0,canvas.width,canvas.height);
    }
    for (let c of clouds) {
        c.x -= c.speed;
        if (c.x + c.r < 0) c.x = canvas.width + c.r;
        ctx.globalAlpha = raining ? 0.5 : 0.8;
        ctx.beginPath();
        ctx.arc(c.x, c.y, c.r, 0, Math.PI * 2);
        ctx.arc(c.x+20, c.y+10, c.r-7, 0, Math.PI * 2);
        ctx.arc(c.x-18, c.y+10, c.r-10, 0, Math.PI * 2);
        ctx.fillStyle="#fff";
        ctx.fill();
        ctx.globalAlpha=1;
    }
    for (let b of buildings) {
        b.x -= skySpeed*0.5;
        if (b.x + b.w < 0) b.x = canvas.width + Math.random()*180;
        ctx.fillStyle="#90a4ae";
        ctx.fillRect(b.x, b.y, b.w, b.h);
        ctx.fillStyle="#cfd8dc";
        for(let i=0;i<3;i++)ctx.fillRect(b.x+5,b.y+8+i*22,8,12);
    }
    for (let t of trees) {
        t.x -= skySpeed;
        if (t.x + 25 < 0) t.x = canvas.width + Math.random()*150;
        ctx.fillStyle="#795548";
        ctx.fillRect(t.x+8,t.y+t.h-17,7,17);
        ctx.beginPath();
        ctx.arc(t.x+12,t.y+15,15,0,2*Math.PI);
        ctx.fillStyle="#388e3c";
        ctx.fill();
        ctx.beginPath();
        ctx.arc(t.x+14,t.y+27,11,0,2*Math.PI);
        ctx.fillStyle="#43a047";
        ctx.fill();
    }
    if (raining) {
        ctx.strokeStyle="#81d4fa";
        ctx.lineWidth=2;
        for(let r of rainDrops){
            ctx.beginPath();
            ctx.moveTo(r.x,r.y);
            ctx.lineTo(r.x, r.y+10);
            ctx.stroke();
            r.y += 12;
            if (r.y > canvas.height) r.y = -10;
        }
    }
    ctx.fillStyle = "#757575";
    ctx.fillRect(0, ground.y, canvas.width, ground.h);
    ctx.setLineDash([20, 20]);
    ctx.strokeStyle = "#fff";
    ctx.lineWidth = 3;
    ctx.beginPath();
    ctx.moveTo(0, ground.y+ground.h/2);
    ctx.lineTo(canvas.width, ground.y+ground.h/2);
    ctx.stroke();
    ctx.setLineDash([]);
}
function drawAvatar() {
    ctx.fillStyle = "#ffcc80";
    ctx.fillRect(avatar.x+12, avatar.y+20, 16, 30);
    ctx.beginPath();
    ctx.arc(avatar.x+20, avatar.y+12, 12, 0, Math.PI * 2);
    ctx.fillStyle = "#ffe0b2";
    ctx.fill();
    ctx.strokeStyle = "#ffcc80";
    ctx.lineWidth = 8;
    ctx.beginPath();
    ctx.moveTo(avatar.x+20, avatar.y+25);
    ctx.lineTo(avatar.x+7, avatar.y+33);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(avatar.x+28, avatar.y+25);
    ctx.lineTo(avatar.x+43, avatar.y+35);
    ctx.stroke();
    ctx.strokeStyle = "#0277bd";
    ctx.lineWidth = 7;
    ctx.beginPath();
    ctx.moveTo(avatar.x+16, avatar.y+50);
    ctx.lineTo(avatar.x+12, avatar.y+62);
    ctx.stroke();
    ctx.beginPath();
    ctx.moveTo(avatar.x+24, avatar.y+50);
    ctx.lineTo(avatar.x+28, avatar.y+62);
    ctx.stroke();
    ctx.strokeStyle = "#bdbdbd";
    ctx.lineWidth = 5;
    ctx.strokeRect(avatar.x+36, avatar.y+30, 25, 20);
    ctx.lineWidth = 2;
    ctx.beginPath();
    ctx.moveTo(avatar.x+36, avatar.y+30);
    ctx.lineTo(avatar.x+50, avatar.y+20);
    ctx.stroke();
    ctx.fillStyle = "#424242";
    ctx.beginPath(); ctx.arc(avatar.x+41, avatar.y+52, 3, 0, 2*Math.PI); ctx.fill();
    ctx.beginPath(); ctx.arc(avatar.x+57, avatar.y+52, 3, 0, 2*Math.PI); ctx.fill();
}
function drawProducts() {
    for(let p of products){
        ctx.save();
        ctx.translate(p.x + p.w/2, p.y + p.h/2);
        ctx.rotate(p.angle);

        // --- Wood box body ---
        ctx.fillStyle = "#deb887"; // burlywood, wood color
        ctx.fillRect(-p.w/2, -p.h/2, p.w, p.h);

        // --- Box border ---
        ctx.strokeStyle = "#8b5c2a"; // deeper brown
        ctx.lineWidth = 4;
        ctx.strokeRect(-p.w/2, -p.h/2, p.w, p.h);

        // --- Cross lines for box effect ---
        ctx.beginPath();
        ctx.moveTo(-p.w/2, -p.h/2); ctx.lineTo(p.w/2, p.h/2);
        ctx.moveTo(-p.w/2, p.h/2); ctx.lineTo(p.w/2, -p.h/2);
        ctx.stroke();

        // --- Wood grain effect ---
        ctx.strokeStyle = "#cd853f";
        ctx.lineWidth = 2;
        ctx.beginPath();
        ctx.moveTo(-p.w/4, -p.h/2 + 8); ctx.lineTo(-p.w/4, p.h/2 - 8);
        ctx.moveTo(p.w/4, -p.h/2 + 8); ctx.lineTo(p.w/4, p.h/2 - 8);
        ctx.moveTo(0, -p.h/2 + 4); ctx.lineTo(0, p.h/2 - 4);
        ctx.stroke();

        ctx.restore();
    }
}
function drawScore() {
    ctx.font = "20px Arial";
    ctx.fillStyle = "#333";
    ctx.fillText("স্কোর: " + score, 10, 30);
    ctx.font = "17px Arial";
    ctx.fillStyle = "#00796b";
    ctx.fillText("সর্বোচ্চ: " + highestScore, 10, 55);
}
function addProduct() {
    let w = 40 + Math.random()*12, h = 40 + Math.random()*12;
    products.push({
        x: canvas.width, y: ground.y - h, w: w, h: h,
        angle: (Math.random()-0.5)*0.1, passed: false
    });
}
function updateProducts() {
    for(let p of products) p.x -= 4;
    if(products.length && products[0].x + products[0].w < 0) products.shift();
    if(Math.random()<0.02) addProduct();
}
function checkCollision() {
    for(let p of products){
        if (
            avatar.x+34 < p.x + p.w &&
            avatar.x+34+32 > p.x &&
            avatar.y+30 + 36 > p.y
        ) return true;
    }
    if (avatar.y+avatar.h > ground.y) return true;
    return false;
}
function updateRain() {
    if (!raining && Math.random()<0.002) { 
        raining = true; 
        rainTimer = 300+Math.random()*200; 
        rainDrops = Array.from({length:30},()=>({x:Math.random()*canvas.width, y:Math.random()*canvas.height}));
    }
    if (raining) {
        rainTimer--;
        if(rainTimer<=0) { raining = false; rainDrops = []; }
    }
}
function gameLoop() {
    drawBackground();
    drawAvatar();
    drawProducts();
    drawScore();
    avatar.vy += gravity;
    avatar.y += avatar.vy;
    if (avatar.y + avatar.h > ground.y) {
        avatar.y = ground.y - avatar.h;
        avatar.vy = 0;
    }
    updateProducts();
    updateRain();
    for(let p of products){
        if(!p.passed && p.x + p.w < avatar.x+34){
            score++;
            p.passed = true;
            if(score > highestScore){
                highestScore = score;
                localStorage.setItem("sohaj-runner-highscore", highestScore);
            }
            // COIN SYSTEM: 1 coin for every 500 points
            let shouldHaveCoins = Math.floor(score / 500);
            while (creditedCoins < shouldHaveCoins) {
                fetch('', {
                    method: 'POST',
                    headers: {'Content-Type':'application/x-www-form-urlencoded'},
                    body: 'action=add_coin'
                })
                  .then(res => res.json())
                  .then(data => {
                      if (data.success) {
                          document.getElementById('coinCount').innerText = data.coins_bn || bnNumber(data.coins);
                      }
                  });
                creditedCoins++;
            }
        }
    }
    if (checkCollision()) {
        gameOver();
        return;
    }
    animationId = requestAnimationFrame(gameLoop);
}
function jump() {
    if (avatar.y + avatar.h >= ground.y) avatar.vy = -12;
    else if (avatar.y > 0) avatar.vy = -10;
}
function gameOver() {
    gameActive = false;
    cancelAnimationFrame(animationId);
    const boxX = 150;
    const boxY = 120;
    const boxWidth = 300;
    const boxHeight = 130;
    const radius = 15;

    // Draw rounded rectangle
    ctx.fillStyle = "rgba(0, 0, 0, 0.7)"; // semi-transparent dark box
    ctx.beginPath();
    ctx.moveTo(boxX + radius, boxY);
    ctx.lineTo(boxX + boxWidth - radius, boxY);
    ctx.quadraticCurveTo(boxX + boxWidth, boxY, boxX + boxWidth, boxY + radius);
    ctx.lineTo(boxX + boxWidth, boxY + boxHeight - radius);
    ctx.quadraticCurveTo(boxX + boxWidth, boxY + boxHeight, boxX + boxWidth - radius, boxY + boxHeight);
    ctx.lineTo(boxX + radius, boxY + boxHeight);
    ctx.quadraticCurveTo(boxX, boxY + boxHeight, boxX, boxY + boxHeight - radius);
    ctx.lineTo(boxX, boxY + radius);
    ctx.quadraticCurveTo(boxX, boxY, boxX + radius, boxY);
    ctx.closePath();
    ctx.fill();

    // Draw main score text
    ctx.font = "28px Arial";
    ctx.fillStyle = "#ffffff";
    ctx.fillText("গেম শেষ! স্কোর: " + score, boxX + 40, boxY + 50);

    // Draw highest score
    ctx.font = "20px Arial";
    ctx.fillText("সর্বোচ্চ: " + highestScore, boxX + 85, boxY + 90);

    document.getElementById('startBtn').disabled = false;
    document.getElementById('restartBtn').style.display = "inline-block";
}

document.getElementById('startBtn').onclick = function() {
    resetGame();
    this.disabled = true; // disable startBtn only
    document.getElementById('restartBtn').style.display = "none";
    gameActive = true;
    gameLoop();
};

document.getElementById('restartBtn').onclick = function() {
    resetGame();
    document.getElementById('startBtn').disabled = true; // keep startBtn disabled
    gameActive = true;
    gameLoop();
};

window.addEventListener('keydown', e=>{
    if (!gameActive) return;
    if (e.code === "Space" || e.key === "ArrowUp") jump();
});
canvas.addEventListener('click', ()=>{
    if (gameActive) jump();
});

// Initial drawing before game starts
resetGame();
</script>
    <section class="other-games-vertical">
        <h4>আরো গেম দেখুন</h4>
        <div class="game-list-scroll">
            <div class="game-card" tabindex="0" title="বস্তু ধরো">
                <img src="../Images/681ddf33b3586_Shop-Banner-Design-MRA-Graphics-scaled.jpg" alt="বস্তু ধরো গেম">
                <div>
                    <strong>বস্তু ধরো</strong><br>
                    <span>দ্রুত বস্তু ধরুন!</span>
                </div>
            </div>
            <div class="game-card" tabindex="0" title="ম্যাচিং গেম">
                <img src="../Images/coin-icon.png" alt="ম্যাচিং গেম">
                <div>
                    <strong>ম্যাচিং গেম</strong><br>
                    <span>মজার মিল খুঁজুন</span>
                </div>
            </div>
            <div class="game-card" tabindex="0" title="কুইজ চ্যালেঞ্জ">
                <img src="../Images/demo.jpg" alt="কুইজ চ্যালেঞ্জ">
                <div>
                    <strong>কুইজ চ্যালেঞ্জ</strong><br>
                    <span>জ্ঞান যাচাই করুন</span>
                </div>
            </div>
            <div class="game-card coming-soon" title="আরো গেম">
                <div class="coming-soon-text">
                    <strong>আরো গেম আসছে...</strong>
                </div>
            </div>
        </div>
    </section>
</main>
<div id="gameModal" class="game-modal" tabindex="-1" aria-modal="true" role="dialog">
  <div class="game-modal-content">
    <span class="game-modal-close" aria-label="Close">&times;</span>
    <p id="gameModalText"></p>
  </div>
</div>
<script src="../java_script/game-card-popup.js"></script>
<footer class="footer">
    <div class="footer-links">
        <div class="footer-column">
            <h4>শপিং অনলাই</h4>
            <ul>
                <li><a href="#">ডেলিভারি</a></li>
                <li><a href="#">অর্ডার হিস্টোরি</a></li>
                <li><a href="#">উইস লিস্ট</a></li>
                <li><a href="#">পেমেন্ট</a></li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>আমাদের সম্পর্কে</h4>
            <ul>
                <li>
                    <a href="../Html/About_us.html">
                        <img src="../Images/light-bulb.png" alt="info icon" class="link-icon">
                        আমাদের সম্পর্কে বিস্তারিত জানুন
                    </a>
                </li>
            </ul>
        </div>
        <div class="footer-column">
            <h4>যোগাযোগের তথ্য</h4>
            <ul>
                <li><a href="#">📞 ফোন</a></li>
                <li><a href="#">✉ ইমেইল</a></li>
            </ul>
        </div> 
    </div>
    <div class="footer-bottom"></div>
</footer>
</body>
</html>