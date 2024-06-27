<?php
namespace frontend\models;

use mdm\admin\components\UserStatus;
use mdm\admin\models\User;
use Yii;
use yii\base\Model;
use yii\helpers\ArrayHelper;

/**
 * Signup form
 */
class SignupEmpleadoLogistica extends Model
{
    public $username;
    public $email;
    public $password;
    public $retypePassword;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
        return [
            ['username', 'filter', 'filter' => 'trim'],
            ['username', 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            ['username', 'unique', 'targetClass' => $class, 'message' => 'Este nombre de usuario ya existe.'],
            ['username', 'string', 'min' => 2, 'max' => 255],

            ['email', 'filter', 'filter' => 'trim'],
            ['email', 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => $class, 'message' => 'Esta dirección de correo electrónico ya existe.'],

            ['password', 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            ['password', 'string', 'min' => 6],

            ['retypePassword', 'required', 'message' => '{attribute} Es Un Valor Obligatorio'],
            ['retypePassword', 'compare', 'compareAttribute' => 'password'],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'username' => 'Nombre de usuario',
            'password' => 'Contraseña',
            'retypePassword' => 'Repetir Contraseña',
            'email' => 'Correo Electrónico',
        ];
    }

    /**
     * Signs user up.
     *
     * @return User|null the saved model or null if saving fails
     */
    public function signup()
    {
        if ($this->validate()) {
            $class = Yii::$app->getUser()->identityClass ? : 'mdm\admin\models\User';
            $user = new $class();
            $user->username = $this->username;
            $user->email = $this->email;
            $user->status = ArrayHelper::getValue(Yii::$app->params, 'user.defaultStatus', UserStatus::ACTIVE);
            $user->setPassword($this->password);
            $user->generateAuthKey();
            if ($user->save()) {
                return $user;
            }
        }

        return null;
    }
}
