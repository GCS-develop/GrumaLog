<?php

namespace frontend\modules\ventas\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "proveedor".
 *
 * @property int $id
 * @property float $nit
 * @property string $razonSocial
 * @property string|null $sucursal
 * @property string|null $clase
 * @property string $codigo
 *
 * @property Claseproveedor $clase0
 * @property Existencias[] $existencias
 * @property Sucursal[] $sucursals
 * @property Userproveedor[] $userproveedors
 * @property Ventapos[] $ventapos
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

    /**
     * @return \yii\db\Connection the database connection used by this AR class.
     */
    public static function getDb()
    {
        return Yii::$app->get('dbVentasPOS');
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['nit', 'razonSocial', 'codigo'], 'required'],
            [['nit'], 'number'],
            [['razonSocial'], 'string', 'max' => 150],
            [['sucursal'], 'string', 'max' => 5],
            [['clase'], 'string', 'max' => 10],
            [['codigo'], 'string', 'max' => 6],
            [['nit'], 'unique'],
            [['codigo'], 'unique'],
            [['clase'], 'exist', 'skipOnError' => true, 'targetClass' => Claseproveedor::class, 'targetAttribute' => ['clase' => 'codigo']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nit' => 'Nit',
            'razonSocial' => 'Razon Social',
            'sucursal' => 'Sucursal',
            'clase' => 'Clase',
            'codigo' => 'Codigo',
        ];
    }

    /**
     * Gets query for [[Clase0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getClase0()
    {
        return $this->hasOne(Claseproveedor::class, ['codigo' => 'clase']);
    }

    /**
     * Gets query for [[Existencias]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getExistencias()
    {
        return $this->hasMany(Existencias::class, ['proveedor' => 'codigo']);
    }

    /**
     * Gets query for [[Sucursals]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSucursals()
    {
        return $this->hasMany(Sucursal::class, ['idProveedor' => 'id']);
    }

    /**
     * Gets query for [[Userproveedors]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUserproveedors()
    {
        return $this->hasMany(Userproveedor::class, ['idProveedor' => 'id']);
    }

    /**
     * Gets query for [[Ventapos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getVentapos()
    {
        return $this->hasMany(Ventapos::class, ['proveedor' => 'codigo']);
    }

    public static  function  getListaData(){
        $data = Proveedor::find()
                        ->select(['id', 'razonSocial AS nombre'])
                        ->orderBy('razonSocial')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
