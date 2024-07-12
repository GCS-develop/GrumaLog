<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\data\SqlDataProvider;
use frontend\models\Agendaentregamercancia;
use frontend\models\Estadoconteo;

/**
 * AgendaentregamercanciaSearch represents the model behind the search form of `frontend\models\Agendaentregamercancia`.
 */
class AgendaentregamercanciaSearch extends Agendaentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idOrdenCompra', 'numeroCajas', 'idTransportadora', 'idEstado', 
            'created_by', 'updated_by', 'idAgenda', 'idEstadoConteo'], 'integer'],
            [['fechaCita', 'contacto', 'fechaContacto', 'numeroGuia', 'observacion', 
            'created_at', 'updated_at', 'razonSocial', 'codigoTipoDocumento',
            'codigoCentroOperacion', 'numeroOrdenCompra', 'nit', 'proveedor', 
            'nombreTransportadora', 'subcategorias',
            'fechaDesde', 'fechaHasta', 'nombreEstado', 'radicado', 'serie'], 'safe'],
            [['unidades'], 'number'],
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
    public function search($params, $idagenda = null, $idestado = null, $idestadoconteo = null)
    {
        $query = Agendaentregamercancia::find()->alias('det');

        $query->join('INNER JOIN', 'categoria cat', 'det.idCategoria = cat.id');
        $query->join('INNER JOIN', 'ordendecompra oc', 'det.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'agendapresupuesto ap', 'det.idAgenda = ap.id');
        $query->join('INNER JOIN', 'estadoagenda es', 'det.idEstado = es.id');
        $query->join('INNER JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'transportadora tr', 'det.idTransportadora = tr.id');

        $query->select([
            'det.id',
            'det.fechaCita',
            'det.horaCita',
            'det.idCategoria',
            'det.subcategorias',
            'det.unidades',
            'det.unidadesCumplidas',
            'det.numeroCajas',
            'det.contacto',
            'det.fechaContacto',
            'det.numeroGuia',
            'det.observacion',
            'det.idEstado',
            'det.idEstadoConteo',
            'det.idEstadoLegalizacion',
            'det.idTransportadora',
            'det.idOrdenCompra',
            'det.idAgendaEntregaMercancia',
            'det.numeroFactura',
            'det.observacionLegalizacion',
            'ap.desde',
            'ap.hasta',
            'es.nombre AS nombreEstado',
            'pr.nit',
            'pr.razonSocial',
            'pr.criterioMercancia',
            'pr.criterioModeloLogistico AS modeloLogistico',
            'oc.idTipoDocumento',
            'oc.idCO',
            'oc.consecutivo AS numeroOrdenCompra',
            'td.codigo AS codigoTipoDocumento',
            'co.codigo AS codigoCentroOperacion',
            'tr.nombre AS nombreTransportadora',
            'cat.nombre AS nombreCategoria',
            'det.created_at',
            'det.created_by'
        ]);

        //$query->andWhere(['>', 'det.cargosAbonos', 0]);

        if ($idagenda != null){
            $query->andWhere(['=', 'det.idAgenda', $idagenda]);  
        }

        if ($idestadoconteo != null){
            $query->andWhere(['=', 'det.idEstadoConteo', $idestadoconteo]);  
        }

        if ($idestado != null){
            $query->andWhere(['=', 'det.idEstado', $idestado]);  
        }
            
        $query->orderBy(['det.created_at' => SORT_DESC]);

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
            'det.id' => $this->id,
            'det.idOrdenCompra' => $this->idOrdenCompra,
            'det.idAgenda' => $this->idAgenda,
            //'det.fechaCita' => $this->fechaCita,
            'det.unidades' => $this->unidades,
            'det.numeroCajas' => $this->numeroCajas,
            'det.idTransportadora' => $this->idTransportadora,
            'det.fechaContacto' => $this->fechaContacto,
            'det.idEstado' => $this->idEstado,
            'det.idEstadoConteo' => $this->idEstadoConteo,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
            'co.codigo' => $this->codigoCentroOperacion,
            'td.codigo' => $this->codigoTipoDocumento,
            'oc.consecutivo' => $this->numeroOrdenCompra,
            'pr.nit' => $this->nit
        ]);

        $query->andFilterWhere(['like', 'det.contacto', $this->contacto])
            ->andFilterWhere(['like', 'det.numeroGuia', $this->numeroGuia])
            ->andFilterWhere(['like', 'pr.razonSocial', $this->razonSocial])
            ->andFilterWhere(['like', 'det.subcategorias', $this->subcategorias])
            ->andFilterWhere(['like', 'det.observacion', $this->observacion]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), det.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }

    public function searchxEstado($params, $lista_estados = null, $menu = null)
    {
        //$ids = isset($lista_estados) ? explode(',', $lista_estados) : [];

        $query = Agendaentregamercancia::find()->alias('det');

        $query->join('INNER JOIN', 'categoria cat', 'det.idCategoria = cat.id');
        $query->join('INNER JOIN', 'ordendecompra oc', 'det.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'agendapresupuesto ap', 'det.idAgenda = ap.id');
        $query->join('INNER JOIN', 'estadoagenda es', 'det.idEstado = es.id');
        $query->join('INNER JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'transportadora tr', 'det.idTransportadora = tr.id');
        $query->join('LEFT JOIN', 'estadoconteo esc', 'det.idEstadoConteo = esc.id');

        $query->select([
            'det.id',
            'det.fechaCita',
            'det.idCategoria',
            'det.unidades',
            'det.unidadesCumplidas',
            'det.numeroCajas',
            'det.contacto',
            'det.fechaContacto',
            'det.numeroGuia',
            'det.observacion',
            'det.idEstado',
            'det.idEstadoConteo',
            'det.idEstadoLegalizacion',
            'det.idOrdenCompra',
            'det.idTransportadora',
            'det.numeroFactura',
            'det.observacionLegalizacion',
            'ap.desde',
            'ap.hasta',
            'es.nombre AS nombreEstado',
            'pr.nit',
            'pr.razonSocial',
            'pr.criterioMercancia',
            'pr.criterioModeloLogistico',
            'oc.idTipoDocumento',
            'oc.idCO',
            'oc.consecutivo AS numeroOrdenCompra',
            'td.codigo AS codigoTipoDocumento',
            'co.codigo AS codigoCentroOperacion',
            'tr.nombre AS nombreTransportadora',
            'cat.nombre AS nombreCategoria'
        ]);

        $query->orderBy(['det.created_at' => SORT_DESC]);

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

        switch ($menu){
            case 'recepcion':
                if (!empty($lista_estados)) {
                    $query->andWhere(['NOT', ['det.idEstado' => $lista_estados]]);
                }
                break;
            case 'programacion':
                $lista_codigo = [1,99];   
                $idsEncontrados = []; 

                foreach ($lista_codigo as $codigo) {
                    // Buscar el modelo Estado por el código
                    $estado = Estadoconteo::findOne(['codigo' => $codigo]);

                    // Si se encuentra el estado, se agrega su ID al array
                    if ($estado !== null) {
                        $idsEncontrados[] = $estado->id;
                    }
                }

                if (!empty($lista_estados)) {
                    $query->andWhere(['IN', 'det.idEstado', $lista_estados]);
                    $query->andWhere(['IN', 'det.idEstadoConteo', $idsEncontrados]);
                }
                break;
            case 'legalizacion':
                $lista_codigo = [2];   
                $idsEncontrados = []; 

                foreach ($lista_codigo as $codigo) {
                    // Buscar el modelo Estado por el código
                    $estado = Estadoconteo::findOne(['codigo' => $codigo]);

                    // Si se encuentra el estado, se agrega su ID al array
                    if ($estado !== null) {
                        $idsEncontrados[] = $estado->id;
                    }
                }

                if (!empty($lista_estados)) {
                    $query->andWhere(['IN', 'det.idEstadoLegalizacion', $lista_estados]);
                    $query->andWhere(['IN', 'det.idEstadoConteo', $idsEncontrados]);
                }
                break;
            default:  
                if (!empty($lista_estados)) {
                    $query->andWhere(['IN', 'det.idEstadoConteo', $lista_estados]);
                }
                break;
        }

        /*if ($menu == 'recepcion'){
            if (!empty($lista_estados)) {
                $query->andWhere(['IN', 'det.idEstado', $lista_estados]);
            }
        }else{
            if (!empty($lista_estados)) {
                $query->andWhere(['IN', 'det.idEstadoConteo', $lista_estados]);
            }
        }*/

        // grid filtering conditions
        $query->andFilterWhere([
            //'det.idEstado' => $ids,
            'oc.consecutivo' => $this->numeroOrdenCompra,
            'det.idEstado' => $this->idEstado,
            'det.idEstadoConteo' => $this->idEstadoConteo,
        ]);

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
            $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $query->andFilterWhere(['between', 'CONVERT(VARCHAR(10), det.fechaCita, 23)', $fechaInicio, $fechaFin]);
        }

        return $dataProvider;
    }

    public function searchReportGeneral($params, $idagenda){

        $sql = "SELECT 
        Q1.periodoAnio
        ,Q1.periodoMes
        ,Q1.desde
        ,Q1.hasta
        ,Q1.bodegaPrincipal
        ,Q1.radicado
        ,Q1.fechaCita
        ,Q1.horaCita
        ,Q1.contacto
        ,Q1.fechaContacto
        ,Q1.horaContacto
        ,Q1.codigoProveedor
        ,Q1.nitProveedor
        ,Q1.proveedor
        ,Q1.tipoProveedor
        ,Q1.modeloLogistico
        ,Q1.nombreCategoria
        ,Q1.nombreSubcategoria
        ,Q1.serie
        ,Q1.numeroOrdenCompra
        ,Q1.nombreTransportadora
        ,Q1.numeroGuia
        ,Q1.nombreEstadoAgenda
        ,Q1.nombreEstadoLegaliza
        ,Q1.observacion
        ,Q1.unidadesOC
        ,Q3.programadas
        ,Q2.unidadesConteo
        ,Q1.idOrdenCompra
        ,Q1.idEstado
        FROM 
        (
            SELECT aem.id  AS radicado, ap.periodoAnio, ap.periodoMes, FORMAT(ap.desde, 'dd/MM/yyyy') AS 'desde', 
            FORMAT(ap.hasta, 'dd/MM/yyyy') AS 'hasta', aem.idBodega, (LTRIM(RTRIM(bo.codigo)) + ' - ' + bo.nombre) AS 'bodegaPrincipal', 
            aem.idOrdenCompra, 
            FORMAT(aem.fechaCita, 'dd/MM/yyyy') AS 'fechaCita',
            aem.horaCita,
            -- FORMAT(aem.fechaCita, 'HH:mm') AS 'horaCita',
            aem.contacto AS 'contacto',
            -- FORMAT(aem.fechaContacto, 'dd/MM/yyyy') AS 'fechaContacto',
            -- FORMAT(aem.fechaContacto, 'HH:mm') AS 'horaContacto',
            FORMAT(aem.created_at, 'dd/MM/yyyy') AS 'fechaContacto',
            FORMAT(aem.created_at, 'HH:mm') AS 'horaContacto',
            prv.idProveedor AS 'codigoProveedor',
            prv.nit AS 'nitProveedor',
            prv.razonSocial AS 'proveedor',
            ISNULL(prv.criterioMercancia,'-') AS 'tipoProveedor',
            ISNULL(prv.criterioModeloLogistico,'-') AS 'modeloLogistico',
            td.codigo AS serie,
            oc.consecutivo AS 'numeroOrdenCompra',
            tr.nombre AS 'nombreTransportadora',
            aem.numeroGuia,
            aem.idEstado,
            est.nombre AS nombreEstadoAgenda,
            estl.nombre AS nombreEstadoLegaliza,
            aem.observacion AS 'observacion',
            it.idCategoria, cat.nombre AS nombreCategoria, 
            it.idSubcategoria , sub.nombre AS nombreSubcategoria, SUM(det.cantidadPendiente) AS unidadesOC
            FROM agendaentregamercancia aem 
            INNER JOIN ordendecompradetalle det ON aem.idOrdenCompra = det.idOrdenCompra
            INNER JOIN ordendecompra oc  ON det.idOrdenCompra = oc.id
            INNER JOIN item it ON det.idItem = it.id 
            INNER JOIN agendapresupuesto ap ON aem.idAgenda = ap.id
            INNER JOIN proveedor prv ON oc.idProveedor = prv.id
            INNER JOIN tipodocumento td ON oc.idTipoDocumento = td.id
            INNER JOIN transportadora tr ON aem.idTransportadora = tr.id
            INNER JOIN estadoagenda est ON aem.idEstado = est.id
            INNER JOIN estadolegalizacion estl ON aem.idEstadoLegalizacion = estl.id 
            INNER JOIN categoria cat ON det.idCategoria = cat.id 
            INNER JOIN subcategoria sub ON det.idSubcategoria = sub.id 
            LEFT JOIN bodegas bo ON aem.idBodega = bo.id
            WHERE det.cantidadPendiente > 0 AND ap.id = :condicion
            GROUP BY aem.id, ap.periodoAnio, ap.periodoMes, ap.desde, ap.hasta, aem.idBodega, 
            bo.codigo, bo.nombre, aem.idOrdenCompra, 
            aem.fechaCita, aem.horaCita, aem.contacto, aem.created_at, prv.idProveedor, prv.nit, prv.razonSocial,
            prv.criterioMercancia, prv.criterioModeloLogistico, td.codigo, oc.consecutivo, tr.nombre, aem.numeroGuia, 
            aem.idEstado, est.nombre,
            estl.nombre, aem.observacion, it.idCategoria, cat.nombre, it.idSubcategoria, sub.nombre 
        ) Q1 
        LEFT JOIN 
        (
            SELECT aem.idOrdenCompra,  it.idCategoria, it.idSubcategoria,
            SUM(cem.unidadesConteo) AS unidadesConteo
            FROM conteoentregamercancia cem
            INNER JOIN item it ON cem.idItem = it.id 
            INNER JOIN programacionentregamercancia pem ON cem.idProgramacionEntregaMercancia = pem.id 
            INNER JOIN agendaentregamercancia aem ON pem.idAgendaEntregaMercancia = aem.id 
            GROUP BY aem.idOrdenCompra, it.idCategoria, it.idSubcategoria
        ) Q2
        ON Q1.idOrdenCompra = Q2.idOrdenCompra AND  Q1.idCategoria = Q2.idCategoria 
        AND Q1.idSubcategoria = Q2.idSubcategoria
        LEFT JOIN
        (
            SELECT aem.idOrdenCompra, it.idCategoria, it.idSubcategoria, 
            SUM(pem.unidadesAsignadas) AS programadas 
            FROM programacionentregamercancia pem 
            INNER JOIN agendaentregamercancia aem ON pem.idAgendaEntregaMercancia = aem.id 
            INNER JOIN ordendecompra oc ON aem.idOrdenCompra = oc.id 
            INNER JOIN
            (SELECT DISTINCT item, idCategoria, idSubcategoria FROM item)  it 
            ON pem.item = it.item 
            GROUP BY aem.idOrdenCompra, it.idCategoria, it.idSubcategoria
        ) Q3
        ON Q1.idOrdenCompra = Q3.idOrdenCompra AND  Q1.idCategoria = Q3.idCategoria 
        AND Q1.idSubcategoria = Q3.idSubcategoria 
        WHERE 1 = 1 ";

        $this->load($params);

        if (!$this->validate()) {
            // La validación falla, no se aplican filtros y se devuelve todo
            return $dataProvider;
        }

        if ($this->radicado){
            $sql = $sql . " AND Q1.radicado = " . $this->radicado;
        }

        if ($this->serie){
            $sql = $sql . " AND Q1.serie = " . "'" . $this->serie . "'";
        }

        if ($this->numeroOrdenCompra){
            $sql = $sql . " AND Q1.numeroOrdenCompra = " . $this->numeroOrdenCompra;
        }

        if ($this->idEstado){
            $sql = $sql . " AND Q1.idEstado = " . $this->idEstado;
        }

        if ($this->proveedor){
            $sql = $sql . " AND Q1.proveedor LIKE " . "'%" . $this->proveedor . "%'";
        }

        if ($this->nombreTransportadora){
            $sql = $sql . " AND Q1.nombreTransportadora LIKE " . "'%" . $this->nombreTransportadora . "%'";
        }

        if ($this->fechaDesde && $this->fechaHasta) {
            $fechaInicio = date('d/m/Y', strtotime($this->fechaDesde));
            $fechaFin = date('d/m/Y', strtotime($this->fechaHasta));
        
            // Aplicar filtro de rango de fechas
            $sql = $sql . "AND Q1.fechaCita BETWEEN " . "'" . $fechaInicio . "' AND '" . $fechaFin . "'"; 
        }

        $condiciones = [':condicion' => $idagenda]; 
        $query = Agendaentregamercancia::findBySql($sql, $condiciones);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
        ]);

        $dataProvider->sort = [
            'attributes' => [
                'column_name' => [
                    'asc' => ['fechaCita' => SORT_ASC],
                    //'desc' => ['column_name' => SORT_DESC],
                    //'default' => SORT_ASC,
                    //'label' => 'Nombre de la columna',
                ],
            ],
        ];

        // Obtener el SQL que se está ejecutando
        //$sql = $query->createCommand()->getRawSql();
        //var_dump($sql); // Mostrar el SQL en pantalla

        return $dataProvider;
    }
}
