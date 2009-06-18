<?php
	session_start();
	$thisSite = "Town";
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	
	$level = $localuser->get_Level();
	$localuser->move($thisSite);
	
	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>The Town tourisim board</title>
</head>

<body>
<?php echo outputSidebar($thisSite);?>
<p>Welcome to town.  The town has recently been attacked by robots, so many of our facilities have been damaged.</p>
<p>Feel free to rest here; this is the only area in this region you can rest in.  Resting will restore some of your hitpoints, but as you gain levels, the percentage of your maximum hit points restored per rest will decrease.</p>
<?php
if ($level < 15)
    echo "The choo-choo to the neighboring regions is damaged.<br/>";
else
    echo "Sorry, the choo-choo's not built yet!  When it is, it will transport you to a different directory, complete with new areas, monsters, and maybe even a quest or two, if THOSE ever get implemented<br/>";
?>
There is some <a href="shop.php">shopping</a>, if you're interested.

</body>
</html>
