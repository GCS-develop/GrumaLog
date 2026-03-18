<?php

namespace frontend\models;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "tipodocumentodespacho".
 *
 * @property int $id
 * @property string $origen       Cedi | Tienda
 * @property string $accion       Despachar | Recibir
 * @property int $idTipoDocumento FK → tipodocumento.id
 * @property string|null $created_at
 * @property int|null $created_by
 * @property string|null $updated_at
 * @property int|null $updated_by
 */
class Tipodocumentodespacho extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'tipodocumentodespacho';
    }

    public function rules()
    {
        return [
            [['origen', 'accion', 'idTipoDocumento'], 'required'],
            [['idTipoDocumento'], 'integer'],
            ['origen', 'in', 'range' => ['Cedi', 'Tienda']],
            ['accion', 'in', 'range' => ['Despachar', 'Recibir']],
            ['idTipoDocumento', 'exist', 'targetClass' => Tipodocumento::class, 'targetAttribute' => 'id'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'              => 'ID',
            'origen'          => 'Origen',
            'accion'          => 'Acción',
            'idTipoDocumento' => 'Tipo Documento',
        ];
    }

    // ── Relación ──────────────────────────────────────────────────────────────

    public function getTipoDocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    // ── Helpers ───────────────────────────────────────────────────────────────

    public static function origenList()
    {
        return ['Cedi' => 'Cedi', 'Tienda' => 'Tienda'];
    }

    public static function accionList()
    {
        return ['Despachar' => 'Despachar', 'Recibir' => 'Recibir'];
    }
}
