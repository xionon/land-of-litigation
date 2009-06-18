<?php
class item
{
	var $id = 0; //the id from  the inventory table
	var $itemname = "";
	var $description = "";
	var $type = 0;
	var $isWearable = 0;
	var $str = 0;
	var $dex = 0;
	var $hp = 0;
	var $isEquipped = 0;
	var $qty = 0;
	var $itemID = 0;

	function item($itemRow)
	{
		$this->id = $itemRow['invID'];
		$this->itemID = $itemrow['itemID'];
		$this->itemname = $itemRow['itemname'];
		$this->type = $itemRow['itemtype'];
		$this->isWearable = $itemRow['isWearable'];
		$this->str = $itemRow['strAdded'];
		$this->dex = $itemRow['dexAdded'];
		$this->hp = $itemRow['hpAdded'];
		$this->isEquipped = $itemRow['isEquipped'];
		$this->qty = $itemRow['qty'];
	}
	
	function getID()
	{
		return $this->id;
	}
	
	function getItemID()
	{
        return $this->itemID;
	}
	
	function getItemName()
	{
		return $this->itemname;
	}
	
	function getDescription()
	{
		return "Descriptions not yet implemented, sorry";
	}
	function useitem()
	{
        $this->resetFromDB();
        $this->qty = $this->qty - 1;
        if ($this->qty < 0) 
          $this->qty = 0;
        $query = "UPDATE inventory SET qty = {$this->qty} WHERE invID = {$this->id}";
        $result = mysql_query( $query ) or die ( "error using item; check item id, item id = {$this->id}" );
	}
	
	function addOne()
	{
        $this->resetFromDB();
        $this->qty = $this->qty + 1;
        $query = "UPDATE inventory SET qty = {$this->qty} WHERE invID = {$this->id}";
        $result = mysql_query( $query ) or die ( "error using item; check item id, item id = {$this->id}" );
	}

    function updateQty()
    {
        $query = "UPDATE inventory SET qty = {$this->qty} WHERE invID = {$this->id}";
        $result = mysql_query( $query ) or die ( "error using item; check item id, item id = {$this->id}" );
    }
	function getType()
	{
		return $this->type;
	}
	
	function getIsWearable()
	{
		if ($this->isWearable == 0)
		 return false; 
		else
		 return true;
	}
	
	function getStr()
	{
		return $this->str;
	}
	
	function getDex()
	{
		return $this->dex;
	}
	
	function getHP()
	{
		return $this->hp;
	}
	
	function getIsEquipped()
	{
		if ($this->isEquipped == 0) 
            return false; 
		else 
            return true;
	}
	
	function equip()
	{
        $this->isEquipped = 1;
        $query = "UPDATE inventory SET isEquipped = 1 WHERE invID = {$this->id}";
        $result = mysql_query( $query ) or die ( "error equipping item; check item id, item id = {$this->id}" );

	}
	
	function unequip()
	{
        $this->isEquipped = 0;
        $query = "UPDATE inventory SET isEquipped = 0 WHERE invID = {$this->id}";
        $result = mysql_query( $query ) or die ( "error unequipping item; check item id" );
	}
	function getSell()
	{
        $this->resetFromDB();
        $toReturn = "";
        //return the itemname and a link to sell it
        if ($this->qty > 0) 
        {
            $p = $this->getPrice();
            $toReturn .= "<tr><td>{$this->itemname} x{$this->qty}</td>";
            $toReturn .= "<td><a href=\"shop.php?task=sell&itemid={$this->id}\">sell for {$p}</a></td></tr>";
        }
        return $toReturn;
	}
	
	function getPrice()
	{
        $price = $this->type + $this->str + $this->dex + 10;
        return $price;
	}
	function getAll()
	{
        $this->resetFromDB();
        $toReturn = "";
	
        if ($this->qty > 0) 
        {	
            $isWearableB = "";
            $isEquippedB = "";
            if (! $this->getIsWearable()) {
                $isWearableB = "No";
            } else {
                $isWearableB = "Yes";
            }
            if (! $this->getIsEquipped()) {
                $isEquippedB = "No";
            } else {
                $isEquippedB = "Yes";
            }
            $typeStr = $this->getTypeStr();
            $toReturn .=  "<tr>
 <td>{$this->qty}x {$this->itemname}</td>
 <td>{$typeStr}</td>
 <td>{$isWearableB}</td>
 <td>{$this->str}</td>
 <td>{$this->dex}</td>
 <td>{$this->hp}</td>
 <td>{$isEquippedB}</td>";
            if ($this->isWearable != 0 && $this->isEquipped == 0) {
            //add equip this item link
                $toReturn .= "<td><a href=\"userProfile.php?task=equip&itemid={$this->id}\">equip</a></td>";
            } 
            else if ($this->isWearable != 0 && $this->isEquipped == 1) {
                $toReturn .= "<td><a href=\"userProfile.php?task=unequip&itemid={$this->id}\">unequip</a></td>";
            }
            else if ($this->type == 0) {
                $toReturn .= "<td><a href=\"userProfile.php?task=usePotion&itemid={$this->id}\">use</a></td>";
            }
            else $toReturn.="<td>&nbsp;</td>";
            $toReturn .= "</tr>";
        }
        return $toReturn;
	}
	function getUse()
	{
        $this->resetFromDB();
        if ($this->qty > 0)
        return "<option name=\"itemid\" value={$this->id}>{$this->itemname} ({$this->qty}x)";
        else return "";
	}
	function getTypeStr()
	{
		$strType="unknown";
		switch ($this->type)
		{
			case 0:
				$strType = "Usable";
				break;
			case 1:
			case 2:
			case 3:
				$strType = "Weapon";
				break;
			case 4:
			case 5:
			case 6:
				$strType = "Armor";
				break;
			case 7:
			case 8:
			case 9:
				$strType = "Accessory";
				break;
			case 10:
				$strType = "Quest";
				break;
			case 11: 
				$strType = "Vendor Trash";
				break;
		}
		return $strType;
	}
	
	function canEquip($uclass)
	{
        switch ($uclass)
        {
            case 1:
                if ($this->type == 1 || $this->type == 4 || $this->type == 7)
                    $toReturn = true;
                else $toReturn = false;
                break;
            case 2:
                if ($this->type == 2 || $this->type == 5 || $this->type == 8)
                    $toReturn = true;
                else $toReturn = false;
                break;
            case 3:
                if ($this->type == 3 || $this->type == 6 || $this->type == 9)
                    $toReturn = true;
                else $toReturn = false;
                break;
            default:
                $toReturn = false;
        }
        return $toReturn;
	}
	
	function resetFromDB()
	{
        //we're loosing the qty and the isEquipped value when they're changed, need to get it from the inventory db
        
        $query = "SELECT qty, isEquipped FROM inventory WHERE invID = {$this->id}";
          $result = mysql_query( $query ) or die ( "error updating item; check item id, item id = {$this->id}" );
        $row = mysql_fetch_array($result);
        
        $this->qty = $row['qty'];
        $this->isEquipped = $row['isEquipped'];
	}
}
?>