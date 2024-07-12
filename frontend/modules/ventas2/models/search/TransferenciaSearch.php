<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Transferencia;

/**
 * TransferenciaSearch represents the model behind the search form of `frontend\modules\ventas\models\Transferencia`.
 */
class TransferenciaSearch extends Transferencia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idFactura', 'cantidadBase', 'error'], 'integer'],
            [['codigoBarra', 'item', 'color', 'talla', 'unidadMedida', 'bodega', 'motivo', 'referencia', 'descripcion'], 'safe'],
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
        $query = Transferencia::find()->where(['idFactura' => $idfactura]);

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
            'idFactura' => $this->idFactura,
            'cantidadBase' => $this->cantidadBase,
            'error' => $this->error,
            'precioUnitario' => $this->precioUnitario,
        ]);

        $query->andFilterWhere(['like', 'codigoBarra', $this->codigoBarra])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'unidadMedida', $this->unidadMedida])
            ->andFilterWhere(['like', 'bodega', $this->bodega])
            ->andFilterWhere(['like', 'motivo', $this->motivo])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion]);

        return $dataProvider;
    }
}
