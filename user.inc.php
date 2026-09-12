<?php
//LAST UPDATED 12-10-05
include_once ("conn.inc");
include_once ("inventory.inc.php");
/************************************************************************************
Object oriented user management
When a user logs in to the site, a new USER object is created and stored in their 
session.  This USER object contains all information about the user themselves - i.e., 
all information stored in the database is put into the object.  This reduces the number 
of database calls I'll need to make in the program.

I got this idea from a script I downloaded, modified it to suit my needs.  Not sure 
if object oriented was the way to go for user management, but wanted to exparament 
with it, anyway.

Passwords are stored as PHP password hashes and never serialized into sessions.
*************************************************************************************/
class user 
{
    //The user's password should not be stored in this object... if they passed
    //the initial login, why would it need to be stored?
	    
	var $user_name = "";
	var $logged_in = false;
	var $permissions = 0;
	var $email = "";
	var $numericClass = 0;
	var $PlayerClass = "";
	var $gold = 0;
	var $experience = 0;
	var $str = 1;
	var $dex = 1;
	var $hp = 1;
	var $maxHP = 1;
	var $turns = 0;
	var $timeOfLastTurn;
	var $timeOfLastLogin;
	var $isUnc = false;
	var $numLoc = 0;
	var $myInventory;
	var $attacks = 1;
	var $calculated = 0;
        
    //Initialization function
    function __construct($user,$pass)
    {
        if ($user != "" && $pass != "")	$this->log_in($user,$pass);
        return $this->logged_in;
    }

    function refresh()
    {
        $row = db_query('SELECT * FROM users WHERE username = ?', [$this->user_name])->fetch();
        if (!$row) { $this->log_out(); return; }
        foreach (['gold' => 'gold', 'hp' => 'hp', 'experience' => 'experience', 'turns' => 'turns', 'numLoc' => 'location'] as $property => $column) {
            $this->$property = $row[$column];
        }
        $this->myInventory = new inventory($this->user_name, $this->numericClass);
        $this->calculateStats();
    }

    function get_calculated()

    {
        return "Calculated " . $this->calculated . " times";
    }

    function get_username()
    {
        if($this->user_name != "")
		{
			return $this->user_name;
		}
        
    }
	
	function get_Turns()
	{
		return $this->turns;
	}

	function get_Level()
	{
		//players earn a new level for every 100 experience points
		return (floor($this->experience / 100) + 1);
	}
	
	function get_Experience()
	{
		return $this->experience;
	}
	
	function get_PlayerClass()
	{
		return $this->PlayerClass;
	}
	
	function get_Str()
	{
		return $this->str;
	}
    
	function get_Dex()
	{
		return $this->dex;
	}

	function get_lastTurnTime()
	{
		return $this->timeOfLastTurn;
	}

	function get_lastLoginTime()
	{
		return $this->timeOfLastLogin;
	}
	
	function get_CurrentHP()
	{
		return $this->hp;
	}

	function get_MaxHP()
	{
		return $this->maxHP;
	}
	
	function get_Location()
	{
		/*
			1 = Town
			2 = TheBlueRiver
			3 = TheDarkForest
			4 = TheDeepCave
		*/
		switch ($this->numLoc)
		{
			case 1:
				return "Town";
				break;
			case 2:
				return "TheBlueRiver";
				break;
			case 3:
				return "TheDarkForest";
				break;
			case 4:
				return "TheDeepCave";
				break;
		}
		return "Town";
	}
	
	function get_PlayerInventory($whichInventory)
	{
		$toReturn = $this->myInventory->showInventory();
		switch ($whichInventory)
		{
			case "all":
				$toReturn = $this->myInventory->showInventory();
				break;
			case "weapon":
				$toReturn = $this->myInventory->showWeaponInventory();
				break;
			case "armor":
				$toReturn = $this->myInventory->showArmorInventory();
				break;
			case "accessory":
				$toReturn = $this->myInventory->showAccessoryInventory();
				break;
            case "potionlist":
                $toReturn = $this->myInventory->showPotionForm();
                break;
            case "selllist":
                $toReturn = $this->myInventory->showSellList();
                break;
        }
		return $toReturn;
	}

