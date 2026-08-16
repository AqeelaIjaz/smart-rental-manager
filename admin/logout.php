<?php
session_start();
session_destroy(); // sab session data clear kar do
header("Location: signin.php");
exit();
?>
