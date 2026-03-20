-- Migración: agregar columna cajas a transferenciaordencompraexcel
-- Fecha: 2026-03-19
-- Descripción: Permite ingresar el número de cajas desde el Excel de Entrada OC
--              en lugar de usar el valor quemado 1.

ALTER TABLE `transferenciaordencompraexcel`
    ADD COLUMN `cajas` INT NOT NULL DEFAULT 1 COMMENT 'Número de cajas ingresado en el Excel de Entrada OC';
