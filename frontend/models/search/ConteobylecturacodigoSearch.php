<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Query;
use yii\db\Expression;
use frontend\models\Conteobylecturacodigo;

/**
 * ConteobylecturacodigoSearch represents the model behind the search form of `app\models\Conteobylecturacodigo`.
 */
class ConteobylecturacodigoSearch extends Conteobylecturacodigo
{

    public $fechaInicio;
    public $fechaFin;
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'modulo', 'idConteoDestino', 'idConteoDetalle', 'unidades', 'isMobile', 'created_by', 'updated_by'], 'integer'],
            [['codigoBarras', 'created_at', 'updated_at', 'fechaInicio', 'fechaFin',
            'nombreEmpleado'], 'safe'],
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
    public function search($params, $modulo=null, $idconteodestino=null, $idconteodetalle = null)
    {
        $query = Conteobylecturacodigo::find();

        $query->andFilterWhere([
            'modulo' => $modulo,
            'idConteoDestino' => $idconteodestino,
            'idConteoDetalle' => $idconteodetalle,
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
            'id' => $this->id,
            'modulo' => $this->modulo,
            'idConteoDestino' => $this->idConteoDestino,
            'idConteoDetalle' => $this->idConteoDetalle,
            'unidades' => $this->unidades,
            'isMobile' => $this->isMobile,
            'created_at' => $this->created_at,
            'created_by' => $this->created_by,
            'updated_at' => $this->updated_at,
            'updated_by' => $this->updated_by,
        ]);

        $query->andFilterWhere(['like', 'codigoBarras', $this->codigoBarras]);

        $query->orderBy(['created_at' => SORT_DESC]);

        return $dataProvider;
    }

    public function searchxUsuario($params)
    {
        // Definir el rango de fechas por defecto (hoy)
        if (empty($this->fechaInicio)) {
            $this->fechaInicio = date('Y-m-d', strtotime('yesterday')) . ' 00:00'; // Hoy a las 00:00
        }
        
        if (empty($this->fechaFin)) {
            $this->fechaFin = date('Y-m-d', strtotime('yesterday')) . ' 23:59'; // Hoy a las 23:59
        }

        $query = self::find()
            ->alias('lect')
            ->select([
                'pem.idUserConteo',
                'emp.nombreEmpleado',
                new Expression('CAST(lect.created_at AS DATE) AS fechaConteo'),
                //'lect.created_at AS fechaHoraconteo',
                'td.codigo AS codigoTipodocumento',
                'oc.consecutivo',
                'cem.idItem',
                'it.item',
                'co.codigo AS color',
                'ta.codigo AS talla',
                'cem.codigoBarras',
                new Expression("ISNULL(it.unidadempaque, 'UND') AS unidadEmpaque"),
                'ue.equivalencia',
                new Expression('SUM(lect.unidades) AS totalUnidades')
            ])
            ->innerJoin('conteoentregamercancia cem', 'lect.idConteoDetalle = cem.id')
            ->innerJoin('programacionentregamercancia pem', 'cem.idProgramacionEntregaMercancia = pem.id')
            ->innerJoin('agendaentregamercancia aem', 'pem.idAgendaEntregaMercancia = aem.id')
            ->innerJoin('ordendecompra oc', 'aem.idOrdenCompra = oc.id')
            ->innerJoin('tipodocumento td', 'oc.idTipoDocumento = td.id')
            ->innerJoin('userconteo uc', 'pem.idUserConteo = uc.id')
            ->innerJoin('[user] us', 'uc.idUser = us.id')
            ->innerJoin('empleado emp', 'us.idEmpleado = emp.id')
            ->innerJoin('item it', 'cem.idItem = it.id')
            ->innerJoin('color co', 'it.idColor = co.id')
            ->innerJoin('talla ta', 'it.idTalla = ta.id')
            ->innerJoin('unidadempaque ue', new Expression("ISNULL(it.unidadEmpaque, 'UND') = ue.codigo"))
            ->where(['lect.modulo' => 1])
            /*->andWhere([
                'between',
                new Expression("FORMAT(lect.created_at, 'yyyy-MM-dd HH:mm')"),
                $this->fechaInicio,
                $this->fechaFin
            ])*/
            ->groupBy([
                'pem.idUserConteo',
                'emp.nombreEmpleado',
                new Expression('CAST(lect.created_at AS DATE)'),
                //'lect.created_at',
                'td.codigo',
                'oc.consecutivo',
                'cem.idItem',
                'it.item',
                'co.codigo',
                'ta.codigo',
                'cem.codigoBarras',
                new Expression("ISNULL(it.unidadempaque, 'UND')"),
                'ue.equivalencia'
            ]);

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => ['pageSize' => 20],
        ]);

        if ($this->load($params) && $this->validate()) {
            $query->andWhere([
                'between',
                new Expression("FORMAT(lect.created_at, 'yyyy-MM-dd HH:mm')"),
                $this->fechaInicio,
                $this->fechaFin
            ]);
        }

        $query->andFilterWhere(['like', 'nombreEmpleado', $this->nombreEmpleado]);

        return $dataProvider;
    }
}
