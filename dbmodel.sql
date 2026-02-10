-- ------
-- BGA framework: © Gregory Isabelli <gisabelli@boardgamearena.com> & Emmanuel Colin <ecolin@boardgamearena.com>
-- coatl implementation : © Ábel Tibor Tóth <toth.abel.tibor2@gmail.com>
-- 
-- This code has been produced on the BGA studio platform for use on http://boardgamearena.com.
-- See http://en.boardgamearena.com/#!doc/Studio for more information.
-- -----

-- dbmodel.sql

CREATE TABLE IF NOT EXISTS `action_log`
(
	`action_id`   INT(2) UNSIGNED  NOT NULL AUTO_INCREMENT,
	`gamelog_id`  INT(5) UNSIGNED  NOT NULL,
	`action_type` VARCHAR(20)      NOT NULL,
	`player_id`   INT(10) UNSIGNED NOT NULL,
	`action_args` JSON,
	PRIMARY KEY (`action_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `pieces`
(
	`card_id`           INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`card_type`         VARCHAR(4)      NOT NULL,
	`card_type_arg`     VARCHAR(6)      NOT NULL,
	`card_location`     VARCHAR(32)     NOT NULL,
	`card_location_arg` VARCHAR(11)     NOT NULL,
	PRIMARY KEY (`card_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `prophecies`
(
	`card_id`           INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`card_type`         VARCHAR(16)     NOT NULL,
	`card_type_arg`     INT(2)          NOT NULL,
	`card_location`     VARCHAR(32)     NOT NULL,
	`card_location_arg` INT(11)         NOT NULL,
	PRIMARY KEY (`card_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `temples`
(
	`card_id`           INT(2) UNSIGNED NOT NULL AUTO_INCREMENT,
	`card_type`         INT(2) UNSIGNED NOT NULL,
	`card_type_arg`     INT(1) UNSIGNED DEFAULT NULL,
	`card_location`     VARCHAR(32)     NOT NULL,
	`card_location_arg` INT(11)         NOT NULL,
	PRIMARY KEY (`card_id`)
) ENGINE = InnoDB
  DEFAULT CHARSET = utf8
  AUTO_INCREMENT = 1;

CREATE TABLE IF NOT EXISTS `coatls`
(
	`id`           INT(2) UNSIGNED  NOT NULL,
	`player_id`    INT(10) UNSIGNED NOT NULL,
	`start_box_id` INT(3) UNSIGNED DEFAULT 150,
	`is_locked`    BOOLEAN         DEFAULT FALSE,
	PRIMARY KEY (`id`)
) ENGINE = InnoDB;

ALTER TABLE `gamelog`
	ADD COLUMN `cancel` BOOLEAN NOT NULL DEFAULT FALSE;

ALTER TABLE `player`
	ADD COLUMN `piece_token`    BOOLEAN DEFAULT TRUE,
	ADD COLUMN `prophecy_token` BOOLEAN DEFAULT TRUE,
	ADD COLUMN `temple_token`   BOOLEAN DEFAULT TRUE;
