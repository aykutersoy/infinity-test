USE `infinity-test`;
CREATE TABLE IF NOT EXISTS `csvData` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `eventDatetime` DATETIME NOT NULL,
  `eventAction` VARCHAR(20) NOT NULL,
  `callRef` BIGINT NOT NULL,
  `eventValue` VARCHAR(45) NULL,
  `eventCurrencyCode` VARCHAR(3) NULL,
  `importedDate` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `fileName` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `importedDate` (`importedDate` ASC),
  INDEX `fileName` (`fileName` ASC),
  INDEX `eventDate` (`eventDatetime` ASC));
