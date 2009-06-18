DROP TABLE users;
DROP TABLE monsters;
DROP TABLE inventory;
DROP TABLE items;
DROP TABLE adventures;
DROP TABLE quests;
DROP TABLE places;

CREATE TABLE users
(
username char(15) NOT NULL,
password char(50) NOT NULL,
email char(50) NOT NULL,
permissions TINYINT NOT NULL,
class TINYINT NOT NULL,
gold INT NOT NULL DEFAULT 0,
hp SMALLINT UNSIGNED NOT NULL,
experience SMALLINT UNSIGNED NOT NULL,
location TINYINT UNSIGNED NOT NULL,
turns TINYINT UNSIGNED NOT NULL,
lastTurn TIMESTAMP NOT NULL,
PRIMARY KEY (username)
) TYPE=INNODB;

CREATE TABLE places
(
name char(25) NOT NULL,
location TINYINT UNSIGNED NOT NULL AUTO_INCREMENT,
level INT UNSIGNED NOT NULL DEFAULT 1,
PRIMARY KEY (location)
) TYPE=INNODB;

CREATE TABLE monsters
(
monstername char(25) NOT NULL,
level TINYINT NOT NULL,
location tinyint NOT NULL DEFAULT 1,
hp SMALLINT NOT NULL,
str TINYINT NOT NULL,
dex TINYINT NOT NULL,
gold TINYINT NOT NULL,
item INT NOT NULL,
PRIMARY KEY (monstername)
) TYPE=INNODB;

CREATE TABLE items
(
itemID INT NOT NULL AUTO_INCREMENT,
itemname char(25) NOT NULL,
itemtype TINYINT NOT NULL,
isWearable BIT DEFAULT 0 NOT NULL,
strAdded SMALLINT DEFAULT 0 NOT NULL,
dexAdded SMALLINT DEFAULT 0 NOT NULL,
hpAdded SMALLINT DEFAULT 0 NOT NULL,
PRIMARY KEY (itemID)
) TYPE=INNODB;

---------------------------------------------------------------------------------------
--NOTE itemtype IN THE inventory TABLE REFERES TO THE itemID FROM THE items TABLE    --
---------------------------------------------------------------------------------------
--The entries in the items table are all TEMPLATES for standard item types, and the  --
--inventory table is a list of items that have been created in the game.  They refer --
--back to the item table to load the characteristics of said item type               --
---------------------------------------------------------------------------------------
CREATE TABLE inventory
(
invID INT NOT NULL AUTO_INCREMENT,
isEquipped BIT NOT NULL DEFAULT 0,
owner char(15) NOT NULL,
itemtype INT NOT NULL,
qty SMALLINT DEFAULT 0 NOT NULL,
PRIMARY KEY (invID),

INDEX (owner),
FOREIGN KEY (owner) 
    REFERENCES users(username)
    ON UPDATE CASCADE ON DELETE CASCADE,

INDEX (itemtype),    
FOREIGN KEY (itemtype)
    REFERENCES items(itemID)
    ON DELETE CASCADE
) TYPE = INNODB;

CREATE TABLE adventures (
id INT NOT NULL AUTO_INCREMENT,
name TEXT,
description TEXT,
location INT NOT NULL,
PRIMARY KEY (id)
) TYPE=INNODB;

CREATE TABLE quests (
questID INT NOT NULL AUTO_INCREMENT,
name TEXT,
description TEXT,
itemToComplete INT,
INDEX (itemToComplete),
FOREIGN KEY (itemToComplete)
    REFERENCES items(itemname)
    ON DELETE CASCADE,
PRIMARY KEY (questID)
) TYPE=INNODB;

INSERT INTO users VALUES ('dill',(password('password')),'email',1,1,1000,3,100,0,10,now());
INSERT INTO users VALUES ('bob',(password('password')),'email',1,1,10,3,100,0,10,now());
INSERT INTO users VALUES ('admin',(password('adminpass')),'email',2,1,10,10,100,0,10,now());

-----name,level,location,hp,str,dex,gold,item
INSERT INTO monsters VALUES ('Goblin',1,2,2,2,3,1,2);
INSERT INTO monsters VALUES ('Big Goblin',3,2,3,4,6,5,2);
INSERT INTO monsters VALUES ('Forest Drake',6,3,10,5,18,15,1);
INSERT INTO monsters VALUES ('Forest Bot',8,3,40,14,10,60,3);
INSERT INTO monsters VALUES ('Cave Bot',10,4,90,30,25,1000,3);
INSERT INTO monsters VALUES ('Fire Bot',10,4,90,30,25,1000,3);

INSERT INTO items VALUES (null,'Steel Sword',1,1,5,1,0);
INSERT INTO items VALUES (null,'Potion',0,0,0,0,10);
INSERT INTO items VALUES (null,'Big Potion',0,0,0,0,50);
INSERT INTO items VALUES (null,'Sheild',2,1,0,5,5);

INSERT INTO inventory VALUES (null,0,'dill',1,1);
INSERT INTO inventory VALUES (null,0,'dill',0,1);
INSERT INTO inventory VALUES (null,0,'dill',2,1);
INSERT INTO inventory VALUES (null,0,'bob',1,1);
INSERT INTO inventory VALUES (null,0,'bob',2,1);
INSERT INTO inventory VALUES (null,0,'dill',3,1);

INSERT INTO places VALUES ('Town',null,1);
INSERT INTO places VALUES ('The Blue River',null,1);
INSERT INTO places VALUES ('The Dark Forest',null,5);
INSERT INTO places VALUES ('The Deep Cave',null,10);

SELECT v.invID, i.itemname, i.itemtype, i.isWearable, i.strAdded, i.dexAdded, i.hpAdded, v.isEquipped
FROM inventory v, items i
WHERE v.itemtype = i.itemID AND v.owner = 'dill';
