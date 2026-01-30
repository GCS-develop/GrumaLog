<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use yii\helpers\Url;

/** @var \frontend\models\LogFactVmi $cab */
/** @var \frontend\models\LogFactVmiItem[] $items */
/** @var yii\base\DynamicModel $model */

$this->title = "Detalle del Documento VMI #".$cab->id;
?>

<h1><?= Html::encode($this->title) ?></h1>

<!-- Encabezado -->
<div class="panel panel-primary">
  <div class="panel-heading"><strong>Encabezado del Documento</strong></div>
  <div class="panel-body">
    <table class="table table-bordered">
      <tr><th>Proveedor</th><td><?= Html::encode($cab->proveedor_id) ?></td></tr>
      <tr><th>Rango</th><td><?= Html::encode($cab->fecha_inicio)." → ".Html::encode($cab->fecha_fin) ?></td></tr>
      <tr><th>Estado</th>
        <td>
          <span class="label label-<?= $cab->estado==='enviado'?'success':($cab->estado==='error'?'danger':'warning') ?>">
            <?= Html::encode($cab->estado) ?>
          </span>
        </td>
      </tr>
      <tr><th>Usuario</th><td><?= Html::encode($cab->usuario) ?></td></tr>
      <tr><th>Fecha realizado</th><td><?= Html::encode($cab->fecha_realizado) ?></td></tr>
      <tr><th>Total unidades</th><td><?= Html::encode($cab->total_unidades) ?></td></tr>
      <tr><th>Total costo</th><td>$ <?= Yii::$app->formatter->asDecimal($cab->total_costo, 2) ?></td></tr>
      <tr>
        <th>Respuesta Siesa</th>
        <td>
            <?php
            $msg = $cab->error_msg ?: 'Sin errores';
            $isError = !empty($cab->error_msg);
            ?>
            <pre class="<?= $isError ? 'text-danger' : 'text-success' ?>" 
                 style="margin:0; white-space:pre-wrap; word-wrap:break-word;  max-height: 300px; overflow-y: auto;">
                <?= Html::encode($msg) ?>
            </pre>
        </td>
      </tr>
    </table>
  </div>
</div>

<!-- 🔹 Formulario para corregir y reenviar -->
<div class="panel panel-warning">
  <div class="panel-heading"><strong>Editar y Reenviar a Siesa</strong></div>
  <div class="panel-body">
    <?php $form = ActiveForm::begin(['id'=>'reenviar-form']); ?>
      <?= $form->field($model, 'consec_doc_prov')->textInput(['type'=>'number']) ?>
      <?= $form->field($model, 'prefijo_doc_prov')->textInput() ?>
      <?= $form->field($model, 'fecha_doc')->input('date') ?>

      <div class="form-group">
        <?= Html::button('Reenviar a Siesa', [
            'class'=>'btn btn-primary',
            'id'=>'btnReenviar'
        ]) ?>
        <?= Html::a('Volver a la lista', ['index-log'], ['class'=>'btn btn-default']) ?>
      </div>

      <?php if ($cab->estado === 'error'): ?>
      <div class="form-group">
        <?= Html::a('Ajustar existencia', 
            ['conciliacion', 'id' => $cab->id], 
            ['class' => 'btn btn-warning',
             'data-confirm' => '¿Seguro que deseas ajustar las bodegas y reenviar este documento?']) ?>
      </div>
      <?php endif; ?>

    <?php ActiveForm::end(); ?>
  </div>
</div>

<!-- Detalle movimientos -->
<div class="panel panel-default">
  <div class="panel-heading"><strong>Detalle de Movimientos</strong></div>
  <div class="panel-body">
    <table class="table table-striped table-condensed">
      <thead>
        <tr>
          <th>N° Línea</th>
          <th>Item</th>
          <th>Ext1</th>
          <th>Ext2</th>
          <th>Bodega</th>
          <th class="text-right">Cantidad</th>
          <th class="text-right">Precio Unitario</th>
          <th class="text-right">Costo Total</th>
        </tr>
      </thead>
      <tbody>
        <?php if (!empty($items)): ?>
          <?php 
            $i = 1; 
            $totalCant = 0;
            $totalCosto = 0;
          ?>
          <?php foreach ($items as $it): ?>
            <?php 
              $totalCant  += (int)$it->cantidad;
              $totalCosto += (float)$it->costo_total;
            ?>
            <tr>
              <td><?= $i++ ?></td>
              <td><?= Html::encode($it->item) ?></td>
              <td><?= Html::encode($it->extension1) ?></td>
              <td><?= Html::encode($it->extension2) ?></td>
              <td><?= Html::encode($it->bodega) ?></td>
              <td class="text-right"><?= Yii::$app->formatter->asDecimal($it->cantidad, 0) ?></td>
              <td class="text-right"><?= Yii::$app->formatter->asDecimal($it->precio_unitario, 2) ?></td>
              <td class="text-right"><?= Yii::$app->formatter->asDecimal($it->costo_total, 2) ?></td>
            </tr>
          <?php endforeach; ?>
        <?php else: ?>
          <tr><td colspan="8" class="text-center text-muted">No hay ítems registrados</td></tr>
        <?php endif; ?>
      </tbody>

      <?php if (!empty($items)): ?>
      <tfoot>
        <tr style="font-weight:bold; background:#f5f5f5;">
          <td colspan="5" class="text-right">TOTAL</td>
          <td class="text-right"><?= Yii::$app->formatter->asDecimal($totalCant, 0) ?></td>
          <td></td>
          <td class="text-right"><?= Yii::$app->formatter->asDecimal($totalCosto, 2) ?></td>
        </tr>
      </tfoot>
      <?php endif; ?>
    </table>
  </div>
</div>


<?php
$reenviarUrl = Url::to(['reenviar', 'id' => $cab->id]);
$redirectUrl = Url::to(['index-log']);
$csrf = Yii::$app->request->getCsrfToken();
$js = <<<JS
jQuery('#btnReenviar').on('click', function(e){
    e.preventDefault();
    var btn = jQuery(this);
    btn.prop('disabled', true).text('Procesando...');

    var data = jQuery('#reenviar-form').serialize();

    fetch('$reenviarUrl', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded; charset=UTF-8',
            'X-CSRF-Token': '$csrf'
        },
        body: data
    }).then(r => r.json())
      .then(res => {
          console.log('Respuesta reenviar:', res);
          alert(res.success ? 'Documento reenviado correctamente' : 'Error al reenviar documento: ' + (res.mensaje || ''));
          window.location.href = '$redirectUrl';
      })
      .catch(err => {
          console.error(err);
          alert('Error inesperado al reenviar.');
          window.location.href = '$redirectUrl';
      })
      .finally(() => {
          btn.prop('disabled', false).text('Reenviar a Siesa');
      });
});
JS;
$this->registerJs($js);
?>
