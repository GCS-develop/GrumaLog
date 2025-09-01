<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Transferdevdocumentos;

/**
 * TransferdevdocumentosSearch represents the model behind the search form of `frontend\models\Transferdevdocumentos`.
 */
class TransferdevdocumentosSearch extends Transferdevdocumentos
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id'], 'integer'],
            [['centro_operacion', 'tipo_documento', 'consecutivo_documento', 'fecha_documento', 'tercero_proveedor', 'notas', 'sucursal_proveedor', 'comprador', 'consignacion'], 'safe'],
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
        $query = Transferdevdocumentos::find();

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
        ]);

        $query->andFilterWhere(['like', 'centro_operacion', $this->centro_operacion])
            ->andFilterWhere(['like', 'tipo_documento', $this->tipo_documento])
            ->andFilterWhere(['like', 'consecutivo_documento', $this->consecutivo_documento])
            ->andFilterWhere(['like', 'fecha_documento', $this->fecha_documento])
            ->andFilterWhere(['like', 'tercero_proveedor', $this->tercero_proveedor])
            ->andFilterWhere(['like', 'notas', $this->notas])
            ->andFilterWhere(['like', 'sucursal_proveedor', $this->sucursal_proveedor])
            ->andFilterWhere(['like', 'comprador', $this->comprador])
            ->andFilterWhere(['like', 'consignacion', $this->consignacion]);

        return $dataProvider;
    }
}
