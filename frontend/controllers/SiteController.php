<?php

namespace frontend\controllers;

use frontend\models\ResendVerificationEmailForm;
use frontend\models\VerifyEmailForm;
use Yii;
use yii\base\InvalidArgumentException;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use common\models\LoginForm;
use frontend\models\PasswordResetRequestForm;
use frontend\models\ResetPasswordForm;
use frontend\models\SignupForm;
use frontend\models\ContactForm;
use yii\web\Response;
use yii\widgets\ActiveForm;


/**
 * Site controller
 */
class SiteController extends Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'only' => ['logout', 'signup'],
                'rules' => [
                    [
                        'actions' => ['signup'],
                        'allow' => true,
                        'roles' => ['?'],
                    ],
                    [
                        'actions' => ['logout'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'logout' => ['post'],
                ],
            ],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function actions()
    {
        return [
            'error' => [
                'class' => \yii\web\ErrorAction::class,
            ],
            'captcha' => [
                'class' => \yii\captcha\CaptchaAction::class,
                'fixedVerifyCode' => YII_ENV_TEST ? 'testme' : null,
            ],
        ];
    }

    /**
     * Displays homepage.
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return $this->render('index');
    }

    /**
     * Logs in a user.
     *
     * @return mixed
     */
    public function actionLogin()
    {
        if (!Yii::$app->user->isGuest) {
            return $this->goHome();
        }

        $model = new LoginForm();
        if ($model->load(Yii::$app->request->post()) && $model->login()) {
            return $this->goBack();
        }

        $model->password = '';

        return $this->render('login', [
            'model' => $model,
        ]);
    }

    /**
     * Logs out the current user.
     *
     * @return mixed
     */
    public function actionLogout()
    {
        Yii::$app->user->logout();

        return $this->goHome();
    }

    /**
     * Displays contact page.
     *
     * @return mixed
     */
    public function actionContact()
    {
        $model = new ContactForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail(Yii::$app->params['adminEmail'])) {
                Yii::$app->session->setFlash('success', 'Thank you for contacting us. We will respond to you as soon as possible.');
            } else {
                Yii::$app->session->setFlash('error', 'There was an error sending your message.');
            }

            return $this->refresh();
        }

        return $this->render('contact', [
            'model' => $model,
        ]);
    }

    /**
     * Displays about page.
     *
     * @return mixed
     */
    public function actionAbout()
    {
        return $this->render('about');
    }

    /**
     * Signs user up.
     *
     * @return mixed
     */
    public function actionSignup()
    {
        $model = new SignupForm();
        if ($model->load(Yii::$app->request->post()) && $model->signup()) {
            Yii::$app->session->setFlash('success', 'Thank you for registration. Please check your inbox for verification email.');
            return $this->goHome();
        }

        return $this->render('signup', [
            'model' => $model,
        ]);
    }

    /**
     * Requests password reset.
     *
     * @return mixed
     */
    // public function actionRequestPasswordReset()
    // {
    //     $model = new PasswordResetRequestForm();
    //     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    //         if ($model->sendEmail()) {
    //             Yii::$app->session->setFlash('success', 'Check your email for further instructions.');

    //             return $this->goHome();
    //         }

    //         Yii::$app->session->setFlash('error', 'Sorry, we are unable to reset password for the provided email address.');
    //     }

    //     return $this->render('requestPasswordResetToken', [
    //         'model' => $model,
    //     ]);
    // }

    /**
     * Resets password.
     *
     * @param string $token
     * @return mixed
     * @throws BadRequestHttpException
     */
    // public function actionResetPassword($token)
    // {
    //     try {
    //         $model = new ResetPasswordForm($token);
    //     } catch (InvalidArgumentException $e) {
    //         throw new BadRequestHttpException($e->getMessage());
    //     }

    //     if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {
    //         Yii::$app->session->setFlash('success', 'New password saved.');

    //         return $this->goHome();
    //     }

    //     return $this->render('resetPassword', [
    //         'model' => $model,
    //     ]);
    // }

    /**
     * Verify email address
     *
     * @param string $token
     * @throws BadRequestHttpException
     * @return yii\web\Response
     */
    public function actionVerifyEmail($token)
    {
        try {
            $model = new VerifyEmailForm($token);
        } catch (InvalidArgumentException $e) {
            throw new BadRequestHttpException($e->getMessage());
        }
        if (($user = $model->verifyEmail()) && Yii::$app->user->login($user)) {
            Yii::$app->session->setFlash('success', 'Your email has been confirmed!');
            return $this->goHome();
        }

        Yii::$app->session->setFlash('error', 'Sorry, we are unable to verify your account with provided token.');
        return $this->goHome();
    }

    /**
     * Resend verification email
     *
     * @return mixed
     */
    public function actionResendVerificationEmail()
    {
        $model = new ResendVerificationEmailForm();
        if ($model->load(Yii::$app->request->post()) && $model->validate()) {
            if ($model->sendEmail()) {
                Yii::$app->session->setFlash('success', 'Check your email for further instructions.');
                return $this->goHome();
            }
            Yii::$app->session->setFlash('error', 'Sorry, we are unable to resend verification email for the provided email address.');
        }

        return $this->render('resendVerificationEmail', [
            'model' => $model
        ]);
    }

    // public function actionRequestPasswordReset()
    // {
    //     $model = new PasswordResetRequestForm();
    //     if ($model->load(Yii::$app->request->post()) && $model->validate()) {
    //         if ($model->sendEmail()) {
    //             Yii::$app->session->setFlash('success', 'Revisa tu correo para continuar.');
    //             return $this->goHome();
    //         }
    //         Yii::$app->session->setFlash('error', 'No se pudo enviar el correo a esa dirección.');
    //     }
    //     return $this->render('requestPasswordResetToken', ['model' => $model]);
    // }
    public function actionRequestPasswordReset()
    {
        $model = new PasswordResetRequestForm();

        if ($model->load(Yii::$app->request->post())) {

            // 1) Si es la llamada de VALIDACIÓN AJAX del ActiveForm:
            if (Yii::$app->request->isAjax) {
                Yii::$app->response->format = Response::FORMAT_JSON;
                return ActiveForm::validate($model); // 👈 SOLO valida, NO envía correo
            }

            // 2) Submit normal (cuando el usuario da Enviar y pasa validación):
            if ($model->validate()) {
                if ($model->sendEmail()) {
                    Yii::$app->session->remove('__returnUrl'); // evita salto al admin
                    Yii::$app->session->setFlash('success', 'Te enviamos un correo con instrucciones para restablecer tu contraseña.');
                    return $this->redirect(['site/login']);
                }
                Yii::$app->session->setFlash('error', 'No se pudo enviar el correo a esa dirección.');
            }
        }

        return $this->render('requestPasswordResetToken', ['model' => $model]);
    }
    public function actionResetPassword($token)
    {
        try {
            $model = new ResetPasswordForm($token);
        } catch (\yii\base\InvalidParamException $e) {
            throw new \yii\web\BadRequestHttpException($e->getMessage());
        }

        if ($model->load(Yii::$app->request->post()) && $model->validate() && $model->resetPassword()) {

            Yii::$app->session->remove('__returnUrl');

            // correo de confirmación
            $user = $model->getUser();
            try {
                Yii::$app->mailService->send(
                    $user->email,
                    'Confirmación de cambio de contraseña',
                    ['html' => 'password-reset-success-html', 'text' => 'password-reset-success-text'],
                    ['user' => $user]
                );
            } catch (\Throwable $e) {
                Yii::warning('No se pudo enviar confirmación de cambio de clave: ' . $e->getMessage(), 'mail');
            }

            Yii::$app->session->setFlash('success', 'Contraseña restablecida. Ya puedes iniciar sesión.');
            return $this->redirect(['site/login']);
        }

        return $this->render('resetPassword', ['model' => $model]);
    }


    public function actionTestMail()
    {
        $to = 'victordburbano@gmail.com'; // Cambia si quieres probar con otro
        $from = 'victorburbanoherpo@gmail.com';

        $result = Yii::$app->mailer->compose()
            ->setFrom($from)
            ->setTo($to)
            ->setSubject('📧 Prueba de correo desde Yii2')
            ->setTextBody("Este es un correo de prueba enviado desde Yii2 el " . date('Y-m-d H:i:s'))
            ->send();

        if ($result) {
            Yii::info("Correo enviado correctamente a $to", 'yii\symfonymailer');
            Yii::$app->session->setFlash('success', '✅ Correo enviado correctamente.');
        } else {
            Yii::info("❌ Falló el envío de correo a $to", 'yii\symfonymailer');
            Yii::$app->session->setFlash('error', '❌ Falló el envío del correo.');
        }

        return $this->redirect(['login']);
    }
}
