<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];

    // Check the email belongs to an admin
    $stmt = $conn->prepare("SELECT * FROM Admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        // NEW/UPDATED: store the OTP in the shared PasswordResets table
        // instead of only in the session, so it works the same way
        // for Users (tenant/landlord) and Admins.
        $otp = rand(100000, 999999);
        $expiry = date("Y-m-d H:i:s", strtotime("+15 minutes"));

        $insert = $conn->prepare("INSERT INTO PasswordResets (identifier, otp, expiry, used) VALUES (?, ?, ?, 0)");
        $insert->bind_param("sss", $email, $otp, $expiry);
        $insert->execute();

        // NOTE: In production, send $otp via email using PHPMailer/SMTP
        // instead of showing it on screen. See Team Documentation Section 2.2.
        header("Location: reset_password.php?email=" . urlencode($email) . "&demo_otp=" . $otp);
        exit();
    } else {
        $error = "No admin account matches this email";
    }
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
  .auth-card input{ width:100%; padding:12px 14px; border:1.5px solid var(--line); border-radius:10px; margin-bottom:12px; font-size:13px; font-family:'Poppins'; color:#222; }
  .auth-card input:focus{ outline:none; border-color:var(--primary); }
  .auth-card button{ width:100%; padding:13px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; margin-top:6px; cursor:pointer; }
  .auth-card button:hover{ background:var(--primary-dark); }
  .error-msg{ color:#C96A56; font-size:11.5px; margin-top:10px; }
  .links{ margin-top:18px; font-size:12px; color:var(--grey); }
  .links a{ color:var(--primary); font-weight:600; text-decoration:none; }
</style>
</head>
<body>
  <div class="auth-card">
    <div class="logo">SRM</div>
    <h2>Forgot Password</h2>
    <p class="sub">Enter your registered email, we'll send a reset code</p>

    <form action="forgot_password.php" method="POST">
      <input type="email" name="email" placeholder="Registered Email Address" required>
      <button type="submit">Send Reset Code</button>
      <?php if ($error != "") { ?><p class="error-msg"><?php echo $error; ?></p><?php } ?>
    </form>

    <div class="links"><a href="login.php">&larr; Back to Sign In</a></div>
  </div>
</body>
</html>
