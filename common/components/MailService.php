<?php

namespace common\components;

use Yii;
use yii\base\Component;

class MailService extends Component
{
    /** @var array|string|null Remitente por defecto. Ej: ['no-reply@tu-dominio.com' => 'GRUMALOG'] */
    public $defaultFrom = null;

    public function init()
    {
        parent::init();
        // Si no te pasan un "from", toma supportEmail de params como remitente por defecto.
        if ($this->defaultFrom === null) {
            $from = Yii::$app->params['supportEmail'] ?? null;
            $name = Yii::$app->name ?? 'GRUMALOG';
            if ($from) {
                $this->defaultFrom = [$from => $name];
            }
        }
    }

    /**
     * Enviar correo usando una vista (string o array ['html' => ..., 'text' => ...]).
     *
     * @param string|array $to        Destinatario(s)
     * @param string       $subject   Asunto
     * @param string|array|null $view Vista de mail (en @common/mail) o null para cuerpo “raw”
     * @param array        $params    Parámetros para la vista
     * @param array        $headers   Opcional: ['from'=>..., 'replyTo'=>..., 'cc'=>..., 'bcc'=>...]
     * @return bool
     */
    public function send($to, string $subject, $view = null, array $params = [], array $headers = []): bool
    {
        $message = Yii::$app->mailer->compose($view, $params);

        // Remitente (prioriza headers['from'] si viene)
        if (isset($headers['from'])) {
            $message->setFrom($headers['from']);
        } elseif ($this->defaultFrom) {
            $message->setFrom($this->defaultFrom);
        }

        if (isset($headers['replyTo'])) $message->setReplyTo($headers['replyTo']);
        if (isset($headers['cc']))      $message->setCc($headers['cc']);
        if (isset($headers['bcc']))     $message->setBcc($headers['bcc']);

        $message->setTo($to)->setSubject($subject);

        return $message->send();
    }

    /**
     * Alternativa: enviar sin vistas, pasando HTML y (opcional) texto plano.
     */
    public function sendRaw($to, string $subject, string $htmlBody, ?string $textBody = null, array $headers = []): bool
    {
        $message = Yii::$app->mailer->compose();

        if (isset($headers['from'])) {
            $message->setFrom($headers['from']);
        } elseif ($this->defaultFrom) {
            $message->setFrom($this->defaultFrom);
        }

        if (isset($headers['replyTo'])) $message->setReplyTo($headers['replyTo']);
        if (isset($headers['cc']))      $message->setCc($headers['cc']);
        if (isset($headers['bcc']))     $message->setBcc($headers['bcc']);

        $message->setTo($to)
            ->setSubject($subject)
            ->setHtmlBody($htmlBody);

        if ($textBody !== null) {
            $message->setTextBody($textBody);
        }

        return $message->send();
    }
}