    function move($moveTo)
    {
        $row = db_query('SELECT location FROM places WHERE name = ?', [str_replace('_', ' ', $moveTo)])->fetch();
        if (!$row) return false;
        $updated = db_query('UPDATE users SET turns = turns - 1, location = ? WHERE username = ? AND turns > 0', [$row['location'], $this->user_name]);
        if (!$updated->rowCount()) return false;
        $this->numLoc = $row['location'];
        $this->turns = db_query('SELECT turns FROM users WHERE username = ?', [$this->user_name])->fetchColumn();
        return true;
    }


    function rest()
    {
        // Rest uses one turn, including when already in Town.
        if (!$this->move('Town')) return false;
        $this->addHP(round(($this->maxHP * .5) / $this->get_Level()));
        return true;
    }


	function calculateTurns()
	{
	 	//Give a player 40 turns every 24 hours
		//first, calculate number of hours since last login
				
		$timeDiff = $this->timeOfLastLogin - $this->timeOfLastTurn;
//		$daysDiff = floor((($timeDiff / 60) / 60) / 24);
		$daysDiff = floor(($timeDiff / 60) / 60); //for testing purposes, time to get turns lowered drastically
		if ($daysDiff > 1)
		{
			$this->turns += $daysDiff * 10;
			$this->timeOfLastTurn = $this->timeOfLastLogin;
			$this->addHP($daysDiff * ($this->maxHP * .25));
		}
		if ($this->turns > 40)
			$this->turns = 40;
	}
	
	function addHP ($hpToAdd)
	{
		$this->hp += (int) round($hpToAdd);
		if ($this->hp > $this->maxHP)
		{
			$this->hp = $this->maxHP;
		}
		db_query("UPDATE users set hp=? where username = ?", [$this->hp, $this->user_name]) or die(" hp {this->hp}, {$this->user_name}");
	}
	
	function looseHP ($hpToLoose)
	{
		$this->hp -= $hpToLoose;
		if ($this->hp <= 0)
		{
			$this->hp = 0;
			$this->playerDies();
		}
		db_query("UPDATE users set hp=? where username = ?", [$this->hp, $this->user_name]) or die(" hp");
	}
	
    function buy($itemID)
    {
        db()->beginTransaction();
        try {
            $row = db_query('SELECT * FROM items WHERE itemID = ? AND itemtype <> 10', [$itemID])->fetch();
            if (!$row) { db()->rollBack(); return 0; }
            $spent = (int) round((max($row['strAdded'] * 3, 0) + max($row['dexAdded'] * 3, 0) + max($row['hpAdded'], 0) + $row['isWearable'] * 25) * 1.15);
            $updated = db_query('UPDATE users SET gold = gold - ? WHERE username = ? AND gold >= ?', [$spent, $this->user_name, $spent]);
            if (!$updated->rowCount()) { db()->rollBack(); return 0; }
            $this->myInventory->addItem($itemID);
            $this->gold = db_query('SELECT gold FROM users WHERE username = ?', [$this->user_name])->fetchColumn();
            db()->commit();
            return $spent;
        } catch (Throwable $error) {
            if (db()->inTransaction()) db()->rollBack();
            throw $error;
        }
    }


    function addItem($itemID)
    {
        return $this->myInventory->addItem($itemID);
    }


	function equip ($itemID)
	{
        if ( $this->myInventory->equip($itemID) ) {
            $this->calculateStats();
            return true;
        }
        else {
            $this->calculateStats();
            return false;
        }
	}
	
    function unequip ($itemID)
    {
        if ( $this->myInventory->unequip($itemID) ) {
            $this->calculateStats();
            return true;
        }
        else {
            $this->calculateStats();
            return false;
        }
    }

	function addGold ($g)
	{
        $this->gold += $g;
        db_query("UPDATE users set gold=? where username = ?", [$this->gold, $this->user_name]) or die("gold update error");
	}

    function spendGold ($g)
    {
        $this->gold -= $g;
        db_query("UPDATE users set gold=? where username = ?", [$this->gold, $this->user_name]) or die("gold update error");
    }

