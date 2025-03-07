<?php

namespace frontend\models;

use Yii;


/**
 * This is the model class for table "documentosiesa".
 *
 * @property int $id
 * @property string|null $tipoDocumento
 * @property int|null $numeroDocumento
 * @property int|null $f350_id_cia
 * @property int|null $f350_rowid
 * @property string|null $f350_id_co
 * @property string|null $f350_id_tipo_docto
 * @property int|null $f350_consec_docto
 * @property int|null $idGruma
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Documentosiesa extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'documentosiesa';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['numeroDocumento', 'f350_id_cia', 'f350_rowid', 'f350_consec_docto', 'idGruma', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'origen'], 'safe'],
            [['tipoDocumento', 'f350_id_co', 'f350_id_tipo_docto'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'tipoDocumento' => 'Tipo Documento',
            'numeroDocumento' => 'Numero Documento',
            'f350_id_cia' => 'F350 Id Cia',
            'f350_rowid' => 'F350 Rowid',
            'f350_id_co' => 'F350 Id Co',
            'f350_id_tipo_docto' => 'F350 Id Tipo Docto',
            'f350_consec_docto' => 'F350 Consec Docto',
            'idGruma' => 'Id Gruma',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    public static function grabarDatos($resultado, $idgruma, $origen = null)
    {

        if (!empty($resultado)) {
            foreach ($resultado as $data) {
                $numerodocumento = $data['numeroDocumento'];
                $tipodocumento = $data['tipoDocumento'];

                if ($origen){
                    $model = Documentosiesa::find()->where([
                        'origen' => $origen,
                        'idGruma' => $idgruma
                    ])->one();
                }else{
                    $model = Documentosiesa::find()->where([
                        'idGruma' => $idgruma
                    ])->one();
                }

                if (!$model) {
                    $model = new Documentosiesa();
                    $model->idGruma = $idgruma;
                    $model->origen = $origen;
                }

                $model->tipoDocumento = $tipodocumento;
                $model->numeroDocumento = $numerodocumento;
                $model->f350_id_tipo_docto = $data['f350_id_tipo_docto'];
                $model->f350_rowid = $data['f350_rowid'];
                $model->f350_id_cia = $data['f350_id_cia'];
                $model->f350_id_co = $data['f350_id_co'];
                $model->f350_consec_docto = $data['f350_consec_docto'];

                return $model->save();

            }
        }

        return false;

    }

    public function getCodigoerp()
    {
        return $this->f350_id_tipo_docto . '-' . $this->f350_consec_docto;
    }
}
