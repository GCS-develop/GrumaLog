<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Userbodegaasignacion;

/**
 * UserbodegaasignacionSearch represents the model behind the search form of `frontend\models\Userbodegaasignacion`.
 */
class UserbodegaasignacionSearch extends Userbodegaasignacion
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idUserBodega', 'idBodega', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at', 'username', 'nombreEmpleado', 'identificacion',
            'codigoAlmacen', 'almacen'], 'safe'],
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
    public function search($params, $iduserbodega = null)
    {
        $query = Userbodegaasignacion::find()->alias('usba');

        $query->join('INNER JOIN', 'Userbodega usb', 'usba.idUserBodega = usb.id');
        $query->join('INNER JOIN', 'user us', 'usb.idUser = us.id');
        $query->join('INNER JOIN', 'empleado emp', 'usb.idEmpleado = emp.id');
        $query->join('INNER JOIN', 'bodegas bo', 'usba.idBodega = bo.id');

        $query->select([
            'usba.id',
            'usb.idUser',
            'usb.idEmpleado',
            'usb.idEstado',
            'us.username',
            'emp.identificacion',
            'emp.nombreEmpleado',
            'bo.codigo AS codigoAlmacen',
            'bo.nombre AS almacen'
        ]);

        $query->andFilterWhere(['usb.id' => $iduserbodega]);

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
            'usba.id' => $this->id,
            'usba.idUserBodega' => $this->idUserBodega,
            'usba.idBodega' => $this->idBodega,
            'usba.idEstado' => $this->idEstado,
            'usba.created_at' => $this->created_at,
            'usba.created_by' => $this->created_by,
            'usba.updated_at' => $this->updated_at,
            'usba.updated_by' => $this->updated_by,
            'emp.identificacion' => $this->identificacion,
            'bo.codigo' => $this->codigoAlmacen,
        ]);

        $query->andFilterWhere(['like', 'emp.nombreEmpleado', $this->nombreEmpleado])
        ->andFilterWhere(['like', 'bo.nombre', $this->almacen])
        ->andFilterWhere(['like', 'us.username', $this->username]);

        $query->orderBy(['emp.nombreEmpleado' => SORT_ASC, 
                        'bo.codigo' => SORT_ASC]);

        return $dataProvider;
    }
}
