<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\SiesaConectorDocumento;

/**
 * SiesaconectordocumentoSearch represents the model behind the search form of `frontend\models\SiesaConectorDocumento`.
 */
class SiesaconectordocumentoSearch extends SiesaConectorDocumento
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'conector_id', 'id_traspaso', 'created_by', 'updated_by'], 'integer'],
            [['nombre', 'descripcion', 'created_at', 'updated_at'], 'safe'],
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
        $query = SiesaConectorDocumento::find()->alias('doct');

        $query->join('LEFT JOIN', 'documentosiesa ds', 'doct.id = ds.idGruma');

        $query->select([
            'doct.*',
            'CONCAT(ds.f350_id_tipo_docto, ds.f350_consec_docto) AS consecutivoSiesa'
        ]);
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
            'conector_id' => $this->conector_id,
            'id_traspaso' => $this->id_traspaso,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at,
            'updated_by' => $this->updated_by,
            'updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'nombre', $this->nombre])
            ->andFilterWhere(['like', 'descripcion', $this->descripcion]);

        $query->orderBy(['id' => SORT_DESC]);
        $query->andWhere("ds.f350_id_tipo_docto IS NULL OR ds.f350_id_tipo_docto = 'AEN'");

        return $dataProvider;
    }
}
