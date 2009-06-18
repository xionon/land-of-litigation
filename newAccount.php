<?php	
	session_start();	
	include_once("user.inc.php");	
    //changed 12/1 to get around log-back-in bug
	if ( isset($_SESSION["user"]) )
	{
        $localuser = unserialize ($_SESSION["user"]);
        if ($localuser->isLoggedIn() == true)
            header("Refresh: 0; URL=userProfile.php");
	}	
	if ( isset( $_POST["username"], $_POST["password"], $_POST["verify_password"], $_POST["email"], $_POST["class"] ) )
	{        		
		$thisPass = $_POST["password"];
     		$thisPassVerify = $_POST["verify_password"];
		if ( $thisPass == $thisPassVerify )
		{
	            $newuser = new user("", "");
	            if ( !$newuser->register ($_POST["username"], $thisPass, $_POST["class"], $_POST["email"] ) )
			{
				echo "Some Error in creating your character!  Be sure all fields are filled out and that your user name has not been used already.";
      		}			
			else	
			{
    				$_SESSION["user"] = serialize ($newuser);
		            session_write_close();                
				header("Refresh: 0; URL=userProfile.php");            
			}
		}	
	}	
	else 	
	{
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1"><link rel="stylesheet" type="text/css" href="style.css" /><title>Register a new account with me!  Please, I'm begging you!</title></head>
<body>Please fill out <em>all</em> information!
    	<form action="newAccount.php" method="post">
        <table border="0">
          <tr><td>Username:</td> <td><input type="text" name="username" /></td></tr>
          <tr><td>Password:</td> <td><input type="password" name="password" /></td></tr>
		  <tr><td>Verify Password:</td> <td><input type="password" name="verify_password" /></td></tr>
		  <tr><td>email address:</td> <td><input type="text" name="email" /></td></tr>
		</table>
		<div id="ChooseClass">
		<div id="ChooseZombie">
            <img src="images/zombieface.jpg" /><br/>
            <input type="radio" name="class" value="1"><strong>Zombie</strong><br />
            The zombie uses his brute streinght to defeat his enemies.  He's not very dexterous.<br/>
		</div>
		
		<div id="ChoosePirate">
            <img src="images/pirateface.jpg" /><br/>
            <input type="radio" name="class" value="2"><strong>Pirate</strong><br />
            Pirates use both their streingth and their dexterity when fighting.  Pirates have no great streinghts or weaknesses, and can use any weapon or armor.  They are neither very strong nor very dexterous.<br/>
		</div>
		
		<div id="ChooseNinja">
            <img src="images/ninjaface.jpg" /><br/>
            <input type="radio" name="class" value="3"><strong>Ninja</strong><br />
            Ninjas use their dexterity to fight their enemies.  They are usually not very strong.  Ninjas can attack twice per round.  They have been disgraced recently, as robots are now everyone's enemies, and robots are obviously related to ninjas.<br/>
		</div>
		</div>
        <input type="submit" name="submit" value="Register!" />
    </form>
  </body>
</html>
<?php 	} ?>