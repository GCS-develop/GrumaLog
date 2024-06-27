<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Userbodega;

/**
 * UserbodegaSearch represents the model behind the search form of `frontend\models\Userbodega`.
 */
class UserbodegaSearch extends Userbodega
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idUser',  'idEmpleado', 'idEstado', 'created_by', 'updated_by'], 'integer'],
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
        $query = Userbodega::find()->alias('usb');

        $query->join('INNER JOIN', 'user us', 'usb.idUser = us.id');
        $query->join('INNER JOIN', 'empleado emp', 'usb.idEmpleado = emp.id');

        $query->select([
            'usb.id',
            'usb.idUser',
            'usb.idEmpleado',
            'usb.idEstado',
            'us.username',
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
            'usb.id' => $this->id,
            'usb.idUser' => $this->idUser,
            'usb.idEmpleado' => $this->idEmpleado,
            'usb.idEstado' => $this->idEstado,
            'usb.created_at' => $this->created_at,
            'usb.created_by' => $this->created_by,
            'usb.updated_at' => $this->updated_at,
            'usb.updated_by' => $this->updated_by,
            'emp.identificacion' => $this->identificacion,
            //'emp.idEstado' => $this->idEstado
        ]);

        $query->andFilterWhere(['like', 'emp.nombreEmpleado', $this->nombreEmpleado])
            ->andFilterWhere(['like', 'us.username', $this->username]);


        return $dataProvider;
    }
}
