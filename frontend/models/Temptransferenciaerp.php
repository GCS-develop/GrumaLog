<?php

namespace frontend\models;

use common\models\OrdendecompraSIESA;
use Yii;
use yii\db\Expression;

/**
 * This is the model class for table "temptransferenciaerp".
 *
 * @property int $id
 * @property string $descripcion
 * @property int|null $documento
 * @property int $numeroRegistros
 * @property int $enviadoWS
 * @property int $idConectorDinamico
 * @property string|null $notas
 * @property string|null $origen
 * @property int|null $idOrdenCompra
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 */
class Temptransferenciaerp extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'temptransferenciaerp';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['descripcion', 'numeroRegistros', 'enviadoWS', 'idConectorDinamico', 'idConteoFactura'], 'required'],
            [['documento', 'numeroRegistros', 'enviadoWS', 'idConectorDinamico', 'idOrdenCompra', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'idConteoFactura', 'idConteoDestino'], 'safe'],
            [['descripcion', 'notas'], 'string', 'max' => 150],
            [['origen'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'descripcion' => 'Descripcion',
            'documento' => 'Documento',
            'numeroRegistros' => 'Numero Registros',
            'enviadoWS' => 'Enviado Ws',
            'idConectorDinamico' => 'Id Conector Dinamico',
            'notas' => 'Notas',
            'origen' => 'Origen',
            'idOrdenCompra' => 'Id Orden Compra',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public static function crearRegistro ($iddocumento, $descripcion, $documento, $notas, $origen, $idconteofactura, $idconteodestino){

        $modelconector = Conectoresdinamicos::find()->where(['idDocumento' => $iddocumento])->one();
    
        $model = new Temptransferenciaerp();
        $model->descripcion = $descripcion;
        $model->documento = $documento;
        $model->numeroRegistros = 0;
        $model->enviadoWS = 0;
        $model->idConectorDinamico = $modelconector->id;
        $model->notas = $notas;
        $model->idConteoFactura = $idconteofactura;
        $model->idConteoDestino = $idconteodestino;
		
		if ($origen != null){
			$model->origen = $origen;
		}

        if (!$model->save()){
            //var_dump($model->getErrors()); die("HOLA");
            return null;
        }

        return $model;
    }

    public static function generarTransferenciaTemporal ($idtransferenciaerp, $idconteofactura, $idconteodestino = null){

        $error = 0;
        $query = Temptransferenciaerp::find()
            ->where(['id' => $idtransferenciaerp,
                                'idConteoFactura' => $idconteofactura
                            ]);

        // Si idConteoDestino es NULL, no se aplica el filtro adicional
        if ($idconteodestino !== null) {
            $query->andWhere(['idConteoDestino' => $idconteodestino]);
        }

        $registros = $query->all();
        //var_dump($idtransferenciaerp . '-' . $idconteofactura . '-' . $idconteodestino); die("fin");
        
        foreach ($registros as $registro) {

            $model = new Transferenciaerp();

            $model->attributes = $registro->attributes;
            unset($model->id);

            if ($model->save()){

                $traspaso = Temptraspasocdsc::crearDocumentoDefinitivo($idtransferenciaerp, $idconteofactura, $idconteodestino, $model->id);

                $detalles = Temptransferenciatransitoexcel::find()->where(['idTransferenciaerp' => $idtransferenciaerp])->all();

                $numeroRegistros = 0;
                foreach($detalles as $detalle){
                    $modeldetalle = new Transferenciatransitoexcel();

                    $modeldetalle->attributes = $detalle->attributes;
                    unset($modeldetalle->id);
                    unset($modeldetalle->idTransferenciaerp);
                    unset($modeldetalle->numero);

                    $modeldetalle->idTransferenciaerp = $model->id;
                    $modeldetalle->numero = $modeldetalle->tipoDocumento . $traspaso->id;
                    if (!$modeldetalle->save()){
                        var_dump($modeldetalle->getErrors()); die('STOP Transferencia');
                    }
                    $numeroRegistros = $numeroRegistros + 1;
                }

                $id = $model->id;
                $model = Transferenciaerp::findOne(['id' => $id]);

                $buscar = "Traspaso CDSC: ";
                
                $numero = 'Traspaso CDSC: ' . $idconteodestino;
                $numero_nuevo = 'Traspaso CDSC: ' . $traspaso->id;

                $model->notas = str_replace($numero, $numero_nuevo, $model->notas);
                $model->descripcion = str_replace($numero, $numero_nuevo, $model->descripcion);
                
                $model->numeroRegistros = $numeroRegistros;

                $model->save(); 

                // Ejecutar Transferencia
                $error = Transferenciatransitoexcel::transferenciaSalidaWS ($model->id, Yii::$app->user->identity->username);

                if ($error == 0){

                    $model = Transferenciaerp::findOne(['id' => $id]);
                    $model->enviadoWS = 1;
                    $model->save(); 

                    Temptransferenciaerp::actualizarDocumentoSIESA ($idconteodestino, $traspaso->id);
                }

            }else{
                var_dump($model->getErrors()); die("STOP Transito");
            }
        }

        return $error;
    }

    public static function actualizarDocumentoSIESA($idconteodestino, $idtraspaso){

        $model = Conteocdscdestino::findOne(['id' => $idconteodestino]);

        $numerodocumento = $idtraspaso;
        $tipodocumento = $model->factura->tipodocumento->codigo;

        $origen = 'Traspaso CDSC';
        $idgruma = $idtraspaso;
    
        $documento = OrdendecompraSIESA::obtenerDatosDocumentoCDSC($tipodocumento, $numerodocumento, $idconteodestino, $origen);

        $guardoDatos = Documentosiesa::grabarDatos($documento, $idgruma, $origen);

        $modelsiesa = Documentosiesa::find()->where([
            'origen' => $origen,
            'idGruma' => $idgruma
        ])->one();

        if ($modelsiesa){
            $model->idErpTraspaso = $modelsiesa->id;
            $model->idEstadoTraspaso = 3; // Generada
            $model->fechaTraspaso = new Expression('GETDATE()');
            $model->idUserTraspaso = Yii::$app->user->identity->id;
            $model->idTraspaso = $idtraspaso;
            $model->save();
        }else{
            echo ("NO ENCONTRADO"); die("hola");
        }
    }
}
