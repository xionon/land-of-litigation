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
		header("Location: login.php"); exit;
	}
	$goldSpent = 0-1;

	//This part unserializes the user
	$localuser = unserialize($_SESSION["user"]);
	//added this code 12-1 to try and get around the log-back-in bug...
	if ($localuser->isLoggedIn() == false) {
        unset ($localuser);
        unset ($_SESSION["user"]);
        header("Location: index.php"); exit;
	}

    $localuser->refresh();
    if (!$localuser->isLoggedIn()) { unset($_SESSION['user'], $_SESSION['adventure']); header('Location: login.php'); exit; }
    //latest changes:
    //removed line breaks before {'s
    //added case: "equip" section
	//If there is a task set, perform it (i.e., logout)
	if (isset($_GET["itemid"]) && (!is_string($_GET["itemid"]) || !ctype_digit($_GET["itemid"]) || (int)$_GET["itemid"] < 1)) { http_response_code(400); exit("Invalid item ID."); }
	if (isset($_GET["task"])) {
		switch ($_GET["task"]) {
            case "logout": {
                $_SESSION = [];
                if (ini_get('session.use_cookies')) {
                    $params = session_get_cookie_params();
                    setcookie(session_name(), '', ['expires' => time() - 42000, 'path' => $params['path'], 'httponly' => true, 'samesite' => 'Lax']);
                }
                session_destroy();
                header('Location: index.php'); exit;
            }
            case "rest": {
                if ($localuser->rest()) {
                    unset($_SESSION['adventure']);
                    $_SESSION['user'] = serialize($localuser);
                    session_write_close();
                    header('Location: Town.php'); exit;
                }
                http_response_code(409);
                exit('No turns remaining.');
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
