<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = $name;
$this->params['breadcrumbs'] = [['label' => $this->title]];
?>
<div class="error-page">
    <div class="error-content" style="margin-left: auto;">
        <h3><i class="fas fa-exclamation-triangle text-danger"></i> <?= Html::encode($name) ?></h3>

        <p>
            <?php if ($exception->statusCode == 403): ?>
            <div class="d-flex flex-column align-items-center">
                <h1>Acceso Denegado</h1>
                <p>No tienes permiso para acceder a esta página. Si crees que esto es un error, contacta con el
                    administrador.</p>
                <a href="<?= Yii::$app->homeUrl ?>" class="btn btn-danger mt-3">Regresar al Inicio</a>
            </div>
        <?php else: ?>
            <?= nl2br(Html::encode($message)) ?>
        <?php endif; ?>
        </p>


        <p>
            El error anterior se produjo mientras el servidor web estaba procesando su solicitud.
            Póngase en contacto con nosotros si cree que se trata de un error del servidor. Gracias.
            Mientras tanto, puede <?= Html::a('regresar al menu principal', Yii::$app->homeUrl); ?>
            o intentar usar el formulario de búsqueda.
        </p>

        <form class="search-form" style="margin-right: 190px;">
            <div class="input-group">
                <input type="text" name="search" class="form-control" placeholder="Search">

                <div class="input-group-append">
                    <button type="submit" name="submit" class="btn btn-danger"><i class="fas fa-search"></i>
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>