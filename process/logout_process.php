<?php

require_once '../src/Controllers/UserController.php';

$userController = new UserController();
$userController->logout();

header("Location: /index.php");
exit();
?>