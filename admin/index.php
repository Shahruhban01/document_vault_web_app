<?php
session_start();
require_once '../classes/AdminAuth.php';

$auth = new AdminAuth();

if (!$auth->isAdminAuthenticated()) {
    header('Location: login.php');
    exit;
}

include 'dashboard.php';
?>
