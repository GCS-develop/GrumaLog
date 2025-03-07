<?php

namespace frontend\modules\ventas\controllers;

use yii\web\Controller;

/**
 * Default controller for the `ventas` module
 */
class AnalisisventaController extends Controller
{
    /**
     * Renders the index view for the module
     * @return string
     */
    public function actionIndex()
    {
        return $this->render('index');
    }
}
