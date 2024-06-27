<?php

?>

<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>"/>
    <title><?= \yii\helpers\Html::encode($this->title) ?></title>
    <?= \yii\helpers\Html::csrfMetaTags() ?>
    <?php $this->head() ?>
</head>
<body>
    <?= $content ?>
</body>
</html>
