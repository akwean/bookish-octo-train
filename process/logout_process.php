<?php

require_once $_SERVER['DOCUMENT_ROOT'] . '/src/Controllers/UserController.php';

$userController = new UserController();
$userController->logout();

header("Location: /index.php");
exit();
?>