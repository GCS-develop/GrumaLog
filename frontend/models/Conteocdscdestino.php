<?php

namespace frontend\models;

use frontend\models\search\ConteocdscdestinodetalleSearch;
use Yii;
use Mike42\Escpos\Printer;
use diecoding\barcode\generator\Barcode;

/**
 * This is the model class for table "conteocdscdestino".
 *
 * @property int $id
 * @property int $idConteocdscdestinofactura
 * @property int $idCentroOperacion
 * @property int $numeroCajas
 * @property int $idUserConteo
 * @property int|null $idItemUltimoConteo
 * @property int|null $total
 * @property int|null $idEstado
 * @property int|null $idLegalizado
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Conteocdscdestinodetalle[] $conteocdscdestinodetalles
 * @property Centrooperacion $centroperacion
 * @property Conteocdscdestinofactura $factura
 * @property Userconteocdsc $idUserConteo0
 */
class Conteocdscdestino extends \yii\db\ActiveRecord
{
    public $almacen;
    public $codigoAlmacen;
    public $codigoProveedor;
    public $nit;
    public $razonSocial;
    public $tipoProveedor;
    public $idEstadoFactura;
    public $idLegalizadoFactura;
    public $idEstadoEntrada;
    public $idEstadoTraspaso;
    public $numeroFactura;
    public $radicado;
    public $fecha;
    public $nombreEmpleado;
    public $identificacion;
    public $fechaDesde;
    public $fechaHasta;
    public $codigoAlmacenLegaliza;
    public $nombreAlmacenLegaliza;
    public $idBodegaOrigen;
    public $idBodegaDestino;
    public $idTipoDocumento;

    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'conteocdscdestino';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['idConteocdscdestinofactura', 'idCentroOperacion', 'numeroCajas', 'idUserConteo', 'created_at', 'created_by', 'updated_at', 'updated_by'], 'required'],
            [['idConteocdscdestinofactura', 'idCentroOperacion', 'numeroCajas', 'idUserConteo', 'idItemUltimoConteo', 'total', 'idEstado', 'idLegalizado', 'created_by', 'updated_by'], 'integer'],
            [
                [
                    'created_at',
                    'updated_at',
                    'idTempTransferencia',
                    'idErpTraspaso',
                    'idUserTraspaso',
                    'fechaTraspaso',
                    'idTraspaso',
                    'idEstadoTraspaso'
                ],
                'safe'
            ],
            [['idUserConteo'], 'exist', 'skipOnError' => true, 'targetClass' => Userconteocdsc::class, 'targetAttribute' => ['idUserConteo' => 'id']],
            [['idCentroOperacion'], 'exist', 'skipOnError' => true, 'targetClass' => Bodegas::class, 'targetAttribute' => ['idCentroOperacion' => 'id']],
            [['idConteocdscdestinofactura'], 'exist', 'skipOnError' => true, 'targetClass' => Conteocdscdestinofactura::class, 'targetAttribute' => ['idConteocdscdestinofactura' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'No. Conteo',
            'idConteocdscdestinofactura' => 'Id Conteocdscdestinofactura',
            'idCentroOperacion' => 'Id Centro Operacion',
            'numeroCajas' => 'No. Cajas',
            'idUserConteo' => 'Id User Conteo',
            'idItemUltimoConteo' => 'Id Item Ultimo Conteo',
            'total' => 'Total',
            'idEstado' => 'Id Estado',
            'idLegalizado' => 'Id Legalizado',
            'created_at' => 'Inicio Conteo',
            'created_by' => 'Created By',
            'updated_at' => 'Fin Conteo',
            'updated_by' => 'Updated By',

            'codigoProveedor' => 'Cód. Proveedor',
            'razonSocial' => 'Proveedor',
            'tipoProveedor' => 'Tipo Proveedor',

            'idEstadoFactura' => 'Estado',
            'idLegalizadoFactura' => 'Legalizado',
            'numeroFactura' => 'Factura',
            'idEstadoEntrada' => 'Entrada',
            'idEstadoTraspaso' => 'Traspaso',

            'nombreEmpleado' => 'Usuario Conteo',
            'identificacion' => 'Identificación',
            'codigoAlmacen' => 'Código Almacén',
            'almacen' => 'Almacén',

            'fechaDesde' => 'Fecha Desde',
            'fechaHasta' => 'Fecha Hasta',

            'codigoAlmacenLegaliza' => 'Cód. Almacén Legaliza',
            'nombreAlmacenLegaliza' => 'Almacén Legaliza',
        ];
    }

    /**
     * Gets query for [[Conteocdscdestinodetalles]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getConteocdscdestinodetalles()
    {
        return $this->hasMany(Conteocdscdestinodetalle::class, ['idConteocdscdestino' => 'id']);
    }

    /**
     * Gets query for [[CentroOperacion]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCentrooperacion()
    {
        return $this->hasOne(Bodegas::class, ['id' => 'idCentroOperacion']);
    }

    /**
     * Gets query for [[Factura]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFactura()
    {
        return $this->hasOne(Conteocdscdestinofactura::class, ['id' => 'idConteocdscdestinofactura']);
    }

    /**
     * Gets query for [[IdUserConteo0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsuarioconteo()
    {
        return $this->hasOne(Userconteocdsc::class, ['id' => 'idUserConteo']);
    }

    public function getCodigoerp()
    {
        return $this->hasOne(Documentosiesa::class, ['id' => 'idErpTraspaso']);
    }

    public static function imprimirEtiquetas($idconteofactura, $printer, $dataProviderDestino)
    {

        foreach ($dataProviderDestino as $destino) {
            for ($i = 1; $i <= $destino->numeroCajas; $i++) {
                // Renderizar la vista con los datos necesarios

                $dataProviderDetalle = $destino->getConteocdscdestinodetalles()
                    ->andFilterWhere([
                        'idConteocdscdestino' => $destino->id,
                    ])->all();

                $totalunidadempaque = 0;

                foreach ($dataProviderDetalle as $detalle) {
                    if ($detalle->item->unidadEmpaque == null) {
                        $equivalencia = 1;
                    } else {
                        $equivalencia = $detalle->item->unidadempaque->equivalencia;
                    }
                    $unidadempaque = $detalle->totalUnidades / $equivalencia;
                    $totalunidadempaque = $totalunidadempaque + $unidadempaque;
                }

                $printer->setTextSize(2, 2); // Tamaño grande para el título
                $printer->setEmphasis(true); // Negrita
                $printer->text("CONTEO CDSC No:");

                $printer->setEmphasis(false);
                $printer->text($idconteofactura . "\n");

                $printer->setEmphasis(true);
                $printer->text("CAJA ");
                $printer->setEmphasis(false);
                $printer->text("$i DE {$destino->numeroCajas}\n");

                $printer->setEmphasis(true);
                $printer->text("PROVEEDOR:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño Normal
                $printer->text("{$destino->factura->proveedor->nit} - {$destino->factura->proveedor->razonSocial}\n");

                $printer->setTextSize(2, 2); // Tamaño grande
                $printer->setEmphasis(true);
                $printer->text("FACTURA:");
                $printer->setEmphasis(false);
                $printer->text("{$destino->factura->numeroFactura}\n");

                $printer->setEmphasis(true);
                $printer->text("ALM. DESTINO:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño normal
                $printer->text("{$destino->centrooperacion->codigo} - {$destino->centrooperacion->nombre}\n");

                $printer->setTextSize(2, 2); // Tamaño grande

                $printer->setEmphasis(true);
                $printer->text("UND. EMPAQUE:");
                $printer->setEmphasis(false);
                $printer->text(round($totalunidadempaque, 0) . "\n");

                $printer->setEmphasis(true);
                $printer->text("TOTAL UNDS:");
                $printer->setEmphasis(false);
                $printer->text("{$destino->total}\n");

                $printer->setEmphasis(true);
                $printer->text("USUARIO:\n");
                $printer->setEmphasis(false);

                $printer->setTextSize(2, 2); // Tamaño normal

                $printer->text("{$destino->usuarioconteo->user->empleado->nombreEmpleado}\n");

                // Separador
                // $printer->text("-------------------------\n");

                // Cortar papel después de cada etiqueta
                $printer->cut();
            }
        }
    }

    public static function generarTraspasoEncabezado($parametros, $destino, $printer)
    {
        // var_dump($destino);die('csc');

        $nombreEmpresa = $parametros['nombreEmpresa'];
        $nitEmpresa = $parametros['nitEmpresa'];
        $direccionEmpresa = $parametros['direccionEmpresa'];
        $telefonoEmpresa = $parametros['telefonoEmpresa'];

        $serie = $destino->factura->tipodocumento->codigo;
        $numero = $destino->factura->numeroEntrada;

        if ($destino->idErpTraspaso) {
            $serie = $destino->codigoerp->f350_id_tipo_docto;
            $numero = $destino->codigoerp->f350_consec_docto;
        }

        $codigoalmacenlegaliza = $destino->factura->centroOperacionLegaliza->codigo;
        $nombrealmacenlegaliza = $destino->factura->centroOperacionLegaliza->nombre;
        //var_dump($destino->factura->fechaEntrada);die();
        $fechatraspaso = date("d/m/Y", strtotime($destino->factura->fechaEntrada));

        $printer->setTextSize(2, 2); // Tamaño grande para el título
        $printer->setEmphasis(true); // Negrita
        $printer->text("TRASPASO MERCANCIA\n");

        $printer->setTextSize(1, 1);
        $printer->setEmphasis(false);
        $printer->text($nombreEmpresa . "\n");
        $printer->text($nitEmpresa . "\n");
        $printer->text($direccionEmpresa . "  Tel: " . $telefonoEmpresa . "\n");

        // Línea de separación
        $printer->text("\n");
        $printer->text(str_repeat('-', 40) . "\n");

        $printer->text(
            "SERIE: " . str_pad($serie, 5) .
            "NUMERO: " . str_pad($numero, 10) .
            "CAJA: KPM\n"
        );

        $printer->text("ALMACEN ORIGEN: " . $codigoalmacenlegaliza . "\n");
        $printer->text($nombrealmacenlegaliza . "\n");

        $printer->text("ALMACEN DESTINO: " . $destino->centrooperacion->codigo . "\n");
        $printer->text($destino->centrooperacion->nombre . "\n");

        $printer->text("FECHA TRASPASO: " . $fechatraspaso . "\n");

        $printer->text(str_repeat('-', 40) . "\n");

        /*
        // Ancho de las columnas (ajústar según el ancho de la impresora)
        $columna1 = 10; // REF
        $columna2 = 20; // DESCRIPCION
        $columna3 = 10; // UNDS

        // Encabezado de la tabla
        $printer->setEmphasis(true); // Negrita para el encabezado
        $printer->text(
            str_pad("REF", $columna1) .
            str_pad("DESCRIPCION", $columna2) .
            str_pad("UNDS", $columna3, ' ', STR_PAD_LEFT) . "\n"
        );
        $printer->setEmphasis(false); // Sin negrita para el contenido

        // Línea divisoria
        $printer->text(str_repeat("-", $columna1 + $columna2 + $columna3) . "\n");

        $printer->text(str_repeat('-', 40) . "\n");
        */
    }

    public static function generarTraspasoDetalle($dataProviderDetalle, $printer)
    {

        // Configuración del ancho de las columnas
        $columna1 = 8; // Ancho para "REF"
        $columna2 = 10; // Ancho para "Color"
        $columna3 = 10; // Ancho para "Talla"
        $columna4 = 4;  // Ancho para "PAQ"
        $columna5 = 4;  // Ancho para "UM"
        $columna6 = 5;  // Ancho para "Total"

        $totalpaquetes = 0;
        $totalunidades = 0;

        // Encabezado
        $printer->setEmphasis(true);
        $printer->text(str_pad("REF", $columna1) .
            str_pad("COLOR", $columna2) .
            str_pad("TALLA", $columna3) .
            str_pad("PAQ", $columna4) .
            str_pad("UM", $columna5) .
            str_pad("TOTAL", $columna6, ' ', STR_PAD_LEFT) . "\n");
        $printer->text(str_repeat("-", $columna1 + $columna2 + $columna3 + $columna4) . "\n");
        $printer->setEmphasis(false);

        var_dump("Paso 1. Empiezo Detalle");
        foreach ($dataProviderDetalle as $referencia) {

            $item = Item::findOne($referencia->idItem);

            $unidadempaque = 'UND';
            $equivalencia = 1;
            if ($item->unidadEmpaque) {
                $unidadempaque = $item->unidadEmpaque;
                $equivalencia = $item->unidadempaque->equivalencia;
            }

            $total = $referencia->totalUnidades * $equivalencia;

            $totalpaquetes += $referencia->totalUnidades;
            $totalunidades += $total;

            // Primera línea con REF, COLOR, TALLA y UNDS
            $printer->text(
                str_pad($item->item, $columna1) .
                str_pad($item->color->codigo, $columna2) .
                str_pad($item->talla->codigo, $columna3) .
                str_pad($referencia->totalUnidades, $columna4) .
                str_pad($unidadempaque, $columna5) .
                str_pad($total, $columna6, ' ', STR_PAD_LEFT) . "\n"
            );

            // Segunda línea con descripción
            $descripcion = $item->descripcion;
            $printer->text(str_pad($descripcion, $columna1 + $columna2 + $columna3 + $columna4) . "\n");

            // Línea divisoria para cada entrada
            $printer->text(str_repeat("-", $columna1 + $columna2 + $columna3 + $columna4) . "\n");

        }

        return [
            'totalunidades' => $totalunidades,
            'totalpaquetes' => $totalpaquetes
        ];

    }

    public static function generarTraspasoPiePagina($destino, $totales, $printer)
    {

        $serie = $destino->factura->tipodocumento->codigo;
        $numero = $destino->factura->numeroEntrada;

        if ($destino->idErpTraspaso) {
            $serie = $destino->codigoerp->f350_id_tipo_docto;
            $numero = $destino->codigoerp->f350_consec_docto;
        }

        // Simular tabla con bordes
        $printer->text("+----------------------------+----+----+\n");
        $printer->text("| TOTAL UNIDADES             | " . str_pad($totales['totalpaquetes'], 4) . "    " . str_pad($totales['totalunidades'], 4) . " |\n");
        $printer->text("+----------------------------+----+----+\n");

        $printer->text("Cajas: " . $destino->numeroCajas .  "\n");

        $printer->text("1. SERIE DOCUMENTO" . "\n");

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->barcode($serie, Printer::BARCODE_CODE39);
        $printer->text($serie);

        // Línea vacía para separación
        $printer->text("\n");

        $printer->setEmphasis(true);
        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("Alm destino: " . $destino->centrooperacion->codigo . "\n");
        $printer->text($destino->centrooperacion->nombre . "\n");
        $printer->setEmphasis(false);

        $printer->text("\n");

        $printer->text("USUARIO CAPTURA:\n");
        $printer->text("{$destino->usuarioconteo->user->empleado->nombreEmpleado}\n");

        /* Yii::$app->user->identity->id */
        $printer->text("USUARIO IMPRIME:\n");
        $printer->text(Yii::$app->user->identity->username);

        $printer->text("\n\n");

        // Espacio para columnas vacías
        $espacioVacio = str_repeat(' ', 25);

        // Texto para "FIRMA SELLO"
        $firmaSello = "FIRMA SELLO:";

        // Impresión simulada de tabla
        $printer->text($espacioVacio . $firmaSello . "\n");
        $printer->text(str_repeat("-", 40) . "\n"); // Línea divisoria o separadora

        // Línea vacía para separación adicional
        $printer->text("\n");

        $printer->setJustification(Printer::JUSTIFY_LEFT);
        $printer->text("2. NUMERO DOCUMENTO" . "\n");

        $printer->setJustification(Printer::JUSTIFY_CENTER);
        $printer->barcode($numero, Printer::BARCODE_CODE39);
        $printer->text($numero);

        $printer->text("\n\n");

    }

    public static function generarTransferenciaFactura($idconteofactura)
    {

        $modelfactura = Conteocdscdestinofactura::findOne(['id' => $idconteofactura]);

        $dataProviderDestino = $modelfactura->getConteocdscdestinos()
            ->andFilterWhere([
                'idConteocdscdestinofactura' => $idconteofactura,
            ])->all();

        foreach ($dataProviderDestino as $destino) {
            $idconteodestino = $destino->id;

            Conteocdscdestino::generarTransferenciaDestino($idconteodestino);
        }
    }

    public static function generarTransferenciaDestino($idconteodestino)
    {

        $modeldestino = Conteocdscdestino::findOne(['id' => $idconteodestino]);

        if ($modeldestino->idTempTransferencia) {
            $id = $modeldestino->idTempTransferencia;
            $numRegistrosBorrados = Temptransferenciatransitoexcel::deleteAll(['idTransferenciaerp' => $id]);
            $numRegistrosBorrados = Temptransferenciaerp::deleteAll(['id' => $id]);
            $numRegistrosBorrados = Temptraspasocdscdetalle::deleteAll(['idConteoDestino' => $modeldestino->id]);
            $numRegistrosBorrados = Temptraspasocdsc::deleteAll(['idConteoDestino' => $modeldestino->id]);
        }

        $transferencia = Conteocdscdestino::crearTemporaltransferenciaerp($modeldestino);

        $searchModel = new ConteocdscdestinodetalleSearch();
        $dataProviderDestinoDetalle = $searchModel->searchTraspasoSIESA($modeldestino->id);

        $traspaso = Temptraspasocdsc::crearRegistro($transferencia->id, $modeldestino);

        $fila = 0;

        //$numero = 'Factura: ' . $transferencia->documento;
        $numero_formateado = str_pad($modeldestino->id, 6, '0', STR_PAD_LEFT);
        $numero = $traspaso->tipodocumento->codigo . $numero_formateado;

        foreach ($dataProviderDestinoDetalle as $detalle) {
            $fila++;
            //var_dump($detalle['item']); 
            Conteocdscdestino::generarTransferenciaDetalle($transferencia, $numero, $detalle, $fila, $traspaso->id);
        }

        $modeldestino->idTempTransferencia = $transferencia->id;
        $modeldestino->save();
        //die("hola");
    }

    public static function generarTransferenciaDetalle($transferencia, $numero, $detalle, $fila, $idtraspaso)
    {

        $modeltransferencia = new Temptransferenciatransitoexcel();

        //$unidad = $detalle['unidadEmpaque'];
        $unidad = 'UND';
        $modeltransferencia->idTransferenciaerp = $transferencia->id;
        $modeltransferencia->centroOperacionDocumento = $detalle['centroOperacionDocumento'];
        $modeltransferencia->tipoDocumento = $detalle['tipoDocumento'];
        $modeltransferencia->fechaDocumento = $detalle['fechaDocumento'];
        $modeltransferencia->bodegaSalidaDocumento = $detalle['bodegaSalidaDocumento'];
        $modeltransferencia->bodegaEntradaDocumento = $detalle['bodegaEntradaDocumento'];
        $modeltransferencia->centroOperacion = $detalle['centroOperacion'];
        $modeltransferencia->tipoDocumentoMovimiento = $detalle['tipoDocumentoMovimiento'];
        $modeltransferencia->bodegaSalidaMovimiento = $detalle['bodegaSalidaMovimiento'];
        $modeltransferencia->centroOperacionMovimiento = $detalle['centroOperacionMovimiento'];
        $modeltransferencia->unidadSalida = $unidad;
        $modeltransferencia->cantidadBase = $detalle['unidades'];
        $modeltransferencia->unidadesConteoEmpaque = $detalle['unidadesConteo'];
        $modeltransferencia->costoPromedioUnitario = $detalle['costoPromedioUnitario'];
        $modeltransferencia->item = $detalle['item'];
        $modeltransferencia->color = $detalle['color'];
        $modeltransferencia->talla = $detalle['talla'];
        $modeltransferencia->numero = $numero;
        $modeltransferencia->procesado = 0;
        $modeltransferencia->fila = $fila;
        $modeltransferencia->codigoUnidadEmpaque = $detalle['unidadEmpaque'];

        if (!$modeltransferencia->save()) {
            var_dump($modeltransferencia->getErrors());
            die("stop");
        }

        $traspasodetalle = Temptraspasocdscdetalle::crearRegistro($detalle['idItem'], $detalle['unidadesConteo'], $idtraspaso, $transferencia);
    }

    public static function crearTemporaltransferenciaerp($destino)
    {

        $factura = $destino->factura;

        $iddocumento = 165613;
        $numero = $factura->ordenCompra->tipoDocumento->codigo . '-' . $factura->ordenCompra->consecutivo;
        $descripcion =
            "Traspaso CDSC: " . $destino->id . ' - ' .
            $factura->proveedor->razonSocial . ' - ' .
            'OC: ' . $numero . ' - ' .
            'Factura: ' . $factura->numeroFactura . ' - ' .
            'Bodega: ' . $destino->centrooperacion->codigo;

        $documento = $factura->numeroFactura;

        $numero = 'Traspaso CDSC: ' . $destino->id;

        $notas = $numero;

        $idconteofactura = $destino->idConteocdscdestinofactura;
        $idconteodestino = $destino->id;

        $origen = 'CDSC';
        $transferencia = Temptransferenciaerp::crearRegistro($iddocumento, $descripcion, $documento, $notas, $origen, $idconteofactura, $idconteodestino);

        return $transferencia;
    }

    public static function getTotalUnidades($idconteodestino)
    {
        return (float) (new \yii\db\Query())
            ->select(['SUM(td.totalUnidades * COALESCE(ue.equivalencia, 1))'])
            ->from('conteocdscdestinodetalle td')
            ->innerJoin('item i', 'td.idItem = i.id')
            ->leftJoin('unidadempaque ue', 'i.unidadEmpaque = ue.codigo')
            ->where(['td.idConteocdscdestino' => $idconteodestino])
            ->scalar();
    }

    public static function getTotalUnidadesEmp($idconteodestino)
    {
        return (float) (new \yii\db\Query())
            ->select(['SUM(td.totalUnidades)'])
            ->from('conteocdscdestinodetalle td')
            ->where(['td.idConteocdscdestino' => $idconteodestino])
            ->scalar();
    }
}
