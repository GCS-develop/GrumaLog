<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\MovimientoGasto;

/**
 * MovimentogastoSearch represents the model behind the search form of `frontend\models\MovimientoGasto`.
 */
class MovimentogastoSearch extends MovimientoGasto
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['F_CIA', 'F350_ID_CO'], 'integer'],
            [['F350_ID_TIPO_DOCTO', 'F350_CONSEC_DOCTO', 'F351_ID_AUXILIAR', 'F351_ID_TERCERO', 'F351_ID_CO_MOV', 'F351_ID_UN', 'F351_ID_CCOSTO', 'F351_ID_FE', 'F351_DOCTO_BANCO', 'F351_NRO_DOCTO_BANCO', 'F351_NOTAS'], 'safe'],
            [['F351_VALOR_DB', 'F351_VALOR_CR', 'F351_BASE_GRAVABLE'], 'number'],
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
        $query = MovimientoGasto::find();

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
            'F_CIA' => $this->F_CIA,
            'F350_ID_CO' => $this->F350_ID_CO,
            'F351_VALOR_DB' => $this->F351_VALOR_DB,
            'F351_VALOR_CR' => $this->F351_VALOR_CR,
            'F351_BASE_GRAVABLE' => $this->F351_BASE_GRAVABLE,
        ]);

        $query->andFilterWhere(['like', 'F350_ID_TIPO_DOCTO', $this->F350_ID_TIPO_DOCTO])
            ->andFilterWhere(['like', 'F350_CONSEC_DOCTO', $this->F350_CONSEC_DOCTO])
            ->andFilterWhere(['like', 'F351_ID_AUXILIAR', $this->F351_ID_AUXILIAR])
            ->andFilterWhere(['like', 'F351_ID_TERCERO', $this->F351_ID_TERCERO])
            ->andFilterWhere(['like', 'F351_ID_CO_MOV', $this->F351_ID_CO_MOV])
            ->andFilterWhere(['like', 'F351_ID_UN', $this->F351_ID_UN])
            ->andFilterWhere(['like', 'F351_ID_CCOSTO', $this->F351_ID_CCOSTO])
            ->andFilterWhere(['like', 'F351_ID_FE', $this->F351_ID_FE])
            ->andFilterWhere(['like', 'F351_DOCTO_BANCO', $this->F351_DOCTO_BANCO])
            ->andFilterWhere(['like', 'F351_NRO_DOCTO_BANCO', $this->F351_NRO_DOCTO_BANCO])
            ->andFilterWhere(['like', 'F351_NOTAS', $this->F351_NOTAS]);

        return $dataProvider;
    }
}
