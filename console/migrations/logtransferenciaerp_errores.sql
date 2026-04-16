-- Agrega columna para capturar snapshot de errores SIESA antes de borrar la transferencia
-- Corre: sqlcmd -S SRVICGBD1\APP -d GRUMALOG -U sa -P "MasterKey.." -i "C:\Apache24\htdocs\GRUMALog\console\migrations\logtransferenciaerp_errores.sql"

ALTER TABLE logtransferenciaerp
    ADD erroresJson NVARCHAR(MAX) NULL;
GO

PRINT 'Columna erroresJson agregada a logtransferenciaerp OK';
