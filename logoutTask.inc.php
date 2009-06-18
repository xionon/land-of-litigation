<?php
    /*
        This file actually does more than what it's name implies.  Origionally, it was just the logout task, but
        I slowly added functions to the main switch, and it just kinda grew.  It was easier than going back and 
        adding 'include_whatever' to all my files, anyway.
        
        As of right now, it handles pretty much all the '_GET' commands.  It should be included on the top of 
        every page, as it also unserializes the user object for use in the page.
    */

	//This snippet of code checks to be sure the user has logged in; if not, they are redirected to the login page.
	if (!isset($_SESSION["user"])) {
		header("Refresh: 0; URL=login.php");
	}
	$goldSpent = 0-1;
	
	//This part unserializes the user
	$localuser = unserialize($_SESSION["user"]);
	//added this code 12-1 to try and get around the log-back-in bug...
	if ($localuser->isLoggedIn() == false) {
        unset ($localuser);
        unset ($_SESSION["user"]);
        header ("Refresh: 0; URL=index.php");
	}

    //latest changes: 
    //removed line breaks before {'s
    //added case: "equip" section
	//If there is a task set, perform it (i.e., logout)
	if (isset($_GET["task"])) {
		switch ($_GET["task"]) {
            case "logout": {
                $localuser->log_out();
                $_SESSION["user"] = serialize($localuser);
                unset($localuser);
                unset($_SESSION["user"]);
                session_write_close();
                header("Refresh:0; URL=index.php");
                break;
            }
            case "rest": {
                $localuser->rest();
                break;
            } 
            case "equip": {
                if (!isset ($_GET['itemid']))
                    break;
                else {
                    $localuser->equip($_GET['itemid']);
                }
                break;
            }
            case "unequip": {
                if (!isset ($_GET['itemid']))
                    break;
                else {
                    $localuser->unequip($_GET['itemid']);
                }
                break;
            }
            case "usePotion":
            {
                if (!isset ($_GET['itemid']))
                    break;
                else {
                    $hpchange = $localuser->usePotion($_GET['itemid']);
                }
                break;
            }
            case "sell":
            {
                if (!isset ($_GET['itemid']))
                    break;
                else {
                    $goldEarned = $localuser->sell($_GET['itemid']);
                }
                break;
            }
            case "buy":
            {
                if (!isset ($_GET['itemid']))
                    {break;}
                else {
                    $goldSpent = $localuser->buy($_GET['itemid']);
                }
            }
        }
	}
?>