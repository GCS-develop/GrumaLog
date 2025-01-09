<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "proveedor".
 *
 * @property int $id
 * @property string $idProveedor
 * @property string $nit
 * @property string $razonSocial
 * @property string|null $tipoIdentificacion
 * @property string|null $sucursal
 * @property string|null $descripcionSucursal
 * @property string|null $contacto
 * @property string|null $direccion
 * @property string|null $pais
 * @property string|null $ciudad
 * @property string|null $departamento
 * @property string|null $telefono
 * @property string|null $email
 * @property string|null $celular
 * @property string|null $criterioMercancia
 * @property string|null $criterioModeloLogistico
 */
class Proveedor extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'proveedor';
    }

    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idProveedor', 'nit', 'razonSocial'], 'required'],
            [['idProveedor', 'nit', 'tipoIdentificacion'], 'string', 'max' => 20],
            [['razonSocial', 'descripcionSucursal', 'contacto', 'direccion', 'email'], 'string', 'max' => 250],
            [['sucursal'], 'string', 'max' => 5],
            [['pais', 'ciudad', 'departamento', 'telefono', 'celular', 'criterioMercancia', 'criterioModeloLogistico'], 'string', 'max' => 50],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idProveedor' => 'Id Proveedor',
            'nit' => 'Nit',
            'razonSocial' => 'Razon Social',
            'tipoIdentificacion' => 'Tipo Identificacion',
            'sucursal' => 'Sucursal',
            'descripcionSucursal' => 'Descripcion Sucursal',
            'contacto' => 'Contacto',
            'direccion' => 'Direccion',
            'pais' => 'Pais',
            'ciudad' => 'Ciudad',
            'departamento' => 'Departamento',
            'telefono' => 'Telefono',
            'email' => 'Email',
            'celular' => 'Celular',
            'criterioMercancia' => 'Criterio Mercancia',
            'criterioModeloLogistico' => 'Criterio Modelo Logistico',
        ];
    }

    public static function actualizarRegistro ($modelPRV){
        

        $model = Proveedor::findOne(['nit' => $modelPRV['nit']]);

        if ($model == null){
            $model = new Proveedor();
            $model->idProveedor = $modelPRV['nit'];
            $model->nit = $modelPRV['nit'];
        }

        $model->razonSocial = $modelPRV['razonSocial'];

        $respuesta = $model->save();
        
        return $model->id;
    }

    public static  function  getListaData(){
        $data = Proveedor::find()
                        ->select(['id', "(nit + ' - ' + razonSocial) AS nombre"])
                        ->orderBy('razonSocial')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }

    public static function actualizarRegistroSIESA ($proveedor){

        $nit = $proveedor['nit'];
        $sucursal = $proveedor['sucursal'];

		$model = Proveedor::findOne(['nit' => $nit, 'sucursal' => $sucursal]);

		if ($model == null){
			$model = new Proveedor();
			$model->nit = $nit;
            $model->sucursal = $sucursal;
		}

        $model->idProveedor = $nit;
		$model->razonSocial = $proveedor['razonSocial'];
		$model->tipoIdentificacion = $proveedor['idTipoIdentificacion'];

		$model->descripcionSucursal = $proveedor['descripcionSucursal'];

		$model->contacto = $proveedor['contactoProveedor'];
		$model->direccion = $proveedor['direccionProveedor'];
		$model->pais = $proveedor['paisProveedor'];
		$model->ciudad = $proveedor['ciudadProveedor'];
		$model->departamento = $proveedor['deptoProveedor'];
		$model->telefono = $proveedor['telefonoProveedor'];
		$model->email = $proveedor['emailProveedor'];
		$model->celular = $proveedor['celularProveedor'];

		$model->criterioMercancia = $proveedor['mercancia'];
		$model->criterioModeloLogistico = $proveedor['modelo'];

		$respuesta = $model->save(); 

		if (!$respuesta){
			return false;
		}

        return $model->id;
    }
}
