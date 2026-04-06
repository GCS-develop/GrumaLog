<?php

use yii\db\Migration;

/**
 * Tablas para el módulo Pedido Monacho.
 * Ejecutar: php yii migrate
 */
class m241226_000001_create_monacho_tables extends Migration
{
    public function safeUp()
    {
        $db = $this->db;

        // monacho_pedido
        if (!$this->tableExists('monacho_pedido')) {
            $db->createCommand("
                CREATE TABLE monacho_pedido (
                    id                  INT IDENTITY(1,1) PRIMARY KEY,
                    proveedor           NVARCHAR(200)   NOT NULL,
                    oc_siesa            NVARCHAR(50)    NULL,
                    oc_icg              NVARCHAR(50)    NULL,
                    fecha_despacho      DATE            NULL,
                    contador_ean_inicio INT             NOT NULL CONSTRAINT DF_monped_contador DEFAULT 1,
                    estado              NVARCHAR(20)    NOT NULL CONSTRAINT DF_monped_estado DEFAULT 'BORRADOR',
                    notas               NVARCHAR(MAX)   NULL,
                    created_at          DATETIME        NULL,
                    created_by          INT             NULL,
                    updated_at          DATETIME        NULL,
                    updated_by          INT             NULL
                )
            ")->execute();
            echo "  > Tabla monacho_pedido creada.\n";
        } else {
            echo "  > Tabla monacho_pedido ya existe.\n";
        }

        // monacho_articulo
        if (!$this->tableExists('monacho_articulo')) {
            $db->createCommand("
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
                    costo           DECIMAL(18,2)   NULL CONSTRAINT DF_monart_costo DEFAULT 0,
                    precio_venta    DECIMAL(18,2)   NULL CONSTRAINT DF_monart_pventa DEFAULT 0,
                    margen          DECIMAL(5,2)    NULL CONSTRAINT DF_monart_margen DEFAULT 0,
                    rango           NVARCHAR(50)    NULL,
                    pvp_mayorista   DECIMAL(18,2)   NULL CONSTRAINT DF_monart_pvp DEFAULT 0,
                    CONSTRAINT FK_monacho_art_pedido FOREIGN KEY (pedido_id)
                        REFERENCES monacho_pedido(id)
                )
            ")->execute();
            echo "  > Tabla monacho_articulo creada.\n";
        } else {
            echo "  > Tabla monacho_articulo ya existe.\n";
        }

        // monacho_detalle
        if (!$this->tableExists('monacho_detalle')) {
            $db->createCommand("
                CREATE TABLE monacho_detalle (
                    id              INT IDENTITY(1,1) PRIMARY KEY,
                    articulo_id     INT             NOT NULL,
                    color           NVARCHAR(100)   NOT NULL,
                    talla           NVARCHAR(20)    NOT NULL,
                    cantidad        INT             NOT NULL CONSTRAINT DF_mondet_cant DEFAULT 0,
                    ean13           NVARCHAR(13)    NULL,
                    CONSTRAINT FK_monacho_det_art FOREIGN KEY (articulo_id)
                        REFERENCES monacho_articulo(id)
                )
            ")->execute();
            echo "  > Tabla monacho_detalle creada.\n";
        } else {
            echo "  > Tabla monacho_detalle ya existe.\n";
        }
    }

    public function safeDown()
    {
        $db = $this->db;
        if ($this->tableExists('monacho_detalle'))  { $db->createCommand('DROP TABLE monacho_detalle')->execute(); }
        if ($this->tableExists('monacho_articulo')) { $db->createCommand('DROP TABLE monacho_articulo')->execute(); }
        if ($this->tableExists('monacho_pedido'))   { $db->createCommand('DROP TABLE monacho_pedido')->execute(); }
    }

    protected function tableExists($table)
    {
        return (int) $this->db->createCommand(
            "SELECT COUNT(*) FROM sysobjects WHERE name = :t AND xtype = 'U'",
            [':t' => $table]
        )->queryScalar() > 0;
    }
}
