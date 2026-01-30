<?php
namespace frontend\models\search;

use yii\base\Model;
use yii\data\SqlDataProvider;
use Yii;

class SiesaDocumentoGastoSearch extends Model
{
    public $F350_ID_CO;
    public $F350_ID_TIPO_DOCTO = '22';
    public $F350_CONSEC_DOCTO;
    public $F350_FECHA_DESDE;
    public $F350_FECHA_HASTA;
    public $F350_ID_TERCERO;
    public $F350_NOTAS;

    public function rules()
    {
        return [
            [['F350_ID_CO', 'F350_CONSEC_DOCTO'], 'integer'],
            [['F350_ID_TIPO_DOCTO','F350_ID_TERCERO','F350_NOTAS'], 'trim'],
            [['F350_FECHA_DESDE','F350_FECHA_HASTA'], 'date', 'format'=>'php:Y-m-d'],
        ];
    }

    public function search($params)
    {
        $this->load($params);
        $this->validate();

        // condiciones base
        $where = [
            "a.f350_id_tipo_docto = :tipo",
            "a.f350_id_cia = :cia",
        ];
        $bind = [
            ':tipo' => (string)($this->F350_ID_TIPO_DOCTO ?: '22'),
            ':cia'  => '7',
        ];

        if ($this->F350_ID_CO) {
            $where[] = "a.f350_id_co = :co";
            $bind[':co'] = (int)$this->F350_ID_CO;
        }
        if ($this->F350_CONSEC_DOCTO) {
            $where[] = "a.f350_consec_docto = :consec";
            $bind[':consec'] = (int)$this->F350_CONSEC_DOCTO;
        }
        if (!empty($this->F350_ID_TERCERO)) {
            $where[] = "b.f200_id LIKE :terc";
            $bind[':terc'] = '%'.$this->F350_ID_TERCERO.'%';
        }
        if (!empty($this->F350_NOTAS)) {
            $where[] = "a.f350_notas LIKE :notas";
            $bind[':notas'] = '%'.$this->F350_NOTAS.'%';
        }
        if (!empty($this->F350_FECHA_DESDE)) {
            $where[] = "CONVERT(DATE, a.f350_fecha, 120) >= :fdesde";
            $bind[':fdesde'] = $this->F350_FECHA_DESDE;
        }
        if (!empty($this->F350_FECHA_HASTA)) {
            $where[] = "CONVERT(DATE, a.f350_fecha, 120) <= :fhasta";
            $bind[':fhasta'] = $this->F350_FECHA_HASTA;
        }

        $whereSql = $where ? 'WHERE '.implode(' AND ', $where) : '';

        $baseSql = "
            FROM t350_co_docto_contable a
            INNER JOIN t200_mm_terceros b ON a.f350_rowid_tercero = b.f200_rowid
            $whereSql
        ";

        $sql = "
            SELECT
                a.f350_id_co AS F350_ID_CO,
                a.f350_id_tipo_docto AS F350_ID_TIPO_DOCTO,
                a.f350_consec_docto AS F350_CONSEC_DOCTO,
                CONVERT(DATE, a.f350_fecha, 120) AS F350_FECHA,
                b.f200_id AS F350_ID_TERCERO,
                a.f350_notas AS F350_NOTAS
            $baseSql
        ";

        $countSql = "SELECT COUNT(1) $baseSql";
        $totalCount = (int)Yii::$app->dbSiesa->createCommand($countSql, $bind)->queryScalar();

        return new SqlDataProvider([
            'db'         => Yii::$app->dbSiesa,
            'sql'        => $sql,
            'params'     => $bind,
            'totalCount' => $totalCount,
            'pagination' => ['pageSize' => 20],
            'sort'       => [
                'attributes' => [
                    'F350_ID_CO',
                    'F350_ID_TIPO_DOCTO',
                    'F350_CONSEC_DOCTO',
                    'F350_FECHA',
                    'F350_ID_TERCERO',
                    'F350_NOTAS',
                ],
                'defaultOrder' => ['F350_CONSEC_DOCTO' => SORT_DESC],
            ],
        ]);
    }
}