    function get_Gold()
    {
        return $this->gold;
    }
	
	function addExp ($e)
	{
        $this->experience += $e;
        db_query("UPDATE users set experience=? where username = ?", [$this->experience, $this->user_name]) or die("exp update error");
        $this->calculateStats();
	}
	
	function playerDies()
	{
		//erm...
		$this->isUnc = true;
	}
	
	function isPlayerUnc()
	{
        return $this->isUnc;
	}
	
    function isLoggedIn()
    {
        if ($this->logged_in == true) { return true; }
        else { return false; }
    }

	function calculateStats()
	{
		//this function calculates the players statistics based on class and level
		//eventually i should implement stats that grow naturally rather than just linearly....
		$bonus = $this->myInventory->getBonus();
		
		switch ($this->numericClass)
		{
			//1=zombie
			//2=pirate
			//3=ninja
			case 1:
                $this->maxHP = round(7 * $this->get_Level()) + $bonus['hp'] + 5;
				$this->str   = round(3.21*$this->get_Level()) + $bonus['str'] + 3;
				$this->dex   = round(1.12*$this->get_Level()) + $bonus['dex'] + 1;
				break;
			case 2:
				$this->maxHP = round(4.8 * $this->get_Level()) + $bonus['hp'] + 4;
				$this->str   = round(2.53*$this->get_Level()) + $bonus['str'] + 3;
				$this->dex   = round(2.27*$this->get_Level()) + $bonus['dex'] + 3;
				break;
			case 3:
                $this->attacks = 2;
				$this->maxHP = round(3.56 * $this->get_Level()) + $bonus['hp'] + 2;
				$this->str   = round(1.23*$this->get_Level()) + $bonus['str'] + 1;
				$this->dex   = round(5.7*$this->get_Level()) + $bonus['dex'] + 6;
				break;
		}
		if ($this->hp > $this->maxHP) 
            $this->hp = $this->maxHP;
        db_query('UPDATE users SET hp = ? WHERE username = ?', [$this->hp, $this->user_name]);
	}
    
    function log_out()
    {
        //this function should eventually send a message to the database flagging the user as logged out
        
        $this->user_name = "";
        $this->permissions = 0;
        $this->logged_in = false;
    }
    
    function log_in($user,$pass)
    {
        // Verify the PHP password hash before hydrating session state.
        //Get permissions for user from users database.
        $params = [$user];
        $query = "SELECT UNIX_TIMESTAMP() as now, username, password, email, class, permissions, experience, UNIX_TIMESTAMP(lastTurn) as lastTurn, turns, location, hp, gold FROM users WHERE username = ?";
        $result = db_query($query, $params)
            or die( "Problem with selecting permissions and email" );
        $row = $result->fetch();

        if ($row && password_verify($pass, $row['password']))
        {
            $this->user_name = $row['username'] ?? $user;
			$this->numericClass = $row['class'];
			switch ($row['class'])
			{
				case 1:
					$this->PlayerClass = "Zombie";
					break;
				case 2:
					$this->PlayerClass = "Pirate";
					break;
				case 3:
					$this->PlayerClass = "Ninja";
					break;
			}		
            $this->permissions = $row['permissions'];
            $this->email = $row['email'];
            $this->logged_in = true;
            $this->experience = $row['experience'];
			$this->myInventory = new inventory($this->user_name, $this->numericClass);
			$this->hp = $row['hp'];
			$this->calculateStats();
			$this->numLoc = $row['location'];
			$this->gold = $row['gold'];	
			$this->turns = $row['turns'];
			$this->timeOfLastTurn = $row['lastTurn'];
			$this->timeOfLastLogin = $row['now'];
			$this->calculateTurns();
			if ($this->timeOfLastTurn == $this->timeOfLastLogin)
			{
				db_query("UPDATE users SET lastTurn = now(),turns=? WHERE username = ?", [$this->turns, $user])
					or die ("error updating lastturn");
			}
		}
        else
        {
            //Just to be sure, set things to "" if the user fails the login.
            $this->user_name = "";
            $this->permissions = 0;
            $this->email = "";
            $this->logged_in = false;
        }
	}

