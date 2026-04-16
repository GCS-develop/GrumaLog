-- =============================================================
-- Módulo: Calificación de Proveedores
-- Tabla: calificacionproveedor
-- Base: SQL Server (GRUMALOG)
-- =============================================================

CREATE TABLE calificacionproveedor (
    id                  INT           IDENTITY(1,1) PRIMARY KEY,
    id_ordendecompra    INT           NULL,             -- FK a ordendecompra.id
    numero_oc           VARCHAR(50)   NOT NULL,         -- Ej: "2CA-00004648"
    id_proveedor        INT           NULL,             -- FK a proveedor.id
    proveedor           NVARCHAR(200) NULL,
    categoria           NVARCHAR(100) NULL,
    subcategoria        NVARCHAR(100) NULL,
    tipo_mercancia      NVARCHAR(100) NULL,
    producto            NVARCHAR(200) NULL,
    transportadora      NVARCHAR(100) NULL,
    unidades_ordenadas  INT           NULL,
    unidades_entregadas INT           NULL,
    fecha_entrega_cita  DATE          NULL,
    fecha_entrega_oc    DATE          NULL,
    revisado_por        NVARCHAR(100) NULL,

    -- Criterios de calidad (escala 1-5)
    caja_bulto            TINYINT NULL,
    calibre               TINYINT NULL,
    rotulo                TINYINT NULL,
    contiene_documentos   TINYINT NULL,
    separa_tallas         TINYINT NULL,
    separa_color          TINYINT NULL,
    separa_referencia     TINYINT NULL,
    etiquetado            TINYINT NULL,
    error_tiqueteo        TINYINT NULL,
    homologacion          TINYINT NULL,
    precio                TINYINT NULL,
    novedad               TINYINT NULL,
    factura               TINYINT NULL,
    orden_compra_doc      TINYINT NULL,

    -- Puntuaciones calculadas (almacenadas para histórico)
    calidad_ponderada     DECIMAL(5,2) NULL,  -- Promedio ponderado criterios (1-5)
    oportunidad_formula   DECIMAL(3,1) NULL,  -- 1, 4 o 5
    cantidad_formula      DECIMAL(3,1) NULL,  -- 1, 4 o 5
    puntaje_total         DECIMAL(5,2) NULL,  -- Score final compuesto (1-5)

    observacion           NVARCHAR(MAX) NULL,

    created_at  DATETIME NULL DEFAULT GETDATE(),
    created_by  INT      NULL,
    updated_at  DATETIME NULL,
    updated_by  INT      NULL,

    CONSTRAINT FK_calificacion_oc       FOREIGN KEY (id_ordendecompra) REFERENCES ordendecompra(id),
    CONSTRAINT FK_calificacion_proveedor FOREIGN KEY (id_proveedor)     REFERENCES proveedor(id),
);

-- Índices para ranking y búsqueda
CREATE INDEX IX_calificacion_proveedor  ON calificacionproveedor (id_proveedor);
CREATE INDEX IX_calificacion_oc         ON calificacionproveedor (id_ordendecompra);
CREATE INDEX IX_calificacion_usuario    ON calificacionproveedor (created_by);
CREATE INDEX IX_calificacion_puntaje    ON calificacionproveedor (puntaje_total);

-- =============================================================
-- Migración v2: nuevos campos
-- =============================================================

ALTER TABLE calificacionproveedor
    ADD calidad_producto  TINYINT      NULL,   -- Calidad del Producto (1-5, peso 30%)
        gancho            TINYINT      NULL,   -- Gancho (1-5, solo informativo)
        tallero           TINYINT      NULL;   -- Tallero (1-5, solo informativo)

-- Nota: puntaje_total ahora = oportunidad*10% + cantidad*30% + calidad_criterios*30% + calidad_producto*30%
-- Los registros históricos quedarán con puntaje_total=NULL hasta ser recalculados.

-- =============================================================
-- Migración v3: múltiples revisores, incumplimientos
-- =============================================================

-- Ampliar campo revisado_por para soportar múltiples nombres
ALTER TABLE calificacionproveedor
    ALTER COLUMN revisado_por NVARCHAR(500) NULL;

-- Contador de incumplimientos al momento de calificar (afecta calidad_producto)
ALTER TABLE calificacionproveedor
    ADD num_incumplimientos INT NOT NULL DEFAULT 0;

-- Tabla para registrar incumplimientos por OC
CREATE TABLE calificacion_incumplimiento (
    id               INT           IDENTITY(1,1) PRIMARY KEY,
    id_ordendecompra INT           NOT NULL,
    numero_oc        NVARCHAR(50)  NOT NULL,
    descripcion      NVARCHAR(500) NOT NULL,
    created_at       DATETIME      NOT NULL DEFAULT GETDATE(),
    created_by       INT           NULL,
    CONSTRAINT FK_incumplimiento_oc FOREIGN KEY (id_ordendecompra) REFERENCES ordendecompra(id)
);

CREATE INDEX IX_incumplimiento_oc ON calificacion_incumplimiento (id_ordendecompra);
