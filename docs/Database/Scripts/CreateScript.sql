# noinspection SqlNoDataSourceInspectionForFile

-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema bachelor
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema bachelor
-- -----------------------------------------------------
CREATE DATABASE IF NOT EXISTS `bachelor` DEFAULT CHARACTER SET utf8 ;
USE `bachelor` ;

-- -----------------------------------------------------
-- Table `bachelor`.`clientSupervisor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`clientSupervisor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `locationDescription` VARCHAR(255) NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idLocalManagement_UNIQUE` (`id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bachelor`.`userRole`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`userRole` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idUR_UNIQUE` (`id` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bachelor`.`team`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`team` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `fk_TeamLeaderTechnician_Id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idTeam_UNIQUE` (`id` ASC) 
  )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bachelor`.`technician`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`technician` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(20) NOT NULL,
  `fk_Team_Id` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_technician_team1_idx` (`fk_Team_Id` ASC) ,
  CONSTRAINT `fk_technician_team1`
    FOREIGN KEY (`fk_Team_Id`)
    REFERENCES `bachelor`.`team` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bachelor`.`team` - foreign key fix
-- -----------------------------------------------------
ALTER TABLE `bachelor`.`team`
ADD INDEX `fk_team_technician1_idx` (`fk_TeamLeaderTechnician_Id` ASC) ,
ADD CONSTRAINT `fk_team_technician1`
    FOREIGN KEY (`fk_TeamLeaderTechnician_Id`)
    REFERENCES `bachelor`.`technician` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION;


-- -----------------------------------------------------
-- Table `bachelor`.`user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`user` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(45) NOT NULL,
  `lastName` VARCHAR(45) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `tempPassword` TINYINT NOT NULL,
  `fk_ClientSupervisor_Id` INT NULL,
  `fk_UserRole_Id` INT NOT NULL,
  `fk_Technician_Id` INT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idUser_UNIQUE` (`id` ASC) ,
  INDEX `fk_user_clientSupervisor1_idx` (`fk_ClientSupervisor_Id` ASC) ,
  INDEX `fk_user_userRole1_idx` (`fk_UserRole_Id` ASC) ,
  INDEX `fk_user_technician1_idx` (`fk_Technician_Id` ASC) ,
  UNIQUE INDEX `email_UNIQUE` (`email` ASC) ,
  CONSTRAINT `fk_user_clientSupervisor1`
    FOREIGN KEY (`fk_ClientSupervisor_Id`)
    REFERENCES `bachelor`.`clientSupervisor` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_userRole1`
    FOREIGN KEY (`fk_UserRole_Id`)
    REFERENCES `bachelor`.`userRole` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_user_technician1`
    FOREIGN KEY (`fk_Technician_Id`)
    REFERENCES `bachelor`.`technician` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bachelor`.`janitor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`janitor` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `phone` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `phone_UNIQUE` (`phone` ASC) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `bachelor`.`address`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`address` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `streetName` VARCHAR(100) NOT NULL,
  `streetNumber` VARCHAR(25) NOT NULL,
  `janitor_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idCO_UNIQUE` (`id` ASC) ,
  INDEX `fk_address_Janitor1_idx` (`janitor_id` ASC) ,
  CONSTRAINT `fk_address_Janitor1`
    FOREIGN KEY (`janitor_id`)
    REFERENCES `bachelor`.`janitor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bachelor`.`officeState`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`officeState` (
  `id` INT NOT NULL,
  `name` VARCHAR(45) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bachelor`.`clientOffice`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`clientOffice` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `description` VARCHAR(255) NOT NULL,
  `note` TEXT NULL,
  `fk_Team_Id` INT NULL,
  `fk_ClientSupervisor_Id` INT NOT NULL,
  `fk_Address_Id` INT NOT NULL,
  `fk_officeState_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_clientOffice_team_idx` (`fk_Team_Id` ASC) ,
  INDEX `fk_clientOffice_clientSupervisor1_idx` (`fk_ClientSupervisor_Id` ASC) ,
  UNIQUE INDEX `CONumber_UNIQUE` (`id` ASC) ,
  INDEX `fk_clientOffice_Address1_idx` (`fk_Address_Id` ASC) ,
  INDEX `fk_clientOffice_officeState1_idx` (`fk_officeState_id` ASC),
  CONSTRAINT `fk_clientOffice_team`
    FOREIGN KEY (`fk_Team_Id`)
    REFERENCES `bachelor`.`team` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_clientOffice_clientSupervisor1`
    FOREIGN KEY (`fk_ClientSupervisor_Id`)
    REFERENCES `bachelor`.`clientSupervisor` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_clientOffice_Address1`
    FOREIGN KEY (`fk_Address_Id`)
    REFERENCES `bachelor`.`address` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_clientOffice_officeState1`
    FOREIGN KEY (`fk_officeState_id`)
    REFERENCES `bachelor`.`officeState` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bachelor`.`officeContact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `bachelor`.`officeContact` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `firstName` VARCHAR(45) NOT NULL,
  `lastName` VARCHAR(45) NOT NULL,
  `fk_ClientOffice_Id` INT NOT NULL,
  `phone` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC) ,
  INDEX `fk_contact_clientOffice1_idx` (`fk_ClientOffice_Id` ASC) ,
  CONSTRAINT `fk_contact_clientOffice1`
    FOREIGN KEY (`fk_ClientOffice_Id`)
    REFERENCES `bachelor`.`clientOffice` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

-- -----------------------------------------------------
-- Table `bachelor`.`changes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `bachelor`.`changes` ;

CREATE TABLE IF NOT EXISTS `bachelor`.`changes` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `time` DATETIME NOT NULL,
  `fk_ClientOffice_Id` INT NOT NULL,
  `officeState_id` INT NOT NULL,
  `user_id` INT NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `idchanges_UNIQUE` (`id` ASC),
  INDEX `fk_changes_clientOffice1_idx` (`fk_ClientOffice_Id` ASC) ,
  INDEX `fk_changes_officeState1_idx` (`officeState_id` ASC) ,
  INDEX `fk_changes_user1_idx` (`user_id` ASC),
  CONSTRAINT `fk_changes_clientOffice1`
    FOREIGN KEY (`fk_ClientOffice_Id`)
    REFERENCES `bachelor`.`clientOffice` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_changes_officeState1`
    FOREIGN KEY (`officeState_id`)
    REFERENCES `bachelor`.`officeState` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_changes_user1`
    FOREIGN KEY (`user_id`)
    REFERENCES `bachelor`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;