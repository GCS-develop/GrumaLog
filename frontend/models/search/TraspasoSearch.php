<?php

namespace frontend\models\search;

use Yii;
use yii\base\Model;
use yii\data\ActiveDataProvider;
use frontend\models\Traspaso;
use frontend\models\Tipodocumento;
use common\models\User;
use yii\helpers\ArrayHelper;
use yii\db\Expression;

/**
 * TraspasoSearch represents the model behind the search form of `app\models\Traspaso`.
 */
class TraspasoSearch extends Traspaso
{
    public $serie;
    public $und_empaque;
    public $und_traspaso;

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idBodegaOrigen', 'idBodegaDestino', 'idEstado'], 'each', 'rule' => ['integer']],
            [['id', 'idTipoDocumento', 'tipoMovimiento'], 'integer'],

            [
                [
                    'updated_at',
                    'created_by',
                    'updated_by',
                    'fechaDesde',
                    'fechaHasta',
                    'estadoPlanilla',
                    'consecutivosiesa',
                    'idEstado',
                    'fechaRecibido',
                    'transferenciaerpNombre',
                    'reciboMasivo',
                    'consecutivoSiesaEnTienda',
                ],
                'safe'
            ],
            [['consecutivo',], 'number'],
            [['serie'], 'string', 'max' => 5],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function scenarios()
    {
        return Model::scenarios();
    }

    /**
     * Creates data provider instance with search query applied
     *
     * @param array $params
     *
     * @return ActiveDataProvider
     */
    public function search($params)
    {
        $query = Traspaso::find()->alias('tr');
        $query->join('left JOIN', 'documentosiesa ds', 'tr.id = ds.idGruma');
        //     $query->join(
        //         'LEFT JOIN',
        //         'planillaembarquetraspaso pet',
        //         'pet.id = (
        //     SELECT TOP 1 p.id
        //     FROM planillaembarquetraspaso p
        //     WHERE p.idTraspaso = tr.id
        //     ORDER BY p.id DESC
        // )
        // AND pet.idEstado <> 3'
        //     );  
        // se supone que si esta anulado en planilla no lo muestro aca

        $query->join(
            'LEFT JOIN',
            'planillaembarquetraspaso pet',
            'pet.id = (
                    SELECT TOP 1 id
                    FROM planillaembarquetraspaso
                    WHERE idTraspaso = tr.id
                    ORDER BY 
                        id DESC,
                        CASE 
                            WHEN idEstado = 3 THEN 0
                            ELSE 1
                        END
                )
                '
        );
        // $query->join('LEFT JOIN', 'estadorecepcion er', 'er.id = pet.idEstado AND er.id <> 5'); // Agregando la relación
        $query->join('LEFT JOIN', 'Estadodocumentoplanilla er', 'er.id = pet.idEstado'); // Agregando la relación
        $query->join('LEFT JOIN', 'conteocdscdestino dest', 'tr.id = dest.idTraspaso');
        $query->join('LEFT JOIN', 'userconteocdsc uc', 'dest.idUserConteo = uc.id');
        $query->join('LEFT JOIN', 'user u', 'uc.idUser = u.id');
        $query->join('LEFT JOIN', 'user ut', 'tr.created_by = ut.id');
        $query->join('LEFT JOIN', 'user uta', 'tr.updated_by = uta.id');
        $query->join('LEFT JOIN', 'tipodocumento tdoc', 'tdoc.id = tr.idTipoDocumento');
        $query->join('LEFT JOIN', 'bodegas bo', 'bo.id = tr.idBodegaOrigen');
        $query->join('LEFT JOIN', 'bodegas bd', 'bd.id = tr.idBodegaDestino');
        $query->join('LEFT JOIN', 'estadotraspaso et', 'et.id = tr.idEstado'); // Agregando la relación


