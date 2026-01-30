<?php

namespace frontend\models;

use common\models\User;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Vtiful\Kernel\Format;
use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "traspasodetalle".
 *
 * @property int $id
 * @property int $idTraspaso
 * @property int $idItem
 * @property int $cantidad
 *
 * @property Item $item
 * @property Traspaso $traspaso
 */
class Traspasodetalle extends \yii\db\ActiveRecord
{
    public $bodegaorigen;
    public $bodegadestino;
    public $numerocajas;
    public $codigoitem;
    public $count;
    public $ultimo_codigo;
    public $cantidad_paquetes;
    public $consecutivointerno;
    public $consecutivosiesa;
    public $talla;
    public $color;


    public $estado;
    public $usuario;
    public $tipomovimiento;
    public $fechaDesde;
    public $fechaHasta;
    public $proveedor;
    public $estadoPlanilla;


    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'traspasodetalle';
    }
    public function behaviors()
    {
        return [
            [
                'class' => TimestampBehavior::className(),
                'createdAtAttribute' => 'created_at',
                'updatedAtAttribute' => 'updated_at',
                'value' => new Expression('GETDATE()'),
            ],
            [
                'class' => BlameableBehavior::className(),
                'createdByAttribute' => 'created_by',
                'updatedByAttribute' => 'updated_by',
                'value' => function ($event) {
                    return Yii::$app->user->id;
                },
            ],
        ];
    }
    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idTraspaso', 'idItem', 'codigoitem'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idTraspaso', 'cantidad', 'created_by', 'updated_by', 'idItem'], 'integer'],
            [['created_at', 'updated_at', 'cantidadTransferencia'], 'safe'],
            [['idTraspaso'], 'exist', 'skipOnError' => true, 'targetClass' => Traspaso::class, 'targetAttribute' => ['idTraspaso' => 'id']],
            /*[
                ['idItem'],
                'exist',
                'skipOnError' => true,
                'targetClass' => Item::class,
                'targetAttribute' => ['idItem' => 'id'],
                'when' => function ($model, $attribute) {
                    // Obtener el valor del campo idItem
                    $idItem = $model->$attribute;

                    // Verificar si el Item asociado está activo
                    $item = Item::findOne(['id' => $idItem, 'idEstado' => 'ACTIVO']);

                    return $item !== null;
                },
                'message' => 'El Item seleccionado no está activo.',
            ],*/
        ];
    }


    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'Id',
            'idTraspaso' => 'Id traspaso',
            'idItem' => 'Item',
            'cantidad' => 'Cantidad',
            'codigoitem' => 'Codigo EAN',
            'total' => 'Cantidad total',
            'bodegaorigen' => 'Bodega origen',
            'bodegadestino' => 'Bodega destino',
            'total' => 'Cantidad total',
            'count' => 'Total',
            'updated_at' => 'Fecha',
            'ultimo_codigo' => 'Ultimo codigo',
            'cantidad_paquetes' => 'Registros',
            'unidad' => 'Unidad de medida',
            'totalum' => 'Um/total',
            'created_at' => 'creado',
            'created_by' => 'creado',
            'updated_by' => 'actualizado'
        ];
    }

    /**
     * Gets query for [[Item]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getItem()
    {
        return $this->hasOne(Item::class, ['id' => 'idItem'])
            ->where(['idEstado' => 'ACTIVO']);
    }
    /**
     * Gets query for [[Traspaso]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTraspaso()
    {
        return $this->hasOne(Traspaso::class, ['id' => 'idTraspaso']);
    }
    public function getUsuariocreated()
    {
        return $this->hasOne(User::class, ['id' => 'created_by']);
    }
    public function getUsuarioupdated()
    {
        return $this->hasOne(User::class, ['id' => 'updated_by']);
    }


    public static function getInventario($codigobarras, $codigobodega)
    {

        $command = \Yii::$app->dbsiesa->createCommand("
                select 
                t400.f400_cant_existencia_1
                -- t131.f131_id, f150_id_co, f150_id, t400.f400_cant_existencia_1
                -- t400.f400_cant_comprometida_1, t400.f400_cant_existencia_2
                from t400_cm_existencia t400
                inner join t150_mc_bodegas t150
                ON t400.f400_rowid_bodega = t150.f150_rowid
                left join [t131_mc_items_barras] t131
                ON t400.f400_rowid_item_ext = t131.f131_rowid_item_ext
                WHERE 1 = 1
                AND t131.f131_id = '" . $codigobarras .
            "' AND f150_id = '" . $codigobodega . "';");

        // $result = $command->queryAll();

        $existencia = $command->queryScalar();

        return $existencia;
    }

    public static function getInventarioWS($codigobarras, $codigobodega)
    {

        $existencia = 0;
        $existenciaBodega = 0;

        if ($codigobarras) {

            $modelinventario = new InventariosWs();
            $lista = $modelinventario->getAllInventariosSiesa($codigobarras);
            foreach ($lista as $bodega) {

                $existencia = $existencia + $bodega['CantidadExistente'];

                if ($codigobodega != null) {
                    if ($bodega['Bodega'] == $codigobodega) {
                        $existenciaBodega = $existenciaBodega + $bodega['CantidadExistente'];
                    }
                }
            }
        }

        if ($codigobodega != null) {
            $existencia = $existenciaBodega;
        }

        return $existencia;
    }

    public static function generarTransferenciaWS($traspaso)
    {

        if ($traspaso->transferenciaerp == 0) {

            $iddocumento = 165613;
            $descripcion = $traspaso->tipodocumento->nombre . ' - ' .
                $traspaso->bodegaDestino->codigo . ' - ' .
                $traspaso->bodegaOrigen->codigo . ' - ' .
                $traspaso->created_at;
            $documento = $traspaso->consecutivo;
            $notas = 'Transferencia Modulo Traspaso GRUMALOG';
            $origen = 'T';
            $transferencia = Transferenciaerp::crearRegistro($iddocumento, $descripcion, $documento, $notas, $origen);

            $fila = 0;
            foreach ($traspaso->traspasodetalles as $detalle) {
                $modeltransferencia = new Transferenciatransitoexcel();

                $fila = $fila + 1;
                $unidad = $detalle->item->unidadEmpaque ? $detalle->item->unidadEmpaque : $detalle->item->unidadOrden;

                $modeltransferencia->idTransferenciaerp = $transferencia->id;
                $modeltransferencia->centroOperacionDocumento = '002';
                $modeltransferencia->tipoDocumento = $traspaso->tipodocumento->codigo;
                $modeltransferencia->fechaDocumento = Yii::$app->formatter->asDatetime($traspaso->updated_at, 'php:Ymd');
                $modeltransferencia->bodegaSalidaDocumento = $traspaso->bodegaDestino->codigo;
                $modeltransferencia->bodegaEntradaDocumento = $traspaso->bodegaOrigen->codigo;
                $modeltransferencia->centroOperacion = '002';
                $modeltransferencia->tipoDocumentoMovimiento = $traspaso->tipodocumento->codigo;
                $modeltransferencia->bodegaSalidaMovimiento = $traspaso->bodegaDestino->codigo;
                $modeltransferencia->centroOperacionMovimiento = '002';
                $modeltransferencia->unidadSalida = $unidad;
                $modeltransferencia->cantidadBase = $detalle->cantidad;
                $modeltransferencia->costoPromedioUnitario = 0;
                $modeltransferencia->item = $detalle->item->item;
                $modeltransferencia->color = $detalle->item->color->nombre;
                $modeltransferencia->talla = $detalle->item->talla->nombre;
                $modeltransferencia->numero = $traspaso->tipodocumento->codigo . $traspaso->consecutivo;
                $modeltransferencia->procesado = 0;
                $modeltransferencia->fila = $fila;

                $modeltransferencia->save();
            }

            if ($fila > 0) {

                $respuesta = 0;
                $model = Transferenciaerp::findOne(['id' => $transferencia->id]);
                $respuesta = Transferenciatransitoexcel::transferenciaSalidaWS($transferencia->id, Yii::$app->user->identity->username);

                if ($respuesta == 0) {
                    //$mensaje = "Proceso de Actualización Finalizo Con Éxito";
                    //Yii::$app->session->setFlash( 'success', $mensaje);

                    $model->enviadoWS = 1;
                } else {
                    //$mensaje = "Proceso de Actualización Presenta Inconsistencia";
                    //Yii::$app->session->setFlash( 'error', $mensaje);
                    $model->enviadoWS = 0;
                }
                $model->save();
            }
        }
    }
    public function retornarInventario()
    {
        try {
            $barcode = $this->item->codigoBarras ?? null;
            $codbodega = $this->traspaso->bodegaOrigen->codigo ?? null;
            $cantidadAnulada = $this->getCantidadUnidades() ?? 0;

            if (!$barcode || !$codbodega || $cantidadAnulada <= 0) {
                throw new \Exception("Código de barras, código de bodega o cantidad no válidos.");
            }

            // Consultar inventario en Siesa
            $inventarioDisponible = Item::getInventario($barcode, $codbodega);

            if ($inventarioDisponible === null) {
                throw new \Exception("No se pudo obtener el inventario para el código de barras: $barcode y bodega: $codbodega.");
            }

            // Determinar la cantidad a retornar (lo menor entre lo anulado y lo disponible)
            $cantidadARetornar = min($cantidadAnulada, $inventarioDisponible);

            if ($cantidadARetornar <= 0) {
                throw new \Exception("No hay inventario suficiente en Siesa para retornar.");
            }
            // Obtener los ítems relacionados
            $itemsRelacionados = Item::find()
                ->where([
                    'item' => $this->item->item,
                    'idTalla' => $this->item->idTalla,
                    'idColor' => $this->item->idColor,
                ])
                ->all();

            if (!$itemsRelacionados) {
                return $this->asJson(['status' => 'error', 'message' => "No se encontraron otros items relacionados."]);
            }

            // 🔹 Extraer los IDs de los items relacionados
            $itemIds = array_column($itemsRelacionados, 'id'); // Convertir objetos en array de IDs

            // Actualizar el inventario para todos los items relacionados
            $actualizado = Inventario::updateAllCounters(
                ['existencia' => $cantidadARetornar],  // Actualizar la existencia con la cantidad a retornar
                [
                    'codigoBodega' => $codbodega,       // Filtrar por la bodega
                    'idItem' => $itemIds                // Filtrar por los IDs de los items relacionados
                ]
            );

            if ($actualizado === 0) {
                throw new \Exception("No se actualizó ninguna fila en el inventario. Verifica los datos.");
            }

            return $actualizado;
        } catch (\Throwable $e) {
            \Yii::error("Error al retornar inventario: " . $e->getMessage(), __METHOD__);
            return false; // Devuelve false para indicar fallo
        }
    }



    public static function generarArchivotransferencia($traspaso)
    {
        $archivo = Yii::getAlias('@app/web/archivos/Formato_Traspaso.xlsx'); // Ruta a la planilla de Excel 

        $spreadsheet = IOFactory::load($archivo);

        $sheet = $spreadsheet->getSheetByName('Documentos');
        $spreadsheet->setActiveSheetIndex(0);

        $linea = 2;

        $sheet->setCellValue('A' . $linea, '002');
        $sheet->setCellValue('B' . $linea, $traspaso->tipodocumento->codigo);
        $sheet->setCellValue('C' . $linea, $traspaso->updated_at);
        $sheet->setCellValue('D' . $linea, $traspaso->bodegaDestino->codigo);
        $sheet->setCellValue('E' . $linea, $traspaso->bodegaOrigen->codigo);

        $sheet = $spreadsheet->getSheetByName('Movimientos');
        $spreadsheet->setActiveSheetIndex(1);


        foreach ($traspaso->traspasodetalles as $detalle) {
            $unidad = $detalle->item->unidadEmpaque ? $detalle->item->unidadEmpaque : $detalle->item->unidadOrden;
            $sheet->setCellValue('A' . $linea, '002');
            $sheet->setCellValue('B' . $linea, $traspaso->tipodocumento->codigo);
            $sheet->setCellValue('C' . $linea, $traspaso->bodegaDestino->codigo);
            $sheet->setCellValue('D' . $linea, '002');
            $sheet->setCellValue('E' . $linea, $unidad); //
            $sheet->setCellValue('F' . $linea, $detalle->cantidad);
            $sheet->setCellValue('G' . $linea, '0');
            $sheet->setCellValue('H' . $linea, $detalle->item->item);
            $sheet->setCellValue('I' . $linea, $detalle->item->color->nombre);
            $sheet->setCellValue('J' . $linea, $detalle->item->talla->nombre);

            $linea = $linea + 1;
        }

        $nombreArchivo = "Entrada_Traspaso_" . $traspaso->tipodocumento->codigo . ' ' . $traspaso->serie . "_" .
            $traspaso->consecutivo . "_" . $traspaso->usuario->username . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $rutaGuardado = Yii::getAlias('@app/web/archivos/') . $nombreArchivo;

        // Guardar el archivo Excel
        $writer->save($rutaGuardado);

        return $rutaGuardado;
    }

    // public function getItemUnidad()
    // {
    //     $item = $this->hasOne(Item::class, ['id' => 'idItem'])
    //     ->where(['idEstado' => 'ACTIVO']);
    //     $equivalencia = $item->unidadEmpaque ?  $item->unidadEmpaque->equivalencia : $item->unidadOrden->equivalencia;
    //     $unidades = 'cantidad' * $equivalencia;

    //     return $unidades ;
    // }

    public function getCantidadUnidades()
    {
        $equivalencia = $this->item->unidadempaque ? $this->item->unidadempaque->equivalencia : 1;
        return $equivalencia * $this->cantidad ?? $this->cantidad;
    }


    public function retornarPedidoAR(): int
    {
        try {
            $traspaso = $this->traspaso ?? null;
            $item     = $this->item ?? null;
            if (!$traspaso || !$item) return 0;

            $idPedido        = (int)($traspaso->idPedido ?? 0);
            $idBodegaDest    = (int)($traspaso->idBodegaDestino ?? 0);
            $cantidadAnulada = (int)($this->getCantidadUnidades() ?? 0);
            if ($idPedido <= 0 || $idBodegaDest <= 0 || $cantidadAnulada <= 0) return 0;

            // 1) Todos los códigos/barcodes del MISMO ítem (misma ref + talla + color)
            $itemIds = $this->getItemsRelaciones();   // array<int> de IDs
            if (empty($itemIds)) return 0;

            // 2) (Recomendado) Tope para no dejar ninguna fila negativa
            $minRecibidas = (int) Pedidodetalle::find()
                ->where(['idPedido' => $idPedido, 'idBodega' => $idBodegaDest])
                ->andWhere(['idItem' => $itemIds])
                ->min('unidadesRecibidas');

            $quita = max(0, min($cantidadAnulada, $minRecibidas));
            if ($quita === 0) return 0;

            // 3) Restar la MISMA cantidad a CADA fila relacionada
            Pedidodetalle::updateAllCounters(
                ['unidadesRecibidas' => -$quita],
                [
                    'idPedido' => $idPedido,
                    'idBodega' => $idBodegaDest,
                    'idItem'   => $itemIds,
                ]
            );

            // 🔁 Regla de negocio: reporta la cantidad del ÍTEM (no multiplicada por códigos)
            return $quita;
        } catch (\Throwable $e) {
            Yii::error('retornarPedidoAR(): ' . $e->getMessage(), __METHOD__);
            return 0;
        }
    }
}
