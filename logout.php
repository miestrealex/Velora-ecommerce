<?php

/*Logout User*/
session_start();
session_destroy();
header("Location: index.php");
exit();
?>
