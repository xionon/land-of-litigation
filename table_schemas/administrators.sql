CREATE TABLE administrators (
	username varchar(50) UNIQUE NOT NULL,
	password varchar(50) NOT NULL,
	first_name varchar(50) NOT NULL,
	last_name varchar(50) NOT NULL,
	email varchar(50) NOT NULL,
	admin_level int(2) NOT NULL,
	id int(10) NOT NULL auto_increment,
	PRIMARY KEY (id)
);

INSERT INTO administrators (username, password, first_name, last_name, email, admin_level, id)
VALUES ('Alec', PASSWORD('foghorn'), 'Alec', 'Hipshear', 'xionon@gmail.com', '1', '');
INSERT INTO administrators (username, password, first_name, last_name, email, admin_level, id)
VALUES ('Julius', PASSWORD('magicdust'), 'Julius', 'Caesar', 'xionon@hotmail.com', '2', '');
