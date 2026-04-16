<?php

namespace frontend\modules\compras\models;

use Yii;
use yii\base\Model;
use yii\data\ArrayDataProvider;

class PortalProveedorListSearch extends Model
{
    public $razonSocial;
    public $codigo;

    public function rules()
    {
        return [
            [['razonSocial', 'codigo'], 'safe'],
        ];
    }

    public function search($params)
    {
        $this->load($params);

        $sql = "SELECT TRIM(f106_id) AS codigo, TRIM(f106_descripcion) AS razonSocial
                FROM t106_mc_criterios_item_mayores
                WHERE f106_id_plan = '015' AND TRIM(f106_id) != ''
                GROUP BY TRIM(f106_id), TRIM(f106_descripcion)
                ORDER BY razonSocial";

        $rows = Yii::$app->dbSiesa->createCommand($sql)->queryAll();

        $rows = array_filter($rows, function ($row) {
            if ($this->razonSocial && stripos($row['razonSocial'], $this->razonSocial) === false) return false;
            if ($this->codigo && stripos($row['codigo'], $this->codigo) === false) return false;
            return true;
        });

        return new ArrayDataProvider([
            'allModels'  => array_values($rows),
            'pagination' => ['pageSize' => 50],
            'sort'       => false,
        ]);
    }
}
