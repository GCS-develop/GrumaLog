<?php
use yii\helpers\Html;

$this->title = 'Documentos VMI';
?>
<h1><?= Html::encode($this->title) ?></h1>

<p>
  <?= Html::a('➕ Crear nuevo documento', ['nuevo'], ['class'=>'btn btn-success']) ?>
</p>

<table class="table table-bordered table-striped">
  <thead>
    <tr>
      <th>ID</th>
      <th>Proveedor</th>
      <th>Fechas</th>
      <th>Unidades</th>
      <th>Costo</th>
      <th>Estado</th>
      <th>Usuario</th>
      <th>Fecha realizado</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>
    <?php foreach ($logs as $log): ?>
      <tr>
        <td><?= Html::encode($log['id']) ?></td>
        <td><?= Html::encode($log['proveedor_id']) ?></td>
        <td><?= Html::encode($log['fecha_inicio']).' → '.Html::encode($log['fecha_fin']) ?></td>
        <td><?= Html::encode($log['total_unidades']) ?></td>
        <td>$ <?= Yii::$app->formatter->asDecimal($log['total_costo'], 2) ?></td>
        <td>
          <span class="label label-<?= $log['estado']==='enviado'?'success':($log['estado']==='error'?'danger':'warning') ?>">
            <?= Html::encode($log['estado']) ?>
          </span>
        </td>
        <td><?= Html::encode($log['usuario']) ?></td>
        <td><?= Html::encode($log['fecha_realizado']) ?></td>
        <td>
          <?= Html::a('👁 Ver', ['view-log','id'=>$log['consulta_id']], ['class'=>'btn btn-xs btn-primary']) ?>
          <?= Html::a('🔄 Reenviar', ['reenviar','id'=>$log['id']], [
                'class'=>'btn btn-xs btn-warning',
                'data-confirm'=>'¿Seguro que deseas reenviar este documento?',
                'data-method'=>'post'
            ]) ?>
        </td>
      </tr>
    <?php endforeach; ?>
  </tbody>
</table>
