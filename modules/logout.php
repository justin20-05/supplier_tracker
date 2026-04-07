<?php
session_start();
session_unset();
session_destroy();

// Step out of /modules to reach the main login page
header("Location: ../index.php");
exit();