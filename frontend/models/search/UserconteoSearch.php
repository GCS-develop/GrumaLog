<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Userconteo;

/**
 * UserconteoSearch represents the model behind the search form of `frontend\models\Userconteo`.
 */
class UserconteoSearch extends Userconteo
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by', 'idEstado'], 'integer'],
            [['created_at', 'updated_at', 'username', 'identificacion', 'nombreEmpleado'], 'safe'],
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
        $query = Userconteo::find()->alias('usc');

        $query->join('INNER JOIN', 'user us', 'usc.idUser = us.id');
        $query->join('INNER JOIN', 'empleadologistica empl', 'usc.idEmpleadoLogistica = empl.id');
        $query->join('INNER JOIN', 'empleado emp', 'empl.idEmpleado = emp.id');

        $query->select([
            'usc.id',
            'usc.idUser',
            'usc.idEmpleadoLogistica',
            'us.username',
            'empl.idEstado',
            'empl.idEmpleado',
            'emp.identificacion',
            'emp.nombreEmpleado',
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
            'usc.id' => $this->id,
            'usc.idUser' => $this->idUser,
            'usc.idEmpleadoLogistica' => $this->idEmpleadoLogistica,
            'usc.created_at' => $this->created_at,
            'usc.created_by' => $this->created_by,
            'usc.updated_at' => $this->updated_at,
            'usc.updated_by' => $this->updated_by,
            'emp.identificacion' => $this->identificacion,
            'empl.idEstado' => $this->idEstado
        ]);

        $query->andFilterWhere(['like', 'emp.nombreEmpleado', $this->nombreEmpleado])
            ->andFilterWhere(['like', 'us.username', $this->username]);

        return $dataProvider;
    }
}
