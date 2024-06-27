<?php

namespace frontend\models;

use Yii;

use common\models\InventarioWs;

/**
 * This is the model class for table "traspasodetalle".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 *
 * @property Item $item
 * @property Traspaso $traspaso
 */
class Traspasodetalle extends \yii\db\ActiveRecord
{
    public $bodegaorigen;
    public $bodegadestino;
    public $numerocajas;
    public $tipodocumento;
    
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalle';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTraspaso', 'idItem'], 'required'],
            [['idTraspaso', 'idItem', 'cantidad'], 'integer'],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            [['idItem'], 'exist', 'skipOnError' => true, 'targetClass' => Item::class, 'targetAttribute' => ['idItem' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idTraspaso' => 'ID Traspaso',
            'idItem' => 'EAN',
            'cantidad' => 'cantidad',
            'bodegaorigen' => 'Bodega Origen',
            'bodegadestino' => 'Bodega Destino',
            'tipodocumento' => 'Tipo Documento',
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[Traspaso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }

    public static function getInventario ($codigobarras, $codigobodega = null){

        $existencia = 0;
        $existenciaBodega = 0;

        if ($codigobarras){

            $modelinventario = new InventariosWs();
            $lista = $modelinventario->getAllInventariosSiesa($codigobarras);
            foreach($lista as $bodega){

                $existencia = $existencia + $bodega['CantidadExistente'];

                if ($codigobodega != null){
                    if ($bodega['Bodega'] == $codigobodega) {
                        $existenciaBodega = $existenciaBodega + $bodega['CantidadExistente'];
                    }
                }
            }
        }

        if ($codigobodega != null){
            $existencia = $existenciaBodega;
        }

        return $existencia;
    }
}
