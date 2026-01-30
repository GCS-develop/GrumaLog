<?php

use yii\helpers\Html;
use yii\helpers\Url;
use yii\widgets\Pjax;
use kartik\grid\GridView;

/** @var \yii\data\ActiveDataProvider $dataProvider */
/** @var mixed $searchModel */
/** @var int|null $idtraspaso */
?>

<?php Pjax::begin([
    'id' => 'pjax-tda-agrupado-ajax',
    'timeout' => 8000,
    'enablePushState' => false,
]); ?>

<?= GridView::widget([
    'dataProvider' => $dataProvider,
    'condensed' => true,
    'hover' => true,
    'responsive' => true,
    'panel' => [
        'type' => 'primary',
        'heading' => 'Resumen por usuario creador',
    ],
    'columns' => [
        [
            'attribute' => 'idTraspaso',
            'label' => 'Traspaso',
            'format' => 'raw',
            'value' => function ($row) {
                $url = Url::to(['index', 'idtraspaso' => $row['idTraspaso']]);
                return Html::a('#' . (int)$row['idTraspaso'], $url);
            },
        ],
        [
            'attribute' => 'created_by',
            'label' => 'Usuario',
            'format' => 'text',
            'value' => function ($row) {
                if (class_exists(\common\models\User::class)) {
                    $u = \common\models\User::find()
                        ->select(['username'])
                        ->where(['id' => (int)$row['created_by']])
                        ->asArray()
                        ->one();
                    if ($u && !empty($u['username'])) {
                        return $u['username'] . " (id {$row['created_by']})";
                    }
                }
                return "id {$row['created_by']}";
            },
        ],
        [
            'attribute' => 'total_registros',
            'label' => 'Registros',
            'hAlign' => 'right',
            'value' => fn($row) => (int)$row['total_registros'],
        ],
        [
            'attribute' => 'total_unidades',
            'label' => 'Unidades',
            'hAlign' => 'right',
            'value' => fn($row) => (int)$row['total_unidades'],
        ],
        [
            'class' => 'kartik\grid\ActionColumn',
            'template' => '{ver} {borrar}',
            'buttons' => [
                'ver' => function ($url, $row) {
                    $url = Url::to(['index', 'idtraspaso' => $row['idTraspaso']]);
                    return Html::a('Ver detalle', $url, ['class' => 'btn btn-sm btn-outline-primary', 'data-pjax' => 0]);
                },
                'borrar' => function ($url, $row) {
                    $url = Url::to([
                        'delete-by-user',
                        'idtraspaso'    => $row['idTraspaso'],
                        'creadorUserId' => $row['created_by'],
                    ]);
                    return Html::a('Borrar aportes', $url, [
                        'class' => 'btn btn-sm btn-outline-danger',
                        'data'  => [
                            'method' => 'post',
                            'confirm' => "¿Seguro que quieres borrar lo auditado por el usuario {$row['created_by']} en el traspaso #{$row['idTraspaso']}? Se guardará historial.",
                            'pjax'   => '0',
                        ],
                    ]);
                },
            ],
        ],
    ],
]); ?>

<?php Pjax::end(); ?>
