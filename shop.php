<?php
	session_start();
	$thisSite = "Town";
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	
	$level = $localuser->get_Level();
    $currGold = $localuser->get_Gold();
	
	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>The Town Store</title>
</head>

<body>
<?php
 echo outputSidebar($thisSite);
 echo $localuser->get_PlayerInventory(selllist);
 echo "<strong>" . $localuser->get_Gold() . "</strong> Gold Remaining<br/>";

 if ($goldSpent > 0) echo "Spent {$goldSpent} gold<br/>"; else if ($goldSpent == 0) echo "Not enough gold!";
 echo "<br/><br/>"; 
 $toshow = $localuser->get_PlayerBuylist();
 echo $toshow;
?>


</body>
</html>