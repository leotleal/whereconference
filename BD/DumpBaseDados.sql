/*================================================================================*/
/* DDL SCRIPT                                                                     */
/*================================================================================*/
/*  Title    :                                                                    */
/*  FileName : Modelagem.ecm                                                      */
/*  Platform : MySQL 5                                                            */
/*  Version  : Concept                                                            */
/*  Date     : sábado, 20 de junho de 2015                                        */
/*================================================================================*/
/*================================================================================*/
/* CREATE TABLES                                                                  */
/*================================================================================*/

CREATE TABLE `Categorias` (
  `CategoriaId` INT AUTO_INCREMENT NOT NULL,
  `Nome` VARCHAR(40),
  CONSTRAINT `PK_Categorias` PRIMARY KEY (`CategoriaId`)
);

CREATE TABLE `Areas` (
  `AreaId` INT AUTO_INCREMENT NOT NULL,
  `Nome` VARCHAR(40),
  `CategoriaId` INT NOT NULL,
  CONSTRAINT `PK_Areas` PRIMARY KEY (`AreaId`)
);

CREATE TABLE `Conferencias` (
  `ConfereciasId` INT AUTO_INCREMENT NOT NULL,
  `Nome` VARCHAR(40),
  `DataIni` DATE,
  `DataFim` DATE,
  `Local` VARCHAR(40),
  `CategoriaId` INT NOT NULL,
  `AreaId` INT NOT NULL,
  CONSTRAINT `PK_Conferencias` PRIMARY KEY (`ConfereciasId`)
);

/*================================================================================*/
/* CREATE FOREIGN KEYS                                                            */
/*================================================================================*/

ALTER TABLE `Areas`
  ADD CONSTRAINT `FK_Areas_Categorias`
  FOREIGN KEY (`CategoriaId`) REFERENCES `Categorias` (`CategoriaId`);

ALTER TABLE `Conferencias`
  ADD CONSTRAINT `FK_Conferencias_Categorias`
  FOREIGN KEY (`CategoriaId`) REFERENCES `Categorias` (`CategoriaId`);

ALTER TABLE `Conferencias`
  ADD CONSTRAINT `FK_Conferencias_Areas`
  FOREIGN KEY (`AreaId`) REFERENCES `Areas` (`AreaId`);

