<?php
session_start();

include_once('user.inc.php');

if ( isset( $_SESSION['user'] ) )
{
	$localuser = unserialize( $_SESSION['user'] );
	if ( $localuser->isLoggedIn() )
	{
		session_write_close();
		header ("Refresh: 0; URL=userProfile.php");
	}
	elseif ( !$localuser->isLoggedIn() )
	{
		unset ($localuser, $_SESSION['user']);
	}
}


?><html>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
<link rel="stylesheet" type="text/css" href="style.css" />
</head>

<body>
    <form action="checkPassword.php" method="post">
        Username: <input type="text" name="username" />&nbsp;||&nbsp;
        Password: <input type="password" name="password" />
        <input type="submit" name="submit" value="Login" />
    </form>
</body>
</html>
<?php

?>