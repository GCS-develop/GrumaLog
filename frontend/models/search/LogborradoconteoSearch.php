<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Logborradoconteo;

class LogborradoconteoSearch extends Logborradoconteo
{
    public $fechaDesde;
    public $fechaHasta;
    public $username;

    public function rules()
    {
        return [
            [['fechaDesde', 'fechaHasta', 'username', 'consecutivoOC', 'accion', 'item', 'codigoCO'], 'safe'],
            [['created_by'], 'integer'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Logborradoconteo::find()
            ->joinWith(['user']);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 50],
            'sort'       => [
                'defaultOrder' => ['created_at' => SORT_DESC],
                'attributes'   => [
                    'created_at' => [
                        'asc'  => ['logborradoconteo.created_at' => SORT_ASC],
                        'desc' => ['logborradoconteo.created_at' => SORT_DESC],
                    ],
                    'consecutivoOC',
                    'accion',
                    'item',
                    'unidadesBorradas',
                    'username' => [
                        'asc'  => ['{{%user}}.username' => SORT_ASC],
                        'desc' => ['{{%user}}.username' => SORT_DESC],
                    ],
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        if ($this->consecutivoOC) {
            $query->andWhere(['like', 'logborradoconteo.consecutivoOC', $this->consecutivoOC]);
        }

        if ($this->codigoCO) {
            $query->andWhere(['like', 'logborradoconteo.codigoCO', $this->codigoCO]);
        }

        if ($this->accion) {
            $query->andWhere(['logborradoconteo.accion' => $this->accion]);
        }

        if ($this->item) {
            $query->andWhere(['like', 'logborradoconteo.item', $this->item]);
        }

        if ($this->username) {
            $query->andWhere(['like', '{{%user}}.username', $this->username]);
        }

        if ($this->fechaDesde) {
            $query->andWhere(['>=', 'logborradoconteo.created_at', $this->fechaDesde . ' 00:00:00']);
        }

        if ($this->fechaHasta) {
            $query->andWhere(['<=', 'logborradoconteo.created_at', $this->fechaHasta . ' 23:59:59']);
        }

        return $dataProvider;
    }
}
