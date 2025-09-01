<?php


namespace common\components;

use Yii;
use yii\base\Component;

class MailService extends Component
{
    public function send($to, $subject, $view, $params = [])
    {
        return Yii::$app->mailer->compose($view, $params)
            ->setFrom([Yii::$app->params['supportEmail'] => Yii::$app->name])
            ->setTo($to)
            ->setSubject($subject)
            ->send();
    }
}
