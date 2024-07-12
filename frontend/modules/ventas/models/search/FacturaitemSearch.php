<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Facturaitem;

/**
 * FacturaitemSearch represents the model behind the search form of `frontend\modules\ventas\models\Facturaitem`.
 */
class FacturaitemSearch extends Facturaitem
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idFactura', 'totalUnidadesFactura', 'totalUnidadesSiesa', 'error'], 'integer'],
            [['codigoBarra', 'item', 'referencia', 'descripcion', 'color', 'talla'], 'safe'],
            [['precioUnitario'], 'number'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        // bypass scenarios() implementation in the parent class
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params, $idfactura)
    {
        $query = Facturaitem::find()->where(['idFactura' => $idfactura]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 200,
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'idFactura' => $this->idFactura,
            'precioUnitario' => $this->precioUnitario,
            'totalUnidadesFactura' => $this->totalUnidadesFactura,
            'totalUnidadesSiesa' => $this->totalUnidadesSiesa,
            'error' => $this->error,
        ]);

        $query->andFilterWhere(['like', 'codigoBarra', $this->codigoBarra])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla]);

        return $dataProvider;
    }
}
