SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `tramitador` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `tramitador` ;

-- -----------------------------------------------------
-- Table `tramitador`.`cuenta`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`cuenta` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`proceso`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`proceso` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `cuenta_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_proceso_cuenta1` (`cuenta_id` ASC) ,
  CONSTRAINT `fk_proceso_cuenta1`
    FOREIGN KEY (`cuenta_id` )
    REFERENCES `tramitador`.`cuenta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`tarea`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`tarea` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `identificador` VARCHAR(32) NOT NULL ,
  `inicial` TINYINT(1) NOT NULL DEFAULT '0' ,
  `final` TINYINT(1) NOT NULL DEFAULT '0' ,
  `nombre` VARCHAR(128) NOT NULL ,
  `posx` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `posy` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `asignacion` ENUM('ciclica','manual','autoservicio','usuario') NOT NULL DEFAULT 'ciclica' ,
  `asignacion_usuario` VARCHAR(128) NULL ,
  `asignacion_notificar` TINYINT(1) NOT NULL DEFAULT 0 ,
  `proceso_id` INT(10) UNSIGNED NOT NULL ,
  `almacenar_usuario` TINYINT(1) NOT NULL DEFAULT 0 ,
  `almacenar_usuario_variable` VARCHAR(128) NULL ,
  `acceso_modo` ENUM('grupos_usuarios','publico','registrados') NOT NULL DEFAULT 'grupos_usuarios' ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `identificador_proceso` (`identificador` ASC, `proceso_id` ASC) ,
  INDEX `fk_tarea_proceso1` (`proceso_id` ASC) ,
  CONSTRAINT `tarea_ibfk_1`
    FOREIGN KEY (`proceso_id` )
    REFERENCES `tramitador`.`proceso` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 51
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`conexion`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`conexion` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `identificador` VARCHAR(32) NOT NULL ,
  `tarea_id_origen` INT(10) UNSIGNED NOT NULL ,
  `tarea_id_destino` INT(10) UNSIGNED NOT NULL ,
  `tipo` ENUM('secuencial','evaluacion','paralelo','paralelo_evaluacion','union') NOT NULL ,
  `regla` VARCHAR(256) NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `tarea_origen_destino` (`tarea_id_origen` ASC, `tarea_id_destino` ASC) ,
  INDEX `fk_ruta_tarea` (`tarea_id_origen` ASC) ,
  INDEX `fk_ruta_tarea1` (`tarea_id_destino` ASC) ,
  CONSTRAINT `conexion_ibfk_1`
    FOREIGN KEY (`tarea_id_origen` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `conexion_ibfk_2`
    FOREIGN KEY (`tarea_id_destino` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 28
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`grupo_usuarios`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`grupo_usuarios` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `cuenta_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_grupo_usuarios_cuenta1` (`cuenta_id` ASC) ,
  UNIQUE INDEX `grupo_usuarios_UNIQUE` (`cuenta_id` ASC, `nombre` ASC) ,
  CONSTRAINT `fk_grupo_usuarios_cuenta1`
    FOREIGN KEY (`cuenta_id` )
    REFERENCES `tramitador`.`cuenta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`usuario`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`usuario` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `usuario` VARCHAR(128) NOT NULL ,
  `password` VARCHAR(256) NULL ,
  `nombre` VARCHAR(128) NULL ,
  `apellidos` VARCHAR(128) NULL ,
  `email` VARCHAR(255) NULL ,
  `registrado` TINYINT(1) NOT NULL DEFAULT 1 ,
  `cuenta_id` INT UNSIGNED NULL ,
  `created_at` DATETIME NULL ,
  `updated_at` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `usuario_UNIQUE` (`usuario` ASC) ,
  INDEX `fk_usuario_cuenta1` (`cuenta_id` ASC) ,
  CONSTRAINT `fk_usuario_cuenta1`
    FOREIGN KEY (`cuenta_id` )
    REFERENCES `tramitador`.`cuenta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`tarea_has_grupo_usuarios`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`tarea_has_grupo_usuarios` (
  `tarea_id` INT(10) UNSIGNED NOT NULL ,
  `grupo_usuarios_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`tarea_id`, `grupo_usuarios_id`) ,
  INDEX `fk_tarea_has_grupo_usuarios_grupo_usuarios1` (`grupo_usuarios_id` ASC) ,
  INDEX `fk_tarea_has_grupo_usuarios_tarea1` (`tarea_id` ASC) ,
  CONSTRAINT `fk_tarea_has_grupo_usuarios_grupo_usuarios1`
    FOREIGN KEY (`grupo_usuarios_id` )
    REFERENCES `tramitador`.`grupo_usuarios` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_tarea_has_grupo_usuarios_tarea1`
    FOREIGN KEY (`tarea_id` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`grupo_usuarios_has_usuario`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`grupo_usuarios_has_usuario` (
  `grupo_usuarios_id` INT UNSIGNED NOT NULL ,
  `usuario_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`grupo_usuarios_id`, `usuario_id`) ,
  INDEX `fk_grupo_usuarios_has_usuario_usuario1` (`usuario_id` ASC) ,
  INDEX `fk_grupo_usuarios_has_usuario_grupo_usuarios1` (`grupo_usuarios_id` ASC) ,
  CONSTRAINT `fk_grupo_usuarios_has_usuario_grupo_usuarios1`
    FOREIGN KEY (`grupo_usuarios_id` )
    REFERENCES `tramitador`.`grupo_usuarios` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_grupo_usuarios_has_usuario_usuario1`
    FOREIGN KEY (`usuario_id` )
    REFERENCES `tramitador`.`usuario` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`tramite`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`tramite` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `proceso_id` INT UNSIGNED NOT NULL ,
  `pendiente` TINYINT(1) NOT NULL ,
  `created_at` DATETIME NULL ,
  `updated_at` DATETIME NULL ,
  `ended_at` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_tramite_proceso1` (`proceso_id` ASC) ,
  CONSTRAINT `fk_tramite_proceso1`
    FOREIGN KEY (`proceso_id` )
    REFERENCES `tramitador`.`proceso` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`etapa`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`etapa` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `tarea_id` INT(10) UNSIGNED NOT NULL ,
  `usuario_id` INT(10) UNSIGNED NULL DEFAULT NULL ,
  `pendiente` TINYINT(1) NOT NULL ,
  `created_at` VARCHAR(45) NULL DEFAULT NULL ,
  `updated_at` VARCHAR(45) NULL DEFAULT NULL ,
  `ended_at` DATETIME NULL DEFAULT NULL ,
  `tramite_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_etapa_tarea1` (`tarea_id` ASC) ,
  INDEX `fk_etapa_usuario1` (`usuario_id` ASC) ,
  INDEX `fk_etapa_tramite1` (`tramite_id` ASC) ,
  CONSTRAINT `etapa_ibfk_1`
    FOREIGN KEY (`tramite_id` )
    REFERENCES `tramitador`.`tramite` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_etapa_tarea1`
    FOREIGN KEY (`tarea_id` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_etapa_usuario1`
    FOREIGN KEY (`usuario_id` )
    REFERENCES `tramitador`.`usuario` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 57
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`formulario`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`formulario` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `proceso_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_formulario_proceso1` (`proceso_id` ASC) ,
  CONSTRAINT `fk_formulario_proceso1`
    FOREIGN KEY (`proceso_id` )
    REFERENCES `tramitador`.`proceso` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`campo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`campo` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(32) NOT NULL ,
  `readonly` TINYINT(1) NOT NULL DEFAULT 0 ,
  `posicion` INT(10) UNSIGNED NOT NULL ,
  `tipo` VARCHAR(32) NOT NULL ,
  `formulario_id` INT(10) UNSIGNED NOT NULL ,
  `etiqueta` VARCHAR(128) NOT NULL ,
  `validacion` VARCHAR(128) NOT NULL ,
  `dependiente_campo` VARCHAR(32) NULL ,
  `dependiente_valor` VARCHAR(32) NULL ,
  `datos` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_campo_formulario1` (`formulario_id` ASC) ,
  CONSTRAINT `campo_ibfk_1`
    FOREIGN KEY (`formulario_id` )
    REFERENCES `tramitador`.`formulario` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`paso`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`paso` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `orden` INT UNSIGNED NOT NULL ,
  `modo` ENUM('edicion','visualizacion') NOT NULL ,
  `formulario_id` INT UNSIGNED NOT NULL ,
  `tarea_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_paso_formulario1` (`formulario_id` ASC) ,
  INDEX `fk_paso_tarea1` (`tarea_id` ASC) ,
  CONSTRAINT `fk_paso_formulario1`
    FOREIGN KEY (`formulario_id` )
    REFERENCES `tramitador`.`formulario` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_paso_tarea1`
    FOREIGN KEY (`tarea_id` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`dato`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`dato` (
  `id` INT(10) UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `valor` TEXT NOT NULL ,
  `tramite_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `nombre` (`nombre` ASC, `tramite_id` ASC) ,
  INDEX `fk_dato_etapa1` (`tramite_id` ASC) ,
  CONSTRAINT `dato_ibfk_1`
    FOREIGN KEY (`tramite_id` )
    REFERENCES `tramitador`.`tramite` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 8
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`usuario_backend`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`usuario_backend` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `usuario` VARCHAR(128) NOT NULL ,
  `password` VARCHAR(255) NOT NULL ,
  `nombre` VARCHAR(128) NOT NULL ,
  `apellidos` VARCHAR(128) NOT NULL ,
  `cuenta_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_usuario_backend_cuenta1` (`cuenta_id` ASC) ,
  CONSTRAINT `fk_usuario_backend_cuenta1`
    FOREIGN KEY (`cuenta_id` )
    REFERENCES `tramitador`.`cuenta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`ci_sessions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`ci_sessions` (
  `session_id` VARCHAR(40) NOT NULL DEFAULT '0' ,
  `ip_address` VARCHAR(16) NOT NULL DEFAULT '0' ,
  `user_agent` VARCHAR(120) NOT NULL ,
  `last_activity` INT(10) UNSIGNED NOT NULL DEFAULT '0' ,
  `user_data` TEXT NOT NULL ,
  PRIMARY KEY (`session_id`) ,
  INDEX `last_activity_idx` (`last_activity` ASC) )
