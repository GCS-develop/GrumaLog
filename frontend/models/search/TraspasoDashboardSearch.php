<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use yii\db\Expression;
use frontend\models\Traspasodetalle;

class TraspasoDashboardSearch extends Model
{
    public $fecha;              // YYYY-MM-DD
    public $desde;              // YYYY-MM-DD
    public $hasta;              // YYYY-MM-DD
    public $hora_desde;   // NUEVO -> HH:MM
    public $hora_hasta;   // NUEVO -> HH:MM
    public $user_id;            // td.created_by
    public $bodega_origen_id;   // t.idBodegaOrigen
    public $bodega_destino_id;  // t.idBodegaDestino
    public $tipo_documento;     // t.idTipoDocumento
    public $bodega_id;          // compatibilidad con la vista actual
    public $bodega_codigo;


    public function rules()
    {
        return [
            [['fecha', 'desde', 'hasta', 'bodega_codigo', 'hora_desde', 'hora_hasta'], 'safe'],
            [['user_id', 'bodega_id', 'bodega_origen_id', 'bodega_destino_id', 'tipo_documento'], 'integer'],
            ['bodega_codigo', 'each', 'rule' => ['string'], 'when' => fn($m) => is_array($m->bodega_codigo), 'skipOnEmpty' => true],

        ];
    }

    public function scenarios()
    {
        return Model::scenarios();
    }

