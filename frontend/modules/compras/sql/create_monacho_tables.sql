-- =============================================
-- Módulo Compras - Tablas Pedido Monacho
-- Ejecutar una sola vez en la BD principal
-- =============================================

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='monacho_pedido' AND xtype='U')
CREATE TABLE monacho_pedido (
    id                  INT IDENTITY(1,1) PRIMARY KEY,
    proveedor           NVARCHAR(200)   NOT NULL,
    oc_siesa            NVARCHAR(50)    NULL,
    oc_icg              NVARCHAR(50)    NULL,
    fecha_despacho      DATE            NULL,
    contador_ean_inicio INT             NOT NULL DEFAULT 1,
    estado              NVARCHAR(20)    NOT NULL DEFAULT 'BORRADOR',
    notas               NVARCHAR(MAX)   NULL,
    created_at          DATETIME        NULL,
    created_by          INT             NULL,
    updated_at          DATETIME        NULL,
    updated_by          INT             NULL
);

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='monacho_articulo' AND xtype='U')
CREATE TABLE monacho_articulo (
    id              INT IDENTITY(1,1) PRIMARY KEY,
    pedido_id       INT             NOT NULL,
    codigo          INT             NOT NULL,
    descripcion     NVARCHAR(300)   NOT NULL,
    referencia      NVARCHAR(100)   NULL,
    estilo1         NVARCHAR(100)   NULL,
    estilo2         NVARCHAR(100)   NULL,
    estilo3         NVARCHAR(100)   NULL,
    estilo4         NVARCHAR(100)   NULL,
    estilo5         NVARCHAR(100)   NULL,
    concepto        NVARCHAR(100)   NULL,
    consumidor      NVARCHAR(100)   NULL,
    universo        NVARCHAR(100)   NULL,
    prenda          NVARCHAR(100)   NULL,
    tendencia       NVARCHAR(100)   NULL,
    costo           DECIMAL(18,2)   NULL DEFAULT 0,
    precio_venta    DECIMAL(18,2)   NULL DEFAULT 0,
    margen          DECIMAL(5,2)    NULL DEFAULT 0,
    rango           NVARCHAR(50)    NULL,
    pvp_mayorista   DECIMAL(18,2)   NULL DEFAULT 0,
    CONSTRAINT FK_monacho_art_pedido FOREIGN KEY (pedido_id) REFERENCES monacho_pedido(id)
);

IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='monacho_detalle' AND xtype='U')
CREATE TABLE monacho_detalle (
    id              INT IDENTITY(1,1) PRIMARY KEY,
    articulo_id     INT             NOT NULL,
    color           NVARCHAR(100)   NOT NULL,
    talla           NVARCHAR(20)    NOT NULL,
    cantidad        INT             NOT NULL DEFAULT 0,
    ean13           NVARCHAR(13)    NULL,
    CONSTRAINT FK_monacho_det_art FOREIGN KEY (articulo_id) REFERENCES monacho_articulo(id)
);
