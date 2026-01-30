<?php

namespace frontend\models\search;

use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspasodetalletienda;

/**
 * TraspasodetalletiendaSearch represents the model behind the search form of `frontend\models\Traspasodetalletienda`.
 */
class TraspasodetalletiendaSearch extends Traspasodetalletienda
{
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['id', 'idTraspaso', 'idItem', 'cantidad', 'idDocumentosiesa', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'created_at',
                    'updated_at',
                    'talla',
                    'color',
                    'item',
                    'diferencia',
                    'consecutivoSiesa',
                    'userTraspaso',
                    'fechaDesde',
                    'fechaHasta',
                    'serie',
                    'Destino',
                    'Origen',
                    'codigoBarras',
                ],
                'safe',
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

    public function search($params, $idtraspaso = null)
    {
        $q = (new \yii\db\Query());

        // === Subconsulta: TRASPASO (esperados) por grupo ===
        $tdgSql = "
        SELECT
            td.idTraspaso,
            i0.item,
            i0.idTalla,
            i0.idColor,
            SUM(ISNULL(td.cantidad,0))                               AS cantEsperadaRegistrosGrupo,
            SUM(ISNULL(ue0.equivalencia,1) * ISNULL(td.cantidad,0))  AS cantEsperadaUnidadesGrupo
        FROM traspasodetalle td
        INNER JOIN item i0 ON i0.id = td.idItem
        LEFT JOIN unidadempaque ue0 ON ue0.codigo = i0.unidadEmpaque
        GROUP BY td.idTraspaso, i0.item, i0.idTalla, i0.idColor
    ";

        // === Subconsulta: DOCUMENTOSIESA agregada a 1 fila por idGruma (evita fan-out/duplicados) ===
        $dsAggSql = "
        SELECT idGruma, MIN(f350_consec_docto) AS f350_consec_docto
        FROM documentosiesa
        GROUP BY idGruma
    ";

        // === FROM + JOINs base (solo tablas que NO dupliquen) ===
        $q->from(['tdt' => 'traspasodetalletienda'])
            ->innerJoin(['i'    => 'item'],           'i.id = tdt.idItem')
            ->innerJoin(['t'    => 'talla'],          't.id = i.idTalla')
            ->innerJoin(['c'    => 'color'],          'c.id = i.idColor')
            ->leftJoin(['ue'   => 'unidadempaque'],  'ue.codigo = i.unidadEmpaque')
            // usar documentosiesa agregado (1 x idGruma)
            ->leftJoin(['ds'   => new \yii\db\Expression("($dsAggSql)")], 'tdt.idTraspaso = ds.idGruma')
            ->leftJoin(['tr'   => 'traspaso'],       'tr.id = tdt.idTraspaso')
            ->leftJoin(['tdoc' => 'tipodocumento'],  'tdoc.id = tr.idTipoDocumento')
            ->leftJoin(['bo'   => 'bodegas'],        'bo.id = tr.idBodegaOrigen')
            ->leftJoin(['bd'   => 'bodegas'],        'bd.id = tr.idBodegaDestino')
            ->leftJoin(['uCreateT' => 'user'],       'uCreateT.id = tr.created_by')
            // usuarios de detalle (se agregan con MIN/MAX para no romper el GROUP BY)
            ->leftJoin(['uUpdate' => 'user'],        'uUpdate.id = tdt.updated_by')
            ->leftJoin(['uCreate' => 'user'],        'uCreate.id = tdt.created_by')
            // totales esperados del traspaso
            ->leftJoin(
                ['tdg' => new \yii\db\Expression("($tdgSql)")],
                'tdg.idTraspaso = tdt.idTraspaso
             AND tdg.item    = i.item
             AND tdg.idTalla = i.idTalla
             AND tdg.idColor = i.idColor'
            );

        if ($idtraspaso !== null) {
            $q->andWhere(['tdt.idTraspaso' => $idtraspaso]);
        }

        // === SELECT agregado por grupo (item+talla+color) ===
        $q->select([
            // Claves del grupo
            'tdt.idTraspaso',
            'item'    => 'i.item',
            'idTalla' => 'i.idTalla',
            'idColor' => 'i.idColor',
            'talla'   => 't.codigo',
            'color'   => 'c.nombre',

            // Cabecera (agregadas con MIN/MAX para no romper el GROUP BY)
            'serie'            => 'tdoc.codigo',
            'consecutivoSiesa' => 'ds.f350_consec_docto',
            'Origen'           => new \yii\db\Expression("(bo.codigo + ' - ' + bo.nombre)"),
            'Destino'          => new \yii\db\Expression("(bd.codigo + ' - ' + bd.nombre)"),
            'userTraspaso'     => 'uCreateT.username',
            'nombreCreo'       => new \yii\db\Expression('MIN(uCreate.username)'),
            'created_at'       => new \yii\db\Expression('MIN(tdt.created_at)'),
            'nombreActualizo'  => new \yii\db\Expression('MAX(uUpdate.username)'),
            'updated_at'       => new \yii\db\Expression('MAX(tdt.updated_at)'),

            // TIENDA: totales del grupo
            'cantidadRegistrosTienda' => new \yii\db\Expression('SUM(ISNULL(tdt.cantidad,0))'),
            'unidades'                => new \yii\db\Expression('SUM(ISNULL(ue.equivalencia,1) * ISNULL(tdt.cantidad,0))'),

            // TRASPASO: totales esperados (ya vienen agrupados en tdg)
            'cantidadTraspasoRegistros' => new \yii\db\Expression('ISNULL(tdg.cantEsperadaRegistrosGrupo,0)'),
            'cantidadTraspasounidades'  => new \yii\db\Expression('ISNULL(tdg.cantEsperadaUnidadesGrupo,0)'),

            // Diferencia en unidades
            'diferencia' => new \yii\db\Expression('
            SUM(ISNULL(ue.equivalencia,1) * ISNULL(tdt.cantidad,0))
            - ISNULL(tdg.cantEsperadaUnidadesGrupo,0)
        '),

            // Códigos de barras concatenados (TIENDA ∪ TRASPASO) por grupo
            'codigoBarras' => new \yii\db\Expression("
            (
              SELECT STUFF((
                  SELECT DISTINCT ' | ' + U.codigoBarras
                  FROM (
                      SELECT i1.codigoBarras
                      FROM traspasodetalletienda t1
                      JOIN item i1 ON i1.id = t1.idItem
                      WHERE t1.idTraspaso = tdt.idTraspaso
                        AND i1.item    = i.item
                        AND i1.idTalla = i.idTalla
                        AND i1.idColor = i.idColor
                      UNION
                      SELECT i3.codigoBarras
                      FROM traspasodetalle d3
                      JOIN item i3 ON i3.id = d3.idItem
                      WHERE d3.idTraspaso = tdt.idTraspaso
                        AND i3.item    = i.item
                        AND i3.idTalla = i.idTalla
                        AND i3.idColor = i.idColor
                  ) AS U
                  FOR XML PATH(''), TYPE
              ).value('.', 'nvarchar(max)'), 1, 3, '')
            )
        "),
        ])
            ->groupBy([
                'tdt.idTraspaso',
                'i.item',
                'i.idTalla',
                'i.idColor',
                't.codigo',
                'c.nombre',
                'tdoc.codigo',
                'ds.f350_consec_docto',
                'bo.codigo',
                'bo.nombre',
                'bd.codigo',
                'bd.nombre',
                'uCreateT.username',
                'tdg.cantEsperadaRegistrosGrupo',
                'tdg.cantEsperadaUnidadesGrupo',
            ]);
        // IMPORTANTE: NO poner ->orderBy aquí (lo maneja el DataProvider) para evitar ORDER BY duplicado.

        // === Filtros del formulario (manteniendo tu lógica) ===
        $this->load($params);
        if ($this->validate()) {
            if (!empty($this->consecutivoSiesa)) {
                $q->andWhere(['like', 'ds.f350_consec_docto', $this->consecutivoSiesa]);
            }
            if (!empty($this->serie)) {
                // filtra por el código visible (serie)
                $q->andWhere(['like', 'tdoc.codigo', $this->serie]);
            }

            // Usar la fecha del detalle (tdt.created_at) para no perder registros
            if ($this->fechaDesde || $this->fechaHasta) {
                $campoFecha = new \yii\db\Expression('CAST(tdt.created_at AS DATE)');
                if ($this->fechaDesde && !$this->fechaHasta) {
                    $q->andWhere(['>=', $campoFecha, date('Y-m-d', strtotime($this->fechaDesde))]);
                } elseif (!$this->fechaDesde && $this->fechaHasta) {
                    $q->andWhere(['<=', $campoFecha, date('Y-m-d', strtotime($this->fechaHasta))]);
                } else {
                    $q->andWhere([
                        'between',
                        $campoFecha,
                        date('Y-m-d', strtotime($this->fechaDesde)),
                        date('Y-m-d', strtotime($this->fechaHasta)),
                    ]);
                }
            }
        }

        // DataProvider (modo array) + tu ordenamiento
        $dataProvider = new \yii\data\ActiveDataProvider([
            'query' => $q,
            'pagination' => ['pageSize' => 100],
            'sort' => [
                'attributes' => [
                    'serie',
                    'consecutivoSiesa',
                    'Origen',
                    'Destino',
                    'item',
                    'talla',
                    'color',
                    'cantidadRegistrosTienda',
                    'cantidadTraspasoRegistros',
                    'unidades',
                    'cantidadTraspasounidades',
                    'diferencia',
                    'userTraspaso',
                    'nombreCreo',
                    'created_at',
                    'nombreActualizo',
                    'updated_at',
                ],
                'defaultOrder' => ['item' => SORT_ASC, 'talla' => SORT_ASC, 'color' => SORT_ASC],
            ],
        ]);

        return $dataProvider;
    }




    public function searchConDiferencias($params, $idtraspaso = null)
    {
        // Reutiliza el armado completo (agrupado) de search()
        $dataProvider = $this->search($params, $idtraspaso);

        /** @var \yii\db\Query $q */
        $q = $dataProvider->query;

        // Filtra sólo grupos con diferencia <> 0 usando HAVING sobre la expresión agregada
        $q->andHaving(new \yii\db\Expression("
        SUM(ISNULL(ue.equivalencia,1) * ISNULL(tdt.cantidad,0))
        - ISNULL(tdg.cantEsperadaUnidadesGrupo,0) <> 0
    "));

        return $dataProvider;
    }
}
