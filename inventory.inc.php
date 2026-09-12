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

	function __construct($user, $uclass)
	{
		$this->items = []; $this->equippedArmor = $this->equippedWeapon = $this->equippedAccessory = 0;
        $this->user_name = $user;
		$this->numericClass = $uclass;

		$params = [$this->user_name];
        $query = "SELECT v.owner, v.invID, v.qty, i.itemname, i.itemID, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped FROM inventory v, items i WHERE v.itemtype = i.itemID AND v.owner = ? AND v.qty > 0 ORDER BY isEquipped DESC, itemtype";
		$result = db_query($query, $params) or die("problem getting inventory");

        $i = 1; //Counter to indicate what index value we are at in the array
		while ( $row = $result->fetch() )
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
        
        return $pot . '</select>';
    }
	
    function addItem($itemID)
    {
        $template = db_query('SELECT itemtype FROM items WHERE itemID = ?', [$itemID])->fetch();
        if (!$template) return false;
        $row = db_query('SELECT invID FROM inventory WHERE owner = ? AND itemtype = ? ORDER BY invID LIMIT 1', [$this->user_name, $itemID])->fetch();
        if ($template['itemtype'] == 0 && $row) {
            db_query('UPDATE inventory SET qty = qty + 1 WHERE invID = ? AND owner = ?', [$row['invID'], $this->user_name]);
        } else {
            db_query('INSERT INTO inventory (owner,itemtype,qty,isEquipped) VALUES (?,?,1,0)', [$this->user_name, $itemID]);
        }
        $this->__construct($this->user_name, $this->numericClass);
        return true;
    }


    function usePotion($id)
    {
        $row = db_query('SELECT i.hpAdded FROM inventory v JOIN items i ON i.itemID = v.itemtype WHERE v.invID = ? AND v.owner = ? AND v.qty > 0 AND i.itemtype = 0', [$id, $this->user_name])->fetch();
        if (!$row) return 0;
        $changed = db_query('UPDATE inventory SET qty = qty - 1, isEquipped = 0 WHERE invID = ? AND owner = ? AND qty > 0', [$id, $this->user_name]);
        $this->__construct($this->user_name, $this->numericClass);
        return $changed->rowCount() ? $row['hpAdded'] : 0;
    }


    function sell($id)
    {
        $row = db_query('SELECT i.itemtype,i.strAdded,i.dexAdded FROM inventory v JOIN items i ON i.itemID = v.itemtype WHERE v.invID = ? AND v.owner = ? AND v.qty > 0', [$id, $this->user_name])->fetch();
        if (!$row) return 0;
        $changed = db_query('UPDATE inventory SET qty = qty - 1, isEquipped = 0 WHERE invID = ? AND owner = ? AND qty > 0', [$id, $this->user_name]);
        $this->__construct($this->user_name, $this->numericClass);
        return $changed->rowCount() ? $row['itemtype'] + $row['strAdded'] + $row['dexAdded'] + 10 : 0;
    }


    function equip($id)
    {
        foreach ($this->items as $curr) {
            if ($curr->getID() != $id || !$curr->canEquip($this->numericClass)) continue;
            db()->beginTransaction();
            try {
                db_query('SELECT username FROM users WHERE username = ? FOR UPDATE', [$this->user_name]);
                foreach ($this->items as $other) {
                    if ($other->getTypeStr() == $curr->getTypeStr()) $other->unequip();
                }
                $curr->equip();
                db()->commit();
            } catch (Throwable $error) {
                if (db()->inTransaction()) db()->rollBack();
                throw $error;
            }
            $this->__construct($this->user_name, $this->numericClass);
            return true;
        }
        return false;
    }


    function unequip($id)
    {
        foreach ($this->items as $curr) {
            if ($curr->getID() == $id) {
                $curr->unequip();
                $this->__construct($this->user_name, $this->numericClass);
                return true;
            }
        }
        return false;
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
