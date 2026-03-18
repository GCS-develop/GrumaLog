<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Tipodocumentodespacho;

class TipodocumentodespachoSearch extends Tipodocumentodespacho
{
    public function rules()
    {
        return [
            [['id', 'idTipoDocumento'], 'integer'],
            [['origen', 'accion'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Tipodocumentodespacho::find()
            ->with('tipoDocumento')
            ->alias('tdd');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort'  => ['defaultOrder' => ['id' => SORT_ASC]],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['tdd.id' => $this->id])
              ->andFilterWhere(['tdd.idTipoDocumento' => $this->idTipoDocumento])
              ->andFilterWhere(['tdd.origen' => $this->origen])
              ->andFilterWhere(['tdd.accion' => $this->accion]);

        return $dataProvider;
    }
}
