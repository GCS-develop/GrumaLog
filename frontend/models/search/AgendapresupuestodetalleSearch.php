<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Agendapresupuestodetalle;

/**
 * AgendapresupuestodetalleSearch represents the model behind the search form of `frontend\models\Agendapresupuestodetalle`.
 */
class AgendapresupuestodetalleSearch extends Agendapresupuestodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaPresupuesto', 'inconsistencia'], 'integer'],
            [['codigo', 'subcategoria', 'consumidor', 'universo', 'producto', 'tendencia', 'talla', 
            'tipo', 'modelo', 'fechaLlegada', 'crossDocking', 'categoria'], 'safe'],
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
    public function search($params, $idagendapresupuesto, $inconsistencia = null)
    {
        if ($inconsistencia == null){
            $query = Agendapresupuestodetalle::find()->where(['idAgendaPresupuesto' => $idagendapresupuesto]);
        }else{
            $query = Agendapresupuestodetalle::find()->where(['idAgendaPresupuesto' => $idagendapresupuesto, 'inconsistencia' => $inconsistencia]);            
        }

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
            'idAgendaPresupuesto' => $this->idAgendaPresupuesto,
            'cantidad' => $this->cantidad,
            'fechaLlegada' => $this->fechaLlegada,
            'inconsistencia' => $this->inconsistencia,
        ]);

        $query->andFilterWhere(['like', 'codigo', $this->codigo])
            ->andFilterWhere(['like', 'categoria', $this->categoria])
            ->andFilterWhere(['like', 'subcategoria', $this->subcategoria])
            ->andFilterWhere(['like', 'consumidor', $this->consumidor])
            ->andFilterWhere(['like', 'universo', $this->universo])
            ->andFilterWhere(['like', 'producto', $this->producto])
            ->andFilterWhere(['like', 'tendencia', $this->tendencia])
            ->andFilterWhere(['like', 'talla', $this->talla])
            ->andFilterWhere(['like', 'tipo', $this->tipo])
            ->andFilterWhere(['like', 'modelo', $this->modelo])
            ->andFilterWhere(['like', 'crossDocking', $this->crossDocking]);

        return $dataProvider;
    }
}
