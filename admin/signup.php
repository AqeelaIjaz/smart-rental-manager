<?php
session_start();
include 'db_connect.php';

$error = "";
$success = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $email = $_POST['email'];
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    if ($password !== $confirm_password) {
        $error = "Password and Confirm Password do not match";
    } else {
        // Check if this email/phone is already registered in Admins
        $check = $conn->prepare("SELECT admin_id FROM Admins WHERE email = ? OR phone = ?");
        $check->bind_param("ss", $email, $phone);
        $check->execute();
        $check_result = $check->get_result();

        if ($check_result->num_rows > 0) {
            $error = "This email or phone is already registered as an admin";
        } else {
            // NEW/UPDATED: Admins table columns: admin_id, name, email, phone, password, status
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $status = "active";

            $stmt = $conn->prepare("INSERT INTO Admins (name, email, phone, password, status) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param("sssss", $name, $email, $phone, $hashed_password, $status);

            if ($stmt->execute()) {
                $success = "Admin account created! You can now log in.";
            } else {
                $error = "Something went wrong, please try again";
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<title>Admin Sign Up - Smart Rental Manager</title>
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root{ --primary:#1F5A52; --primary-dark:#17423C; --grey:#8A8A8A; --line:#E7E0D3; --success:#5AA89A; }
  *{ box-sizing:border-box; margin:0; padding:0; }
  body{
    font-family:'Poppins', sans-serif;
    background: linear-gradient(135deg, var(--primary), var(--primary-dark));
    min-height:100vh; display:flex; align-items:center; justify-content:center;
    padding:30px 0;
  }
  .auth-card{ width:380px; background:#fff; border-radius:18px; padding:36px; text-align:center; box-shadow:0 20px 50px rgba(0,0,0,.25); }
  .logo{ width:56px; height:56px; border-radius:14px; background:var(--primary); margin:0 auto 16px; display:flex; align-items:center; justify-content:center; color:#fff; font-size:22px; font-weight:700; }
  .auth-card h2{ font-size:19px; color:var(--primary-dark); margin-bottom:4px; }
  .auth-card p.sub{ font-size:12px; color:var(--grey); margin-bottom:22px; }
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
    <h2>Admin Sign Up</h2>
    <p class="sub">Create a new admin account</p>

    <form action="signup.php" method="POST">
      <input type="text" name="name" placeholder="Full Name" required>
      <input type="text" name="phone" placeholder="Phone Number" required>
      <input type="email" name="email" placeholder="Email Address" required>
      <input type="password" name="password" placeholder="Password" required>
      <input type="password" name="confirm_password" placeholder="Confirm Password" required>
      <button type="submit">Sign Up</button>
      <?php if ($error != "") { ?><p class="error-msg"><?php echo $error; ?></p><?php } ?>
      <?php if ($success != "") { ?><p class="success-msg"><?php echo $success; ?></p><?php } ?>
    </form>

    <div class="links">Already have an account? <a href="login.php">Sign In</a></div>
  </div>
</body>
</html>
