<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Planillaembarquetraspaso;
use yii\data\SqlDataProvider;


/**
 * PlanillaembarquetraspasoSearch represents the model behind the search form of `frontend\models\Planillaembarquetraspaso`.
 */
class PlanillaembarquetraspasoSearch extends Planillaembarquetraspaso
{

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idPlanillaEmbarque', 'idTraspaso', 'idBodegaOrigen', 'idBodegaDestino', 'unidades', 'unidadesEmp', 'idUsuarioRecibido', 'idEstado', 'created_by', 'updated_by'], 'integer'],
            [['sello'], 'number'],
            [
                [
                    'fechaRecibido',
                    'created_at',
                    'updated_at',
                    'codAlmacenOrigen',
                    'codAlmacenDestino',
                    'almacenOrigen',
                    'almacenDestino',
                    'usuarioRecibido',
                    'tipoDocumento',
                    'consecutivoDocumento',
                    'fechaPlanillaembarque',
                    'horaPlanillaembarque',
                    'fechaTraspaso',
                    'horaTraspaso',
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
            $query = Planillaembarquetraspaso::find()->alias('pet');
        } else {
            $query = Planillaembarquetraspaso::find()->alias('pet') // Define el alias de la tabla principal
                ->where(['pet.idPlanillaEmbarque' => $id]); // Usa el alias 'pet' para evitar la ambigüedad
        }

        $query->join('INNER JOIN', 'planillaembarque pe', 'pet.idPlanillaEmbarque = pe.id'); // planillaembarque
        $query->join('INNER JOIN', 'traspaso tr', 'pet.idTraspaso = tr.id');
        $query->join('INNER JOIN', 'bodegas bo', 'pet.idBodegaOrigen = bo.id');
        $query->join('INNER JOIN', 'bodegas bd', 'pet.idBodegaDestino = bd.id');
        $query->join('INNER JOIN', 'tipodocumento td', 'tr.idTipoDocumento = td.id');
        $query->join('INNER JOIN', 'estadodespacho ed', 'ed.id = pet.idEstado');
        $query->join('LEFT JOIN', 'documentosiesa ds', 'tr.id = ds.idGruma');
        $query->join('LEFT JOIN', 'user us', 'pet.idUsuarioRecibido = us.id');

        // $query->join('LEFT JOIN', 'user us', 'pet.idUsuarioRecibido = us.id');

        $query->join('INNER JOIN', 'user usc', 'usc.id = pe.created_by');
        $query->join('INNER JOIN', 'user usp', 'usp.id = pet.created_by');
        // $query->join('LEFT JOIN', 'planillaembarquebodega peb', 'peb.idBodegaDestino = bd.id');
        $query->join('LEFT JOIN', 'planillaembarquebodega peb', 'peb.idBodegaDestino = bd.id AND peb.idPlanillaEmbarque = pe.id
        
        AND peb.selloLlegada IS NOT NULL');




        $query->select([
            'pet.id',
            'pet.idPlanillaEmbarque',
            'pet.idTraspaso',
            'pet.unidades',
            'pet.unidadesEmp',
            'pet.fechaRecibido',
            'pet.created_at',

            'pe.fechaDespacho AS fechaPlanillaembarque',
            'pe.horaDespacho AS horaPlanillaembarque',

            'usp.username AS usuarioPlanilla',

            'usc.username AS usuarioCreador',

            'us.username AS usuarioRecibido',

            'ed.nombre AS estado',

            'bo.codigo AS codAlmacenOrigen',
            'bo.nombre AS almacenOrigen',
            'bd.codigo AS codAlmacenDestino',
            'bd.nombre AS almacenDestino',


            'ds.f350_consec_docto AS consecutivoDocumento',
            'ds.f350_id_tipo_docto AS tipoDocumento',


            'td.codigo AS tipoDocumentoInterno',

            'tr.consecutivo AS consecutivoInterno',

            'tr.created_at AS fechaTraspaso',

            'peb.selloLlegada AS selloLlegada',
            'peb.selloSalida AS selloSalida',

            'peb.orden AS orden',
            'pe.nombreConductor AS conductor',

            //'pet.*'
        ]);


        // add conditions that should always apply here

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => 100,  // Define la cantidad de registros por página
            ],
            'sort' => [
                'defaultOrder' => [
                    //  'idPlanillaEmbarque' => SORT_DESC,
                    // Asegúrate de no incluir 'idPlanillaEmbarque' en otro lugar
                ]
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
            'pet.id' => $this->id,
            'pet.idPlanillaEmbarque' => $this->idPlanillaEmbarque,
            'pet.idTraspaso' => $this->idTraspaso,
            'pet.idBodegaOrigen' => $this->idBodegaOrigen,
            'pet.idBodegaDestino' => $this->idBodegaDestino,
            'pet.unidades' => $this->unidades,
            'pet.unidadesEmp' => $this->unidadesEmp,
            'pet.sello' => $this->sello,
            'pet.fechaRecibido' => $this->fechaRecibido,
            'pet.idUsuarioRecibido' => $this->idUsuarioRecibido,
            'pet.idEstado' => $this->idEstado,
            'pet.created_at' => $this->created_at,
            'pet.created_by' => $this->created_by,
            'pet.updated_at' => $this->updated_at,
            'pet.updated_by' => $this->updated_by,

            'bo.codigo' => $this->codAlmacenOrigen,
            'bd.codigo' => $this->codAlmacenDestino,

            // 'us.username' => $this->usuarioRecibido,
            'ed.nombre' => $this->estado,
            'td.codigo' => $this->tipoDocumento,
            // 'tr.consecutivo' => $this->consecutivoDocumento,
            'pe.fechaDespacho' => $this->fechaPlanillaembarque,
            'pe.horaDespacho' => $this->horaPlanillaembarque,
            'tr.created_at' => $this->fechaTraspaso,
        ]);

        $query->andFilterWhere(['like', 'bo.nombre', $this->almacenOrigen])
            ->andFilterWhere(['like', 'bd.nombre', $this->almacenDestino])
            ->andFilterWhere(['like', 'tr.created_at', $this->fechaTraspaso])
            ->andFilterWhere(['like', 'us.username', $this->usuarioRecibido])
            ->andFilterWhere(['like', 'ed.nombre', $this->estado])
            ->andFilterWhere(['like', 'td.codigo', $this->tipoDocumento])
            ->andFilterWhere(['like', 'tr.consecutivo', $this->consecutivoDocumento])
            ->andFilterWhere(['like', 'pe.fechaDespacho', $this->fechaPlanillaembarque])
            ->andFilterWhere(['like', 'pe.horaDespacho', $this->horaPlanillaembarque]);

        $query->orderBy([

            'pet.idPlanillaEmbarque' => SORT_DESC,
            'orden' => SORT_ASC,
            'bo.codigo' => SORT_ASC,
            'bd.codigo' => SORT_ASC,
            'pet.fechaRecibido' => SORT_ASC,

        ]);

        return $dataProvider;
    }
}
