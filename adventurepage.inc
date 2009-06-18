<?php

    if ($localuser->get_CurrentHP() <= 0)
    {
  		header("Refresh:0; URL=userProfile.php");
    }
    $localuser->move($thisSite);
    $ts = str_replace("_","",$thisSite);
    $msg = array();

    if (!isset($_SESSION['adventure'])) {
        $localadventure = new adventure($thisSite,($localuser->get_Level+5));
	}
    else {
		$localadventure = unserialize($_SESSION['adventure']);
	}
	
    if (isset($_POST['adventurecommand']))
    {
        $localstats = $localuser->get_Stats();
        $msg = $localadventure->performCommand($_POST['adventurecommand'],$localstats);
        $localuser->looseHP($msg['damage']);
    }

    $hp = $localuser->get_CurrentHP();
    $commands = "<br/><table border=\"1\"><tr><td colspan=\"10\">what do you do?</td></tr>";
    $commands .= "<tr><td colspan=\"10\">hp left: {$hp}</td></tr>";

    if ($localadventure->getType() == 1 && $localadventure->isCompleted()==false)
    {
//        $commands .= "<tr><td>There is nothing for you to do.  The " .
//        "adventure is complete.</td></tr>";
		$msg = $localadventure->testAdventure($localuser->get_Stats());

    }

    else if ($localadventure->getType() == 2 && $localadventure->isCompleted()==false && $localuser->get_CurrentHP() > 0)
    {
        //setup fight commands
        $commands .= "<tr><td><form action=\"{$ts}.php\" method=\"POST\">" . 
        "<input type=\"submit\" name=\"adventurecommand\" value=\"Attack\"></td>" . 
        "<td><input type=\"submit\" name=\"adventurecommand\" value=\"Run\"></td></tr></form>" . 
        "<tr><td><form action=\"{$ts}.php\" method=\"GET\"><input type=\"hidden\" name=\"task\" value=\"usePotion\">" . $localuser->get_PlayerInventory("potionlist") . "<input type=\"submit\" value=\"Use\"></form></td></tr>";
    }
    
    if ($localadventure->isCompleted()==true && $localuser->get_CurrentHP() > 0)
    {
        $reward = array();
        $reward = $localadventure->getReward();
        if ($reward['item']!= 0) {
            $localuser->addItem($reward['item']);
            $commands .= "<tr><td>You got an item!</td></tr>";
        }
        if ($reward['gold'] != 0) {
            $localuser->addGold($reward['gold']);
            $commands .= "<tr><td>You got {$reward['gold']} gold!</td></tr>";
        }
        if ($reward['experience'] != 0) {
            $localuser->addExp($reward['experience']);
            $commands .= "<tr><td>You earned {$reward['experience']} experience points.</td></tr>";
            }
        $commands .= "<tr><td><a href=\"{$ts}.php\">Adventure again at {$ts}!</a></td></tr>";
        $commands .= "<tr><td><a href=\"mainMap.php\">Back to main map</a></td></tr>";
        $commands .= "<tr><td><a href=\"userProfile.php\">Go to user profile</a></td></tr>";
    }
    
    if ($localuser->get_CurrentHP() <= 0)
    {
        $commands .= "<tr><td>You have been knocked out!</td></tr> <tr><td><a href=\"Town.php\">Click here to go back to town, or wait 5 seconds.</a>";
  		header("Refresh:5; URL=Town.php");
        $localadventure->complete();
    }

    $commands .="</table>";
    
    if ($localadventure->isCompleted() == false && $localuser->get_CurrentHP() > 0) 
        $_SESSION['adventure'] = serialize($localadventure);
    else 
        unset($_SESSION['adventure']);
	$_SESSION['user'] = serialize($localuser);
	session_write_close();

?>
