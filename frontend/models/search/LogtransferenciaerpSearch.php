<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Logtransferenciaerp;

class LogtransferenciaerpSearch extends Logtransferenciaerp
{
    public function rules()
    {
        return [
            [['idTransferenciaerpBorrada', 'idTransferenciaerpNueva', 'idOrdenCompra',
              'numeroRegistros', 'enviadoWS', 'created_by'], 'integer'],
            [['accion', 'descripcion', 'origen', 'created_at'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Logtransferenciaerp::find()->orderBy(['id' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 50],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['idTransferenciaerpBorrada' => $this->idTransferenciaerpBorrada]);
        $query->andFilterWhere(['idTransferenciaerpNueva'   => $this->idTransferenciaerpNueva]);
        $query->andFilterWhere(['idOrdenCompra'             => $this->idOrdenCompra]);
        $query->andFilterWhere(['enviadoWS'                 => $this->enviadoWS]);
        $query->andFilterWhere(['created_by'                => $this->created_by]);
        $query->andFilterWhere(['like', 'accion',       $this->accion]);
        $query->andFilterWhere(['like', 'descripcion',  $this->descripcion]);
        $query->andFilterWhere(['like', 'origen',       $this->origen]);

        return $dataProvider;
    }
}
