<?php

namespace frontend\models;

use Yii;
use yii\web\NotFoundHttpException;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

/**
 * This is the model class for table "traspasodetalletienda".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 * @property int|null $idDocumentosiesa
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Item $idItem0
 * @property Traspaso $idTraspaso0
 */
class Traspasodetalletienda extends \yii\db\ActiveRecord
{

    public $item;
    public $talla;
    public $color;
    public $nombreActualizo;
    public $unidades;
    public $nombreCreo;
    public $fechaDesde;
    public $fechaHasta;
    public $cantidadTraspasoRegistros;
    public $cantidadTraspasounidades;
    public $diferencia;
    public $consecutivoSiesa;
    public $serie;
    public $Origen;
    public $Destino;
    public $userTraspaso;
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalletienda';
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
            [['idTraspaso', 'idItem', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idTraspaso', 'idItem', 'cantidad', 'idDocumentosiesa', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
            'idTraspaso' => 'Id Traspaso',
            'idItem' => 'Id Item',
            'cantidad' => 'Cantidad',
            'idDocumentosiesa' => 'Id Documentosiesa',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[IdItem0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem']);
    }

    /**
     * Gets query for [[IdTraspaso0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIdTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }

    
    public static function resumenPorUsuarioTraspaso($idtraspaso = null)
    {
        $params = [];

        if ($idtraspaso !== null) {
            $params[':idtraspaso'] = $idtraspaso;
        }

        return Yii::$app->db->createCommand("
        WITH Diferencias AS (
            SELECT 
                tda.idTraspaso,
                tr.created_by,
                COALESCE(ue.equivalencia,1)*(tda.cantidad - td.cantidad) AS diferencia
            FROM traspasodetalletienda tda
            INNER JOIN traspasodetalle td ON td.idTraspaso = tda.idTraspaso AND td.idItem = tda.iditem
            INNER JOIN traspaso tr ON tr.id = tda.idTraspaso
            LEFT JOIN unidadempaque ue ON ue.codigo = (
                SELECT unidadEmpaque FROM item WHERE item.id = tda.idItem
            )
            WHERE COALESCE(ue.equivalencia,1)*(tda.cantidad - td.cantidad) <> 0
            " . ($idtraspaso !== null ? "AND tda.idTraspaso = :idtraspaso" : "") . "
        ),
        TraspasosConDiferencias AS (
            SELECT DISTINCT d.idTraspaso, d.created_by
            FROM Diferencias d
        ),
        DocumentosUnicos AS (
            SELECT DISTINCT 
                tr.id AS idTraspaso,
                ds.f350_consec_docto AS consecutivoSiesa,
                tr.created_by,
                tp.codigo AS serie
            FROM traspaso tr
            LEFT JOIN documentosiesa ds ON ds.idGruma = tr.id
            LEFT JOIN tipodocumento tp ON tp.id = tr.idTipoDocumento
            WHERE tr.id IN (SELECT idTraspaso FROM TraspasosConDiferencias)
        )

        SELECT 
            u.username AS usuario,
            (
                SELECT SUM(CASE WHEN d.diferencia > 0 THEN d.diferencia ELSE 0 END)
                FROM Diferencias d
                WHERE d.created_by = tr.created_by
            ) AS novedades_positivas,
            (
                SELECT SUM(CASE WHEN d.diferencia < 0 THEN ABS(d.diferencia) ELSE 0 END)
                FROM Diferencias d
                WHERE d.created_by = tr.created_by
            ) AS novedades_negativas,
            (
                SELECT STUFF((
                    SELECT ', ' + du.serie + '-' + CAST(du.idTraspaso AS varchar)
                    FROM DocumentosUnicos du
                    WHERE du.created_by = tr.created_by
                    FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 2, '')
            ) AS traspasos,
            (
                SELECT STUFF((
                    SELECT ', ' + du.serie + '-' + ISNULL(CAST(du.consecutivoSiesa AS varchar), 'Sin SIESA')
                    FROM DocumentosUnicos du
                    WHERE du.created_by = tr.created_by
                    FOR XML PATH(''), TYPE).value('.', 'NVARCHAR(MAX)'), 1, 2, '')
            ) AS documentosSiesa

        FROM TraspasosConDiferencias tcd
        INNER JOIN traspaso tr ON tr.id = tcd.idTraspaso
        INNER JOIN [user] u ON u.id = tr.created_by
        GROUP BY u.username, tr.created_by
        ORDER BY usuario
    ", $params)->queryAll();
    }
}