        $query->select([
            'tr.*',
            'tr.created_by',
            "(CASE
                WHEN tr.tipoMovimiento <> 3 THEN (CAST(tr.created_by AS NVARCHAR) + ' - ' + ut.username)
                ELSE (CAST(uc.idUser AS NVARCHAR) + ' - ' + u.username)
            END) AS idusertraspasocdsc",
            'tr.consecutivo',
            'tr.idBodegaOrigen',
            'tr.idBodegaDestino',
            'tr.tipoMovimiento',
            'ds.f350_consec_docto as consecutivosiesa',
            'er.nombre as estadoPlanilla',
            'pet.fechaRecibido',
            'tdoc.codigo as serie',
            "(bo.codigo + ' - ' + bo.nombre) AS Origen",
            "(bd.codigo + ' - ' + bd.nombre) AS Destino",
            "(SELECT TOP 1 i.nombreProveedor
            FROM traspasodetalle td
            LEFT JOIN item i ON td.idItem = i.id
            WHERE td.idTraspaso = tr.id
          ) AS nombreProveedor",
            'et.nombre as estadoNombre',
            "(CASE 
            WHEN tr.transferenciaerp = 1 THEN 'Enviado'
            ELSE 'Sin Enviar'
        END) AS transferenciaerpNombre",
            "FORMAT(tr.created_at, 'yyyy-MM-dd HH:mm') AS [FechaCrea]",
            "FORMAT(tr.updated_at, 'yyyy-MM-dd HH:mm') AS [FechaActualiza]",
            'uta.username as userActualiza',
            // ' tr.tipoMovimiento as tipoMovimientoNombre'
            "(CASE 
            WHEN tr.tipoMovimiento = 3 THEN 'CDSC'
            WHEN tr.tipoMovimiento = 2 THEN 'Entradas'
            ELSE 'Traspaso'
        END) AS tipoMovimientoNombre",
        ]);


        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'pageSize' => '10',
            ],

        ]);
        $query->orderBy([
            "CASE 
                WHEN pet.fechaPlanillaembarque IS NOT NULL 
                AND DATEDIFF(DAY, pet.fechaPlanillaembarque, GETDATE()) > 2 
                THEN 0 
                ELSE 1 
            END" => SORT_ASC
        ]);

        $query->orderBy(['tr.created_at' => SORT_DESC]);

        $this->load($params);

        if (!$this->validate()) {
            // uncomment the following line if you do not want to return any records when validation fails
            // $query->where('0=1');
            return $dataProvider;
        }

        // grid filtering conditions
        $query->andFilterWhere([
            'tr.id' => $this->id,
            'tr.numeroCajas' => $this->numeroCajas,
            'tr.idTipoDocumento' => $this->idTipoDocumento,
            'tr.created_by' => $this->created_by,
            'tr.created_at' => $this->created_at,
            'tr.tipoMovimiento' => $this->tipoMovimiento,
            'tr.updated_by' => $this->updated_by,
            'tr.reciboMasivo' => $this->reciboMasivo,
            'ds.f350_consec_docto' => $this->consecutivosiesa,


        ]);

        if ($this->estadoPlanilla === '__sin_estado__') {
            $query->andWhere(['pet.idEstado' => null]);
        } elseif (!empty($this->estadoPlanilla)) {
            $query->andWhere(['er.id' => $this->estadoPlanilla]);
        }


        if (empty($this->fechaDesde)) {
            $this->fechaDesde = date('Y-m-01');
        }


        if ($this->fechaDesde || $this->fechaHasta) {
            // Si solo está presente fechaDesde, buscar por esa fecha exacta
            if ($this->fechaDesde && !$this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $query->andWhere(['>=', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaInicio]);
            }
            // Si solo está presente fechaHasta, buscar hasta esa fecha
            elseif (!$this->fechaDesde && $this->fechaHasta) {
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['<=', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaFin]);
            }
            // Si están presentes ambas, buscar entre ambas fechas
            elseif ($this->fechaDesde && $this->fechaHasta) {
                $fechaInicio = date('Y-m-d', strtotime($this->fechaDesde));
                $fechaFin = date('Y-m-d', strtotime($this->fechaHasta));
                $query->andWhere(['between', new \yii\db\Expression('CAST(tr.created_at AS DATE)'), $fechaInicio, $fechaFin]);
            }
        }

        if (empty($this->idBodegaOrigen)) {
            $this->idBodegaOrigen = [1, 2, 13];
        }

        $query->andFilterWhere(['IN', 'tr.idBodegaOrigen', $this->idBodegaOrigen]);

        $query->andFilterWhere(['IN', 'tr.idBodegaDestino', $this->idBodegaDestino]);

        $query->andFilterWhere(['IN', 'tr.idEstado', $this->idEstado]);

        $query->andFilterWhere(['like', 'tr.consecutivo', $this->consecutivo]);

        $query->andFilterWhere(['like', 'tr.created_at', $this->created_at]);

        if ($this->created_by !== null) {
            $usuarios = User::find()
                ->where(['LIKE', 'username', '%' . trim($this->created_by) . '%', false])
                ->all();

            // Verificar si se encontraron usuarios
            if (!empty($usuarios)) {
                $userIds = array_map(function ($usuario) {
                    return $usuario->id;
                }, $usuarios);

                // Filtrar por IDs de usuario encontrados
                $query->andFilterWhere(['IN', 'tr.created_by', $userIds]);
            } else {
                // Manejar el caso en que no se encuentren usuarios
                // Por ejemplo, puedes aplicar un filtro predeterminado
                $query->andFilterWhere(['tr.created_by' => null]); // Filtro predeterminado
            }
        }


        if ($this->serie !== null) {
            $tipoDocumento = Tipodocumento::findOne(['codigo' => $this->serie]);
            if ($tipoDocumento !== null) {
                $query->andFilterWhere(['tr.idTipoDocumento' => $tipoDocumento->id]);
            } else {
                // Si el tipo de documento no se encuentra, no se filtrará por tipo de documento
                $query->andFilterWhere(['tr.idTipoDocumento' => null]);
            }
        }

        return $dataProvider;
    }
}