ENGINE = InnoDB
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_general_ci;


-- -----------------------------------------------------
-- Table `tramitador`.`accion`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`accion` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `tipo` VARCHAR(32) NOT NULL ,
  `extra` TEXT NULL ,
  `proceso_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_trigger_proceso1` (`proceso_id` ASC) ,
  CONSTRAINT `fk_trigger_proceso1`
    FOREIGN KEY (`proceso_id` )
    REFERENCES `tramitador`.`proceso` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`widget`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`widget` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `tipo` VARCHAR(32) NOT NULL ,
  `nombre` VARCHAR(128) NOT NULL ,
  `posicion` INT UNSIGNED NOT NULL ,
  `config` TEXT NULL ,
  `cuenta_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_widget_cuenta1` (`cuenta_id` ASC) ,
  CONSTRAINT `fk_widget_cuenta1`
    FOREIGN KEY (`cuenta_id` )
    REFERENCES `tramitador`.`cuenta` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`evento`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`evento` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `instante` ENUM('antes','despues') NOT NULL ,
  `tarea_id` INT(10) UNSIGNED NOT NULL ,
  `accion_id` INT UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_evento_tarea1` (`tarea_id` ASC) ,
  INDEX `fk_evento_accion1` (`accion_id` ASC) ,
  CONSTRAINT `fk_evento_tarea1`
    FOREIGN KEY (`tarea_id` )
    REFERENCES `tramitador`.`tarea` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `fk_evento_accion1`
    FOREIGN KEY (`accion_id` )
    REFERENCES `tramitador`.`accion` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `tramitador`.`dato_seguimiento`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `tramitador`.`dato_seguimiento` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT ,
  `nombre` VARCHAR(128) NOT NULL ,
  `valor` TEXT NOT NULL ,
  `etapa_id` INT(10) UNSIGNED NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_dato_seguimiento_etapa1` (`etapa_id` ASC) ,
  UNIQUE INDEX `nombre_etapa` (`nombre` ASC, `etapa_id` ASC) ,
  CONSTRAINT `fk_dato_seguimiento_etapa1`
    FOREIGN KEY (`etapa_id` )
    REFERENCES `tramitador`.`etapa` (`id` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
