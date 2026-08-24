<?php
session_start();
include 'db_connect.php';

$error = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $identifier = $_POST['identifier']; // email ya phone, dono chalega
    $password = $_POST['password'];

    // NOTE: Abhi ke liye simple check hai (asal database query baad mein aayegi)
    // Real query jaisi hogi:
    // $result = $conn->query("SELECT * FROM admins WHERE (email='$identifier' OR phone='$identifier') AND password='".md5($password)."'");

    if (($identifier == "03001234567" || $identifier == "admin@srm.com") && $password == "admin123") {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_name'] = "Aqeela";
        header("Location: dashboard.php");
        exit();
    } else {
        $error = "Email/Phone ya password ghalat hai";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Sign In - Smart Rental Manager</title>
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
  .auth-card p.sub{ font-size:12px; color:var(--grey); margin-bottom:24px; }
  .auth-card input{ width:100%; padding:12px 14px; border:1.5px solid var(--line); border-radius:10px; margin-bottom:12px; font-size:13px; font-family:'Poppins'; color:#222; }
  .auth-card input:focus{ outline:none; border-color:var(--primary); }
  .auth-card button{ width:100%; padding:13px; border:none; border-radius:10px; background:var(--primary); color:#fff; font-weight:700; font-size:14px; margin-top:6px; cursor:pointer; }
  .auth-card button:hover{ background:var(--primary-dark); }
  .error-msg{ color:#C96A56; font-size:11.5px; margin-top:10px; }
  .links{ margin-top:18px; font-size:12px; color:var(--grey); }
  .links a{ color:var(--primary); font-weight:600; text-decoration:none; }
  .forgot{ text-align:right; font-size:11.5px; margin:-4px 0 14px; }
  .forgot a{ color:var(--primary); text-decoration:none; }
  .foot{ font-size:10.5px; color:var(--grey); margin-top:16px; }
</style>
</head>
<body>
  <div class="auth-card">
    <div class="logo">SRM</div>
    <h2>Sign In</h2>
    <p class="sub">Smart Rental Manager &mdash; Admin Panel</p>

    <form action="signin.php" method="POST">
      <input type="text" name="identifier" placeholder="Email or Phone Number" required>
      <input type="password" name="password" placeholder="Password" required>
      <div class="forgot"><a href="forgot_password.php">Forgot Password?</a></div>
      <button type="submit">Sign In</button>
      <?php if ($error != "") { ?>
        <p class="error-msg"><?php echo $error; ?></p>
      <?php } ?>
    </form>

    <div class="links">Don't have an account? <a href="signup.php">Sign Up</a></div>
    <div class="foot">Test login: admin@srm.com / admin123</div>
  </div>
</body>
</html>
