<?php

namespace tsmd\base\user\models;

use Yii;
use tsmd\base\dynlog\models\DynLog;
use tsmd\base\helpers\DeviceDetectHelper;

/**
 * Login form
 */
class UserLoginForm extends \yii\base\Model
{
    /**
     * @var string cellphone|email|username
     */
    public $username;
    /**
     * @var string  1 cellphone | 2 email | 4 username | 8 facebook access token or apple identityToken
     */
    public $usernameType = 15;
    /**
     * @var string
     */
    public $password;
    /**
     * @var int 0|1
     */
    public $rememberMe = 1;
    /**
     * @var int|array 0|[1, 2]
     */
    public $role;
    /**
     * @var string
     */
    public $logRoute;
    /**
     * @var string
     */
    public $logAction;

    /**
     * @var User
     */
    private $_user;

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'username' => Yii::t('base', 'Username'),
            'password' => Yii::t('base', 'Password'),
            'rememberMe' => Yii::t('base', 'Remember Me'),
            'role' => Yii::t('base', 'Role'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['username', 'trim'],
            ['username', 'required'],

            ['password', 'trim'],
            ['password', 'required'],
            ['password', 'validatePassword'],

            ['rememberMe', 'default', 'value' => '1'],
            ['rememberMe', 'in', 'range' => [0, 1]],
        ];
    }

    /**
     * Finds user by [[username]]
     *
     * @return User|null
     */
    public function getUser()
    {
        if ($this->_user === null) {
            if ((1 & $this->usernameType) && preg_match('#^(?:09\d{8}|1\d{10})$#', $this->username)) {
                $this->usernameType = 1;
                $this->_user = User::findOne(['cellphone' => $this->username]);

            } elseif ((2 & $this->usernameType) && stripos($this->username, '@')) {
                $this->usernameType = 2;
                $this->_user = User::findOne(['email' => $this->username]);

            } elseif (4 & $this->usernameType && strlen($this->username) <= 64) {
                $this->usernameType = 4;
                $this->_user = User::findOne(['username' => $this->username]);

            } elseif (8 & $this->usernameType) {
                $this->usernameType = 8;
                // Apple identityToken 為 JWT 格式數據
                if (substr_count($this->username, '.') == 2) {
                    $appleUser = User::getAppleUserBy($this->username);
                    if ($appleUser) {
                        $this->_user = User::findOne(['appleUser' => $appleUser]);
                        $this->password = $this->_user ? $this->_user->getPassword() : $this->password;
                    }
                } else {
                    $fb = User::getFacebookUserBy($this->username);
                    if ($fb['id']) {
                        $this->_user = User::findOne(['fbid' => $fb['id']]);
                        $this->password = $this->_user ? $this->_user->getPassword() : $this->password;
                    }
                }
            }
        }

        return $this->_user;
    }

    /**
     * Validates the password.
     * This method serves as the inline validation for password.
     *
     * @param string $attribute the attribute currently being validated
     * @param array $params the additional name-value pairs given in the rule
     */
    public function validatePassword($attribute, $params)
    {
        if ($this->hasErrors()) return;

        $user = $this->getUser();
        if (!$user || !$user->validatePassword($this->password)) {
            $this->addError($attribute, Yii::t('base', 'Incorrect username or password.'));

        } elseif ($this->role !== null && !in_array($user->role, (array) $this->role)) {
            $this->addError($attribute, Yii::t('base', 'Incorrect username or password.'));

        } elseif (!in_array($user->status, [User::STATUS_OK])) {
            $this->addError($attribute, Yii::t('base', 'Incorrect username or password.'));
        }
    }

    /**
     * Logs in a user using the provided username and password.
     *
     * @return boolean whether the user is logged in successfully
     */
    public function login()
    {
        $canLogin = DynLog::canLogin($this->username);
        if ($canLogin !== true) {
            $this->addError('username', $canLogin);
            return false;
        }

        $user = $this->getUser();
        if (!$this->_user && $this->usernameType === 8) {
            if (substr_count($this->username, '.') == 2) {
                $this->addError('appleUserNotExists', 'Apple 尚未關聯您的巧巧郞賬號。');
            } else {
                $this->addError('fbidNotExists', 'Facebook 尚未關聯您的巧巧郞賬號。');
            }
            return false;
        }

        $logined = false;
        if ($this->validate()) {
            $logined = Yii::$app->user->login(
                $user,
                $this->rememberMe ? Yii::$app->params['userRememberMe'] : 0);

            if ($logined) {
                $user->assignMetaProperties();

                $user->loginCounter += 1;
                DeviceDetectHelper::init(Yii::$app->request->getUserAgent());
                if (DeviceDetectHelper::isTablet()) {
                    $user->isTablet = 1;
                } elseif (DeviceDetectHelper::isMobile()) {
                    $user->isMobile = 1;
                } else {
                    $user->isDesktop = 1;
                }
                $user->update();
            }
        }

        $logPost = $_POST;
        unset($logPost['password']);
        DynLog::createBy([
            'uid'    => $logined ? $user->uid : 0,
            'object' => User::getLogObject($this->username),
            'route'  => $this->logRoute,
            'action' => $this->logAction,
            'crud'   => DynLog::CRUD_LOGIN,
            'user404'   => $user ? 1 : 0,
            'errorData' => !$logined && $user ? "status={$user->status}&role={$user->role}" : '',
            'dataPost'  => $logPost,
        ]);

        return $logined;
    }
}
