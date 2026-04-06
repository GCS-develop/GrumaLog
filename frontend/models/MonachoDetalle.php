<?php

namespace frontend\models;

use common\models\MonachoParser;

/**
 * Detalle: combinación color/talla con cantidad y EAN13.
 *
 * @property int    $id
 * @property int    $articulo_id
 * @property string $color
 * @property string $talla
 * @property int    $cantidad
 * @property string $ean13
 */
class MonachoDetalle extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'monacho_detalle';
    }

    public function rules()
    {
        return [
            [['articulo_id', 'color', 'talla'], 'required'],
            [['articulo_id', 'cantidad'], 'integer'],
            [['color'], 'string', 'max' => 100],
            [['talla'], 'string', 'max' => 20],
            [['ean13'], 'string', 'max' => 13],
            [['ean13'], function ($attr) {
                if (!empty($this->$attr) && !MonachoParser::validarEan13($this->$attr)) {
                    $this->addError($attr, 'EAN13 inválido (13 dígitos, dígito de control incorrecto).');
                }
            }],
        ];
    }

    public function getArticulo()
    {
        return $this->hasOne(MonachoArticulo::class, ['id' => 'articulo_id']);
    }

    public function isValid()
    {
        return !empty($this->ean13) && MonachoParser::validarEan13($this->ean13);
    }
}
