<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\VwGrumascanMapa;

class VwGrumascanMapaSearch extends Model
{
    public $idbodega;
    public $ubicacion;
    public $seccion;
    public $desde;
    public $hasta;
    public $cantidad;

    public function rules()
    {
        return [
            [['idbodega', 'cantidad'], 'integer'],
            [['ubicacion', 'seccion'], 'safe'],
            [['desde', 'hasta'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = VwGrumascanMapa::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'defaultOrder' => [
                    'idbodega' => SORT_ASC,
                    'ubicacion' => SORT_ASC,
                    'seccion' => SORT_ASC,
                    'desde' => SORT_ASC,
                ],
                'attributes' => [
                    'idbodega',
                    'ubicacion',
                    'seccion',
                    'desde',
                    'hasta',
                    'cantidad',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('1=0');
            return $dataProvider;
        }

        $query->andFilterWhere(['idbodega' => $this->idbodega]);

        $query->andFilterWhere(['like', 'ubicacion', $this->ubicacion])
            ->andFilterWhere(['like', 'seccion', $this->seccion]);

        if ($this->desde !== null && $this->desde !== '') {
            $query->andWhere(['desde' => (int)$this->desde]);
        }

        if ($this->hasta !== null && $this->hasta !== '') {
            $query->andWhere(['hasta' => (int)$this->hasta]);
        }

        if ($this->cantidad !== null && $this->cantidad !== '') {
            $query->andWhere(['cantidad' => (int)$this->cantidad]);
        }

        return $dataProvider;
    }
}
