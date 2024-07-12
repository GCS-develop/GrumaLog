<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Cotizacionprecio;

/**
 * CotizacionprecioSearch represents the model behind the search form of `frontend\modules\ventas\models\Cotizacionprecio`.
 */
class CotizacionprecioSearch extends Cotizacionprecio
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['codigoProveedor', 'razonSocial', 'descripcion', 'color', 'talla', 'fechaactivacion', 'unidad', 'moneda', 'fechahasta'], 'safe'],
            [['nitProveedor', 'item', 'preciounitario', 'tiempoentrega'], 'number'],
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
        $query = Cotizacionprecio::find();

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
            'nitProveedor' => $this->nitProveedor,
            'item' => $this->item,
            'fechaactivacion' => $this->fechaactivacion,
            'preciounitario' => $this->preciounitario,
            'tiempoentrega' => $this->tiempoentrega,
        ]);

        $query->andFilterWhere(['like', 'codigoProveedor', $this->codigoProveedor])
            ->andFilterWhere(['like', 'razonSocial', $this->razonSocial])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'unidad', $this->unidad])
            ->andFilterWhere(['like', 'moneda', $this->moneda])
            ->andFilterWhere(['like', 'fechahasta', $this->fechahasta]);

        return $dataProvider;
    }
}
