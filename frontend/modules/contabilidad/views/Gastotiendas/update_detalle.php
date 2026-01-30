<?php
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use kartik\select2\Select2;
use yii\helpers\Url;

/** @var yii\web\View $this */
/** @var frontend\models\DocumentoGasto $doc */
/** @var frontend\models\MovimientoGasto[] $detalle */

$this->title = "Editar Detalles del Documento {$doc->F350_CONSEC_DOCTO}";
?>

<div class="container">
    <h2><?= Html::encode($this->title) ?></h2>

    <?php $form = ActiveForm::begin(); ?>

    <table class="table table-bordered">
        <thead>
            <tr>
                <th>Cuenta Auxiliar</th>
                <th>Valor</th>
                <th>Notas</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($detalle as $i => $row): ?>
                <tr>
                    <!-- Select2 solo con descripción -->
                    <td>
                        <?= Select2::widget([
                            'model' => $row,
                            'attribute' => "[$i]F351_ID_AUXILIAR",
                            'data' => \yii\helpers\ArrayHelper::map(
                                \frontend\models\GrupoConceptoCuenta::find()->all(),
                                'cuenta',
                                function ($cuenta) {
                                    return $cuenta->descripcion; // 👈 solo descripción
                                }
                            ),
                            'options' => [
                                'placeholder' => 'Seleccione una cuenta...',
                                'class' => 'select2-cuenta',
                                'data-index' => $i
                            ],
                            'pluginOptions' => ['allowClear' => true],
                        ]) ?>
                        <?= Html::activeHiddenInput($row, "[$i]naturaleza", [
                            'class' => 'naturaleza-field',
                            'value' => $row->naturaleza
                        ]) ?>
                    </td>

                    <!-- Campo Tercero oculto -->
                    <td style="display:none;">
                        <?= Html::activeHiddenInput($row, "[$i]F351_ID_TERCERO", [
                            'value' => $doc->F350_ID_TERCERO
                        ]) ?>
                    </td>

                    <!-- Valor editable -->
                    <td>
                        <?= Html::activeTextInput($row, "[$i]valor", [
                            'class' => 'form-control',
                            'value' => $row->F351_VALOR_DB > 0 ? $row->F351_VALOR_DB : $row->F351_VALOR_CR
                        ]) ?>
                    </td>

                    <!-- Notas -->
                    <td>
                        <?= Html::activeTextInput($row, "[$i]F351_NOTAS", ['class' => 'form-control']) ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        </tbody>
    </table>

    <div class="form-group">
        <?= Html::submitButton('💾 Guardar Cambios', ['class' => 'btn btn-success']) ?>
        <?= Html::a('← Cancelar', ['view', 'id' => $doc->ID_TRANSACCION], ['class' => 'btn btn-secondary']) ?>
    </div>

    <?php ActiveForm::end(); ?>
</div>

<?php
$naturalezaUrl = Url::to(['naturaleza-cuenta']);
$js = <<<JS
$('.select2-cuenta').on('change', function() {
    let cuenta = $(this).val();
    let rowIndex = $(this).data('index');
    let inputNaturaleza = $("input[name='MovimientoGasto[" + rowIndex + "][naturaleza]']");
    let inputValor = $("input[name='MovimientoGasto[" + rowIndex + "][valor]']");

    if (cuenta) {
        $.getJSON("{$naturalezaUrl}", {cuenta: cuenta}, function(data) {
            if (data.success) {
                inputNaturaleza.val(data.naturaleza);
                inputValor.val('');
            }
        });
    } else {
        inputNaturaleza.val('');
        inputValor.val('');
    }
});
JS;
$this->registerJs($js);
?>
