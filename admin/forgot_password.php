<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $method = $_POST['method'];   // "email" ya "phone"
    $identifier = $_POST['identifier'];

    // NOTE: Yahan real app mein hoga:
    // - Agar method == "email" -> PHPMailer se email bhejna
    // - Agar method == "phone" -> Twilio jaisi SMS API se code bhejna
    // Abhi demo ke liye ek random code generate kar ke session mein rakh rahe hain
    $otp = rand(100000, 999999);
    $_SESSION['reset_otp'] = $otp;
    $_SESSION['reset_identifier'] = $identifier;

    header("Location: reset_password.php?demo_otp=" . $otp);
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Forgot Password - Smart Rental Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --grey:#8A8A8A; --line:#E7E0D3; }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{
    font-family:'Poppins', sans-serif;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    min-height:100vh; display:flex; align-items:center; justify-content:center;
  }
  .auth-card{ width:380px; background:#fff; border-radius:18px; padding:40px 36px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,.25); }
  .logo{ width:56px; height:56px; border-radius:14px; background:var(--primary); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; font-weight:700; }
  .auth-card h2{ font-size:19px; color:var(--primary-dark); margin-bottom:4px; }
  .auth-card p.sub{ font-size:12px; color:var(--grey); margin-bottom:22px; }

  .method-toggle{ display:flex; background:#F5EFE6; border-radius:12px; padding:4px; margin-bottom:16px; }
  .method-toggle label{ flex:1; text-align:center; padding:9px; font-size:12.5px; font-weight:600; color:var(--grey); border-radius:9px; cursor:pointer; }
  .method-toggle input{ display:none; }
  .method-toggle input:checked + label{ background:var(--primary); color:#fff; }

  .auth-card input[type=text]{ width:100%; padding:12px 14px; border:1.5px solid var(--line); border-radius:10px; margin-bottom:12px; font-size:13px; font-family:'Poppins'; color:#222; }
  .auth-card input:focus{ outline:none; border-color:var(--primary); }
  .auth-card button{ width:100%; padding:13px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; margin-top:6px; cursor:pointer; }
  .auth-card button:hover{ background:var(--primary-dark); }
  .links{ margin-top:18px; font-size:12px; color:var(--grey); }
  .links a{ color:var(--primary); font-weight:600; text-decoration:none; }
</style>
</head>
<body>
  <div class="auth-card">
    <div class="logo">SRM</div>
    <h2>Forgot Password</h2>
    <p class="sub">Password reset karne ka tareeqa chunein</p>

    <form action="forgot_password.php" method="POST">
      <div class="method-toggle">
        <input type="radio" name="method" id="m_email" value="email" checked>
        <label for="m_email">Via Email</label>
        <input type="radio" name="method" id="m_phone" value="phone">
        <label for="m_phone">Via Mobile</label>
      </div>

      <input type="text" name="identifier" placeholder="Enter Email or Phone Number" required>
      <button type="submit">Send Reset Code</button>
    </form>

    <div class="links"><a href="signin.php">&larr; Back to Sign In</a></div>
  </div>
</body>
</html>