    public function searchPerUser($params)
    {
        $this->load($params);

        // Rango (Bogotá)
        $tz = new \DateTimeZone('America/Bogota');

        // Normalizar horas (si no envían, usamos todo el día)
        $hDesde = $this->hora_desde ?: '00:00';
        $hHasta = $this->hora_hasta ?: '23:59';

        if (!$this->desde && !$this->hasta) {
            $f = $this->fecha ?: (new \DateTime('now', $tz))->format('Y-m-d');
            $inicio = (new \DateTime($f . ' 00:00:00', $tz))->format('Y-m-d H:i:s');
            $fin    = (new \DateTime($f . ' 00:00:00', $tz))->modify('+1 day')->format('Y-m-d H:i:s');
        } else {
            // Rango de días (combinar con horas)
            $d = $this->desde ?: (new \DateTime('now', $tz))->format('Y-m-d');
            $h = $this->hasta ?: $d;

            // Si el rango es de un solo día, se respetan ambas horas
            if ($d === $h) {
                $inicio = (new \DateTime("$d $hDesde:00", $tz))->format('Y-m-d H:i:s');
                $fin    = (new \DateTime("$h $hHasta:59", $tz))->format('Y-m-d H:i:s');
            } else {
                // Varios días: desde día inicial con hora_desde hasta día final con hora_hasta
                $inicio = (new \DateTime("$d $hDesde:00", $tz))->format('Y-m-d H:i:s');
                $fin    = (new \DateTime("$h $hHasta:59", $tz))->format('Y-m-d H:i:s');
            }
        }

        // td = traspasodetalle, t = traspaso, uc = usuario (created_by)
        $q = Traspasodetalle::find()
            ->alias('td')
            ->joinWith(['traspaso t', 'usuariocreated uc'])
            ->leftJoin('item i', 'i.id = td.idItem')
            ->leftJoin('unidadempaque u', 'u.codigo = i.unidadEmpaque')
            ->leftJoin('bodegas bo', 'bo.id = t.idBodegaOrigen')
            ->leftJoin('bodegas bd', 'bd.id = t.idBodegaDestino');

        // Filtro de fecha robusto
        $conv = new Expression('TRY_CAST([[td]].[[created_at]] AS datetime2)');
        $q->andWhere(['between', $conv, $inicio, $fin]);
        $q->andWhere(new Expression('TRY_CAST([[td]].[[created_at]] AS datetime2) IS NOT NULL'));

        // Excluir traspasos anulados (idEstado = 2)
        $q->andWhere(['!=', 't.idEstado', 2]);

        // Filtros opcionales
        if ($this->user_id) {
            $q->andWhere(['td.created_by' => (int)$this->user_id]);
        }
        if ($this->bodega_origen_id) {
            $q->andWhere(['t.idBodegaOrigen' => (int)$this->bodega_origen_id]);
        }
        if ($this->bodega_destino_id) {
            $q->andWhere(['t.idBodegaDestino' => (int)$this->bodega_destino_id]);
        }
        if ($this->tipo_documento) {
            $q->andWhere(['t.idTipoDocumento' => (int)$this->tipo_documento]);
        }

        // Filtrar por uno o varios códigos de bodega (origen)
        if ($this->bodega_codigo !== null && $this->bodega_codigo !== '') {
            $codes = $this->bodega_codigo;

            // Normaliza a array
            if (!is_array($codes)) {
                $codes = [$codes];
            }

            // Trim, quita vacíos y duplicados
            $codes = array_values(array_unique(array_filter(array_map(function ($c) {
                return trim((string)$c);
            }, $codes), function ($v) {
                return $v !== '';
            })));

            if (!empty($codes)) {
                // LTRIM/RTRIM para evitar espacios en DB ('207   ')
                $q->andWhere([
                    'or',
                    ['in', new \yii\db\Expression('LTRIM(RTRIM(bo.[id]))'), $codes],
                ]);
            }
        }
        if ($this->bodega_destino_id !== null && $this->bodega_destino_id !== '') {
            $codes = $this->bodega_destino_id;

            // Normaliza a array
            if (!is_array($codes)) {
                $codes = [$codes];
            }

            // Trim, quita vacíos y duplicados
            $codes = array_values(array_unique(array_filter(array_map(function ($c) {
                return trim((string)$c);
            }, $codes), function ($v) {
                return $v !== '';
            })));

            if (!empty($codes)) {
                // LTRIM/RTRIM para evitar espacios en DB ('207   ')
                $q->andWhere([
                    'or',
                    ['in', new \yii\db\Expression('LTRIM(RTRIM(bd.[id]))'), $codes],
                ]);
            }
        }




        // Agregados
        $countRegs      = new Expression('COUNT_BIG(1)'); // # líneas
        $sumCantidad    = new Expression('SUM([[td]].[[cantidad]])'); // unidades crudas
        $sumCantidadEq  = new Expression('SUM((COALESCE(NULLIF(u.equivalencia,0),1)) * [[td]].[[cantidad]])'); // unidades equivalentes

        $minCreated = new Expression('MIN(TRY_CAST([[td]].[[created_at]] AS datetime2))');
        $maxCreated = new Expression('MAX(TRY_CAST([[td]].[[created_at]] AS datetime2))');
        $durSeconds = new Expression('DATEDIFF(SECOND, MIN(TRY_CAST([[td]].[[created_at]] AS datetime2)), MAX(TRY_CAST([[td]].[[created_at]] AS datetime2)))');

        $q->select([
            'td.created_by',
            'username'            => 'uc.username',
            'total_registros'     => $countRegs,
            'total_unidades'      => $sumCantidad,
            'total_unidades_eq'   => $sumCantidadEq,
            't0'                  => $minCreated,
            't1'                  => $maxCreated,
            'dur_seconds'         => $durSeconds,
        ])
            ->groupBy(['td.created_by', 'uc.username'])
            ->asArray();

        $dataProvider = new ActiveDataProvider([
            'query' => $q,
            'pagination' => ['pageSize' => 50],
            'key' => 'created_by',
            'sort' => [
                'attributes' => [
                    'username' => [
                        'asc'  => ['uc.username' => SORT_ASC],
                        'desc' => ['uc.username' => SORT_DESC],
                    ],
                    'total_registros' => [
                        'asc'  => ['total_registros' => SORT_ASC],
                        'desc' => ['total_registros' => SORT_DESC],
                    ],
                    'total_unidades_eq' => [
                        'asc'  => ['total_unidades_eq' => SORT_ASC],
                        'desc' => ['total_unidades_eq' => SORT_DESC],
                    ],
                    'dur_seconds' => [
                        'asc'  => ['dur_seconds' => SORT_ASC],
                        'desc' => ['dur_seconds' => SORT_DESC],
                    ],
                ],
                'defaultOrder' => ['total_unidades_eq' => SORT_DESC],
            ],
        ]);

        // Velocidad UPH con UNIDADES EQUIVALENTES
        $models = $dataProvider->getModels();
        foreach ($models as &$m) {
            $dur = (int)($m['dur_seconds'] ?? 0);
            $unidadesEq = (float)($m['total_unidades_eq'] ?? 0);
            $m['velocidad_uph'] = $dur > 0
                ? round($unidadesEq * 3600 / $dur, 2)
                : null;
        }
        unset($m);
        $dataProvider->setModels($models);

        return $dataProvider;
    }

    public function attributeLabels()
    {
        return [
            'fecha'             => 'Fecha',
            'desde'             => 'Desde',
            'hasta'             => 'Hasta',
            'user_id'           => 'Usuario',
            'bodega_codigo'     => 'Código Bodega',
            'bodega_id'         => 'Bodega',
            'bodega_origen_id'  => 'Bodega Origen',
            'bodega_destino_id' => 'Bodega Destino',
            'tipo_documento'    => 'Tipo Documento',
        ];
    }
}
