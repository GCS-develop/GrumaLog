<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\SiesaConectorDocumento;
use yii\db\Expression;

/**
 * SiesaconectordocumentoSearch represents the model behind the search form of `frontend\models\SiesaConectorDocumento`.
 */
class SiesaconectordocumentoSearch extends SiesaConectorDocumento
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'conector_id', 'id_traspaso', 'created_by', 'updated_by'], 'integer'],
            [[
                'nombre',
                'descripcion',
                'created_at',
                'updated_at',
                'consecutivoSiesa',
                'usuarioTransferencia',
                'consecutivosiesatraspaso',
                'bodegaentrada',
                'tieneAen',
            ], 'safe'],
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
        $query = SiesaConectorDocumento::find()->alias('doct');

        $query->join('LEFT JOIN', 'documentosiesa ds', 'doct.id = ds.idGruma');
        $query->join('INNER JOIN', 'user u', 'u.id = doct.created_by');
        $query->join('INNER JOIN', 'traspaso t', 't.id = doct.id_traspaso');
        $query->join('LEFT JOIN', 'documentosiesa dst', 't.id = dst.idGruma');
        $query->join('INNER JOIN', 'bodegas b', 'b.id = t.idbodegadestino');

        $query->select([
            'doct.*',
            'CONCAT(ds.f350_id_tipo_docto, ds.f350_consec_docto) AS consecutivoSiesa',
            'u.username as usuarioTransferencia',
            'CONCAT(dst.f350_id_tipo_docto, dst.f350_consec_docto) AS consecutivosiesatraspaso',
            'CONCAT(b.codigo, b.nombre) as bodegaentrada',
            new Expression("CASE WHEN ds.f350_id_tipo_docto = 'AEN' THEN 1 ELSE 0 END AS tieneAen"),

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
            'doct.id' => $this->id,
            'doct.conector_id' => $this->conector_id,
            'doct.id_traspaso' => $this->id_traspaso,
            'doct.created_by' => $this->created_by,
            'doct.created_at' => $this->created_at,
            'doct.updated_by' => $this->updated_by,
            'doct.updated_at' => $this->updated_at,
        ]);

        $query->andFilterWhere(['like', 'doct.nombre', $this->nombre])
            ->andFilterWhere(['like', 'doct.descripcion', $this->descripcion]);
        $query->andFilterWhere(['like', 'u.username', $this->usuarioTransferencia]);
        $query->andFilterWhere(['like', 'ds.f350_consec_docto', $this->consecutivoSiesa]);

        $query->orderBy([
            'id' => SORT_DESC,
        ]);
        // ----- FILTRO AEN -----
        if ($this->tieneAen !== null && $this->tieneAen !== '') {
            if ((int)$this->tieneAen === 1) {
                $query->andWhere(['ds.f350_id_tipo_docto' => 'AEN']);
            } elseif ((int)$this->tieneAen === 0) {
                $query->andWhere('ds.f350_id_tipo_docto IS NULL OR ds.f350_id_tipo_docto <> \'AEN\'');
            }
        } else {
            $query->andWhere("ds.f350_id_tipo_docto IS NULL OR ds.f350_id_tipo_docto = 'AEN'");
        }
        return $dataProvider;
    }
}
