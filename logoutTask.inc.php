<?php
	//This snippet of code checks to be sure the user has logged in; if not, they are redirected to the login page.
	if (!isset($_SESSION["user"])) {
		header("Refresh: 0; URL=login.php");
	}
	
	//This part unserializes the user
	$localuser = unserialize($_SESSION["user"]);

    //latest changes: 
    //removed line breaks before {'s
    //added case: "equip" section
	//If there is a task set, perform it (i.e., logout)
	if (isset($_GET["task"])) {
		switch ($_GET["task"]) {
            case "logout": {
                $localuser->log_out();
                unset($localuser);
                unset($_SESSION["user"]);
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
        }
	}
?>