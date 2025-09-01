<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;
/**
 * This is the model class for table "siesa_conector".
 *
 * @property int $id
 * @property string $nombre
 * @property string $url_base
 * @property string|null $id_compania
 * @property string|null $id_documento
 * @property string|null $nombre_documento
 * @property string|null $id_sistema
 * @property string $header_key
 * @property string $header_token
 * @property int|null $created_by
 * @property string|null $created_at
 * @property int|null $updated_by
 * @property string|null $updated_at
 *
 * @property SiesaConectorDocumento[] $siesaConectorDocumentos
 */
class SiesaConector extends \yii\db\ActiveRecord
{

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'siesa_conector';
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
            [['nombre', 'url_base', 'header_key', 'header_token'], 'required'],
            [['url_base', 'header_token'], 'string'],
            [['created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
            [['nombre', 'nombre_documento', 'header_key'], 'string', 'max' => 255],
            [['id_compania', 'id_documento', 'id_sistema'], 'string', 'max' => 20],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'nombre' => 'Nombre',
            'url_base' => 'Url Base',
            'id_compania' => 'Id Compania',
            'id_documento' => 'Id Documento',
            'nombre_documento' => 'Nombre Documento',
            'id_sistema' => 'Id Sistema',
            'header_key' => 'Header Key',
            'header_token' => 'Header Token',
            'created_by' => 'Created By',
            'created_at' => 'Created At',
            'updated_by' => 'Updated By',
            'updated_at' => 'Updated At',
        ];
    }

    /**
     * Gets query for [[SiesaConectorDocumentos]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSiesaConectorDocumentos()
    {
        return $this->hasMany(SiesaConectorDocumento::class, ['conector_id' => 'id']);
    }

    public static function getListaData()
    {
        $data = self::find()
            ->select(['id', "nombre"])->asArray()->all();
        $listadata = ArrayHelper::map($data, 'id', 'nombre');
        return $listadata;
    }
}
