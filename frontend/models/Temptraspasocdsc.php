<?php

namespace frontend\models;

use Yii;

/**
 * This is the model class for table "temptraspasocdsc".
 *
 * @property int $id
 * @property int|null $idCentroOperacion
 * @property int $idBodegaOrigen
 * @property int $idBodegaDestino
 * @property int $numeroCajas
 * @property int|null $idTipoDocumento
 * @property float|null $consecutivo
 * @property int|null $idEstado
 * @property int|null $idUltimoItem
 * @property int|null $transferenciaerp
 * @property int|null $tipoMovimiento
 */
class Temptraspasocdsc extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temptraspasocdsc';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idCentroOperacion', 'idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idTipoDocumento', 'idEstado', 'idUltimoItem', 'transferenciaerp', 'tipoMovimiento'], 'integer'],
            [['idBodegaOrigen', 'idBodegaDestino', 'numeroCajas', 'idConteoFactura', 'idConteoDestino'], 'required'],
            [['consecutivo'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idCentroOperacion' => 'Id Centro Operacion',
            'idBodegaOrigen' => 'Id Bodega Origen',
            'idBodegaDestino' => 'Id Bodega Destino',
            'numeroCajas' => 'Numero Cajas',
            'idTipoDocumento' => 'Id Tipo Documento',
            'consecutivo' => 'Consecutivo',
            'idEstado' => 'Id Estado',
            'idUltimoItem' => 'Id Ultimo Item',
            'transferenciaerp' => 'Transferenciaerp',
            'tipoMovimiento' => 'Tipo Movimiento',
        ];
    }

    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::className(), ['id' => 'idTipoDocumento']);
    }

    public static function crearRegistro($idtransferenciaerp, $model){
        $traspaso = new Temptraspasocdsc();
        $traspaso->idCentroOperacion = $model->factura->idCentroOperacionMovimiento;
        $traspaso->idBodegaOrigen = $model->factura->idBodegaMovimiento;
        $traspaso->idBodegaDestino = $model->idCentroOperacion;
        $traspaso->numeroCajas = 1;
        $traspaso->idTipoDocumento = $model->factura->idTipoDocumentoMovimiento;
        $traspaso->consecutivo = $idtransferenciaerp;
        $traspaso->idEstado = 3;
        $traspaso->idUltimoItem = null;
        $traspaso->transferenciaerp = 1;
        $traspaso->tipoMovimiento = 3;
        $traspaso->idConteoFactura = $model->idConteocdscdestinofactura;
        $traspaso->idConteoDestino = $model->id;

        if (!$traspaso->save()){
            var_dump($traspaso->getErrors()); die("hola");
            return null;
        }
        return $traspaso;
    }

    public static function crearDocumentoDefinitivo ($consecutivo, $idconteofactura, $idconteodestino, $idtransferenciaerp){

        $modeltemp = Temptraspasocdsc::find()
                        ->where([
                            'consecutivo' => $consecutivo,
                            'idConteoFactura' => $idconteofactura,
                            'idConteoDestino' => $idconteodestino
                        ])->one();

        $model = new Traspaso();
        $model->attributes = $modeltemp->attributes;
        unset($model->id);
        unset($model->transferenciaerp);
        unset($model->consecutivo);

        $model->transferenciaerp = 0;

        if ($model->save()){

            $query = Temptraspasocdscdetalle::find()
                            ->where([
                                'idTraspaso' => $modeltemp->id,
                                'idConteoFactura' => $idconteofactura,
                                'idConteoDestino' => $idconteodestino
                            ]);

            $registros = $query->all();
            
            foreach ($registros as $detalle) {

                $modeldetalle = new Traspasodetalle();

                $modeldetalle->attributes = $detalle->attributes;
                unset($modeldetalle->id);
                unset($modeldetalle->idTraspaso);

                $modeldetalle->idTraspaso = $model->id;
                $modeldetalle->codigoitem = $modeldetalle->item->codigoBarras;

                if (!$modeldetalle->save()){
                    var_dump($modeldetalle->getErrors()); die("Detalle Traspaso");
                }
            }

            $id = $model->id;
            $model = Traspaso::findOne(['id' => $id]);
            $model->consecutivo = $id;
            $model->save();

            return $model;
        }else{
            var_dump($model->getErrors()); die("Error Traspaso DEfinitivo");
        }

        return null;

    }
}
