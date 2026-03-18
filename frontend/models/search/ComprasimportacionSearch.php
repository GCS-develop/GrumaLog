<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Comprasimportacion;

class ComprasimportacionSearch extends Comprasimportacion
{
    public function rules()
    {
        return [
            [['id', 'numeroRegistros', 'created_by', 'updated_by', 'estadoSiesa', 'estadoCarvajal'], 'integer'],
            [['totalUnidades'], 'number'],
            [['created_at', 'updated_at', 'mensajeSiesa', 'mensajeCarvajal'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Comprasimportacion::find()->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'id'             => $this->id,
            'numeroRegistros'=> $this->numeroRegistros,
            'created_by'     => $this->created_by,
        ]);

        return $dataProvider;
    }
}