    function register($u, $p, $c, $e)
    {
        if (!is_string($u) || !preg_match('/\A[A-Za-z0-9_]{1,15}\z/', $u)
            || !is_string($p) || strlen($p) < 8 || strlen($p) > 72 || str_contains($p, "\0")
            || !is_string($c) || !is_string($e)
            || !in_array((string)$c, ['1', '2', '3'], true)
            || !filter_var($e, FILTER_VALIDATE_EMAIL) || strlen($e) > 254) {
            return false;
        }
        try {
            db_query('INSERT INTO users (username,password,email,permissions,class,gold,hp,experience,location,turns,lastTurn) VALUES (?,?,?,1,?,0,20,0,1,40,CURRENT_TIMESTAMP)',
                [$u, password_hash($p, PASSWORD_DEFAULT), $e, $c]);
        } catch (PDOException $error) {
            if ($error->errorInfo[1] === 1062) return false;
            throw $error;
        }
        $this->log_in($u, $p);
        return true;
    }


    function get_Stats()
    {
        $toReturn['str']     = $this->str;
        $toReturn['dex']     = $this->dex;
        $toReturn['hp']      = $this->hp;
        $toReturn['class']   = $this->numericClass;
        $toReturn['attacks'] = $this->attacks;
        return $toReturn;
    }
    
    function getUserInfoTable()
    {
        $lv = $this->get_Level();
        $toReturn = "<!--USER INFO TABLE--> <table id=\"character_sheet\" width=\"600\" border = \"1\" bordercolor=\"black\" noshadow> <tr> " .
        "<th align=\"right\" width=\"125\">Character Name:</th> <td width=\"75\">{$this->user_name}</td><th align=\"right\" width=\"100\"> " .
        "Level: </th><td width=\"100\">{$lv}</td><th align=\"right\" width=\"125\">Total Experience:</th><td width=\"75\">" . 
        "{$this->experience}</td></tr> <tr><th align=\"right\">Streingth:</th><td>{$this->str}" .
        "</td><th align=\"right\">Dexterity:</th><td>{$this->dex}</td><th align=\"right\">Hit Points:</th>" .
        "<td>{$this->hp} / {$this->maxHP}</td></tr><tr><th align=\"right\" colspan=\"3\">" . 
        "Turns Remaining:</th><td colspan=\"3\">{$this->turns}</td></tr></table>";

    return $toReturn;
    }
    
    function usePotion($id)
    {
        db()->beginTransaction();
        try {
            db_query('SELECT username FROM users WHERE username = ? FOR UPDATE', [$this->user_name]);
            $hpchange = $this->myInventory->usePotion($id);
            $this->addHP($hpchange);
            db()->commit();
            return $hpchange;
        } catch (Throwable $error) {
            if (db()->inTransaction()) db()->rollBack();
            throw $error;
        }
    }


    function sell($id)
    {
        db()->beginTransaction();
        try {
            db_query('SELECT username FROM users WHERE username = ? FOR UPDATE', [$this->user_name]);
            $goldChange = $this->myInventory->sell($id);
            db_query('UPDATE users SET gold = gold + ? WHERE username = ?', [$goldChange, $this->user_name]);
            $this->gold = db_query('SELECT gold FROM users WHERE username = ?', [$this->user_name])->fetchColumn();
            $this->calculateStats();
            db()->commit();
            return $goldChange;
        } catch (Throwable $error) {
            if (db()->inTransaction()) db()->rollBack();
            throw $error;
        }
    }


    function get_PlayerBuyList()
    {
        $toreturn = "<div id=\"PlayerBuyList\">Buy something?<br/>";
        $params = [];
        $query = "SELECT itemID, itemname, itemtype, isWearable, strAdded, dexAdded, hpAdded
FROM items
WHERE itemtype <> 10
ORDER BY itemtype";
        $result = db_query($query, $params) or die("error getting playerbuylist");
        
        while ( $row = $result->fetch() )
        {
            $toreturn .= "<a href=\"shop.php?task=buy&itemid={$row['itemID']}\">buy {$row['itemname']}</a><br/>";
        }
        $toreturn .= "</div>";
        return $toreturn;
    }

}
?>
