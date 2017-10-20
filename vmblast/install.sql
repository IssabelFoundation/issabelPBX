CREATE TABLE IF NOT EXISTS `vmblast` 
( 
	`grpnum` INT( 11 ) NOT NULL , 
	`description` VARCHAR( 35 ) NOT NULL , 
	`audio_label` INT( 11 ) NOT NULL DEFAULT -1 , 
	`password` VARCHAR( 20 ) NOT NULL , 
	PRIMARY KEY  (`grpnum`) 
); 

CREATE TABLE IF NOT EXISTS vmblast_groups 
(
	grpnum  VARCHAR(50), 
	ext VARCHAR(25),
	PRIMARY KEY (grpnum , ext)
);
