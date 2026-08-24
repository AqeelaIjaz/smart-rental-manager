<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";

$email_prefill = isset($_GET['email']) ? $_GET['email'] : '';
$demo_otp = isset($_GET['demo_otp']) ? $_GET['demo_otp'] : '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $entered_otp = $_POST['otp'];
    $new_password = $_POST['new_password'];
    $confirm_password = $_POST['confirm_password'];

    if ($new_password !== $confirm_password) {
        $error = "Passwords do not match";
    } else {
        // NEW/UPDATED: verify the OTP against the PasswordResets table
        // (must match identifier, not be used, and not be expired)
        $stmt = $conn->prepare("SELECT * FROM PasswordResets WHERE identifier = ? AND otp = ? AND used = 0 AND expiry > NOW() ORDER BY reset_id DESC LIMIT 1");
        $stmt->bind_param("ss", $email, $entered_otp);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows == 1) {
            $reset_row = $result->fetch_assoc();
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

            // Update the admin's password
            $update = $conn->prepare("UPDATE Admins SET password = ? WHERE email = ?");
            $update->bind_param("ss", $hashed_password, $email);
            $update->execute();

            // Mark this OTP as used so it can't be reused
            $mark_used = $conn->prepare("UPDATE PasswordResets SET used = 1 WHERE reset_id = ?");
            $mark_used->bind_param("i", $reset_row['reset_id']);
            $mark_used->execute();

            $success = "Password has been reset successfully! You can now log in.";
        } else {
            $error = "That code is invalid, expired, or already used";
        }
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
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --grey:#8A8A8A; --line:#E7E0D3; --success:#5AA89A; }
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
    <p class="sub">Enter the code sent to your email and a new password</p>

    <?php if ($demo_otp != '') { ?>
      <div class="demo-box">Demo Mode: your code is <strong><?php echo $demo_otp; ?></strong> (in production this arrives by email via PHPMailer/SMTP)</div>
    <?php } ?>

    <form action="reset_password.php" method="POST">
      <input type="email" name="email" placeholder="Your Email" value="<?php echo htmlspecialchars($email_prefill); ?>" required>
      <input type="text" name="otp" placeholder="Enter 6-digit Code" required>
      <input type="password" name="new_password" placeholder="New Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm New Password" required>
      <button type="submit">Reset Password</button>
      <?php if ($error != "") { ?><p class="error-msg"><?php echo $error; ?></p><?php } ?>
      <?php if ($success != "") { ?><p class="success-msg"><?php echo $success; ?></p><?php } ?>
    </form>

    <div class="links"><a href="login.php">&larr; Back to Sign In</a></div>
  </div>
</body>
</html>
