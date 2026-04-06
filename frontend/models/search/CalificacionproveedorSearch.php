<?php

namespace frontend\models\search;

use frontend\models\Calificacionproveedor;
use yii\data\ActiveDataProvider;

class CalificacionproveedorSearch extends Calificacionproveedor
{
    public function rules()
    {
        return [
            [['numero_oc', 'proveedor', 'categoria', 'transportadora'], 'safe'],
            [['id_proveedor', 'id_ordendecompra'], 'integer'],
        ];
    }

    public function search($params)
    {
        $query = Calificacionproveedor::find()->orderBy(['created_at' => SORT_DESC]);

        $dataProvider = new ActiveDataProvider([
            'query'      => $query,
            'pagination' => ['pageSize' => 30],
        ]);

        $this->load($params);
        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere(['like', 'numero_oc',      $this->numero_oc])
              ->andFilterWhere(['like', 'proveedor',      $this->proveedor])
              ->andFilterWhere(['like', 'categoria',      $this->categoria])
              ->andFilterWhere(['like', 'transportadora', $this->transportadora])
              ->andFilterWhere(['id_proveedor'    => $this->id_proveedor])
              ->andFilterWhere(['id_ordendecompra' => $this->id_ordendecompra]);

        return $dataProvider;
    }
}
