-- Tabla de auditoría de eliminaciones/zereo de conteos de entrega de mercancía
-- Registra quién borró qué, cuándo, y desde qué acción
CREATE TABLE logborradoconteo (
    id                              INT IDENTITY(1,1) PRIMARY KEY,
    idProgramacionEntregaMercancia  INT          NOT NULL,
    idAgendaEntregaMercancia        INT          NOT NULL,
    -- Datos de la OC para referencia rápida sin joins
    consecutivoOC                   VARCHAR(20)  NULL,
    tipoDocumentoOC                 VARCHAR(10)  NULL,
    codigoCO                        VARCHAR(10)  NULL,
    -- Qué se borró
    accion                          VARCHAR(50)  NOT NULL,
    -- 'BORRAR_TODOS'   → deleteconteos (pone todo a 0)
    -- 'REDUCIR_ITEM'   → reducirconteositem
    -- 'REDUCIR_FILA'   → reducirconteosfila (item+color)
    -- 'REDUCIR_TALLA'  → reducirconteostalla (item+color+talla)
    -- 'ELIMINAR_FILA'  → delete (fila individual)
    item                            VARCHAR(50)  NULL,
    color                           VARCHAR(50)  NULL,
    talla                           VARCHAR(50)  NULL,
    unidadesAntes                   INT          NULL,   -- suma antes del borrado
    unidadesBorradas                INT          NULL,   -- cuántas se quitaron
    -- Auditoría
    created_at                      DATETIME     NOT NULL DEFAULT GETDATE(),
    created_by                      INT          NOT NULL,
    CONSTRAINT FK_logborradoconteo_prog FOREIGN KEY (idProgramacionEntregaMercancia)
        REFERENCES programacionentregamercancia(id),
    CONSTRAINT FK_logborradoconteo_agenda FOREIGN KEY (idAgendaEntregaMercancia)
        REFERENCES agendaentregamercancia(id),
    CONSTRAINT FK_logborradoconteo_user  FOREIGN KEY (created_by)
        REFERENCES [user](id)
);

-- Índices para consultas frecuentes
CREATE INDEX IX_logborradoconteo_prog  ON logborradoconteo (idProgramacionEntregaMercancia);
CREATE INDEX IX_logborradoconteo_agenda ON logborradoconteo (idAgendaEntregaMercancia);
CREATE INDEX IX_logborradoconteo_fecha ON logborradoconteo (created_at);
