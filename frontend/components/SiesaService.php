<?php

namespace frontend\components;

use Yii;
use frontend\models\Comprasimportaciondetalle;
use frontend\models\Conectoresdinamicos;

/**
 * SiesaService — construye el JSON para ORDEN DE COMPRA GRUMA
 * y lo envía al conector dinámico de SIESA.
 *
 * Soporta:
 *  - URL global de params['endpoints']['service']['urlConector']
 *  - URL específica por conector: conectoresdinamicos.urlConector
 *  - Param de sistema: 'idInterface' (legacy) o 'idSistema' (v3.1)
 *  - Credenciales específicas por conector (anulan las globales)
 */
class SiesaService
{
    const ID_DOCUMENTO      = 237632;
    const CENTRO_OPERACION  = '002';
    const MOTIVO            = '01';
    const SECCION_DOCUMENTO = 'Documentos';
    const SECCION_MOVIMIENTO = 'Movimientos';

    /**
     * @param int   $idImportacion
     * @param array $params  tipoDocumento, fechaDocumento, terceroComprador,
     *                       terceroProveedor, sucursalProveedor, condicionPago, bodega
     * @return array ['ok'=>bool, 'message'=>string, 'response'=>mixed]
     */
    public static function enviar(int $idImportacion, array $params): array
    {
        $conector = Conectoresdinamicos::findOne(['idDocumento' => self::ID_DOCUMENTO]);
        if (!$conector) {
            return ['ok' => false, 'message' => 'Conector SIESA no configurado (idDocumento=' . self::ID_DOCUMENTO . ').', 'response' => null];
        }

        $json = self::buildJson($idImportacion, $params);
        if ($json === null) {
            return ['ok' => false, 'message' => 'No hay registros para enviar.', 'tipoDoc' => null, 'numDoc' => null, 'jsonEnviado' => null, 'response' => null];
        }

        $resultado = self::postSiesa($json, $conector);
        $resultado['jsonEnviado'] = json_encode($json, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        return $resultado;
    }

    // ----------------------------------------------------------
    // Construcción del JSON
    // ----------------------------------------------------------
    public static function buildJson(int $idImportacion, array $p): ?array
    {
        $rows = Comprasimportaciondetalle::find()
            ->select(['codigo', 'color', 'talla', 'mes', 'costo', 'SUM(uds) AS totalUds'])
            ->where(['idImportacion' => $idImportacion])
            ->groupBy(['codigo', 'color', 'talla', 'mes', 'costo'])
            ->orderBy(['codigo' => SORT_ASC])
            ->asArray()
            ->all();

        if (empty($rows)) return null;

        $documento = [
            'Centro de operación'    => self::CENTRO_OPERACION,
            'Tipo de documento'      => $p['tipoDocumento'],
            'Fecha del documento'    => $p['fechaDocumento'],
            'Tercero comprador'      => $p['terceroComprador'],
            'Tercero proveedor'      => $p['terceroProveedor'],
            'Sucursal del proveedor' => $p['sucursalProveedor'],
            'Condición de pago'      => $p['condicionPago'],
        ];

        $movimientos = [];
        $reg = 1;
        foreach ($rows as $row) {
            $movimientos[] = [
                'Centro de operación'            => self::CENTRO_OPERACION,
                'Tipo de documento'              => $p['tipoDocumento'],
                'Numero de registro 10'          => str_pad($reg, 10, '0', STR_PAD_LEFT),
                'Bodega'                         => $p['bodega'],
                'Motivo'                         => self::MOTIVO,
                'Centro de operación movimiento' => self::CENTRO_OPERACION,
                'Cantidad pedida'                => (int)($row['totalUds'] ?? 0),
                'Fecha de entrega'               => self::parseFecha($row['mes']),
                'Precio unitario'                => (float)($row['costo'] ?? 0),
                'ITEM'                           => $row['codigo'],
                'COLOR'                          => $row['color'] ?? '',
                'TALLA'                          => $row['talla'] ?? '',
            ];
            $reg++;
        }

        return [self::SECCION_DOCUMENTO => [$documento], self::SECCION_MOVIMIENTO => $movimientos];
    }

    // ----------------------------------------------------------
    // POST al conector SIESA
    // Usa URL y credenciales del conector si están, sino las globales
    // ----------------------------------------------------------
    private static function postSiesa(array $body, $conector): array
    {
        $baseUrl    = (string)$conector->urlConector;
        $paramName  = !empty($conector->paramSistema) ? (string)$conector->paramSistema : 'idInterface';
        $conniKey   = (string)$conector->conniKey;
        $conniToken = (string)$conector->conniToken;
        $idCompania = (string)$conector->idCompania;
        $idInterface = (string)$conector->idInterface;
        $idDocumento = (string)$conector->idDocumento;
        $nombreSIESA = (string)$conector->nombreSIESA;

        $url = $baseUrl
            . '?idCompania='        . $idCompania
            . '&' . $paramName . '=' . $idInterface
            . '&idDocumento='       . $idDocumento
            . '&nombreDocumento='   . urlencode($nombreSIESA);

        if ($paramName === 'idInterface') {
            $url .= '&validarEstructura=false';
        }

        Yii::info("SIESA URL: $url | key_len=" . strlen($conniKey) . " token_len=" . strlen($conniToken), __METHOD__);

        try {
            $ch = curl_init($url);
            curl_setopt_array($ch, [
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_POST           => true,
                CURLOPT_POSTFIELDS     => json_encode($body, JSON_UNESCAPED_UNICODE),
                CURLOPT_HTTPHEADER     => [
                    'Content-Type: application/json',
                    'ConniKey: '   . $conniKey,
                    'ConniToken: ' . $conniToken,
                ],
                CURLOPT_TIMEOUT        => 30,
                CURLOPT_SSL_VERIFYPEER => false,
            ]);
            $raw      = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $curlErr  = curl_error($ch);
            curl_close($ch);

            if ($raw === false) {
                throw new \Exception($curlErr ?: 'curl_exec failed');
            }

            $decoded  = json_decode($raw, true);

            Yii::error("SIESA HTTP $httpCode raw: $raw", __METHOD__);

            $flatStr = function($v) {
                if (is_array($v)) return json_encode($v, JSON_UNESCAPED_UNICODE);
                return (string)$v;
            };

            // Guardar respuesta cruda para depuración
            file_put_contents(sys_get_temp_dir() . '/siesa_last_response.json', $raw);

            $codigo = isset($decoded['codigo']) ? (int)$decoded['codigo'] : -1;

            if ($httpCode >= 200 && $httpCode < 300 && $codigo === 0) {
                // Consultar consecutivo creado en SIESA ERP (t420_cm_oc_docto)
                $tipoDoc = '2CA';
                $numDoc  = null;
                try {
                    $docSiesa = \Yii::$app->dbSiesa->createCommand(
                        "SELECT TOP 1 f420_id_tipo_docto, f420_consec_docto
                         FROM t420_cm_oc_docto
                         WHERE f420_id_tipo_docto = :tipo
                           AND f420_fecha_ts_creacion >= DATEADD(minute, -10, GETDATE())
                         ORDER BY f420_consec_docto DESC"
                    )->bindValue(':tipo', $tipoDoc)->queryOne();
                    if ($docSiesa) {
                        $tipoDoc = (string)$docSiesa['f420_id_tipo_docto'];
                        $numDoc  = (string)$docSiesa['f420_consec_docto'];
                    }
                } catch (\Exception $ex) {
                    \Yii::warning('No se pudo obtener consecutivo SIESA: ' . $ex->getMessage(), __METHOD__);
                }
                return [
                    'ok'       => true,
                    'message'  => 'Enviado correctamente a SIESA.',
                    'tipoDoc'  => $tipoDoc,
                    'numDoc'   => $numDoc,
                    'response' => $decoded,
                ];
            }

            if ($httpCode >= 200 && $httpCode < 300 && isset($decoded['errors']) && !empty($decoded['errors'])) {
                $errMsg = is_array($decoded['errors'])
                    ? implode(' | ', array_map($flatStr, $decoded['errors']))
                    : (string)$decoded['errors'];
                return ['ok' => false, 'message' => $errMsg, 'tipoDoc' => null, 'numDoc' => null, 'response' => $decoded];
            }

            // Extraer mensaje de error de SIESA
            if (!empty($decoded['errors']) && is_array($decoded['errors'])) {
                $msg = implode(' | ', array_map($flatStr, $decoded['errors']));
            } else {
                $raw_msg = $decoded['detalle'] ?? $decoded['mensaje'] ?? $decoded['message'] ?? $decoded['error'] ?? "Error HTTP $httpCode.";
                $msg = is_array($raw_msg) ? implode(' | ', array_map($flatStr, $raw_msg)) : (string)$raw_msg;
            }
            return ['ok' => false, 'message' => $msg, 'tipoDoc' => null, 'numDoc' => null, 'response' => $decoded];

        } catch (\Exception $e) {
            Yii::error('SiesaService: ' . $e->getMessage() . ' @ ' . $e->getFile() . ':' . $e->getLine(), __METHOD__);
            return ['ok' => false, 'message' => 'Error de conexión: ' . $e->getMessage(), 'tipoDoc' => null, 'numDoc' => null, 'response' => null];
        }
    }

    private static function parseFecha($valor): string
    {
        if (empty($valor)) return '';
        if (preg_match('/^\d{8}$/', (string)$valor)) return (string)$valor;
        if (is_numeric($valor) && $valor > 40000) {
            return date('Ymd', \PhpOffice\PhpSpreadsheet\Shared\Date::excelToTimestamp((float)$valor));
        }
        $ts = strtotime((string)$valor);
        return $ts !== false ? date('Ymd', $ts) : (string)$valor;
    }
}
