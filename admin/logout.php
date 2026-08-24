<?php
session_start();
session_destroy(); // sab session data clear kar do
header("Location: login.php");
exit();
?>
