-- Migración: agregar columna cajas a transferenciatransitoexcel
-- Fecha: 2026-03-20
-- Descripción: Permite ingresar el número de cajas desde el Excel de Transferencia
--              en tránsito salida, en lugar de usar el valor quemado 1.

ALTER TABLE transferenciatransitoexcel
    ADD cajas INT NOT NULL DEFAULT 1;
