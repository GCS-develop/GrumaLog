-- Tabla de auditoría de eliminaciones en pedidos (OC, Item, SKU)
-- Registra quién borró qué, cuándo y desde qué nivel
CREATE TABLE logborradopedido (
    id                  INT IDENTITY(1,1) PRIMARY KEY,
    idPedido            INT          NOT NULL,
    idOrdenCompra       INT          NULL,
    -- Datos OC denormalizados para referencia rápida sin joins
    consecutivoOC       VARCHAR(20)  NULL,
    tipoDocumentoOC     VARCHAR(10)  NULL,
    codigoCO            VARCHAR(10)  NULL,
    -- Nivel de acción
    accion              VARCHAR(30)  NOT NULL,
    -- 'ELIMINAR_OC'   → se elimina toda la OC del pedido
    -- 'ELIMINAR_ITEM' → se elimina un item (todas las bodegas/SKUs de ese item en esa OC)
    -- 'ELIMINAR_SKU'  → se elimina una fila específica de pedidodetalle
    -- Contexto del item/SKU (NULL cuando accion = ELIMINAR_OC)
    idItem              INT          NULL,
    itemCodigo          VARCHAR(50)  NULL,
    colorCodigo         VARCHAR(50)  NULL,
    tallaCodigo         VARCHAR(50)  NULL,
    idBodega            INT          NULL,
    bodegaCodigo        VARCHAR(50)  NULL,
    -- Unidades antes del borrado
    unidadesAntes       INT          NULL,
    -- Auditoría
    created_at          DATETIME     NOT NULL DEFAULT GETDATE(),
    created_by          INT          NOT NULL,
    CONSTRAINT FK_logborradopedido_pedido FOREIGN KEY (idPedido)
        REFERENCES pedido(id),
    CONSTRAINT FK_logborradopedido_user  FOREIGN KEY (created_by)
        REFERENCES [user](id)
);

CREATE INDEX IX_logborradopedido_pedido    ON logborradopedido (idPedido);
CREATE INDEX IX_logborradopedido_oc        ON logborradopedido (idOrdenCompra);
CREATE INDEX IX_logborradopedido_fecha     ON logborradopedido (created_at);
CREATE INDEX IX_logborradopedido_usuario   ON logborradopedido (created_by);
