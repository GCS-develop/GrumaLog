<?php

namespace frontend\models;

use Yii;
use yii\db\Expression;

/**
 * Auditoría de eliminaciones de conteoentregamercancia.
 *
 * @property int    $id
 * @property int    $idProgramacionEntregaMercancia
 * @property int    $idAgendaEntregaMercancia
 * @property string $consecutivoOC
 * @property string $tipoDocumentoOC
 * @property string $codigoCO
 * @property string $accion
 * @property string $item
 * @property string $color
 * @property string $talla
 * @property int    $unidadesAntes
 * @property int    $unidadesBorradas
 * @property string $created_at
 * @property int    $created_by
 */
class Logborradoconteo extends \yii\db\ActiveRecord
{
    public static function tableName()
    {
        return 'logborradoconteo';
    }

    public function rules()
    {
        return [
            [['idProgramacionEntregaMercancia', 'idAgendaEntregaMercancia', 'accion'], 'required'],
            [['idProgramacionEntregaMercancia', 'idAgendaEntregaMercancia', 'unidadesAntes', 'unidadesBorradas', 'created_by'], 'integer'],
            [['accion'], 'string', 'max' => 50],
            [['item', 'consecutivoOC', 'tipoDocumentoOC', 'codigoCO'], 'string', 'max' => 50],
            [['color', 'talla'], 'string', 'max' => 50],
            [['created_at'], 'safe'],
        ];
    }

    public function attributeLabels()
    {
        return [
            'id'                             => 'ID',
            'idProgramacionEntregaMercancia' => 'Programación',
            'idAgendaEntregaMercancia'       => 'Agenda / Radicado',
            'consecutivoOC'                  => 'OC',
            'tipoDocumentoOC'                => 'Tipo Doc.',
            'codigoCO'                       => 'C.O.',
            'accion'                         => 'Acción',
            'item'                           => 'Item',
            'color'                          => 'Color',
            'talla'                          => 'Talla',
            'unidadesAntes'                  => 'Unidades Antes',
            'unidadesBorradas'               => 'Unidades Borradas',
            'created_at'                     => 'Fecha',
            'created_by'                     => 'Usuario',
        ];
    }

    public function getUser()
    {
        return $this->hasOne(\common\models\User::class, ['id' => 'created_by']);
    }

    /**
     * Registra en el log una acción de borrado/zereo de conteos.
     *
     * @param Programacionentregamercancia $programacion
     * @param string $accion   BORRAR_TODOS | REDUCIR_ITEM | REDUCIR_FILA | REDUCIR_TALLA | ELIMINAR_FILA
     * @param int    $unidadesAntes
     * @param int    $unidadesBorradas
     * @param string|null $item
     * @param string|null $color
     * @param string|null $talla
     */
    public static function registrar($programacion, $accion, $unidadesAntes, $unidadesBorradas, $item = null, $color = null, $talla = null)
    {
        $log = new self();
        $log->idProgramacionEntregaMercancia = $programacion->id;
        $log->idAgendaEntregaMercancia       = $programacion->idAgendaEntregaMercancia;
        $log->accion           = $accion;
        $log->item             = $item;
        $log->color            = $color;
        $log->talla            = $talla;
        $log->unidadesAntes    = (int) $unidadesAntes;
        $log->unidadesBorradas = (int) $unidadesBorradas;
        $log->created_by       = Yii::$app->user->id;
        $log->created_at       = new Expression('GETDATE()');

        // Datos OC para referencia rápida
        try {
            $oc = $programacion->agendaEntregaMercancia->ordenCompra;
            $log->consecutivoOC  = $oc->consecutivo;
            $log->tipoDocumentoOC = $oc->tipoDocumento ? $oc->tipoDocumento->codigo : null;
            $log->codigoCO        = $oc->cO ? $oc->cO->codigo : null;
        } catch (\Exception $e) {
            // Si no se pueden obtener los datos de OC, se guarda igual
        }

        $log->save(false);
    }
}
