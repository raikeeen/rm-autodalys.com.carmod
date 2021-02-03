CREATE TABLE IF NOT EXISTS `_DB_PREFIX_lpexpress_terminal` (
  `id_lpexpress_terminal` INT NOT NULL AUTO_INCREMENT,
  `machineid` VARCHAR(4) NOT NULL,
  `active` BOOLEAN NOT NULL DEFAULT 1,
  `name` VARCHAR(128) NOT NULL,
  `address` VARCHAR(128) NOT NULL,
  `zip` VARCHAR(10) NOT NULL,
  `city` VARCHAR(128) NOT NULL,
  `comment` VARCHAR(256) NOT NULL,
  `inside` BOOLEAN NOT NULL,
  `boxcount` SMALLINT NOT NULL,
  `collectinghours` VARCHAR(128) NOT NULL,
  `workinghours` VARCHAR(128) NOT NULL,
  `latitude` DECIMAL(10, 8) NOT NULL,
  `longitude` DECIMAL(10, 8) NOT NULL,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_lpexpress_terminal`),
  INDEX (`id_lpexpress_terminal`, `machineid`, `city`)
) ENGINE=_MYSQL_ENGINE_ CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_lpexpress_box` (
  `id_lpexpress_box` INT NOT NULL AUTO_INCREMENT,
  `size` VARCHAR(128) NOT NULL,
  `date_add` DATETIME NOT NULL,
  `date_upd` DATETIME NOT NULL,
  PRIMARY KEY (`id_lpexpress_box`),
  INDEX (`id_lpexpress_box`, `size`)
) ENGINE=_MYSQL_ENGINE_ CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_lpexpress_terminal_box` (
  `id_lpexpress_terminal` INT NOT NULL,
  `id_lpexpress_box` INT NOT NULL,
  INDEX (`id_lpexpress_terminal`, `id_lpexpress_box`)
) ENGINE=_MYSQL_ENGINE_ CHARSET=UTF8;

CREATE TABLE IF NOT EXISTS `_DB_PREFIX_lpexpress_terminal_order` (
  `id_lpexpress_terminal_order` INT NOT NULL AUTO_INCREMENT,
  `id_cart` INT NOT NULL,
  `id_order` INT NULL,
  `id_lpexpress_terminal` INT NULL,
  `id_lpexpress_box` INT NULL,
  `type` ENUM('terminal', 'address', 'post'),
  `weight` DECIMAL(20, 6) NOT NULL,
  `packets` INT NOT NULL DEFAULT 1,
  `cod` BOOLEAN NOT NULL DEFAULT 0,
  `cod_amount` DECIMAL(20,6) NULL,
  `comment` VARCHAR(200) NOT NULL,
  `label_number` INT NOT NULL DEFAULT 0,
  `orderid` VARCHAR(64) NULL,
  `orderpdfid` VARCHAR(64) NULL,
  `identcode` VARCHAR(64) NULL,
  `manifestid` VARCHAR(64) NULL,
  `post_address` VARCHAR(128) NOT NULL,
  PRIMARY KEY (`id_lpexpress_terminal_order`),
  INDEX (`id_cart`, `id_order`)
) ENGINE=_MYSQL_ENGINE_ CHARSET=UTF8;
