<?php

namespace frontend\models\forms;

use frontend\models\Bodegas;
use yii\base\Model;

class ExportInvFisicoForm extends Model
{
    public $fecha;     // YYYY-MM-DD
    public $idbodega;  // int

    public function rules()
    {
        return [
            [['fecha', 'idbodega'], 'required'],
            ['fecha', 'date', 'format' => 'php:Y-m-d'],
            ['idbodega', 'integer'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'fecha' => 'Fecha',
            'idbodega' => 'Bodega',
        ];
    }

    /**
     * En un Form Model NO hay relaciones AR.
     * Esto es un helper para obtener la bodega.
     */
    public function getBodegaModel(): ?Bodegas
    {
        return $this->idbodega ? Bodegas::findOne((int)$this->idbodega) : null;
    }
}
