<?php
	session_start();
	//include_once("conn.inc");
	//include_once("item.inc.php");
	include_once("user.inc.php");
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>Untitled Document</title>
</head>

<body>
This is a test!<br/>
TESTCASE LOL<br/>
<form action = "checkPassword.php" method = "post">
    <input type = "hidden" name = "username" value = "dill" />
    <input type = "hidden" name = "password" value = "password" />
    <input type="submit" name="submit" value="login" />
</form>
<?php
    $localuser = new user('dill','password');
    echo $localuser->isLoggedIn();
?>

</body>
</html>
