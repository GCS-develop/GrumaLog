<?php
use yii\helpers\Html;

/** @var array $jsonData */
/** @var string|null $respuesta */
/** @var int $descartadosEnvio */

$this->title = 'Resultado de Envío a Siesa';
?>
<h1 class="page-title"><?= Html::encode($this->title) ?></h1>

<?php if (!empty($descartadosEnvio) && $descartadosEnvio > 0): ?>
  <div class="alert alert-warning">
      <?= $descartadosEnvio ?> ítem(s) fueron descartados del envío porque tenían cantidad o costo negativo.
  </div>
<?php endif; ?>

<div class="panel panel-primary">
  <div class="panel-heading"><strong>Respuesta de Siesa</strong></div>
  <div class="panel-body">
    <?php if ($respuesta): ?>
      <pre style="background:#f8f9fa;padding:10px;border-radius:6px;
                  max-height:600px; overflow:auto;
                  white-space:pre-wrap; word-wrap:break-word;">
<?= is_string($respuesta)
      ? $respuesta
      : json_encode($respuesta, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
      </pre>
    <?php else: ?>
      <div class="alert alert-warning">No se recibió respuesta de Siesa.</div>
    <?php endif; ?>
  </div>
</div>

<div class="panel panel-default">
  <div class="panel-heading"><strong>JSON Enviado a Siesa</strong></div>
  <div class="panel-body">
    <pre style="background:#f8f9fa;padding:10px;border-radius:6px;
                max-height:600px; overflow:auto;
                white-space:pre-wrap; word-wrap:break-word;">
<?= json_encode($jsonData, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) ?>
    </pre>
  </div>
</div>

<p>
  <?= Html::a('← Volver a Consulta VMI', ['index','show'=>1], ['class'=>'btn btn-primary']) ?>
</p>
