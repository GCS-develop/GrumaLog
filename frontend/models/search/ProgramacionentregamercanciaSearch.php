<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Programacionentregamercancia;

/**
 * ProgramacionentregamercanciaSearch represents the model behind the search form of `frontend\models\Programacionentregamercancia`.
 */
class ProgramacionentregamercanciaSearch extends Programacionentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idAgendaEntregaMercancia', 'idEmpleadoLogistica', 'idEstado', 
            'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at',  'fechaDesde', 'fechaHasta', 
            'numeroOrdenCompra', 'nombreUsuario'], 'safe'],
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
    public function search($params, $id=null, $idestadoconteo = null, $idestadoprogramacion = null)
    {
        if ($id){
            $query = Programacionentregamercancia::find()->where(['det.idAgendaEntregaMercancia' => $id]);
        }else{
            $query = Programacionentregamercancia::find();
        }

        $query->alias('det');

        $query->join('INNER JOIN', 'agendaentregamercancia ag', 'det.idAgendaEntregaMercancia = ag.id');

        $query->orderBy(['ag.fechaCita' => SORT_DESC, 'ag.idOrdenCompra' => SORT_ASC]);
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

        $query->andFilterWhere(['ag.idEstadoConteo' => $idestadoconteo]);
        $query->andFilterWhere(['det.idEstado' => $idestadoprogramacion]);

        // grid filtering conditions
        $query->andFilterWhere([
            'det.id' => $this->id,
            'det.idAgendaEntregaMercancia' => $this->idAgendaEntregaMercancia,
            'det.idEmpleadoLogistica' => $this->idEmpleadoLogistica,
            'det.idEstado' => $this->idEstado,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
        ]);

        //var_dump($this->fechaDesde); die("hola");

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), ag.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        $query->orderBy(['det.item' => SORT_ASC]);

        return $dataProvider;
    }

    public function searchxagenda($params, $id=null, $idestadoconteo = null, $idestadoprogramacion = null, $idagenda = null)
    {

        $query = Programacionentregamercancia::find()->alias('det')
                    ->select([  
                        'det.id AS idProgramacion',
                        'ag.id AS idAgenda',
                        'ag.idOrdenCompra', 
                        'ag.fechaCita',
                        'co.codigo AS codigoCentroOperacion',
                        'td.codigo AS codigoTipoDocumento',
                        'oc.consecutivo AS numeroOrdenCompra',
                        'cat.nombre AS nombreCategoria',
                        'det.idUserConteo',
                        'emp.nombreEmpleado AS nombreUsuario', 
                        'det.unidadesAsignadas AS unidadesEmpaque',
                        'det.item',
                        'det.descripcion',
                        'ag.idEstadoConteo',
                        'det.idEstado',
                        'estc.nombre AS nombreEstadoConteo',
                        'estp.nombre AS nombreEstadoProgramacion',
                        'det.created_at',
                        'ag.idEstadoConteo'
                    ])
                    ->join('INNER JOIN', 'Agendaentregamercancia ag','det.idAgendaEntregaMercancia = ag.id')
                    ->join('INNER JOIN', 'ordendecompra oc','ag.idOrdenCompra = oc.id')
                    ->join('INNER JOIN', 'tipodocumento td','oc.idTipoDocumento = td.id')
                    ->join('INNER JOIN', 'centrooperacion co','oc.idCO = co.id')
                    ->join('INNER JOIN', 'categoria cat','ag.idCategoria = cat.id')
                    ->join('INNER JOIN', 'userconteo usc', 'det.idUserConteo = usc.id')
                    ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                    ->join('INNER JOIN', 'empleadologistica empl', 'det.idEmpleadoLogistica = empl.id')
                    ->join('INNER JOIN', 'empleado emp', 'empl.idEmpleado = emp.id')
                    ->join('INNER JOIN', 'estadoconteo estc', 'ag.idEstadoConteo = estc.id')
                    ->join('INNER JOIN', 'estadoprogramacion estp', 'det.idEstado = estp.id');

        $query = $query->andFilterWhere(['det.idAgendaEntregaMercancia' => $idagenda]);
        
        /*$query = Programacionentregamercancia::find()
                    ->select([  
                                //'det.id AS idProgramacion',
                                'ag.id AS idAgenda',
                                'ag.idOrdenCompra', 
                                'ag.fechaCita',
                                'co.codigo AS codigoCentroOperacion',
                                'td.codigo AS codigoTipoDocumento',
                                'oc.consecutivo AS numeroOrdenCompra',
                                'cat.nombre AS nombreCategoria',
                                'det.idUserConteo',
                                'emp.nombreEmpleado AS nombreUsuario', 
                                'SUM(det.unidadesAsignadas) AS unidadesEmpaque',
                                //'det.item AS articulo'
                            ])
                    ->alias('det')
                    ->join('INNER JOIN', 'Agendaentregamercancia ag','det.idAgendaEntregaMercancia = ag.id')
                    ->join('INNER JOIN', 'ordendecompra oc','ag.idOrdenCompra = oc.id')
                    ->join('INNER JOIN', 'tipodocumento td','oc.idTipoDocumento = td.id')
                    ->join('INNER JOIN', 'centrooperacion co','oc.idCO = co.id')
                    ->join('INNER JOIN', 'categoria cat','ag.idCategoria = cat.id')
                    ->join('INNER JOIN', 'userconteo usc', 'det.idUserConteo = usc.id')
                    ->join('INNER JOIN', 'user us', 'usc.idUser = us.id')
                    ->join('INNER JOIN', 'empleadologistica empl', 'det.idEmpleadoLogistica = empl.id')
                    ->join('INNER JOIN', 'empleado emp', 'empl.idEmpleado = emp.id')
                    ->groupBy([
                                //'det.id',
                                'ag.id',
                                'ag.idOrdenCompra', 
                                'ag.fechaCita',
                                'co.codigo',
                                'td.codigo',
                                'oc.consecutivo',
                                'cat.nombre',
                                'det.idUserConteo',
                                'emp.nombreEmpleado'
                                //'det.unidadesAsignadas',
                                //'det.item'
                            ])
                    ->andFilterWhere(['ag.idEstadoConteo' => $idestadoconteo])
                    ->andFilterWhere(['det.idEstado' => $idestadoprogramacion]);*/

        $query->orderBy(['det.created_at' => SORT_DESC, 'ag.idOrdenCompra' => SORT_ASC]);
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
            'det.idEstado' => $this->idEstado,
            'oc.consecutivo' => $this->numeroOrdenCompra
        ]);

        $query->andFilterWhere(['like', 'emp.nombreEmpleado', $this->nombreUsuario]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), det.created_at, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }
}
