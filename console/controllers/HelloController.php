<?php
namespace console\controllers;

use yii\console\Controller;

class HelloController extends Controller
{
    /**
     * Este comando muestra un mensaje de bienvenida.
     */
    public function actionIndex()
    {
        echo "Bienvenido a mi aplicación desde consola en Yii2 (Advanced)\n";
    }
}