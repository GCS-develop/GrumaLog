<?php

use yii\helpers\Html;
use yii\widgets\DetailView;
use yii2fullcalendar\yii2fullcalendar;

use kartik\icons\Icon;

Icon::map($this, Icon::FAS);

/** @var yii\web\View $this */
/** @var frontend\models\Agendaentregamercancia $model */

$this->title = 'Calendario';
$this->params['breadcrumbs'][] = ['label' => 'Agenda Entrega Mercancia', 'url' => [$action, 'id' => $model->id]];

$this->params['breadcrumbs'][] = $this->title;
\yii\web\YiiAsset::register($this);
?>
<div class="agendaentregamercancia-view">

    <div class="row">
        <div class="col-lg-12 centrar">
            <?= Html::a('Regresar', [$action, 'id' => $model->id], ['class' => 'btn btn-primary btn-lg btn-create']) ?>
        </div>
    </div>

    <?=
        \yii2fullcalendar\yii2fullcalendar::widget(
            array(
                'events' => $events,
                'clientOptions' => [
                    'lang' => 'es', // Configurar el idioma en español
                    //'defaultView' => 'month',
                    'dayNamesShort' => ['Dom', 'Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
                    'monthNames' => ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                    'buttonText' => [
                        'month' => 'Mes', // Cambiar el texto del botón "Month" a "Mes"
                        'week' => 'Semana', // Cambiar el texto del botón "Week" a "Semana"
                        'day' => 'Día', // Cambiar el texto del botón "Week" a "Semana"
                        'prev' => 'Anterior', // Cambiar el texto del botón "prev" a "Anterior"
                        'next' => 'Siguiente', // Cambiar el texto del botón "next" a "Siguiente"
                    ],
                    'header' => [
                        'left' => 'prev,next', // Mostrar solo los botones "prev" y "next"
                        'center' => 'title',
                        'right' => 'month,agendaWeek,agendaDay', // Mostrar las vistas "month", "agendaWeek" y "agendaDay"
                    ],
                    'eventRender' => new \yii\web\JsExpression('
                    function(event, element) {
                        var title = event.title;
                        if (title.length > 20) {
                            element.css("font-size", "10px"); // Cambiar el tamaño de fuente si el título es largo
                        }

                        if(event.estado.nombre.includes("No")){
                            element.css("color", "white");
                            element.css("background-color", "red");                       
                        }

                    }
                ')
                ],
            )
        );
    ?>

</div>