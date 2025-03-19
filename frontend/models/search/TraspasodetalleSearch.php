<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasodetalle;
use frontend\models\Item;
use frontend\models\Traspaso;
use frontend\models\Documentosiesa;
use frontend\models\Talla;
use frontend\models\Color;
use frontend\models\Unidadempaque;
use frontend\models\Estadotraspaso;



/**
 * TraspasodetalleSearch represents the model behind the search form of `frontend\models\Traspasodetalle`.
 */
class TraspasodetalleSearch extends Traspasodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTraspaso', 'idItem', 'created_by',], 'integer'],
            [
                [
                    'created_at',
                    'updated_at',
                    'consecutivointerno',
                    'consecutivosiesa',
                    'codigoitem',
                    'talla',
                    'color',
                    'estado',
                    'updated_by',
                    'tipomovimiento',
                    'cantidad',
                    'fechaDesde',
                    'fechaHasta',
                    'proveedor',
                    'bodegaorigen',
                    'bodegadestino',
                    'estadoPlanilla',
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
    public function search($params, $id = null)
    {

        if ($id == null) {
            $query = Traspasodetalle::find()->alias('td');
        } else {
            $query = Traspasodetalle::find()->where(['idTraspaso' => $id])->alias('td');
        }
        // Realizar el JOIN con la tabla 'item'
        // $query->joinWith(['item']);


        $query->join('INNER JOIN', 'item i', 'i.id = td.iditem');
        $query->join('INNER JOIN', 'traspaso tr', 'td.idTraspaso = tr.id');
        $query->join('left JOIN', 'documentosiesa ds', 'tr.id = ds.idGruma');
        $query->join('INNER JOIN', 'talla t', 't.id = i.idTalla');
        $query->join('INNER JOIN', 'color c', 'c.id = i.idColor');
        $query->join('INNER JOIN', 'estadotraspaso e', 'e.id = tr.idestado');
        $query->join(
            'LEFT JOIN',
            'planillaembarquetraspaso pet',
            'pet.id = (SELECT MAX(id) FROM planillaembarquetraspaso WHERE idTraspaso = tr.id)'
        );
        $query->join('LEFT JOIN', 'estadorecepcion er', 'er.id = pet.idEstado AND er.id <> 5'); // Agregando la relación

        // $query->join('INNER JOIN', 'unidadempaque ue', 'ue.id = i.unidadEmpaque');



        // $query->joinWith(['traspaso']);
        // $query->joinWith(['documentosiesa']);
        // var_dump($this->consecutivointerno);
        // die();

        $query->select([
            'td.*',
            'td.updated_by',
            'tr.consecutivo',
            'ds.f350_consec_docto',
            'i.item',
            'c.nombre',
            't.codigo',
            'e.codigo',
            'tr.tipoMovimiento',
            'i.nombreProveedor',
            'tr.idBodegaOrigen',
            'tr.idBodegaDestino',
            'er.nombre as estadoPlanilla'

        ]);

        // add conditions that should always apply here

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
        // var_dump($this->estadoPlanilla);die()
        // grid filtering conditions
        $query->andFilterWhere([
            'id' => $this->id,
            'idTraspaso' => $this->idTraspaso,
            'idItem' => $this->idItem,
            'td.cantidad' => $this->cantidad,
            // 'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'td.updated_by' => $this->updated_by,
            'i.item' => $this->codigoitem,
            'tr.consecutivo' => $this->consecutivointerno,
            'ds.f350_consec_docto' => $this->consecutivosiesa,
            'c.nombre' => $this->color,
            't.codigo' => $this->talla,
            'e.codigo' => $this->estado,
            'tr.tipoMovimiento' => $this->tipomovimiento,
            'tr.idBodegaOrigen' => $this->bodegaorigen,
            'tr.idBodegaDestino' => $this->bodegadestino,
            'er.id' => $this->estadoPlanilla,
            // 'tr.created_at' => $this->fechainicio,
            // 'tr.updated_at' => $this->fechafin,

            // 'i.item'=> $this->codigoitem,
        ]);

        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['=', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaInicio, $fechaFin]);
            }
        }


        if (!empty($this->proveedor)) {
            $query->andFilterWhere(['like', 'i.nombreProveedor', trim($this->proveedor)]);
        }
        // 
        // $query->andFilterWhere(['like', 'tr.consecutivo', $this->consecutivo]);

        $query->orderBy(['idItem' => SORT_ASC]); // Orden por defecto

        return $dataProvider;
    }
}
