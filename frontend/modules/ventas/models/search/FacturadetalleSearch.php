<?php

namespace frontend\modules\ventas\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\modules\ventas\models\Facturadetalle;

use frontend\modules\ventas\models\Tempexistencia;
use frontend\modules\ventas\models\Transferencia;
use frontend\modules\ventas\models\Facturaitem;

/**
 * FacturadetalleSearch represents the model behind the search form of `frontend\modules\ventas\models\Facturadetalle`.
 */
class FacturadetalleSearch extends Facturadetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idFactura', 'cantidadBase', 'error'], 'integer'],
            [['codigoBarra', 'item', 'color', 'talla', 'unidadMedida', 'bodega', 
            'motivo', 'descripcion', 'referencia', 'tipoMovimiento'], 'safe'],
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
    public function search($params, $model = null)
    {
        if ($model){
            $query = Facturadetalle::find()
                        ->where([
                                'idFactura' => $model->idFactura,
                                'codigoBarra' => $model->codigoBarra,
                                'item' => $model->item,
                                'color' => $model->color,
                                'talla' => $model->talla
                            ]);
        }else{
            $query = Facturadetalle::find();
        }

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
            'error' => $this->error,
            'cantidadBase' => $this->cantidadBase,
            'precioUnitario' => $this->precioUnitario,
            'tipoMovimiento' => $this->tipoMovimiento,
        ]);

        $query->andFilterWhere(['like', 'codigoBarra', $this->codigoBarra])
            ->andFilterWhere(['like', 'item', $this->item])
            ->andFilterWhere(['like', 'color', $this->color])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'unidadMedida', $this->unidadMedida])
            ->andFilterWhere(['like', 'bodega', $this->bodega])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'motivo', $this->motivo]);

        return $dataProvider;
    }

}
