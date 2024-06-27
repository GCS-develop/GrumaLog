<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Agendapresupuestosemana;

/**
 * AgendapresupuestosemanaSearch represents the model behind the search form of `frontend\models\Agendapresupuestosemana`.
 */
class AgendapresupuestosemanaSearch extends Agendapresupuestosemana
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaPresupuesto', 'periodoAnio', 'periodoMes', 'numeroSemanaAnio'], 'integer'],
            [['crossDocking', 'categoria'], 'safe'],
            [['cantidad'], 'number'],
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
    public function search($params, $idagendapresupuesto)
    {
        $query = Agendapresupuestosemana::find()->where(['idAgendaPresupuesto' => $idagendapresupuesto]);

        $query->orderBy(['crossDocking' => SORT_ASC,
                        'categoria' => SORT_ASC,
                        'numeroSemanaAnio' => SORT_ASC]);

        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 50, // Mostrar 50 registros por página
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
            'idAgendaPresupuesto' => $this->idAgendaPresupuesto,
            'periodoAnio' => $this->periodoAnio,
            'periodoMes' => $this->periodoMes,
            'numeroSemanaAnio' => $this->numeroSemanaAnio,
            'cantidad' => $this->cantidad,
        ]);

        $query->andFilterWhere(['like', 'crossDocking', $this->crossDocking])
            ->andFilterWhere(['like', 'categoria', $this->categoria]);

        return $dataProvider;
    }

}
