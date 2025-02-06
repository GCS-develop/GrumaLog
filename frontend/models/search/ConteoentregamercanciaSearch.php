<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteoentregamercancia;
use yii\db\Query;

/**
 * ConteoentregamercanciaSearch represents the model behind the search form of `frontend\models\Conteoentregamercancia`.
 */
class ConteoentregamercanciaSearch extends Conteoentregamercancia
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idProgramacionEntregaMercancia', 'idItem', 'unidadesConteo', 'created_by', 'updated_by'], 'integer'],
            [['created_at', 'updated_at'], 'safe'],
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
    public function search($params, $idprogramacion = null, $item = null, $idagenda = null, $iduserconteo = null, $idfactura = null)
    {
        $query = Conteoentregamercancia::find()->alias('det');
        $query->join('INNER JOIN', 'programacionentregamercancia pe', 'det.idProgramacionEntregaMercancia = pe.id');
        $query->join('INNER JOIN', 'facturaentregamercancia fe', 'pe.idFacturaEntregaMercancia = fe.id');
        $query->join('INNER JOIN', 'item it', 'det.idItem = it.id');
        $query->join('INNER JOIN', 'agendaentregamercancia ae', 'pe.idAgendaEntregaMercancia = ae.id');
        $query->join('INNER JOIN', 'userconteo usc', 'pe.idUserConteo = usc.id');
        $query->join('INNER JOIN', 'user us', 'usc.idUser = us.id');
        $query->join('INNER JOIN', 'empleadologistica empl', 'usc.idEmpleadoLogistica = empl.id');
        $query->join('INNER JOIN', 'empleado emp', 'empl.idEmpleado = emp.id');
        $query->join('INNER JOIN', 'ordendecompra oc', 'ae.idOrdenCompra = oc.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'agendapresupuesto ap', 'ae.idAgenda = ap.id');
        $query->join('INNER JOIN', 'centrooperacion co', 'oc.idCO = co.id');
        $query->join('LEFT JOIN', 'talla ta', 'it.idTalla = ta.id');
        $query->join('LEFT JOIN', 'color col', 'it.idColor = col.id');
        $query->join('LEFT JOIN', 'marca ma', 'it.idMarca = ma.id');
        $query->join('LEFT JOIN', 'unidadempaque ue', 'it.unidadEmpaque = ue.nombre');
        $query->join('LEFT JOIN', 'categoria cat', 'it.idCategoria = cat.id');
        $query->join('LEFT JOIN', 'subcategoria sub', 'it.idSubcategoria = sub.id');
        $query->join('LEFT JOIN', 'proveedor pr', 'oc.idProveedor = pr.id');
        $query->join('LEFT JOIN', 'ordendecompradetalle detoc', 'it.id = detoc.idItem AND oc.id = detoc.idOrdenCompra');


        $query->select([
            'det.id',
            'det.idProgramacionEntregaMercancia',
            'det.idItem',
            'det.unidadesAsignadas',
            'det.unidadesConteo',
            'det.created_at',
            'pe.idAgendaEntregaMercancia',
            'pe.idUserConteo',
            'us.id AS idUser',
            'us.username',
            'emp.identificacion',
            'emp.nombreEmpleado',
            'ae.idOrdenCompra',
            'ae.idAgenda',
            'ae.idCategoria',
            //'ae.numeroFactura',
            'fe.numeroFactura',

            'co.codigo AS codigoCentroOperacion',
            'co.nombre AS centroOperacion',

            'oc.consecutivo',
            'oc.comprador',
            'oc.nitcomprador',
            'detoc.bodega',
            'detoc.codigointernomovto',

            'td.codigo AS codigoTipoDocumento',
            'td.nombre AS tipoDocumento',

            'ap.periodoAnio',
            'ap.periodoMes', 
            
            'it.item',
            'it.referencia',
            'it.descripcion',
            "ISNULL(it.unidadEmpaque, 'UND') AS unidadEmpaque",

            'ma.nombre AS marca',
            'col.nombre AS color',
            'LTRIM(RTRIM(ta.nombre)) AS talla',
            'ue.equivalencia AS equivalencia',

            'it.idCategoria',
            'cat.nombre AS categoria',
            'it.idSubcategoria',
            'sub.nombre AS Subcategoria',

            'oc.fechaEntrega',
            'pr.razonSocial',
            'ROW_NUMBER() OVER (PARTITION BY oc.consecutivo ORDER BY it.item) AS numeroRegistro',
            'ROW_NUMBER() OVER (ORDER BY (SELECT NULL)) AS numeroFila'
        ]);

        $query = $query->andFilterWhere(['pe.idAgendaEntregaMercancia' => $idagenda]);
        $query = $query->andFilterWhere(['det.idProgramacionEntregaMercancia' => $idprogramacion]);
        $query = $query->andFilterWhere(['det.item' => $item]);
        $query = $query->andFilterWhere(['pe.idUserConteo' => $iduserconteo]);
        $query = $query->andFilterWhere(['fe.id' => $idfactura]);

        $query->orderBy(['det.idProgramacionEntregaMercancia' => SORT_ASC,
                        'det.item' => SORT_ASC,
                        'ta.orden' => SORT_ASC]);

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
            'det.idProgramacionEntregaMercancia' => $this->idProgramacionEntregaMercancia,
            'det.idItem' => $this->idItem,
            'det.unidadesConteo' => $this->unidadesConteo,
            'det.created_at' => $this->created_at,
            'det.created_by' => $this->created_by,
            'det.updated_at' => $this->updated_at,
            'det.updated_by' => $this->updated_by,
        ]);

        return $dataProvider;
    }

    public function searchSIESA($idagenda, $idfactura)
    {
        $query = Conteoentregamercancia::find()->alias('cem');
        $query->join('INNER JOIN', 'programacionentregamercancia pem', 'cem.idProgramacionEntregaMercancia = pem.id');
        $query->join('INNER JOIN', 'facturaentregamercancia fe', 'pem.idFacturaEntregaMercancia = fe.id');
        $query->join('INNER JOIN', 'agendaentregamercancia aem', 'pem.idAgendaEntregaMercancia = aem.id');
        $query->join('INNER JOIN', 'item it', 'cem.idItem = it.id');
        $query->join('INNER JOIN', 'color col', 'it.idColor = col.id');
        $query->join('INNER JOIN', 'talla tal', 'it.idTalla = tal.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'fe.idTipoDocumentoEntrada = td.id');
        $query->join('INNER JOIN', 'view_ordendecompradetalle vi', 'vi.idOrdenCompra = aem.idOrdenCompra AND it.item = vi.item AND col.codigo = vi.color AND tal.codigo = vi.talla ');

        //$query->join('LEFT JOIN', 'unidadempaque ue', "ISNULL(it.unidadEmpaque,'UND') = ue.codigo");

        $query->select([
            "vi.CO AS codigoCentroOperacionDocumentoEntrada", 
            "td.codigo AS codigoTipoDocumentoEntrada", 
            "fe.consecutivoDocumentoEntrada",
            "CONVERT(VARCHAR(8), fe.fechaDocumentoEntrada, 112) AS fechaDocumentoEntrada",
            //"fe.fechaDocumentoEntrada",
            "vi.tercero",
            "aem.numeroFactura",
            "vi.sucursalProveedor", 
            "vi.nitcomprador", 
            "fe.consignacion", 
            "vi.CO AS codigoCentroOperacionOC",
            "vi.codigoTipoDoctoOC", 
            "vi.consecutivo AS consecutivoOC",
            "vi.bodega", 
            "ISNULL(it.unidadEmpaque,'UND') AS unidadEmpaque", 
            "vi.fechaEntrega",
            "cem.unidadesConteo" ,
            // "(cem.unidadesConteo * ISNULL(ue.equivalencia, 1)) AS [unidadesConteo]", 
            "it.item", 
            "col.codigo AS color", 
            "tal.codigo AS talla", 
            "vi.codigointernomovto"
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Deshabilita la paginación
            /*'pagination' => [
                'pageSize' => 50,
            ],*/
        ]);

        $query = $query->andFilterWhere(['pem.idAgendaEntregaMercancia' => $idagenda]);
        $query = $query->andFilterWhere(['pem.idFacturaEntregaMercancia' => $idfactura]);

        // echo $query->createCommand()->getRawSql(); die("hola");
 
        // add conditions that should always apply here

        return $dataProvider;
    }
}
