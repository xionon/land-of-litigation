<?php
	session_start();
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	include_once('adventure.inc.php');
	$thisSite = "TheBlueRiver";
	include_once('adventurepage.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<title>Adventure in The Blue River</title>
</head>
<body>
<i>So far so good...</i>
<?php
    echo $localadventure->showAdventure();
    echo $msg;
    echo "<br/>" . $commands;
?>
</body>
</html>
