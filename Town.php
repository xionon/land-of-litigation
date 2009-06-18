<?php
	session_start();
	$thisSite = "Town";
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	
	$localuser->move($thisSite);
	
	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>Untitled Document</title>
</head>

<body>
Welcome to town.  What do you want to do?
<br/>
<?php
echo outputSidebar($thisSite) . "<br/>";
echo $localuser->getUserInfoTable();
?>
</body>
</html>
