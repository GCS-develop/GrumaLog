<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace common\models;

use Yii;
use yii\helpers\ArrayHelper;
use yii\base\ErrorException;
use PHPExcel;

use common\models\User;

/**
 * Description of ProcedimientosGenerales
 *
 * @author Gustavo
 */
class ProcedimientosGenerales {
    
    /**
     * 
     * @param type $Nit
     * @return boolean
     * Tomado de la pagina http://www.forosdelweb.com/f18/digito-verificacion-colombia-938744/
     */
    public static function calcularDigitoVerificacion ($Nit){
        
        $dv = '';
        $Ok = 0;
        
        if (is_string($Nit)){
            if (is_numeric($Nit)){
                $Ok = 1;
            }
        }else{
            if (is_numeric($Nit)){
                $Ok = 1;
            }
        }
        
        if ($Ok == 1){
            $arr = array(1 => 3, 4 => 17, 7 => 29, 10 => 43, 13 => 59, 2 => 7, 5 => 19, 
                            8 => 37, 11 => 47, 14 => 67, 3 => 13, 6 => 23, 9 => 41, 12 => 53, 15 => 71);
    
            $x = 0;
            $y = 0;
            $z = strlen($Nit);
            $dv = '';

            for ($i=0; $i<$z; $i++) {
                $y = substr($Nit, $i, 1);
                $x += ($y*$arr[$z-$i]);
            }

            $y = $x%11;

            if ($y > 1) {
                $dv = 11-$y;
                return $dv;
            } else {
                $dv = $y;
                return $dv;
            }
        }

        return $dv;
    }
    
    function minutos_transcurridos($fecha_i,$fecha_f)
    {
        $minutos = (strtotime($fecha_i)-strtotime($fecha_f))/60;
        $minutos = abs($minutos); $minutos = floor($minutos);
        return $minutos;
    }
    
    public static function diferenciaFechas ($fechainicio, $fechafinal) {
        $fecha1 = new \DateTime($fechainicio);
        $fecha2 = new \DateTime($fechafinal);
        $fecha = $fecha1->diff($fecha2);
        
        if (($fecha->m == 11) && ($fecha->d >=30)){
            $fecha->y = $fecha->y + 1;
            $fecha->m = 0;
            $fecha->d = 0;
        }
        $tiempo = $fecha->y . ' años ' . $fecha->m . ' meses ' . $fecha->d . ' días';
        //printf('%d años, %d meses, %d días, %d horas, %d minutos', $fecha->y, $fecha->m, $fecha->d, $fecha->h, $fecha->i);        
        
        return $tiempo;
    }

    public static function obtenerPrimerUltimoDiaDelMes($anio, $mes) {
        // Obtener el número de días en el mes
        $numDias = cal_days_in_month(CAL_GREGORIAN, $mes, $anio);

        // Crear un objeto DateTime para el primer día del mes
        $primerDia = new \DateTime("$anio-$mes-01");

        // Crear un objeto DateTime para el último día del mes
        $ultimoDia = new \DateTime("$anio-$mes-$numDias");

        // Devolver un array con el primer y último día del mes
        return [
            'primerDia' => $primerDia->format('Y-m-d'),
            'ultimoDia' => $ultimoDia->format('Y-m-d')
        ];
    }
    
    public static function erroresModelo ($arrayErrors){
        
        $message = '';
        foreach ($arrayErrors as $name => $error){
            
            if (!is_array($error)) {
                continue;
            }
            $message .= $name . ': ';
            foreach ($error as $e) {
                $message .= $e . '; ';
            }
        }
        return $message;        
    }

    public static function array_union($x, $y)
    { 
        $aunion=  array_merge(
            array_intersect($x, $y),
            array_diff($x, $y),     
            array_diff($y, $x)      
        );
        
        return $aunion;
    }
    
    public static function sendEmail($layout, $email, $subject, $body, $emailfrom, $emailfromname)
    {
        return Yii::$app->mailer->compose($layout, 
                                    [   'content' => $body
                                    ])
            ->setTo($email)
            ->setFrom([$emailfrom => $emailfromname])
            ->setReplyTo([$emailfrom => $emailfromname])
            ->setSubject($subject)
            //->setHtmlBody($body)
            ->send();
    }

    public static function meses (){
        $listameses = [
            "1" => 'Enero',
            "2" => 'Febrero',
            "3" => 'Marzo',
            "4" => 'Abril',
            "5" => 'Mayo',
            "6" => 'Junio',
            "7" => 'Julio',
            "8" => 'Agosto',
            "9" => 'Septiembre',
            "10" => 'Octubre',
            "11" => 'Noviembre',
            "12" => 'Diciembre'
        ];
        
        return $listameses;
    }

    public static function nombreMes ($mes){
        $nombre = '';
        switch($mes){
            case 1: $nombre = 'Enero'; break;
            case 2: $nombre = 'Febrero'; break;
            case 3: $nombre = 'Marzo'; break;
            case 4: $nombre = 'Abril'; break;
            case 5: $nombre = 'Mayo'; break;
            case 6: $nombre = 'Junio'; break;
            case 7: $nombre = 'Julio'; break;
            case 8: $nombre = 'Agosto'; break;
            case 9: $nombre = 'Septiembre'; break;
            case 10: $nombre = 'Octubre'; break;
            case 11: $nombre = 'Noviembre'; break;
            case 12: $nombre = 'Diciembre'; break;
        }
        return $nombre;
    }

    public static function diassemana (){
        $listadiassemana = [
            "Domingo" => 'Domingo',
            "Lunes" => 'Lunes',
            "Martes" => 'Martes',
            "Miercoles" => 'Miércoles',
            "Jueves" => 'Jueves',
            "Viernes" => 'Viernes',
            "Sabado" => 'Sábado',
        ];
        
        return $listadiassemana;
    }

    public static function listaAnios (){
        $numero = 0;
        $currentMonth = date('m');
        if ($currentMonth == 12){
            $numero = 1;
        }

        $currentYear = date('Y');
        $yearsList = range($currentYear, $currentYear + $numero);
        $yearsList = array_combine($yearsList, $yearsList);

        return $yearsList;
    }

    public static function valorSINO ($valor){
        $resp = ' - ';

        if (!is_null($valor)) {
            $resp = 'NO';
            if ($valor == 1){
                $resp = 'SI';
            }
        }

        return $resp;
    }

    public static function nombreCarpetaUpload ($modulo, $tipoarchivo, $nombre){
        $barra = '\\';
        if (PHP_OS == 'Linux'){
            $barra = '//';    
        }

        $ruta_dctos = \Yii::$app->params['rutadctos_upload_WIN'];     

        $ruta = $ruta_dctos . $barra . $modulo . $barra . $tipoarchivo . $barra . $nombre; 

        return $ruta;
    }

    public static function convertirValorTextoNumero ($valor){

        $valorTransformado = null;
        $esNegativo = false;
    
        if ($valor != null){
            $valor = str_replace(',', '', $valor);  # Eliminar la coma
    
            $ultimoCaracter = substr($valor, -1);
            if ($ultimoCaracter === '-') {
                $valor = str_replace('-', '', $valor);  # Eliminar el signo "-"
                $esNegativo = true;
            }
    
            try{
                $valorTransformado = floatval($valor);  # Convertir la cadena a un número flotante
                if ($esNegativo){
                    $valorTransformado = $valorTransformado * -1;
                }
            } catch (Exception $e) {
                #print("La cadena no es un número válido")
                $valorTransformado = null;
            }
        }
    
        return $valorTransformado;        
    }

}