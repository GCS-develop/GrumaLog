<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasodetalletienda;

/**
 * TraspasodetalletiendaSearch represents the model behind the search form of `frontend\models\Traspasodetalletienda`.
 */
class TraspasodetalletiendaSearch extends Traspasodetalletienda
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTraspaso', 'idItem', 'cantidad', 'idDocumentosiesa', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'created_at',
                    'updated_at',
                    'talla',
                    'color',
                    'item',
                    'diferencia',
                    'consecutivoSiesa',
                    'userTraspaso',
                    'fechaDesde',
                    'fechaHasta',
                    'serie',
                    'Destino',
                    'Origen',
                    'codigoBarras',
                ],
                'safe',
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
            $query = Traspasodetalletienda::find()->alias('tdt');
        } else {
            $query = Traspasodetalletienda::find()->where(['tdt.idTraspaso' => $idtraspaso])->alias('tdt');
        }

        $query->join('INNER JOIN', 'item i', 'i.id = tdt.iditem');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');
        $query->join('INNER JOIN', 'user uUpdate', 'uUpdate.id = tdt.updated_by');
        $query->join('INNER JOIN', 'user uCreate', 'uCreate.id = tdt.created_by');
        $query->join('LEFT JOIN', 'unidadempaque ue', 'ue.codigo = i.unidadEmpaque');
        $query->join('LEFT JOIN', 'traspasodetalle td', 'td.idTraspaso = tdt.idTraspaso AND td.idItem = tdt.idItem');
        $query->join('LEFT JOIN', 'documentosiesa ds', 'tdt.idTraspaso = ds.idGruma');
        $query->join('LEFT JOIN', 'traspaso tr', 'tr.id = tdt.idTraspaso');
        $query->join('LEFT JOIN', 'user uCreateT', 'uCreateT.id = tr.created_by');
        $query->join('LEFT JOIN', 'tipodocumento tdoc', 'tdoc.id = tr.idTipoDocumento');
        $query->join('LEFT JOIN', 'bodegas bo', 'bo.id = tr.idBodegaOrigen');
        $query->join('LEFT JOIN', 'bodegas bd', 'bd.id = tr.idBodegaDestino');

        // add conditions that should always apply here

        $query->select([
            'tdt.*',
            'i.item as item',
            'c.nombre as color',
            't.codigo as talla',
            'uUpdate.username as nombreActualizo',
            'uCreate.username as nombreCreo',
            'COALESCE(ue.equivalencia, 1) * tdt.cantidad as unidades',
            'td.cantidad as cantidadTraspasoRegistros',
            'COALESCE(ue.equivalencia, 1) * td.cantidad as cantidadTraspasounidades',
            'COALESCE(ue.equivalencia, 1) * (tdt.cantidad - td.cantidad) AS diferencia',
            'ds.f350_consec_docto AS consecutivoSiesa',
            'tdoc.codigo as serie',
            'uCreateT.username AS userTraspaso',
            "(bo.codigo + ' - ' + bo.nombre) AS Origen",
            "(bd.codigo + ' - ' + bd.nombre) AS Destino",
            "i.codigoBarras AS codigoBarras",


        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '100',
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
            'id' => $this->id,
            'idTraspaso' => $this->idTraspaso,
            'idItem' => $this->idItem,
            'cantidad' => $this->cantidad,
            'idDocumentosiesa' => $this->idDocumentosiesa,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        if (!empty($this->consecutivoSiesa)) {
            $query->andFilterWhere(['like', 'ds.f350_consec_docto', $this->consecutivoSiesa]);
        }
        if (!empty($this->serie)) {
            $query->andFilterWhere(['like', 'tdoc.id', $this->serie]);
        }

        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['>=', new \yii\db\Expression('CAST(tdt.created_at AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(tdt.created_at AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(tdt.created_at AS DATE)'), $fechaInicio, $fechaFin]);
            }
        }
        $query->orderBy(['tdt.created_by' => SORT_ASC]); // ✅ Correcto

        return $dataProvider;
    }

    public function searchConDiferencias($params, $idtraspaso = null)
    {
        $dataProvider = $this->search($params, $idtraspaso);
        $query = $dataProvider->query;

        // Filtrar solo donde haya diferencia positiva o negativa
        $query->andWhere(['<>', new \yii\db\Expression('COALESCE(ue.equivalencia, 1) * (tdt.cantidad - td.cantidad)'), 0]);

        return $dataProvider;
    }

}
