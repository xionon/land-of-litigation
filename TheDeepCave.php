<?php
	session_start();
    $thisSite = "The_Deep_Cave";
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	include_once('adventure.inc.php');
	include_once('adventurepage.inc');
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>Adventure in The Dark Forest</title>
</head>
<body>
<?php
    if ($localuser->get_CurrentHP() > 0) echo $localadventure->showAdventure();
    echo $msg['message'];
    echo $msg['response'];
    echo "<br/>" . $commands;
?>
</body>
</html>
