<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Productostiquetesprecio;

/**
 * ProductostiquetesprecioSearch represents the model behind the search form of `frontend\models\Productostiquetesprecio`.
 */
class ProductostiquetesprecioSearch extends Productostiquetesprecio
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'item', 'existencia', 'precio', 'created_by', 'updated_by'], 'integer'],
            [['descBodega', 'codigoBarra', 'descItem', 'detalleExt1', 'detalleExt2', 'proveedor', 'marca', 'referencia', 'categoria', 'subcategoria', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $descBodega = null)
    {

        if ($descBodega == null) {
            $query = Productostiquetesprecio::find();
        } else {
            $query = Productostiquetesprecio::find()->where(['descBodega' => $descBodega]);
        }

        // $query = Productostiquetesprecio::find();

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
            'item' => $this->item,
            'existencia' => $this->existencia,
            'precio' => $this->precio,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
            'codigoBarra' => $this->codigoBarra,
        ]);

        $query->andFilterWhere(['like', 'descBodega', $this->descBodega])
            // ->andFilterWhere(['like', 'codigoBarra', $this->codigoBarra])
            ->andFilterWhere(['like', 'descItem', $this->descItem])
            ->andFilterWhere(['like', 'detalleExt1', $this->detalleExt1])
            ->andFilterWhere(['like', 'detalleExt2', $this->detalleExt2])
            ->andFilterWhere(['like', 'proveedor', $this->proveedor])
            ->andFilterWhere(['like', 'marca', $this->marca])
            ->andFilterWhere(['like', 'referencia', $this->referencia])
            ->andFilterWhere(['like', 'categoria', $this->categoria])
            ->andFilterWhere(['like', 'subcategoria', $this->subcategoria]);

        return $dataProvider;
    }
}
