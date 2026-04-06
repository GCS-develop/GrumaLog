<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Userauditoriaconteo;

class UserauditoriaconteoSearch extends Userauditoriaconteo
{
    public function rules()
    {
        return [
            [['id', 'idUser', 'idEmpleadoLogistica', 'created_by', 'updated_by', 'status'], 'integer'],
            [['created_at', 'updated_at', 'username', 'identificacion', 'nombreEmpleado', 'email'], 'safe'],
        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function search($params)
    {
        $query = Userauditoriaconteo::find()->alias('uac');

        $query->join('INNER JOIN', 'user us', 'uac.idUser = us.id');
        $query->join('LEFT JOIN', 'empleadologistica eml', 'uac.idEmpleadoLogistica = eml.id');
        $query->join('LEFT JOIN', 'empleado emp', 'eml.idEmpleado = emp.id');

        $query->select([
            'uac.id',
            'uac.idUser',
            'uac.idEmpleadoLogistica',
            'us.username',
            'us.status',
            'us.email',
            'emp.identificacion',
            'emp.nombreEmpleado',
        ]);

        $dataProvider = new ActiveDataProvider(['query' => $query]);

        $this->load($params);

        if (!$this->validate()) {
            return $dataProvider;
        }

        $query->andFilterWhere([
            'uac.id'                  => $this->id,
            'uac.idUser'              => $this->idUser,
            'uac.idEmpleadoLogistica' => $this->idEmpleadoLogistica,
            'us.status'               => $this->status,
        ]);

        $query->andFilterWhere(['like', 'emp.nombreEmpleado', $this->nombreEmpleado])
            ->andFilterWhere(['like', 'us.email', $this->email])
            ->andFilterWhere(['like', 'us.username', $this->username]);

        return $dataProvider;
    }
}
