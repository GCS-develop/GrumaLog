<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Grumascanmarcacion;

/**
 * GrumascanmarcacionSearch represents the model behind the search form of `frontend\models\Grumascanmarcacion`.
 */
class GrumascanmarcacionSearch extends Grumascanmarcacion
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idbodega', 'created_by', 'updated_by', 'idconteo'], 'integer'],
            [['ubicacion', 'seccion', 'created_at', 'updated_at', 'estado'], 'safe'],
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
    public function search($params, $id = null)
    {
        if ($id == null) {
            $query = Grumascanmarcacion::find()->alias('gsm');
        } else {
            $query = Grumascanmarcacion::find()->where(['gsm.id' => $id])->alias('gsm');
        }

        $query->join('LEFT JOIN', 'grumascanconteo gsc', 'gsm.id = gsc.idmarcacion');
        $query->join('LEFT JOIN', 'grumascanestado gse', 'gse.id = gsc.idestado');
        // add conditions that should always apply here
        $query->select([
            'gsm.*',
            'gsc.id AS idconteo',
            'gse.nombre AS estado',

        ]);
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
            'gsm.id' => $this->id,
            'gsm.idbodega' => $this->idbodega,
            'gsm.created_at' => $this->created_at,
            'gsm.created_by' => $this->created_by,
            'gsm.updated_at' => $this->updated_at,
            'gsm.updated_by' => $this->updated_by,
            'gsc.id' => $this->idconteo,
        ]);
        // ✅ filtro estado (arregla el 0)
        if ($this->estado !== null && $this->estado !== '') {
            if ((string)$this->estado === '3') {
                // Sin conteo
                $query->andWhere(['gsc.id' => null]);
            } else {
                // Estado específico (incluye 0)
                $query->andWhere(['gse.id' => (int)$this->estado]);
            }
        }

        $query->andFilterWhere(['like', 'ubicacion', $this->ubicacion])
            ->andFilterWhere(['like', 'seccion', $this->seccion]);


        return $dataProvider;
    }
}
