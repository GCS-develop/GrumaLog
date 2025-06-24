<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasodetalletiendadelete;

/**
 * TraspasodetalletiendadeleteSearch represents the model behind the search form of `frontend\models\Traspasodetalletiendadelete`.
 */
class TraspasodetalletiendadeleteSearch extends Traspasodetalletiendadelete
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
                    'item',
                    'consecutivoSiesa',
                    'serie',

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
    public function search($params,$idtraspaso=null)
    {
        if ($idtraspaso == null) {
            $query = Traspasodetalletiendadelete::find()->alias('tdtd');
        } else {
            $query = Traspasodetalletiendadelete::find()->where(['tdtd.idTraspaso' => $idtraspaso])->alias('tdtd');
        }

        $query->join('INNER JOIN', 'item i', 'i.id = tdtd.iditem');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');
        $query->join('INNER JOIN', 'user uUpdate', 'uUpdate.id = tdtd.updated_by');
        $query->join('INNER JOIN', 'user uCreate', 'uCreate.id = tdtd.created_by');
        $query->join('LEFT JOIN', 'unidadempaque ue', 'ue.codigo = i.unidadEmpaque');
        $query->join('LEFT JOIN', 'documentosiesa ds', 'tdtd.idTraspaso = ds.idGruma');
        $query->join('LEFT JOIN', 'traspaso tr', 'tr.id = tdtd.idTraspaso');
        $query->join('LEFT JOIN', 'tipodocumento tdoc', 'tdoc.id = tr.idTipoDocumento');

        // add conditions that should always apply here

        $query->select([
            'tdtd.*',
            'i.item as item',
            'c.nombre as color',
            't.codigo as talla',
            'uUpdate.username as nombreActualizo',
            'uCreate.username as nombreCreo',
            'COALESCE(ue.equivalencia, 1) * tdtd.cantidad as unidades',
            'ds.f350_consec_docto AS consecutivoSiesa',
            'tdoc.codigo as serie',
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
            'tdtd.id' => $this->id,
            'tdtd.idTraspaso' => $this->idTraspaso,
            'tdtd.idItem' => $this->idItem,
            'tdtd.cantidad' => $this->cantidad,
            'tdtd.created_at' => $this->created_at,
            'tdtd.created_by' => $this->created_by,
            'tdtd.updated_at' => $this->updated_at,
            'tdtd.updated_by' => $this->updated_by,
        ]);
        if (!empty($this->consecutivoSiesa)) {
            $query->andFilterWhere(['like', 'ds.f350_consec_docto', $this->consecutivoSiesa]);
        }
        if (!empty($this->serie)) {
            $query->andFilterWhere(['like', 'tdoc.id', $this->serie]);
        }

        return $dataProvider;
    }
}
