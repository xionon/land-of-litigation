<?php
	session_start();
	include_once('user.inc.php');
	include_once('sidebar.inc.php');
	include_once('logoutTask.inc.php');
	$thisSite = "userProfile";
	
	if (isset($_POST["inventory"]))
	{
		$invTable = $localuser->get_PlayerInventory($_POST["inventory"]);
	}
	

	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>

<html>
<head>
<title><?php echo $localuser->get_username(); ?>'s Profile Page</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
</head>

<body>
	Welcome <?php echo $localuser->get_username(); ?> the <?php echo $localuser->get_PlayerClass(); ?>.
	<br/>
	<br/>
	<!--USER INFO TABLE-->
	<table id="character_sheet">
		<tr><td>Character Name:</td> <td><?php echo $localuser->get_username(); ?></td></tr>
		<tr><td>Level: </td><td><?php echo $localuser->get_Level(); ?></td></tr>
		<tr><td>Total Experience:</td><td><?php echo $localuser->get_Experience(); ?></td></tr>
		<tr><td>Streingth:</td><td><?php echo $localuser->get_Str(); ?></td></tr>
		<tr><td>Dexterity:</td><td><?php echo $localuser->get_Dex(); ?></td></tr>
		<tr><td>Hit Points:</td><td><?php 
		$currhp = $localuser->get_CurrentHP(); 
		$maxhp = $localuser->get_MaxHP(); 
		echo "{$currhp} / {$maxhp}"; ?></td></tr>
		<tr><td>Turns Remaining:</td><td><?php echo $localuser->get_Turns(); ?></td></tr>
	</table>
	<form action="userProfile.php" method="POST" id="filter-inventory">
		display which inventory:
		<input type="submit" name="inventory" value="all"/>
		<input type="submit" name="inventory" value="weapon"/>
		<input type="submit" name="inventory" value="armor"/>
		<input type="submit" name="inventory" value="accessory"/>
	</form>
	<?php 
	if (isset($invTable)) echo $invTable;
	outputSidebar($thisSite); ?>
</body>
</html>