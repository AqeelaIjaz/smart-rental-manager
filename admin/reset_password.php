<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";

// Demo ke liye OTP screen pe dikha rahe hain (asal app mein ye email/SMS se aayega, screen pe nahi)
$demo_otp = isset($_GET['demo_otp']) ? $_GET['demo_otp'] : (isset($_SESSION['reset_otp']) ? $_SESSION['reset_otp'] : '');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $entered_otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($entered_otp != $_SESSION['reset_otp']) {
        $error = "Code ghalat hai";
    } elseif ($new_password !== $confirm_password) {
        $error = "Password match nahi ho raha";
    } else {
        // NOTE: Real app mein yahan database update hoga:
        // $hashed = md5($new_password);
        // $conn->query("UPDATE admins SET password='$hashed' WHERE email='".$_SESSION['reset_identifier']."' OR phone='".$_SESSION['reset_identifier']."'");

        $success = "Password successfully reset ho gaya! Ab Sign In karo.";
        unset($_SESSION['reset_otp']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Reset Password - Smart Rental Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --grey:#8A8A8A; --line:#E7E0D3; --success:#5AA89A; --gold:#C99A3A; }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{
    font-family:'Poppins', sans-serif;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    min-height:100vh; display:flex; align-items:center; justify-content:center;
  }
  .auth-card{ width:380px; background:#fff; border-radius:18px; padding:40px 36px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,.25); }
  .logo{ width:56px; height:56px; border-radius:14px; background:var(--primary); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; font-weight:700; }
  .auth-card h2{ font-size:19px; color:var(--primary-dark); margin-bottom:4px; }
  .auth-card p.sub{ font-size:12px; color:var(--grey); margin-bottom:20px; }
  .demo-box{ background:#FBF0DA; border-radius:10px; padding:10px; font-size:11.5px; color:#8a6a1f; margin-bottom:16px; }
  .auth-card input{ width:100%; padding:12px 14px; border:1.5px solid var(--line); border-radius:10px; margin-bottom:12px; font-size:13px; font-family:'Poppins'; color:#222; }
  .auth-card input:focus{ outline:none; border-color:var(--primary); }
  .auth-card button{ width:100%; padding:13px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; margin-top:6px; cursor:pointer; }
  .auth-card button:hover{ background:var(--primary-dark); }
  .error-msg{ color:#C96A56; font-size:11.5px; margin-top:10px; }
  .success-msg{ color:var(--success); font-size:11.5px; margin-top:10px; font-weight:600; }
  .links{ margin-top:18px; font-size:12px; color:var(--grey); }
  .links a{ color:var(--primary); font-weight:600; text-decoration:none; }
</style>
</head>
<body>
  <div class="auth-card">
    <div class="logo">SRM</div>
    <h2>Reset Password</h2>
    <p class="sub">Bheja gaya code aur naya password darj karein</p>

    <?php if ($demo_otp != '') { ?>
      <div class="demo-box">Demo Mode: Aapka code hai <strong><?php echo $demo_otp; ?></strong> (real app mein ye email/SMS pe aayega)</div>
    <?php } ?>

    <form action="reset_password.php" method="POST">
      <input type="text" name="otp" placeholder="Enter 6-digit Code" required>
      <input type="password" name="new_password" placeholder="New Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      <button type="submit">Reset Password</button>
      <?php if ($error != "") { ?><p class="error-msg"><?php echo $error; ?></p><?php } ?>
      <?php if ($success != "") { ?><p class="success-msg"><?php echo $success; ?></p><?php } ?>
    </form>

    <div class="links"><a href="signin.php">&larr; Back to Sign In</a></div>
  </div>
</body>
</html>
