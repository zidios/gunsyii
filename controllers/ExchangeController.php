<?php

namespace app\controllers;

use Yii;
use yii\web\Controller;

class ExchangeController extends Controller
{
    public function actionIndex()
    {
        // Отключаем CSRF валидацию для этого действия
        $this->enableCsrfValidation = false;

        return Yii::$app->commerceML->handleRequest();
    }
}