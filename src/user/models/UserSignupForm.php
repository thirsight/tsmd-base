<?php

namespace tsmd\base\user\models;

use Yii;
use tsmd\base\dynlog\models\DynLog;
use tsmd\base\captcha\models\Captcha;
use tsmd\base\helpers\DeviceDetectHelper;

/**
 * Signup form
 */
class UserSignupForm extends \yii\base\Model
{
    use UserMigrateTrait;

    public $site;
    public $alpha2;
    public $cellphone;
    public $email;
    public $username;
    public $password;
    public $role;

    public $captcha;

    public $signupReferer;
    public $signupDeviceID;

    public $logRoute;
    public $logAction;

    /**
     * @var User
     */
    private $_user;

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'site'      => Yii::t('base', 'Site'),
            'alpha2'    => Yii::t('base', 'Country'),
            'cellphone' => Yii::t('base', 'Cellphone'),
            'email'     => Yii::t('base', 'email'),
            'username'  => Yii::t('base', 'username'),
            'password'  => Yii::t('base', 'Password'),
            'role'      => Yii::t('base', 'Role'),
            'captcha'   => Yii::t('base', 'Captcha'),

            'signupReferer'  => '註冊來源',
            'signupDeviceID' => '設備ID',
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['site', 'default', 'value' => User::SITE_KK],
            ['site', 'in', 'range' => array_keys(User::presetSites())],

            ['alpha2', 'default', 'value' => 'TW'],
            ['alpha2', 'required'],
            ['alpha2', 'in', 'range' => ['CN', 'TW']],
            //['alpha2', 'in', 'range' => ['CN', 'HK', 'MO', 'TW']],
            //['alpha2', 'in', 'range' => array_keys(\tsmd\base\helpers\CountryHelper::$countries)],

            ['cellphone', 'trim'],
            ['cellphone', 'required'],
            ['cellphone', '\tsmd\base\yii\YiiCellphoneValidator'],

            ['email', 'trim'],
            ['email', 'email'],
            ['email', 'unique', 'targetClass' => '\tsmd\base\user\models\User'],

            ['username', 'trim'],
            ['username', 'filter', 'filter' => 'strtolower'],
            ['username', 'match', 'pattern' => '#[\w]{4,64}#'],
            ['username', 'unique', 'targetClass' => '\tsmd\base\user\models\User'],

            ['password', 'trim'],
            ['password', 'string', 'min' => 6, 'max' => '32'],

            ['role', 'required'],
            ['role', 'in', 'range' => array_keys(User::presetRoles())],

            ['captcha', 'trim'],
            ['captcha', 'required'],
            ['captcha', 'string', 'length' => 6],

            ['signupReferer', 'string'],
            ['signupDeviceID', 'string'],
        ];
    }

    /**
     * 发送验证码
     *
     * @return bool|void
     */
    public function sendCaptcha()
    {
        if (!$this->validate(['alpha2', 'cellphone'])) {
            return false;
        }
        $capModel = Captcha::sendBy($this->cellphone, Captcha::TYPE_LOGIN_SIGNUP);
        return $capModel->hasErrors() ? $this->addErrors($capModel->firstErrors) : true;
    }

    /**
     * Signs user up.
     *
     * @param bool $skipCaptcha
     * @return bool
     */
    public function signup($skipCaptcha = false)
    {
        // 数据验证
        if (!$this->validate(['site', 'alpha2', 'role', 'cellphone', 'password', 'captcha', 'signupReferer', 'signupDeviceID'])) {
            return false;
        }

        // 验证码验证
        if (!$skipCaptcha) {
            $res = Captcha::validateBy($this->cellphone, Captcha::TYPE_LOGIN_SIGNUP, $this->captcha);
            if ($res !== true) {
                $this->addErrors($res);
                return false;
            }
        }

        $userExist = User::findOne(['cellphone' => $this->cellphone]);
        if ($userExist) {
            $this->_user = $userExist;
            $this->addError('cellphoneExist', Yii::t('base', 'Cellphone has already been taken.'));
            return false;
        }

        $user = new User();
        $user->site      = $this->site;
        $user->alpha2    = $this->alpha2;
        $user->cellphone = $this->cellphone;
        //$user->email     = $this->email;
        //$user->username  = $this->username;
        $user->status    = User::STATUS_OK;
        $user->role      = $this->role;

        $this->password = $this->password ?: 'Tsmd' . mt_rand(11110000, 99990000);
        $user->setAuthKey();
        $user->setPassword($this->password);

        // Usermeta
//        DeviceDetectHelper::init(Yii::$app->request->getUserAgent());
//        $user->signupReferer  = $this->signupReferer;
//        $user->signupDeviceID = $this->signupDeviceID;
//        $user->signupIP = Yii::$app->request->getUserIP();
//        $user->signupOS = DeviceDetectHelper::os();
//        $user->signupBrowser = DeviceDetectHelper::browser();

//        $user->loginCounter = 1;
//        $user->isCellphoneVerified = 1;

//        if (DeviceDetectHelper::isTablet()) {
//            $user->isTablet = 1;
//        } elseif (DeviceDetectHelper::isMobile()) {
//            $user->isMobile = 1;
//        } else {
//            $user->isDesktop = 1;
//        }

//        if (in_array($user->role, [User::ROLE_MERCHANT, User::ROLE_CONSOLIDATOR])) {
//            $user->setMerchantSecurityKey();
//        }

        // save
        $user->insert(false);

        // save usermbr
        $usermbr['mbrUid'] = $user->uid;
        DeviceDetectHelper::init(Yii::$app->request->getUserAgent());
        if (DeviceDetectHelper::isTablet()) {
            $usermbr['isTablet'] = 1;
        } elseif (DeviceDetectHelper::isMobile()) {
            $usermbr['isMobile'] = 1;
        } else {
            $usermbr['isDesktop'] = 1;
        }
        Usermbr::saveBy($usermbr);

        // 日志
        $logPost = $_POST;
        unset($logPost['password']);
        DynLog::createBy([
            'uid'    => $user->uid ?: 0,
            'object' => User::getLogObject($this->cellphone),
            'route'  => $this->logRoute,
            'action' => $this->logAction,
            'crud'   => DynLog::CRUD_CREATE,
            'dataPost' => $logPost,
        ]);

        $this->_user = $user;
        $this->migrateMchUid();
        return true;
    }
}
