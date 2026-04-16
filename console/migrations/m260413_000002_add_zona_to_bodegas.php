<?php

use yii\db\Migration;

/**
 * Agrega la columna `zona` (nullable INT) a la tabla `bodegas`.
 *
 * La zona permite agrupar tiendas para que los usuarios de la PDA
 * puedan cambiar su bodega de recepción solo dentro de su grupo.
 */
class m260413_000002_add_zona_to_bodegas extends Migration
{
    public function safeUp()
    {
        $this->addColumn('bodegas', 'zona', $this->integer()->null()->defaultValue(null)->comment('Zona de agrupación de tiendas para cambio de bodega destino en PDA'));
    }

    public function safeDown()
    {
        $this->dropColumn('bodegas', 'zona');
    }
}
