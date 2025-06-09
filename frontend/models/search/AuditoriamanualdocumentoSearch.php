<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Auditoriamanualdocumento;

/**
 * AuditoriamanualdocumentoSearch represents the model behind the search form of `frontend\models\Auditoriamanualdocumento`.
 */
class AuditoriamanualdocumentoSearch extends Auditoriamanualdocumento
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idInterfase', 'created_by', 'updated_by'], 'integer'],
            [['codigoBodegaSalida', 'numeroDocumento', 'fecha', 'notasDocumento', 'created_at', 'updated_at'], 'safe'],
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
    public function search($params, $registrada = null)
    {
        if ($registrada == null){
            $query = Auditoriamanualdocumento::find();
        }else{
            $query = Auditoriamanualdocumento::find()->where(['registrada' => $registrada]);
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
            'idInterfase' => $this->idInterfase,
            'fecha' => $this->fecha,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBodegaSalida', $this->codigoBodegaSalida])
            ->andFilterWhere(['like', 'numeroDocumento', $this->numeroDocumento])
            ->andFilterWhere(['like', 'notasDocumento', $this->notasDocumento]);

        return $dataProvider;
    }
}
