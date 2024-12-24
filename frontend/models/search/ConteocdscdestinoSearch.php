<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteocdscdestino;

/**
 * ConteocdscdestinoSearch represents the model behind the search form of `frontend\models\Conteocdscdestino`.
 */
class ConteocdscdestinoSearch extends Conteocdscdestino
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idConteocdscdestinofactura', 'idCentroOperacion', 'numeroCajas', 'idUserConteo', 
            'idItemUltimoConteo', 'total', 'idEstado', 'idLegalizado', 'created_by', 'updated_by',
            'idEstadoFactura', 'idEstadoEntrada', 'idEstadoTraspaso'], 'integer'],
            [['created_at', 'updated_at', 'razonSocial', 'codigoAlmacen', 'numeroFactura', 'almacen',
            'fechaDesde', 'fechaHasta'], 'safe'],
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
    public function search($params, $idconteofactura = null)
    {
        $query = Conteocdscdestino::find()
                                    ->alias('dest')
                                    ->join('INNER JOIN', 'conteocdscdestinofactura fact', 'dest.idConteocdscdestinofactura = fact.id')
                                    //->join('INNER JOIN', 'centrooperacion co', 'dest.idCentroOperacion = co.id')
                                    ->join('INNER JOIN', 'bodegas co', 'dest.idCentroOperacion = co.id')
                                    ->join('INNER JOIN', 'userconteocdsc usc', 'dest.idUserConteo = usc.id')
                                    //->join('INNER JOIN', 'empleadologistica empl' , 'usc.idEmpleadoLogistica = empl.id')
                                    ->join('INNER JOIN', 'user us' , 'usc.idUser = us.id')
                                    ->join('INNER JOIN', 'empleado emp', 'us.idEmpleado = emp.id')
                                    ->join('INNER JOIN', 'proveedor prv', 'fact.idProveedor = prv.id')
                                    ->join('LEFT JOIN', 'bodegas bod', 'fact.idCentroOperacionLegaliza = bod.id');

        $query->select([
            'dest.id',
            'dest.idConteocdscdestinofactura',
            'dest.idCentroOperacion',
            'dest.numeroCajas',
            'dest.idUserConteo',
            'dest.total',
            'dest.created_at',
            'dest.updated_at',

            'fact.id AS radicado',
            'fact.idProveedor',
            'fact.numeroFactura',
            'fact.fecha',
            'fact.totalUnidades',
            'fact.idEstado AS idEstadoFactura',
            'fact.idLegalizado AS idLegalizadoFactura',
            'fact.idEstadoEntrada',
            'fact.idEstadoTraspaso',
            'bod.codigo AS codigoAlmacenLegaliza',
            'bod.nombre AS nombreAlmacenLegaliza',

            'co.nombre AS almacen',
            'co.codigo AS codigoAlmacen',
            'emp.nombreEmpleado',
            'emp.identificacion',
            'prv.razonSocial',
            'prv.nit',
            'prv.idProveedor AS codigoProveedor',
            'prv.criterioMercancia AS tipoProveedor',
            'prv.criterioModeloLogistico AS modeloLogistico'
        ]);

        $query->andFilterWhere([
            'fact.id' => $idconteofactura,
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
            'dest.id' => $this->id,
            'dest.idConteocdscdestinofactura' => $this->idConteocdscdestinofactura,
            'dest.idCentroOperacion' => $this->idCentroOperacion,
            'dest.numeroCajas' => $this->numeroCajas,
            'dest.idUserConteo' => $this->idUserConteo,
            'dest.idItemUltimoConteo' => $this->idItemUltimoConteo,
            'dest.total' => $this->total,
            'dest.idEstado' => $this->idEstado,
            'dest.idLegalizado' => $this->idLegalizado,
            /*'dest.created_at' => $this->created_at,
            'dest.created_by' => $this->created_by,
            'dest.updated_at' => $this->updated_at,
            'dest.updated_by' => $this->updated_by,*/
            'co.codigo' => $this->codigoAlmacen,
            'fact.numeroFactura' => $this->numeroFactura,
            'fact.idEstado' => $this->idEstadoFactura,
            'fact.idEstadoEntrada' => $this->idEstadoEntrada,
            'fact.idEstadoTraspaso' => $this->idEstadoTraspaso,
        ]);

        $query->andFilterWhere(['like', 'prv.razonSocial', $this->razonSocial])
                ->andFilterWhere(['like', 'co.nombre', $this->almacen]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), dest.created_at, 23)', $fechaInicio, $fechaFin]);
        }

        $query->orderBy(['dest.created_at' => SORT_DESC,
                        'dest.id' => SORT_DESC]);

        return $dataProvider;
    }
}
