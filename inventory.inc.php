<?php 
include_once ("conn.inc");
include_once ("item.inc.php");
/*******

Inventory object
an inventory is composed of multiple items
the inventoryTable string can be returned to easilly print out an inventory from anywhere, huzzah

*******/
class inventory
{
	var $items = array();
	var $user_name = "";
	var $numericClass = 0;
    //the equipped items variables store the index value of the item of that type that is equipped.
	var $equippedArmor = 0;
	var $equippedWeapon = 0;
	var $equippedAccessory = 0;

	function inventory($user, $uclass)
	{
		$this->user_name = $user;
		$this->numericClass = $uclass;

		$query = "SELECT v.invID, v.qty, i.itemname, i.itemID, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.owner = '{$this->user_name}' ORDER BY isEquipped DESC, itemtype";
		$result = mysql_query($query) or die("problem getting inventory");

        $i = 1; //Counter to indicate what index value we are at in the array
		while ( $row = mysql_fetch_array( $result ) )
		{
            $this->items[$i] = new item( $row );
			if ($this->items[$i]->getIsEquipped() == true)
			{
                switch ( $this->items[$i]->getTypeStr() )
                {
                    case "Weapon":
                        $this->equippedWeapon = $i;
                        break;
                    case "Armor":
                        $this->equippedArmor = $i;
                        break;
                    case "Accessory":
                        $this->equippedAccessory = $i;
                        break;
                }
			}
			$i++;
		} 
	}
	function showInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		foreach ($this->items as $i => $curr)
		{
			$inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showSellList()
	{
		$inv = "<table border=\"1\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr>".
		"<tr><th>Item Name</th><th>Sell?</th></tr>";
		foreach ($this->items as $i => $curr)
		{
			$inv .= $this->items[$i]->getSell();
		}
		$inv .= "</table>";
	return $inv;
	
	}
	
	function showWeaponInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
			if ( $iT == "Weapon") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showArmorInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
			if ( $iT == "Armor") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
	
	function showAccessoryInventory()
	{
		$inv = "<table border=\"2\"><tr><th colspan=\"10\">{$this->user_name}'s Inventory</th></tr><tr><th>Item Name</th><th>Item Type</th><th>Is it wearable?</th><th>Streingth Bonus</th><th>Dexterity Bonus</th><th>HP Bonus</th><th>Equipped?</th></tr>";
		
		foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
				if ( $iT == "Accessory") $inv .= $this->items[$i]->getAll();
		}
		$inv .= "</table>";
	return $inv;
	}
    
    function showPotionForm()
    {
        $pot = "<select name=itemid>";
        
        foreach ($this->items as $i => $curr)
		{
			$iT = $this->items[$i]->getTypeStr(); 
				if ( $iT == "Usable") {
                    $pot .= $this->items[$i]->getUse();
				}
		}
        
        return $pot;
    }
	
	function addItem($itemID)
	{   
        //we want to know if there's an item in the inventory that is:
        //of the same type as an id in the items table (v.itemtype = i.itemID)
        //of the same type as the type passed to us (v.itemtype = $itemID)
        //owned by the current user (owner='___')
        //is stackable (i.itemtype = 0)
        
        $query = "SELECT v.invID, i.itemtype FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.itemtype = {$itemID} AND owner = '{$this->user_name}' AND i.itemtype = 0";
        $result = mysql_query($query) or die ("problem locating items in inv");
        $row = mysql_fetch_array($result);
        if (mysql_num_rows($result) == 1)
        {	
			echo "if 1<br/>";
            foreach($this->items as $curr)
            {
				echo "foreach<br/>";
                //loop through the array of items till we find the one 
                //of the same type and then add to it
                //CHANGE 10am if ($curr->getItemID() == $itemID)
                $iid = $curr->getID();
                if ($row['invID'] == $iid ) {
					echo "if foreach<br/>";
                    $curr->addOne(); 
                    break;
                }
                
            }
        }
        else {
			echo "else";
            //the item needs to be added to the inventory database, i guess
            $query = "INSERT INTO inventory VALUES (null,0,'{$this->user_name}',{$itemID},1)";
            $result = mysql_query( $query ) or die ( "error adding new item to inventory; check item id" );

            $query = "SELECT v.invID, i.itemname, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped  FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.owner = '{$this->user_name}' AND v.itemtype = {$itemID}";
            $result = mysql_query($query) or die("problem getting inventory");

            $row = mysql_fetch_array( $result );
			$this->items[] = new item( $row );
        }
	}
	
