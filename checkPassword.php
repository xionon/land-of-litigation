<?php
session_start();
require_once 'user.inc.php';
if (!isset($_POST['username'], $_POST['password']) || !is_string($_POST['username']) || !is_string($_POST['password'])) {
    header('Location: login.php'); exit;
}
$localuser = new user($_POST['username'], $_POST['password']);
if ($localuser->isLoggedIn()) {
    session_regenerate_id(true);
    unset($_SESSION['adventure']);
    $_SESSION['user'] = serialize($localuser);
    session_write_close();
    header('Location: userProfile.php'); exit;
}
http_response_code(401);
echo 'There is a problem with your username or password. <a href="login.php">Try again</a>.';
