<?php

namespace frontend\models;

use Yii;
use yii\behaviors\BlameableBehavior;
use yii\behaviors\TimestampBehavior;
use yii\db\Expression;

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\IOFactory;

/**
 * This is the model class for table "ordendecompratemporal".
 *
 * @property int $id
 * @property int $idCO
 * @property int $idTipoDocumento
 * @property string $fechaDocumento
 * @property int $idProveedor
 * @property string $sucursalProveedor
 * @property int $idComprador
 * @property int $idCondicionPago
 * @property string $created_at
 * @property int $created_by
 * @property string $updated_at
 * @property int $updated_by
 *
 * @property Centrooperacion $co
 * @property Comprador $comprador
 * @property Condicionpago $condicionpago
 * @property Proveedor $proveedor
 * @property Tipodocumento $tipodocumento
 * @property Ordendecompratemporalitem[] $ordendecompratemporalitems
 */
class Ordendecompratemporal extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'ordendecompratemporal';
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
            [['idCO', 'idTipoDocumento', 'fechaDocumento', 'idProveedor', 'sucursalProveedor', 
            'idComprador', 'idCondicionPago'], 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            [['idCO', 'idTipoDocumento', 'idProveedor', 'idComprador', 'idCondicionPago', 'created_by', 'updated_by'], 'integer'],
            [['fechaDocumento', 'created_at', 'updated_at'], 'safe'],
            [['sucursalProveedor'], 'string', 'max' => 5],
            [['idCO'], 'exist', 'skipOnError' => true, 'targetClass' => Centrooperacion::class, 'targetAttribute' => ['idCO' => 'id']],
            [['idTipoDocumento'], 'exist', 'skipOnError' => true, 'targetClass' => Tipodocumento::class, 'targetAttribute' => ['idTipoDocumento' => 'id']],
            [['idProveedor'], 'exist', 'skipOnError' => true, 'targetClass' => Proveedor::class, 'targetAttribute' => ['idProveedor' => 'id']],
            [['idComprador'], 'exist', 'skipOnError' => true, 'targetClass' => Comprador::class, 'targetAttribute' => ['idComprador' => 'id']],
            [['idCondicionPago'], 'exist', 'skipOnError' => true, 'targetClass' => Condicionpago::class, 'targetAttribute' => ['idCondicionPago' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'idCO' => 'CO',
            'idTipoDocumento' => 'Tipo Documento',
            'fechaDocumento' => 'Fecha Documento',
            'idProveedor' => 'Proveedor',
            'sucursalProveedor' => 'Sucursal',
            'idComprador' => 'Comprador',
            'idCondicionPago' => 'Condición de Pago',
            'created_at' => 'Created At',
            'created_by' => 'Created By',
            'updated_at' => 'Updated At',
            'updated_by' => 'Updated By',
        ];
    }

    /**
     * Gets query for [[Co]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCo()
    {
        return $this->hasOne(Centrooperacion::class, ['id' => 'idCO']);
    }

    /**
     * Gets query for [[Comprador]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getComprador()
    {
        return $this->hasOne(Comprador::class, ['id' => 'idComprador']);
    }

    /**
     * Gets query for [[Condicionpago]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCondicionpago()
    {
        return $this->hasOne(Condicionpago::class, ['id' => 'idCondicionPago']);
    }

    /**
     * Gets query for [[Proveedor]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getProveedor()
    {
        return $this->hasOne(Proveedor::class, ['id' => 'idProveedor']);
    }

    /**
     * Gets query for [[Tipodocumento]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getTipodocumento()
    {
        return $this->hasOne(Tipodocumento::class, ['id' => 'idTipoDocumento']);
    }

    /**
     * Gets query for [[Ordendecompratemporalitems]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getOrdendecompratemporalitems()
    {
        return $this->hasMany(Ordendecompratemporalitem::class, ['idOrdenCompra' => 'id']);
    }

    public static function generarexcelsiesa ($idordencompra){
        
        $archivo = Yii::getAlias('@app/web/archivos/Formato_PLANO_SIESA.xlsx'); // Ruta al archivo Excel
        
        $spreadsheet = IOFactory::load($archivo);

        $sheet = $spreadsheet->getSheetByName('Movimientos');
        $spreadsheet->setActiveSheetIndex(1);

        $ordendecompra = Ordendecompratemporal::findOne(['id' => $idordencompra]);  

        $modelitems = Ordendecompratemporalitem::find()
                                    ->where(['idOrdenCompra' => $idordencompra])
                                    ->all();

        $linea = 2;

        $valordocumento = 0;

        foreach($modelitems as $detalle){

            $fechaEntrega = str_replace('-', '', $detalle->fechaEntrega);

            $sheet->setCellValue('A'.$linea, $ordendecompra->co->codigo);
            $sheet->setCellValue('B'.$linea, $ordendecompra->tipodocumento->codigo);
            $sheet->setCellValue('C'.$linea, $detalle->numeroRegistro);
            $sheet->setCellValue('D'.$linea, $detalle->bodega->codigo);
            $sheet->setCellValue('E'.$linea, $detalle->codigoMotivo);
            $sheet->setCellValue('F'.$linea, $detalle->comovimiento->codigo);
            $sheet->setCellValue('G'.$linea, $detalle->cantidadPedida);
            $sheet->setCellValue('H'.$linea, $fechaEntrega);
            $sheet->setCellValue('I'.$linea, $detalle->precioUnitario);
            $sheet->setCellValue('J'.$linea, $detalle->item);
            $sheet->setCellValue('K'.$linea, $detalle->color->codigo);
            $sheet->setCellValue('L'.$linea, $detalle->talla->codigo);

            $linea = $linea + 1;
        }

        // Seleccionar la hoja por su nombre
        $sheet = $spreadsheet->getSheetByName('Documentos');
        $spreadsheet->setActiveSheetIndex(0);

        $fechaDocumento = str_replace('-', '', $ordendecompra->fechaDocumento);

        $sheet->setCellValue('A2', $ordendecompra->co->codigo);
        $sheet->setCellValue('B2', $ordendecompra->tipodocumento->codigo);
        $sheet->setCellValue('C2', $fechaDocumento);
        $sheet->setCellValue('D2', $ordendecompra->comprador->documento);
        $sheet->setCellValue('E2', $ordendecompra->proveedor->nit);
        $sheet->setCellValue('F2', $ordendecompra->sucursalProveedor);
        $sheet->setCellValue('G2', $ordendecompra->condicionpago->codigo);

        $nombreArchivo = "PLANO_SIESA_OC_" . 
                    $ordendecompra->proveedor->nit . "_" . $fechaDocumento . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $rutaGuardado = Yii::getAlias('@app/web/output/') . $nombreArchivo;
        
        // Guardar el archivo Excel
        $writer->save($rutaGuardado);

        return $rutaGuardado;
    }

    public static function generarexcelicg ($idordencompra){
        
        $archivo = Yii::getAlias('@app/web/archivos/Formato_PLANO_ICG.xlsx'); // Ruta al archivo Excel
        
        $spreadsheet = IOFactory::load($archivo);

        $sheet = $spreadsheet->getSheetByName('Movimientos');
        $spreadsheet->setActiveSheetIndex(0);

        $ordendecompra = Ordendecompratemporal::findOne(['id' => $idordencompra]);  
        $fechaDocumento = str_replace('-', '', $ordendecompra->fechaDocumento);

        $modelitems = Ordendecompratemporalitem::find()
                                    ->where(['idOrdenCompra' => $idordencompra])
                                    ->all();

        $linea = 2;


        foreach($modelitems as $detalle){
            $sheet->setCellValue('A'.$linea, $detalle->item);
            $sheet->setCellValue('B'.$linea, $detalle->talla->codigo);
            $sheet->setCellValue('C'.$linea, $detalle->color->codigo);
            $sheet->setCellValue('D'.$linea, $detalle->cantidadPedida);
            $sheet->setCellValue('E'.$linea, $detalle->precioUnitario);
            
            $linea = $linea + 1;
        }

        $nombreArchivo = "PLANO_ICG_OC_" . 
                    $ordendecompra->proveedor->nit . "_" . $fechaDocumento . '.xlsx';

        $writer = new Xlsx($spreadsheet);

        $rutaGuardado = Yii::getAlias('@app/web/output/') . $nombreArchivo;
        
        // Guardar el archivo Excel
        $writer->save($rutaGuardado);

        return $rutaGuardado;
    }
}
