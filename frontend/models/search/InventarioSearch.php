<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Inventario;

/**
 * InventarioSearch represents the model behind the search form of `frontend\models\Inventario`.
 */
class InventarioSearch extends Inventario
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'item', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'codigoBodega', 'fechaUltimaActualizacion', 'created_at', 'updated_at', 'talla', 'color'], 'safe'],
            [['existencia'], 'number'],
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
        $query = Inventario::find()->alias('inv');
        $query->join('INNER JOIN', 'item i', 'i.id = inv.idItem');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');

        $query->select([
            'inv.*',
            'c.nombre AS color',
            't.codigo AS talla',
        ]);
        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '15',
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
            'inv.id' => $this->id,
            'inv.item' => $this->item,
            'inv.idItem' => $this->idItem,
            'inv.existencia' => $this->existencia,
            'inv.fechaUltimaActualizacion' => $this->fechaUltimaActualizacion,
            'inv.created_at' => $this->created_at,
            'inv.created_by' => $this->created_by,
            'inv.updated_at' => $this->updated_at,
            'inv.updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'inv.codigoBarras', $this->codigoBarras])
            ->andFilterWhere(['like', 'inv.codigoBodega', $this->codigoBodega]);

        if (!empty($this->color)) {
            $query->andFilterWhere(['like', 'c.nombre', trim($this->color)]);
        }
        if (!empty($this->talla)) {
            $query->andFilterWhere(['like', 't.codigo', trim($this->talla)]);
        }

        $query->orderBy(['inv.existencia' => SORT_DESC]); // Orden por defecto

        return $dataProvider;
    }
}
