<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasodetalledelete;
use common\models\User;

/**
 * TraspasodetalledeleteSearch represents the model behind the search form of `frontend\models\Traspasodetalledelete`.
 */
class TraspasodetalledeleteSearch extends Traspasodetalledelete
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTraspaso', 'idItem', 'cantidad', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'created_at',
                    'updated_at',
                    'talla',
                    'color',
                    'item'
                ],
                'safe'
            ],
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
    public function search($params, $idtraspaso = null)
    {
        if ($idtraspaso == null) {
            $query = Traspasodetalledelete::find()->alias('tdd');
        } else {
            $query = Traspasodetalledelete::find()->where(['tdd.idTraspaso' => $idtraspaso])->alias('tdd');
        }

        $query->join('INNER JOIN', 'item i', 'i.id = tdd.iditem');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');
        $query->join('INNER JOIN', 'user uUpdate', 'uUpdate.id = tdd.updated_by');
        $query->join('INNER JOIN', 'user uCreate', 'uCreate.id = tdd.created_by');
        $query->join('LEFT JOIN', 'unidadempaque ue', 'ue.codigo = i.unidadEmpaque');

        // add conditions that should always apply here

        $query->select([
            'tdd.*',
            'i.item as item',
            'c.nombre as color',
            't.codigo as talla',
            'uUpdate.username as nombreActualizo',
            'uCreate.username as nombreCreo',
            'COALESCE(ue.equivalencia, 1) * tdd.cantidad as unidades',
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '15',
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
            'tdd.id' => $this->id,
            'tdd.idTraspaso' => $this->idTraspaso,
            'tdd.idItem' => $this->idItem,
            'tdd.cantidad' => $this->cantidad,
            'tdd.created_at' => $this->created_at,
            'tdd.created_by' => $this->created_by,
            'tdd.updated_at' => $this->updated_at,
            'tdd.updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }
}
