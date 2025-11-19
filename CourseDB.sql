-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema laravel_app
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema laravel_app
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `laravel_app` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci ;
USE `laravel_app` ;

-- -----------------------------------------------------
-- Table `laravel_app`.`fields`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`fields` (
  `field_id` INT NOT NULL AUTO_INCREMENT,
  `field_name` VARCHAR(50) NOT NULL,
  `field_area` FLOAT NULL DEFAULT NULL,
  `polygon` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`field_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`agronomic_surveys`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`agronomic_surveys` (
  `survey_id` INT NOT NULL AUTO_INCREMENT,
  `field_id` INT NOT NULL,
  `survey_date` DATE NULL DEFAULT NULL,
  `pest_level` VARCHAR(50) NULL DEFAULT NULL,
  `disease_level` VARCHAR(50) NULL DEFAULT NULL,
  `condition_notes` TEXT NULL DEFAULT NULL,
  `yield_mass_kg` FLOAT NULL DEFAULT NULL,
  `yield_per_hectare` FLOAT NULL DEFAULT NULL,
  `harvest_date` DATE NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`survey_id`),
  INDEX `fk_agronomic_surveys_field_id` (`field_id` ASC) VISIBLE,
  CONSTRAINT `fk_agronomic_surveys_field_id`
    FOREIGN KEY (`field_id`)
    REFERENCES `laravel_app`.`fields` (`field_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`crops_catalog`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`crops_catalog` (
  `crop_id` INT NOT NULL AUTO_INCREMENT,
  `crop_name` VARCHAR(50) NOT NULL,
  `variety` VARCHAR(50) NULL DEFAULT NULL,
  `vegetation_period` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`crop_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`operation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`operation` (
  `operation_id` INT NOT NULL AUTO_INCREMENT,
  `operation_type` VARCHAR(100) NULL DEFAULT NULL,
  `operation_date` DATE NULL DEFAULT NULL,
  `description` TEXT NULL DEFAULT NULL,
  `weather_conditions` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`operation_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`fields_has_operation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`fields_has_operation` (
  `field_id` INT NOT NULL,
  `operation_id` INT NOT NULL,
  `crop_id` INT NOT NULL,
  `applied_materials` VARCHAR(50) NULL DEFAULT NULL,
  `application_rate` FLOAT NULL DEFAULT NULL,
  `season_name` VARCHAR(50) NULL DEFAULT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`field_id`, `operation_id`, `crop_id`),
  INDEX `fk_fields_has_operation_operation_id` (`operation_id` ASC) VISIBLE,
  INDEX `fk_fields_has_operation_crop_id` (`crop_id` ASC) VISIBLE,
  CONSTRAINT `fk_fields_has_operation_crop_id`
    FOREIGN KEY (`crop_id`)
    REFERENCES `laravel_app`.`crops_catalog` (`crop_id`),
  CONSTRAINT `fk_fields_has_operation_field_id`
    FOREIGN KEY (`field_id`)
    REFERENCES `laravel_app`.`fields` (`field_id`),
  CONSTRAINT `fk_fields_has_operation_operation_id`
    FOREIGN KEY (`operation_id`)
    REFERENCES `laravel_app`.`operation` (`operation_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`user_roles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`user_roles` (
  `role_id` INT NOT NULL AUTO_INCREMENT,
  `role_name` VARCHAR(50) NOT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`role_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`users`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`users` (
  `user_id` VARCHAR(255) NOT NULL,
  `username` VARCHAR(50) NOT NULL,
  `email` VARCHAR(50) NOT NULL,
  `password` VARCHAR(256) NOT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


-- -----------------------------------------------------
-- Table `laravel_app`.`user_role_assignments`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `laravel_app`.`user_role_assignments` (
  `user_id` VARCHAR(255) NOT NULL,
  `role_id` INT NOT NULL,
  `created_at` DATETIME NULL DEFAULT NULL,
  `updated_at` DATETIME NULL DEFAULT NULL,
  PRIMARY KEY (`user_id`, `role_id`),
  INDEX `fk_user_role_assignments_role_id` (`role_id` ASC) VISIBLE,
  CONSTRAINT `fk_user_role_assignments_role_id`
    FOREIGN KEY (`role_id`)
    REFERENCES `laravel_app`.`user_roles` (`role_id`),
  CONSTRAINT `fk_user_role_assignments_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `laravel_app`.`users` (`user_id`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8mb4
COLLATE = utf8mb4_0900_ai_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
