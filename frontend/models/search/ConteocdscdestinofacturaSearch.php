<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteocdscdestinofactura;

/**
 * ConteocdscdestinofacturaSearch represents the model behind the search form of `frontend\models\Conteocdscdestinofactura`.
 */
class ConteocdscdestinofacturaSearch extends Conteocdscdestinofactura
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idProveedor', 'idCentroOperacionLegaliza', 'totalUnidades', 'idEstado', 
            'idLegalizado', 'created_by', 'updated_by', 'idEstadoEntrada', 'idEstadoTraspaso'], 'integer'],
            [['numeroFactura', 'fecha', 'created_at', 'updated_at', 'codigoProveedor', 'nit', 
            'razonSocial', 'almacen'], 'safe'],
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
    public function search($params, $idestadofactura = null, $idestadolegaliza = null, 
                            $idestadoentrada = null, $idestadotraspaso = null)
    {
        $query = Conteocdscdestinofactura::find()
                                            ->alias('fact')
                                            ->join('INNER JOIN', 'proveedor prv', 'fact.idProveedor = prv.id')
                                            ->join('LEFT JOIN', 'bodegas bo', 'fact.idCentroOperacionLegaliza = bo.id');

        $query->select([
        
            'fact.id',
            'fact.idProveedor',
            'fact.numeroFactura',
            'fact.fecha',
            'fact.totalUnidades',
            'fact.idEstado',
            'fact.idLegalizado',
            'fact.idEstadoEntrada',
            'fact.idEstadoTraspaso',
            'fact.created_at',

            'fact.fechaLegaliza',
            'fact.observacionLegalizacion',
            'fact.idUserLegaliza',

            'fact.fechaEntrada',
            'fact.idUserEntrada',
            'fact.idSerieEntrada',
            'fact.numeroEntrada',
            'fact.idUserTraspaso',
            'fact.fechaTraspaso',

            'bo.nombre AS almacen',
            'bo.codigo AS codigoAlmacen',
            'prv.razonSocial',
            'prv.nit',
            'prv.idProveedor AS codigoProveedor',
            'prv.criterioMercancia AS tipoProveedor',
            'prv.criterioModeloLogistico AS modeloLogistico'
        ]);

        if ($idestadolegaliza == 0 && $idestadofactura == 2){
            $query->andFilterWhere([
                'fact.idEstado' => $idestadofactura,
                'fact.idLegalizado' => $idestadolegaliza,
                'fact.idErpTraspaso' => null,
                'fact.idErpEntrada' => null
            ]);
        }

        if ($idestadoentrada == 1){
            $query->andWhere([
                'and',
                ['is not', 'idUserLegaliza', null],
                [
                    'or',
                    ['idUserEntrada' => null],
                    ['idErpEntrada' => null]
                ],
                ['idErpTraspaso' => null]
            ]);
        }

        if ($idestadoentrada == 2 && $idestadotraspaso == 2){
            $query->andWhere([
                'and',
                ['is not', 'idUserLegaliza', null],
                ['is not', 'idUserEntrada', null],
                ['is not', 'idErpEntrada', null],
                ['idErpTraspaso' => null]
            ]);
            
        }

        /*$query->andFilterWhere([
            'fact.idEstado' => $idestadofactura,
            'fact.idLegalizado' => $idestadolegaliza,
            'fact.idEstadoEntrada' => $idestadoentrada,
            'fact.idEstadoTraspaso' => $idestadotraspaso,
        ]);

        $query->orWhere([
            'and',
            ['fact.idEstadoTraspaso' => 2],
            ['fact.idErpTraspaso' => null],
            ['not', ['fact.idErpEntrada' => null]]
        ]);

        $query->orWhere([
            'and',
            ['fact.idEstadoEntrada' => 2],
            ['fact.idErpEntrada' => null]
        ]);*/

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
            'fact.id' => $this->id,
            'fact.idProveedor' => $this->idProveedor,
            'fact.fecha' => $this->fecha,
            'fact.idCentroOperacionLegaliza' => $this->idCentroOperacionLegaliza,
            'fact.totalUnidades' => $this->totalUnidades,
            'fact.idEstado' => $this->idEstado,
            'fact.idLegalizado' => $this->idLegalizado,
            'fact.created_at' => $this->created_at,
            'fact.created_by' => $this->created_by,
            'fact.updated_at' => $this->updated_at,
            'fact.updated_by' => $this->updated_by,
            'prv.idProveedor' => $this->codigoProveedor,
            'prv.nit' => $this->nit
        ]);

        $query->andFilterWhere(['like', 'fact.numeroFactura', $this->numeroFactura])
            ->andFilterWhere(['like', 'prv.razonSocial', $this->razonSocial])
            ->andFilterWhere(['like', 'co.nombre', $this->almacen]);

        return $dataProvider;
    }
}
