<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Empleadologistica;

/**
 * EmpleadologisticaSearch represents the model behind the search form of `frontend\models\Empleadologistica`.
 */
class EmpleadologisticaSearch extends Empleadologistica
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idEmpleado', 'idEstado'], 'integer'],
            [['identificacion', 'nombreEmpleado', 'username'], 'safe'],
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
        $query = Empleadologistica::find()->alias('log');

        $query->join('INNER JOIN', 'empleado em', 'log.idEmpleado = em.id');
        $query->join('LEFT JOIN', 'userconteo usc', 'log.id = usc.idEmpleadoLogistica');
        $query->join('LEFT JOIN', 'user us', 'usc.idUser = us.id');

        $query->select([
            'log.id',
            'log.idEmpleado',
            'log.idEstado',
            'em.identificacion',
            'em.nombreEmpleado',
            'us.username'
        ]);

        $query->orderBy(['em.nombreEmpleado' => SORT_ASC]);


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
            'log.id' => $this->id,
            'log.idEmpleado' => $this->idEmpleado,
            'log.idEstado' => $this->idEstado,
            'em.identificacion' => $this->identificacion,
        ]);

        $query->andFilterWhere(['like', 'em.nombreEmpleado', $this->nombreEmpleado])
                ->andFilterWhere(['like', 'us.username', $this->username]);


        return $dataProvider;
    }
}
