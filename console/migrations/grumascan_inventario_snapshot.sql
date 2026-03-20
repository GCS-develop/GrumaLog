-- ============================================================
-- Tabla: grumascan_inventario_snapshot
-- Propósito: Almacena el estado del inventario Siesa (t400_cm_existencia)
--            al momento de crear un grumascanconteo, para que el reporte
--            conteo vs inventario compare contra ese momento y no contra
--            el inventario actual.
-- ============================================================

CREATE TABLE grumascan_inventario_snapshot (
    id                  INT             IDENTITY(1,1)   NOT NULL,
    idgrumascanconteo   INT             NOT NULL,
    idItem              INT             NULL,           -- FK a item.id (NULL si el barcode no estaba en inventario local)
    codigoBarras        NVARCHAR(50)    NOT NULL,
    codigoBodega        NVARCHAR(20)    NOT NULL,
    existencia          DECIMAL(18,2)   NOT NULL        DEFAULT 0,
    fecha_snapshot      DATETIME        NOT NULL        DEFAULT GETDATE(),

    CONSTRAINT PK_grumascan_inventario_snapshot
        PRIMARY KEY (id),

    CONSTRAINT FK_snap_conteo
        FOREIGN KEY (idgrumascanconteo)
        REFERENCES grumascanconteo(id),

    CONSTRAINT FK_snap_item
        FOREIGN KEY (idItem)
        REFERENCES item(id)
);
GO

-- Índice para búsquedas por conteo (consulta principal del reporte)
CREATE INDEX IX_snap_idconteo
    ON grumascan_inventario_snapshot (idgrumascanconteo);
GO

-- Índice para búsquedas por bodega (útil en diagnósticos)
CREATE INDEX IX_snap_bodega
    ON grumascan_inventario_snapshot (codigoBodega);
GO
