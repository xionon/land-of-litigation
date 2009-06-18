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
<head>
<title>Please login to the game.</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
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