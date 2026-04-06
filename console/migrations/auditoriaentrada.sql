-- Tabla cabecera de auditoría de entrada
CREATE TABLE auditoriaentrada (
    id                        INT IDENTITY(1,1) PRIMARY KEY,
    idTransferenciaerp        INT           NOT NULL,
    centroOperacionOrdenCompra VARCHAR(10)  NULL,
    tipoDocumentoOrdenCompra   VARCHAR(10)  NULL,
    consecutivoOrdenCompra     INT          NOT NULL,
    descripcion                NVARCHAR(255) NULL,
    idestado                   INT          NOT NULL DEFAULT 1,  -- 1=Abierta  2=Finalizada
    created_at                 DATETIME     NULL,
    created_by                 INT          NULL,
    updated_at                 DATETIME     NULL,
    updated_by                 INT          NULL
);

-- Tabla de escaneos por usuario
CREATE TABLE auditoriaentradadetalle (
    id                  INT IDENTITY(1,1) PRIMARY KEY,
    idauditoriaentrada  INT          NOT NULL,
    ean                 VARCHAR(50)  NULL,
    item                INT          NOT NULL,
    color               VARCHAR(50)  NULL,
    talla               VARCHAR(20)  NULL,
    cantidad            INT          NOT NULL DEFAULT 1,
    created_at          DATETIME     NULL,
    created_by          INT          NULL,
    updated_at          DATETIME     NULL,
    updated_by          INT          NULL,
    FOREIGN KEY (idauditoriaentrada) REFERENCES auditoriaentrada(id)
);

-- Tabla de usuarios habilitados para auditoría de entrada
CREATE TABLE userauditoriaconteo (
    id                   INT IDENTITY(1,1) PRIMARY KEY,
    idUser               INT NOT NULL,
    idEmpleadoLogistica  INT NOT NULL,
    created_at           DATETIME NULL,
    created_by           INT NULL,
    updated_at           DATETIME NULL,
    updated_by           INT NULL,
    CONSTRAINT UQ_userauditoriaconteo_idUser UNIQUE (idUser),
    CONSTRAINT UQ_userauditoriaconteo_idEmp  UNIQUE (idEmpleadoLogistica),
    CONSTRAINT FK_userauditoriaconteo_user   FOREIGN KEY (idUser)              REFERENCES [user](id),
    CONSTRAINT FK_userauditoriaconteo_emp    FOREIGN KEY (idEmpleadoLogistica) REFERENCES empleadologistica(id)
);
