<?php	session_start();	include_once("user.inc.php");	if ( isset($_SESSION["user"]) )		{		header("Refresh: 0; URL=userProfile.php");	}	if ( isset( $_POST["username"], $_POST["password"], $_POST["verify_password"], $_POST["email"], $_POST["class"] ) )		{        		$thisPass = $_POST["password"];        		$thisPassVerify = $_POST["verify_password"];		if ( $thisPass == $thisPassVerify )		{			            $newuser = new user("", "");            if ( !$newuser->register ($_POST["username"], $thisPass, $_POST["class"], $_POST["email"] ) )			{				echo "Some Error in creating your character!  Be sure all fields are filled out and that your user name has not been used already.";            			}			else			{                $_SESSION["user"] = serialize ($newuser);                session_write_close();                header("Refresh: 0; URL=userProfile.php");            }					}	}	else 	{?><html><head><title>Register a new account with me!  Please, I'm begging you!</title></head><body>Please fill out <em>all</em> information!    	<form action="newAccount.php" method="post">        Username: <input type="text" name="username" /><br/>        Password: <input type="password" name="password" /><br />		Verify Password: <input type="password" name="verify_password" /><br />		email address: <input type="text" name="email" /><br />		<div id="ChooseZombie">		<input type="radio" name="class" value="1">Zombie<br />		<p>The zombie uses his brute streinght to defeat his enemies.  He's not very dexterous.</p>		</div>		<div id="ChoosePirate">		<input type="radio" name="class" value="2">Pirate<br />		<p>Pirates use both their streingth and their dexterity when fighting.  They are neither very strong nor very dexterous.</p>		</div>		<div id="ChooseNinja">		<input type="radio" name="class" value="3">Ninja<br />		<p>Ninjas use their dexterity to fight their enemies.  They are usually not very strong.  They have been disgraced recently, as robots are now everyone's enemies, and robots are obviously related to ninjas.</p>		</div>        <input type="submit" name="submit" value="Register!" />    </form></body></html><?php 	} ?>