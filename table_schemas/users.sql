-- Fresh installation only. Existing player data is intentionally not imported.
CREATE TABLE places (
 name VARCHAR(25) NOT NULL UNIQUE,
 location TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
 level INT UNSIGNED NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE items (
 itemID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 itemname VARCHAR(25) NOT NULL,
 itemtype TINYINT NOT NULL,
 isWearable TINYINT NOT NULL DEFAULT 0,
 strAdded SMALLINT NOT NULL DEFAULT 0,
 dexAdded SMALLINT NOT NULL DEFAULT 0,
 hpAdded SMALLINT NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE users (
 username VARCHAR(15) NOT NULL PRIMARY KEY,
 password VARCHAR(255) NOT NULL,
 email VARCHAR(254) NOT NULL,
 permissions TINYINT NOT NULL DEFAULT 1,
 class TINYINT NOT NULL,
 gold INT UNSIGNED NOT NULL DEFAULT 0,
 hp SMALLINT UNSIGNED NOT NULL DEFAULT 20,
 experience INT UNSIGNED NOT NULL DEFAULT 0,
 location TINYINT UNSIGNED NOT NULL DEFAULT 1,
 turns TINYINT UNSIGNED NOT NULL DEFAULT 40,
 lastTurn TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
 FOREIGN KEY (location) REFERENCES places(location),
 CHECK (class BETWEEN 1 AND 3)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE monsters (
 monstername VARCHAR(25) NOT NULL PRIMARY KEY,
 level TINYINT NOT NULL,
 location TINYINT UNSIGNED NOT NULL DEFAULT 1,
 hp SMALLINT NOT NULL,
 str TINYINT NOT NULL,
 dex TINYINT NOT NULL,
 gold INT UNSIGNED NOT NULL,
 item INT NULL,
 itemChance TINYINT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY (location) REFERENCES places(location),
 FOREIGN KEY (item) REFERENCES items(itemID),
 CHECK (itemChance <= 100)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE inventory (
 invID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 isEquipped TINYINT NOT NULL DEFAULT 0,
 owner VARCHAR(15) NOT NULL,
 itemtype INT NOT NULL,
 qty SMALLINT UNSIGNED NOT NULL DEFAULT 0,
 FOREIGN KEY (owner) REFERENCES users(username) ON UPDATE CASCADE ON DELETE CASCADE,
 FOREIGN KEY (itemtype) REFERENCES items(itemID) ON DELETE CASCADE,
 CHECK (isEquipped IN (0,1)),
 CHECK (qty > 0 OR isEquipped = 0)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE adventures (
 adventureID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 name TEXT NOT NULL,
 description TEXT NOT NULL,
 location TINYINT UNSIGNED NOT NULL,
 level INT NOT NULL DEFAULT 1,
 testAgainst VARCHAR(10) NOT NULL DEFAULT '',
 testValue INT NOT NULL DEFAULT 0,
 completeText TEXT NOT NULL,
 failText TEXT NOT NULL,
 rewardExperience INT UNSIGNED NOT NULL DEFAULT 10,
 rewardItemID INT NULL,
 FOREIGN KEY (location) REFERENCES places(location),
 FOREIGN KEY (rewardItemID) REFERENCES items(itemID)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
CREATE TABLE quests (
 questID INT NOT NULL AUTO_INCREMENT PRIMARY KEY,
 name TEXT,
 description TEXT,
 itemToComplete INT,
 FOREIGN KEY (itemToComplete) REFERENCES items(itemID) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
INSERT INTO places (name,location,level) VALUES
 ('Town',1,1),('The Blue River',2,1),('The Dark Forest',3,5),('The Deep Cave',4,10);
INSERT INTO items (itemID,itemname,itemtype,isWearable,strAdded,dexAdded,hpAdded) VALUES
 (1,'Steel Sword',1,1,5,1,0),(2,'Potion',0,0,0,0,10),
 (3,'Big Potion',0,0,0,0,50),(4,'Sheild',4,1,0,5,5);
-- No drop probabilities were bundled. Retain item references with zero chance.
INSERT INTO monsters (monstername,level,location,hp,str,dex,gold,item) VALUES
 ('Goblin',1,2,2,2,3,1,2),('Big Goblin',3,2,3,4,6,5,2),
 ('Forest Drake',6,3,10,5,18,15,1),('Forest Bot',8,3,40,14,10,60,3),
 ('Cave Bot',10,4,90,30,25,1000,3),('Fire Bot',10,4,90,30,25,1000,3);
