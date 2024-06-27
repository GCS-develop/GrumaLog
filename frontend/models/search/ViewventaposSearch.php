<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Viewventapos;

/**
 * ViewventaposSearch represents the model behind the search form of `frontend\models\Viewventapos`.
 */
class ViewventaposSearch extends Viewventapos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['codigoCentroOperacion', 'nombreCentroOperacion', 'fecha', 'item', 'referencia', 'descripcion', 'color', 'talla', 'proveedor', 'nombreProveedor'], 'safe'],
            [['subtotal', 'unidades', 'preciounitario', 'total'], 'number'],
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
    public function search($params)
    {
        $query = Viewventapos::find();

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
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
            'fecha' => $this->fecha,
            'subtotal' => $this->subtotal,
            'unidades' => $this->unidades,
            'preciounitario' => $this->preciounitario,
            'total' => $this->total,
        ]);

        $query->andFilterWhere(['like', 'codigoCentroOperacion', $this->codigoCentroOperacion])
            ->andFilterWhere(['like', 'nombreCentroOperacion', $this->nombreCentroOperacion])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'proveedor', $this->proveedor])
            ->andFilterWhere(['like', 'nombreProveedor', $this->nombreProveedor]);

        return $dataProvider;
    }
}
