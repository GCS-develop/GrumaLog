<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\grid\GridView;

/** @var yii\web\View $this */
/** @var frontend\models\search\GrumascanRankingOperariosSearch $searchModel */
/** @var yii\data\SqlDataProvider $dataProvider */
/** @var array $chartLabels */
/** @var array $chartUnidades */
/** @var array $chartPaquetes */

$this->title = 'Ranking Operarios (Terminados)';
?>

<div class="ranking-operarios">

    <div class="card mb-3">
        <div class="card-body">

            <?php $form = ActiveForm::begin(['method' => 'get']); ?>

            <div class="row">
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'desde')->input('date') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'hasta')->input('date') ?>
                </div>
                <div class="col-md-3">
                    <?= $form->field($searchModel, 'bodega')->textInput() ?>
                </div>
                <div class="col-md-3" style="margin-top:25px">
                    <?= Html::submitButton('Filtrar', ['class' => 'btn btn-primary w-100']) ?>
                </div>
            </div>

            <?php ActiveForm::end(); ?>

        </div>
    </div>

    <!-- GRAFICA -->
    <div class="card mb-3">
        <div class="card-body">

            <div style="height:400px;">
                <canvas id="rankingChart"></canvas>
            </div>

        </div>
    </div>

    <!-- GRID -->
    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        'panel' => [
            'type' => 'primary',
            'heading' => 'Ranking por Operario',
        ],
        'columns' => [
            ['class' => 'kartik\grid\SerialColumn'],
            'username',
            [
                'attribute' => 'unidades_reales',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'paquetes',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'conteos',
                'hAlign' => 'right',
            ],
            [
                'attribute' => 'velocidad_u_h',
                'format' => ['decimal', 2],
                'hAlign' => 'right',
            ],
        ],
    ]); ?>

</div>

<?php
$this->registerJsFile('@web/js/chart.umd.min.js', ['position' => \yii\web\View::POS_END]);

$labels   = json_encode($chartLabels);
$unidades = json_encode($chartUnidades);

$js = <<<JS

document.addEventListener("DOMContentLoaded", function(){

    const labels = $labels;
    const dataValues = $unidades.map(Number);

    const canvas = document.getElementById("rankingChart");
    if (!canvas || typeof Chart === "undefined") return;

    const valuePlugin = {
        id: 'valuePlugin',
        afterDatasetsDraw(chart) {
            const {ctx} = chart;
            ctx.save();
            ctx.font = '12px Arial';
            ctx.fillStyle = '#000';
            ctx.textAlign = 'center';

            chart.data.datasets.forEach((dataset, i) => {
                const meta = chart.getDatasetMeta(i);
                meta.data.forEach((bar, index) => {
                    const value = dataset.data[index];
                    if (value > 0) {
                        ctx.fillText(
                            value.toLocaleString('es-CO'),
                            bar.x,
                            bar.y - 5
                        );
                    }
                });
            });

            ctx.restore();
        }
    };

    new Chart(canvas, {
        type: 'bar',
        data: {
            labels: labels,
            datasets: [{
                label: 'Unidades reales',
                data: dataValues,
                backgroundColor: '#2E86DE'
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true
                }
            }
        },
        plugins: [valuePlugin]
    });

});

JS;

$this->registerJs($js, \yii\web\View::POS_END);
?>