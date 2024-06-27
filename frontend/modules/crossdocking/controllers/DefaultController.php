<?php

namespace frontend\modules\crossdocking\controllers;

use yii\web\Controller;

/**
 * Default controller for the `crossdocking` module
 */
class DefaultController extends Controller
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
