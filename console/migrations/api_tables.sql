-- =====================================================
-- Tablas para módulo API de Agendamiento de Proveedores
-- GRUMALog - SQL Server
-- =====================================================

-- Tabla de credenciales API por NIT de proveedor
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='agendaapicredencial' AND xtype='U')
CREATE TABLE agendaapicredencial (
    id         INT IDENTITY(1,1) PRIMARY KEY,
    nit        NVARCHAR(20)  NOT NULL,
    razonSocial NVARCHAR(200) NULL,
    token      NVARCHAR(64)  NOT NULL UNIQUE,
    activo     INT           NOT NULL DEFAULT 1,
    created_at INT           NULL,
    created_by INT           NULL,
    updated_at INT           NULL,
    updated_by INT           NULL
);

-- Tabla de horarios bloqueados por administradores
IF NOT EXISTS (SELECT * FROM sysobjects WHERE name='horariobloqueado' AND xtype='U')
CREATE TABLE horariobloqueado (
    id                   INT IDENTITY(1,1) PRIMARY KEY,
    idAgendaPresupuesto  INT          NULL,
    fecha                DATE         NULL,
    horaInicio           TIME(0)      NULL,
    horaFin              TIME(0)      NULL,
    motivo               NVARCHAR(200) NULL,
    todoElDia            INT          NOT NULL DEFAULT 0,
    activo               INT          NOT NULL DEFAULT 1,
    created_at           INT          NULL,
    created_by           INT          NULL,
    updated_at           INT          NULL,
    updated_by           INT          NULL
);

-- Parámetros requeridos (insertar si no existen)
IF NOT EXISTS (SELECT 1 FROM parametroscontrol WHERE codigo = 'HAC')
    INSERT INTO parametroscontrol (codigo, nombre, valor, tipoDato)
    VALUES ('HAC', 'Horas anticipacion cancelacion/reprogramacion agenda', '1', 'integer');

IF NOT EXISTS (SELECT 1 FROM parametroscontrol WHERE codigo = 'APK')
    INSERT INTO parametroscontrol (codigo, nombre, valor, tipoDato)
    VALUES ('APK', 'API Key compartida portal proveedores', 'GRUMA_PROV_2024_KEY', 'string');
