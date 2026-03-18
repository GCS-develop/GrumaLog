-- =============================================
-- Módulo Compras - Tablas de importación Excel
-- =============================================

-- Tabla cabecera: un registro por archivo subido
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='comprasimportacion' AND xtype='U')
CREATE TABLE comprasimportacion (
    id              INT IDENTITY(1,1) PRIMARY KEY,
    numeroRegistros INT             NULL,
    totalUnidades   FLOAT           NULL,
    estadoSiesa     INT             NULL,   -- NULL=sin enviar, 1=exitoso, 0=error
    mensajeSiesa    NVARCHAR(500)   NULL,
    estadoCarvajal  INT             NULL,   -- NULL=sin enviar, 1=exitoso, 0=error
    mensajeCarvajal NVARCHAR(500)   NULL,
    created_at      DATETIME        NULL,
    created_by      INT             NULL,
    updated_at      DATETIME        NULL,
    updated_by      INT             NULL
);

-- Tabla detalle: filas del Excel
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='comprasimportaciondetalle' AND xtype='U')
CREATE TABLE comprasimportaciondetalle (
    id              INT IDENTITY(1,1) PRIMARY KEY,
    idImportacion   INT             NOT NULL,
    cc              NVARCHAR(50)    NULL,
    codigo          NVARCHAR(100)   NULL,
    categoria       NVARCHAR(100)   NULL,
    consumidor      NVARCHAR(100)   NULL,
    universo        NVARCHAR(100)   NULL,
    producto        NVARCHAR(200)   NULL,
    tendencia       NVARCHAR(100)   NULL,
    rn              NVARCHAR(50)    NULL,
    talla           NVARCHAR(20)    NULL,
    color           NVARCHAR(50)    NULL,
    uds             FLOAT           NULL DEFAULT 0,
    mes             NVARCHAR(50)    NULL,
    tienda          NVARCHAR(100)   NULL,
    CONSTRAINT fk_comprasimpdet_imp FOREIGN KEY (idImportacion) REFERENCES comprasimportacion(id)
);
