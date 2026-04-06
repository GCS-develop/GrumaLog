<?php

namespace frontend\models;

use Yii;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * Columnas reales del Excel:
 * CC | Código | Talla | Color | Producto | UDS. | MES | Tienda | COSTO
 *
 * @property int $id
 * @property int $idImportacion
 * @property string|null $cc
 * @property string|null $codigo
 * @property string|null $producto
 * @property string|null $talla
 * @property string|null $color
 * @property float|null  $uds
 * @property string|null $mes
 * @property string|null $tienda
 * @property float|null  $costo
 */
class Comprasimportaciondetalle extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'comprasimportaciondetalle';
    }

    public function rules()
    {
        return [
            [['idImportacion'], 'required'],
            [['idImportacion'], 'integer'],
            [['uds', 'costo'], 'number'],
            [['cc', 'talla', 'color'], 'string', 'max' => 50],
            [['codigo', 'mes', 'tienda'], 'string', 'max' => 100],
            [['producto'], 'string', 'max' => 200],
            [['idImportacion'], 'exist', 'skipOnError' => true,
                'targetClass' => Comprasimportacion::class,
                'targetAttribute' => ['idImportacion' => 'id']],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'            => 'ID',
            'idImportacion' => 'Importación',
            'cc'            => 'CC',
            'codigo'        => 'Código',
            'producto'      => 'Producto',
            'talla'         => 'Talla',
            'color'         => 'Color',
            'uds'           => 'UDS.',
            'mes'           => 'MES',
            'tienda'        => 'Tienda',
            'costo'         => 'Costo',
        ];
    }

    public function getImportacion()
    {
        return $this->hasOne(Comprasimportacion::class, ['id' => 'idImportacion']);
    }

    // =========================================================
    // UPLOAD — guarda archivo y procesa
    // Retorna: false | ['registros'=>int, 'sinCosto'=>int]
    // =========================================================
    public static function upload($archivo)
    {
        $tempPath = Yii::getAlias('@app/temp/');
        if (!is_dir($tempPath)) {
            mkdir($tempPath, 0755, true);
        }
        $tempFileName = $tempPath . $archivo->baseName . '.' . $archivo->extension;
        $archivo->saveAs($tempFileName);

        $cabecera = new Comprasimportacion();
        $cabecera->numeroRegistros = 0;
        $cabecera->totalUnidades   = 0;
        if (!$cabecera->save()) {
            Yii::error($cabecera->getErrors(), __METHOD__);
            @unlink($tempFileName);
            return false;
        }
        $idImportacion = $cabecera->id;

        $resultado = self::extraerDataArchivo($tempFileName, $idImportacion);

        if ($resultado === false) {
            @unlink($tempFileName);
            return false;
        }

        $cabecera = Comprasimportacion::findOne($idImportacion);
        $cabecera->numeroRegistros = (int) self::find()->where(['idImportacion' => $idImportacion])->count();
        $cabecera->totalUnidades   = (float)(self::find()->where(['idImportacion' => $idImportacion])->sum('uds') ?? 0);
        $cabecera->save();

        @unlink($tempFileName);
        return $resultado; // ['registros' => int, 'sinCosto' => int]
    }

    // =========================================================
    // EXTRAER — lee Excel con detección dinámica de encabezados
    // =========================================================
    public static function extraerDataArchivo($archivoExcel, $idImportacion)
    {
        ini_set('memory_limit', '2048M');
        ini_set('max_execution_time', '1500');

        $spreadsheet = IOFactory::load($archivoExcel);
        $sheet       = $spreadsheet->getActiveSheet();
        $totalFilas  = $sheet->getHighestRow();
        $ultimaCol   = $sheet->getHighestColumn();

        // Mapa: texto del encabezado → campo del modelo
        $mapaEncabezados = [
            'cc'       => 'cc',
            'código'   => 'codigo',
            'codigo'   => 'codigo',
            'producto' => 'producto',
            'talla'    => 'talla',
            'color'    => 'color',
            'uds.'     => 'uds',
            'uds'      => 'uds',
            'mes'      => 'mes',
            'tienda'   => 'tienda',
            'costo'    => 'costo',
        ];

        // Detectar fila encabezado (la que tenga CC y UDS en las primeras 10 filas)
        $filaEncabezado = null;
        $colMap = [];

        for ($f = 1; $f <= min(10, $totalFilas); $f++) {
            $colMap = [];
            foreach (range('A', $ultimaCol) as $col) {
                $val = trim((string)($sheet->getCell($col . $f)->getValue() ?? ''));
                $key = mb_strtolower($val);
                if (isset($mapaEncabezados[$key])) {
                    $colMap[$mapaEncabezados[$key]] = $col;
                }
            }
            if (isset($colMap['cc']) && isset($colMap['uds'])) {
                $filaEncabezado = $f;
                break;
            }
        }

        if ($filaEncabezado === null) {
            Yii::error('No se encontró fila de encabezados.', __METHOD__);
            return false;
        }

        $registros = 0;
        $sinCosto  = 0;

        for ($fila = $filaEncabezado + 1; $fila <= $totalFilas; $fila++) {

            $colUds   = $colMap['uds'] ?? null;
            $valorUds = $colUds ? $sheet->getCell($colUds . $fila)->getValue() : null;

            if ($valorUds === null || $valorUds === '') {
                continue;
            }

            $model = new self();
            $model->idImportacion = $idImportacion;

            // ── VALIDACIÓN 1: CC debe empezar con '0' ──────────────
            $ccRaw = self::celda($sheet, $colMap, 'cc', $fila);
            if ($ccRaw !== null && $ccRaw !== '' && $ccRaw[0] !== '0') {
                $ccRaw = '0' . $ccRaw;
            }
            $model->cc = $ccRaw;

            $model->codigo  = self::celda($sheet, $colMap, 'codigo',  $fila);
            $model->producto= self::celda($sheet, $colMap, 'producto',$fila);
            $model->talla   = self::celda($sheet, $colMap, 'talla',   $fila);
            $model->color   = self::celda($sheet, $colMap, 'color',   $fila);
            $model->uds     = is_numeric($valorUds) ? (float)$valorUds : 0;
            $model->mes     = self::celda($sheet, $colMap, 'mes',     $fila);
            $model->tienda  = self::celda($sheet, $colMap, 'tienda',  $fila);

            // ── VALIDACIÓN 2: costo debe ser > 0 ───────────────────
            $colCosto = $colMap['costo'] ?? null;
            $valCosto = $colCosto ? $sheet->getCell($colCosto . $fila)->getValue() : null;
            $costoNum = is_numeric($valCosto) ? (float)$valCosto : 0;
            $model->costo = $costoNum > 0 ? $costoNum : null;
            if (!($costoNum > 0)) {
                $sinCosto++;
            }

            if (!$model->save()) {
                Yii::error(['fila' => $fila, 'errors' => $model->getErrors()], __METHOD__);
                continue;
            }
            $registros++;
        }

        return ['registros' => $registros, 'sinCosto' => $sinCosto];
    }

    private static function celda($sheet, $colMap, $campo, $fila)
    {
        if (!isset($colMap[$campo])) return null;
        $cell = $sheet->getCell($colMap[$campo] . $fila);
        $raw  = $cell->getValue();

        // Detectar celdas de fecha de Excel y convertirlas a YYYY-MM-DD
        if (is_numeric($raw) && \PhpOffice\PhpSpreadsheet\Shared\Date::isDateTime($cell)) {
            $dt  = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($raw);
            $val = $dt->format('Y-m-d');
        } else {
            $val = trim((string)($raw ?? ''));
        }

        return $val !== '' ? $val : null;
    }
}
