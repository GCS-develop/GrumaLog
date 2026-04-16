-- Tabla de auditoría de eliminaciones de transferenciaerp
-- Corre: sqlcmd -S SRVICGBD1\APP -d GRUMALOG -E -i logtransferenciaerp.sql
-- Propósito:
--   Antes, cuando un registro en transferenciaerp era eliminado (manual o automático),
--   quedaba un "muñeco sin cabeza": las filas en transferencialogws aún referenciaban
--   un id que ya no existía en transferenciaerp. Esta tabla conserva la cabecera
--   del registro eliminado para poder reconstituir la trazabilidad completa.

CREATE TABLE logtransferenciaerp (
    id                       INT IDENTITY(1,1) PRIMARY KEY,

    -- ID del registro eliminado (clave para JOIN con transferencialogws huérfanos)
    idTransferenciaerpBorrada INT         NOT NULL,

    -- ID del nuevo registro que lo reemplazó (solo en borrados automáticos)
    idTransferenciaerpNueva   INT         NULL,

    -- Causa del borrado
    -- MANUAL_USUARIO      = usuario borró desde la pantalla Integración ERP
    -- AUTO_REGENERACION   = el sistema regeneró la transferencia (módulo conteo / CDSC / traspaso)
    accion                   VARCHAR(30)  NOT NULL,

    -- Copia de los campos clave del registro eliminado
    descripcion              VARCHAR(500) NULL,
    origen                   VARCHAR(5)   NULL,
    documento                VARCHAR(50)  NULL,
    idOrdenCompra            INT          NULL,
    idConectorDinamico       INT          NULL,
    numeroRegistros          INT          NULL,
    enviadoWS                INT          NULL,  -- 1=fue enviado a SIESA, 0=no
    notas                    VARCHAR(500) NULL,

    -- Auditoría del registro original
    creadoOrigEn             DATETIME     NULL,
    creadoOrigPor            INT          NULL,

    -- Auditoría del borrado
    created_at               DATETIME     NOT NULL DEFAULT GETDATE(),
    created_by               INT          NOT NULL DEFAULT 0
);

CREATE INDEX ix_logtransferenciaerp_borrada ON logtransferenciaerp (idTransferenciaerpBorrada);
CREATE INDEX ix_logtransferenciaerp_nueva   ON logtransferenciaerp (idTransferenciaerpNueva);
CREATE INDEX ix_logtransferenciaerp_oc      ON logtransferenciaerp (idOrdenCompra);
GO

PRINT 'logtransferenciaerp creada OK';
