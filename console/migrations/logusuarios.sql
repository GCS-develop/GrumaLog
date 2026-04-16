-- Log de auditoría de usuarios: creación, actualización, eliminación y asignación de roles
-- Base de datos: SQL Server
-- Ejecutar manualmente en la BD del proyecto

IF NOT EXISTS (SELECT * FROM sys.tables WHERE name = 'logusuarios')
BEGIN
    CREATE TABLE [logusuarios] (
        [id]                   INT            NOT NULL IDENTITY(1,1),
        [accion]               NVARCHAR(30)   NOT NULL, -- CREAR | ACTUALIZAR | ELIMINAR | ASIGNAR_ROL | REVOCAR_ROL
        [id_usuario_afectado]  INT            NOT NULL, -- ID del usuario sobre el que se actuó
        [username_afectado]    NVARCHAR(255)  NOT NULL, -- Username denormalizado
        [detalles]             NVARCHAR(MAX)  NULL,     -- JSON con campos cambiados o roles afectados
        [created_at]           INT            NOT NULL,
        [created_by]           INT            NOT NULL, -- ID del admin que realizó la acción
        CONSTRAINT [PK_logusuarios] PRIMARY KEY ([id])
    );

    CREATE INDEX [idx_logusuarios_afectado] ON [logusuarios] ([id_usuario_afectado]);
    CREATE INDEX [idx_logusuarios_creador]  ON [logusuarios] ([created_by]);
    CREATE INDEX [idx_logusuarios_accion]   ON [logusuarios] ([accion]);
    CREATE INDEX [idx_logusuarios_fecha]    ON [logusuarios] ([created_at]);
END
