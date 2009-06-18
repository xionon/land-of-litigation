<?php
	$localuser->move($thisSite);
    $msg = "";

    if (!isset($_SESSION['adventure']))
        $localadventure = new adventure(1,($localuser->get_Level+5));
    else $localadventure = unserialize($_SESSION['adventure']);

    if (isset($_POST['adventurecommand']))
    {
        $localstats = $localuser->get_Stats();
        $msg = $localadventure->performCommand($_POST['adventurecommand'],$localstats);
    }

    $commands = "<br/><table border=\"1\"><tr><td colspan=\"10\">what do you do?</td></tr>";
    if ($localadventure->getType() == 1 && $localadventure->isCompleted()==false)
    {
        $commands .= "<tr><td>There is nothing for you to do.  The " .
        "adventure is complete.</td></tr>";
    
        $localadventure->complete();
    }
    else if ($localadventure->getType() == 2 && $localadventure->isCompleted()==false)
    {
        //setup fight commands
        $commands .= "<tr><td><form action=\"{$thisSite}.php\" method=\"POST\">" . 
        "<input type=\"submit\" name=\"adventurecommand\" value=\"Attack\"></td>" . 
        "<td><input type=\"submit\" name=\"adventurecommand\" value=\"Run\"></td></tr>";
        $commands .="</table>";
    }
    if ($localadventure->isCompleted()==true)
    {
        $reward = array();
        $reward = $localadventure->getReward();
        $localuser->addItem($reward['item']);
        $localuser->addGold($reward['gold']);
        $localuser->addExp($reward['experience']);
        $commands .= "<tr><td><a href=\"{$thisSite}.php\">Adventure again!</a></td></tr>";
    }
    $commands .="</table>";
    if ($localadventure->isCompleted() == false) 
        $_SESSION['adventure'] = serialize($localadventure);
    else 
        unset($_SESSION['adventure']);
	$_SESSION['user'] = serialize($localuser);
	session_write_close();
?>