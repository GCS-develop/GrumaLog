<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\DocumentoGasto;
use yii;

/**
 * GastotiendasSearch represents the model behind the search form of `frontend\models\DocumentoGasto`.
 */
class GastotiendasSearch extends DocumentoGasto
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['F350_ID_CO'], 'integer'],
            [['F350_ID_TIPO_DOCTO', 'F350_CONSEC_DOCTO', 'F350_FECHA', 'F350_ID_TERCERO', 'F350_IND_ESTADO', 'F350_NOTAS'], 'safe'],
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
        $query = DocumentoGasto::find();

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'sort' => [
                'defaultOrder' => ['ID_TRANSACCION' => SORT_DESC], // último primero
                'attributes' => [
                    'ID_TRANSACCION',
                    'F350_ID_CO',
                    'F350_ID_TIPO_DOCTO',
                    'F350_CONSEC_DOCTO',
                    'F350_FECHA',
                    'F350_ID_TERCERO',
                    'F350_NOTAS',
                    'estado_envio',
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'F350_ID_CO' => $this->F350_ID_CO,
            'F350_FECHA' => $this->F350_FECHA,
        ]);

        $query->andFilterWhere(['like', 'F350_ID_TIPO_DOCTO', $this->F350_ID_TIPO_DOCTO])
            ->andFilterWhere(['like', 'F350_CONSEC_DOCTO', $this->F350_CONSEC_DOCTO])
            ->andFilterWhere(['like', 'F350_ID_TERCERO', $this->F350_ID_TERCERO])
            ->andFilterWhere(['like', 'F350_IND_ESTADO', $this->F350_IND_ESTADO])
            ->andFilterWhere(['like', 'F350_NOTAS', $this->F350_NOTAS]);

        return $dataProvider;
    }




}
