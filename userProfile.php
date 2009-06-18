<?php
	session_start(); 
    $thisSite = "userProfile";
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	
	if (isset($_POST["inventory"]))
	{
		$invTable = $localuser->get_PlayerInventory($_POST["inventory"]);
	}
	

    $sidebar = outputSidebar($thisSite);
	$_SESSION["user"] = serialize($localuser);
	session_write_close();
?>

<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title><?php echo $localuser->get_username(); ?>'s Profile Page</title>
</head>

<body>
    <h1>User Control panel</h1>
	<?php 
	    echo $sidebar;
        if (isset($hpchange)) echo "HP went up by {$hpchange}";
	?>
	<br/>    
	<?php echo $localuser->getUserInfoTable(); ?>	
    <form action="userProfile.php" method="POST" id="filter-inventory">
		display which inventory:
		<input type="submit" name="inventory" value="all"/>
		<input type="submit" name="inventory" value="weapon"/>
		<input type="submit" name="inventory" value="armor"/>
		<input type="submit" name="inventory" value="accessory"/>
	</form>
	<?php 
	if (isset($invTable)) echo $invTable; unset($invTable);
	?>
</body>
</html>