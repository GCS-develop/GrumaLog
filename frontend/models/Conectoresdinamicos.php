<?php

namespace frontend\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "conectoresdinamicos".
 *
 * @property int $id
 * @property int $idDocumento
 * @property string $nombreDocumento
 * @property int $idCompania
 * @property int $idInterface
 * @property string|null $urlConector
 * @property string|null $paramSistema
 * @property string|null $conniKey
 * @property string|null $conniToken
 * @property string|null $nombreSIESA
 */
class Conectoresdinamicos extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conectoresdinamicos';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idDocumento', 'nombreDocumento', 'idCompania', 'idInterface'], 'required'],
            [['idDocumento', 'idCompania', 'idInterface'], 'integer'],
            [['nombreDocumento', 'nombreSIESA'], 'string', 'max' => 50],
            [['urlConector', 'conniKey', 'conniToken', 'paramSistema'], 'string', 'max' => 500],
            [['idDocumento'], 'unique'],
            [['nombreDocumento'], 'unique'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idDocumento' => 'Documento',
            'nombreDocumento' => 'Nombre',
            'idCompania' => 'Compañia',
            'idInterface' => 'Interface',
        ];
    }

    public static  function  getListaData(){
        $data = Conectoresdinamicos::find()
                        ->select(['id', "(CAST(idDocumento AS NVARCHAR(255)) + ' - ' + nombreDocumento) AS nombre"])
                        ->orderBy('nombre')->asArray()->all();
    	$listadata = ArrayHelper::map($data, 'id', 'nombre');
    	return $listadata;
    }
}
