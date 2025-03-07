<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Conteocdscdestinodetalle;
use yii\db\Expression;

/**
 * ConteocdscdestinodetalleSearch represents the model behind the search form of `frontend\models\Conteocdscdestinodetalle`.
 */
class ConteocdscdestinodetalleSearch extends Conteocdscdestinodetalle
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idConteocdscdestino', 'idItem', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'created_at', 'updated_at'], 'safe'],
            [['totalUnidades'], 'number'],
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
    public function search($params, $idconteodestino = null)
    {
        $query = Conteocdscdestinodetalle::find();

        $query->andFilterWhere([
            'idConteocdscdestino' => $idconteodestino,
        ]);

        $query->andFilterWhere(['>', 'totalUnidades', 0]);

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
            'idConteocdscdestino' => $this->idConteocdscdestino,
            'idItem' => $this->idItem,
            'totalUnidades' => $this->totalUnidades,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        return $dataProvider;
    }

    public function searchSIESA($idconteofactura){
        $query = Conteocdscdestinodetalle::find()->alias('det');
        $query->join('LEFT JOIN', 'conteocdscdestino dest', 'det.idConteocdscdestino = dest.id');
        $query->join('LEFT JOIN', 'conteocdscdestinofactura fac', 'dest.idConteocdscdestinofactura = fac.id');
        $query->join('LEFT JOIN', 'ordendecompra oc', 'fac.idOrdenCompra = oc.id');
        $query->join('LEFT JOIN', 'tipodocumento td', 'oc.idTipoDocumento = td.id');
        $query->join('LEFT JOIN', 'proveedor prv', 'oc.idProveedor = prv.id');
        $query->join('LEFT JOIN', 'bodegas bo', 'dest.idCentroOperacion = bo.id');
        $query->join('LEFT JOIN', 'item it', 'det.idItem = it.id');
        $query->join('LEFT JOIN', 'color col', 'it.idColor = col.id');
        $query->join('LEFT JOIN', 'talla tal', 'it.idTalla = tal.id');
        $query->join('LEFT JOIN', 'bodegas bom', 'fac.idBodegaMovimiento = bom.id');
        $query->join('LEFT JOIN', 'tipodocumento tdm', 'fac.idTipoDocumentoMovimiento = tdm.id');
        $query->join('LEFT JOIN', 'centrooperacion com', 'fac.idBodegaMovimiento = com.id');
        $query->join('INNER JOIN', 'view_ordendecompradetalle vi', 'vi.idOrdenCompra = fac.idOrdenCompra AND it.id = vi.idItem');
        $query->join('INNER JOIN', 'tipodocumento tde', 'fac.idSerieEntrada = tde.id');
        $query->join('LEFT JOIN', 'unidadempaque ue', "ISNULL(it.unidadEmpaque,'UND') = ue.codigo");

        $query->select([
            "vi.CO AS codigoCentroOperacionDocumentoEntrada",
            "tde.codigo AS codigoTipoDocumentoEntrada", 
            'fac.numeroEntrada AS consecutivoDocumentoEntrada',
            "CONVERT(VARCHAR(8), fac.fechaEntrada, 112) AS fechaDocumentoEntrada",
            "vi.tercero",
            "fac.numeroFacturaEntrada AS numeroFactura",
            "vi.sucursalProveedor", 
            "vi.nitcomprador", 
            "fac.consignacion",
            //new Expression('0 AS consignacion'),
            "vi.CO AS codigoCentroOperacionOC",
            "vi.codigoTipoDoctoOC", 
            "vi.consecutivo AS consecutivoOC",
            "vi.bodega", 
            "ISNULL(it.unidadEmpaque,'UND') AS unidadEmpaque", 
            "vi.fechaEntrega",
            "det.totalUnidades AS unidades" ,
            "(det.totalUnidades * ISNULL(ue.equivalencia, 1)) AS unidadesConteo", 
            'ISNULL(ue.equivalencia,1) AS equivalencia',
            "it.item", 
            "col.codigo AS color", 
            "tal.codigo AS talla", 
            "vi.codigointernomovto"            
        ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => false, // Deshabilita la paginación
        ]);

        $query = $query->andFilterWhere(['fac.id' => $idconteofactura]);

        // echo $query->createCommand()->getRawSql(); die("hola");
 
        // add conditions that should always apply here

        return $dataProvider;
    }

    public function searchTraspasoSIESA($idconteodestino){

        $query = "
            SELECT 
                fac.id, com.codigo AS centroOperacionDocumento, 
                tdm.codigo AS tipoDocumento, 
                FORMAT(fac.updated_at, 'yyyyMMdd') AS fechaDocumento, 
                bom.codigo AS bodegaSalidaDocumento, 
                bo.codigo AS bodegaEntradaDocumento, 
                com.codigo AS centroOperacion, 
                tdm.codigo AS tipoDocumentoMovimiento,
                bom.codigo AS bodegaSalidaMovimiento, 
                com.codigo AS centroOperacionMovimiento, 
                ISNULL(it.unidadEmpaque,'UND') AS unidadEmpaque, 
                
                det.totalUnidades AS unidadesConteo, 
                (det.totalUnidades * ISNULL(ue.equivalencia, 1)) AS unidades, 

                ISNULL(ue.equivalencia,1) AS equivalencia, 
                0 AS costoPromedioUnitario, 
                it.item, 
                col.nombre AS color, 
                LTRIM(RTRIM(tal.nombre)) AS talla,
                bom.id AS idBodegaOrigen,
                bo.id AS idBodegaDestino,
                com.id AS idCentroOperacion,
                tdm.id AS idTipoDocumento,
                det.idItem 
            FROM conteocdscdestinodetalle det 
            LEFT JOIN conteocdscdestino dest ON det.idConteocdscdestino = dest.id 
            LEFT JOIN conteocdscdestinofactura fac ON dest.idConteocdscdestinofactura = fac.id 
            LEFT JOIN ordendecompra oc ON fac.idOrdenCompra = oc.id 
            LEFT JOIN tipodocumento td ON oc.idTipoDocumento = td.id 
            LEFT JOIN proveedor prv ON oc.idProveedor = prv.id 
            LEFT JOIN bodegas bo ON dest.idCentroOperacion = bo.id 
            LEFT JOIN item it ON det.idItem = it.id 
            LEFT JOIN color col ON it.idColor = col.id 
            LEFT JOIN talla tal ON it.idTalla = tal.id 
            LEFT JOIN bodegas bom ON fac.idBodegaMovimiento = bom.id 
            LEFT JOIN tipodocumento tdm ON fac.idTipoDocumentoMovimiento = tdm.id 
            LEFT JOIN centrooperacion com ON fac.idCentroOperacionMovimiento = com.id 
            LEFT JOIN unidadempaque ue ON ISNULL(it.unidadEmpaque,'UND') = ue.codigo 
            WHERE det.idConteocdscdestino = :idconteodestino    
        ";

        $connection = Yii::$app->db;
        $command = $connection->createCommand($query);
        $command->bindValue(':idconteodestino', $idconteodestino); // Pasar el parámetro

        $detalles = $command->queryAll(); // Ejecuta la consulta y obtiene los resultados en un array
        return $detalles;
    }
}