	function usePotion ($id)
	{
        $toReturn = 0;
        foreach ($this->items as $curr) {
            if ($curr->getID() == $id)
            {
                $toReturn = $curr->getHP();
                $curr->useitem();
            }
        }
        return $toReturn;
	}
	
	function sell ($id)
	{
        $toReturn = 0;
        foreach ($this->items as $curr) {
            if ($curr->getID() == $id)
            {
                $toReturn = $curr->getPrice();
                $curr->useitem();
            }
        }
        return $toReturn;
	}
	
	function equip($id)
	{   
        $curType = "";
        //$id is the id of the item in the INVENTORY TABLE, asshole
        $i = 1; //index of the current item in the array
        
        foreach ($this->items as $curr)
        {
            if ($curr->getID() == $id) { 
                //need to check to see if the user can equip the item, as well
                if ($curr->canEquip($this->numericClass)) {
                      $curr->equip(); 
                      $curType = $curr->getTypeStr();
                } else {
                     $curType = "CannotEquip";
                }
                break; 
            }
            $i ++;
        }
	  

        //set the id for the new equipped item type
        //unequip old item if one is equipped
        switch ($curType)
        {
            case "Weapon":
                if ($this->equippedWeapon > 0)
                    {
                    $this->items[$this->equippedWeapon]->unequip();
                    }
                $this->equippedWeapon = $i;
                break;
            case "Armor":
                if ($this->equippedArmor > 0)
                    $this->items[$this->equippedArmor]->unequip();
                $this->equippedArmor = $i;
                break;
            case "Accessory":
                if ($this->equippedAccessory > 0)
                    $this->items[$this->equippedAccessory]->unequip();
                $this->equippedAccessory = $i;
                break;

            case "CannotEquip": //do something?
                break;
            default:
                echo $curType . "<br>";
        }
	} 
	
    function unequip($id)
    {
    //$id is the id of the item in the INVENTORY TABLE, asshole
        $i = 1;
        foreach ($this->items as $curr)
        {
            if ($curr->getID() == $id) { 
                $curr->unequip(); 
                $curType = $curr->getTypeStr();
                break; 
            }
            $i ++;
        }    
        //$curType = $this->items[$id]->getTypeStr();
        //$this->items[$id]->unequip($id);
        //set the id for the new equipped item type
        switch ($curType)
        {
            case "Weapon":
                $this->equippedWeapon = 0;
                break;
            case "Armor":
                $this->equippedArmor = 0;
                break;
            case "Accessory":
                $this->equippedAccessory = 0;
                break;
        }
    }

	function getBonus()
	{
        $bonus = array();
        $bonus['dex'] = 0;
        $bonus['str'] = 0;
        $bonus['hp']  = 0;
        if ($this->equippedWeapon > 0)
        {
            $bonus['dex'] += $this->items[$this->equippedWeapon]->getDex();
            $bonus['str'] += $this->items[$this->equippedWeapon]->getStr();
            $bonus['hp']  += $this->items[$this->equippedWeapon]->getHP();
        }
        
        if ($this->equippedArmor > 0)
        {
            $bonus['dex'] += $this->items[$this->equippedArmor]->getDex();
            $bonus['str'] += $this->items[$this->equippedArmor]->getStr();
            $bonus['hp']  += $this->items[$this->equippedArmor]->getHP();
        }
        
        if ($this->equippedAccessory > 0)
        {
            $bonus['dex'] += $this->items[$this->equippedAccessory]->getDex();
            $bonus['str'] += $this->items[$this->equippedAccessory]->getStr();
            $bonus['hp']  += $this->items[$this->equippedAccessory]->getHP();
        }
        return $bonus;
	}
	/**/
}
?>
