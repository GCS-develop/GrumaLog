<?php

$this->registerCss('
    .mi-gridview {
        font-size: 10px; /* Ajusta el tamaño de la fuente según sea necesario */
        /* Otros estilos CSS según sea necesario */
    }

	.btn-create {
        width: 300px;
    }    
');

use common\models\ProveedoresWs;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\grid\ActionColumn;
use kartik\grid\GridView;

use common\widgets\Alert;

/** @var yii\web\View $this */
/** @var common\models\search\ProveedoresWsSearch $searchModel */
/** @var yii\data\ActiveDataProvider $dataProvider */

$this->title = 'Proveedores - ERP';
$this->params['breadcrumbs'][] = $this->title;

$nit = null;
if (isset($params["ProveedoresWsSearch"]["Nit"])) {
    $nit = $params["ProveedoresWsSearch"]["Nit"];
 }
?>
<div class="proveedoresws-index">

    <?= Alert::widget() ?>

    <?php echo $this->render('_search', ['model' => $searchModel]); ?>

    <p align="center">
        <?= Html::a('Sincronizar ERP', ['sincronizarerp', 'nit' => $nit], ['class' => 'btn btn-success btn-lg btn-create']) ?>
        &nbsp;
        <?= Html::a('Sincronizar Todos desde SIESA', ['sincronizarsiesa'], [
            'class' => 'btn btn-primary btn-lg btn-create',
            'data-confirm' => '¿Sincronizar TODOS los proveedores desde SIESA DB? Esto puede tardar unos segundos.',
            'data-method' => 'get',
        ]) ?>
    </p>

    <?= GridView::widget([
        'dataProvider' => $dataProvider,
        //'filterModel' => $searchModel,
		
		'summary' => 'Mostrando {begin} - {end} de {totalCount} resultados',
		'formatter' => ['class' => 'yii\i18n\Formatter', 'nullDisplay' => '-'],
		'options' => [
			'class' => 'mi-gridview', // Agrega una clase CSS a la tabla generada por el GridView
		],
		
        'columns' => [
            ['class' => 'yii\grid\SerialColumn'],

            'Id',
            'Nit',
            'Razon_Social', 
            'Tipo_Identificacion', 
            'Sucursal', 
            'Descripcion_Sucursal',
            'Contacto', 
            'Direccion', 
            'Pais', 
            'Ciudad', 
            'Departamento', 
            'Telefono', 
            'Email',    
            'Celular', 
            'CriterioMercancia',
            'CriterioModeloLogistico',
            /*[
                'class' => ActionColumn::className(),
                'urlCreator' => function ($action, ProveedoresWs $model, $key, $index, $column) {
                    return Url::toRoute([$action, 'Id' => $model->Id]);
                 }
            ],*/
        ],
    ]); 
	?>


</div>
