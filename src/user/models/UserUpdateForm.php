<?php

namespace tsmd\base\user\models;

use Yii;
use tsmd\base\captcha\models\Captcha;
use tsmd\base\captcha\models\CaptchaStats;
use tsmd\base\dynlog\models\DynLog;

/**
 * User update form
 */
class UserUpdateForm extends \yii\base\Model
{
    /**
     * @var User
     */
    protected $user;

    public $captcha;
    public $password;
    public $newPassword;

    public $newUsername;
    public $newRealname;
    public $newNickname;
    public $newSlug;

    public $newCellphone;
    public $newCellphoneCaptcha;

    public $newEmail;
    public $newEmailCaptcha;

    // 欲修改的字段名、值
    public $editFieldName;
    public $editFieldValue;

    public $logRoute;
    public $logAction;

    /**
     * UserUpdateForm constructor.
     * @param User $user
     * @param array $config
     */
    public function __construct($user, $config = [])
    {
        parent::__construct($config);

        $this->user = $user;
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'captcha'  => Yii::t('app', 'Captcha'),
            'password' => Yii::t('app', 'Password'),
            'newPassword' => Yii::t('app', 'New Password'),

            'newUsername'  => Yii::t('app', 'New Username'),
            'newRealname'  => Yii::t('app', 'New Realname'),
            'newNickname'  => Yii::t('app', 'New Nickname'),
            'newSlug'      => Yii::t('app', 'New Slug'),

            'newCellphone' => Yii::t('app', 'New Cellphone'),
            'newCellphoneCaptcha' => Yii::t('app', 'New Cellphone Captcha'),

            'newEmail' => Yii::t('app', 'New Email Captcha'),
            'newEmailCaptcha' => Yii::t('app', 'New Email Captcha'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['captcha', 'required'],
            ['captcha', 'number', 'min' => '100000', 'max' => '999999'],

            ['password', 'required'],
            ['password', 'string', 'min' => 6, 'max' => '32'],

            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6, 'max' => '32'],

            ['newUsername', 'required'],
            ['newUsername', 'match', 'pattern' => '#\w{6,64}#'],
            ['newUsername', 'match', 'pattern' => "#^{$this->user->username}$#", 'not' => true,
                'message' => '新用户名与当前用户名一致，无须更换。'],
            ['newUsername', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'username'],

            ['newRealname', 'required'],
            ['newRealname', 'string', 'max' => 128],
            ['newRealname', 'match', 'pattern' => '#^[\x{3400}-\x{9FA5}]{2,4}$#u', 'message' => '「{value}」真實姓名應為中文 (2-4)。'],
            ['newRealname', 'match', 'pattern' => "#^{$this->user->realname}$#", 'not' => true, 'message' => '新姓名与当前姓名一致，无需更换。'],
            ['newRealname', function ($attribute, $params) {
                if (stripos($this->logAction, 'v1backend') === false) {
                    // 如果不是后台操作，判断 realname 是否允许更新
                    $meta = Usermeta::query()
                        ->select('value')
                        ->where(['uid' => $this->user->uid, 'key' => 'realnameLocked'])
                        ->one();
                    if ($meta && $meta['value']) {
                        $this->addError($attribute, '真实姓名被锁定，无法更换。');
                    }
                }
            }],

            ['newNickname', 'required'],
            ['newNickname', 'string', 'max' => 64],
            ['newNickname', 'match', 'pattern' => "#^{$this->user->nickname}$#", 'not' => true,
                'message' => '新昵称与当前昵称一致，无须更换。'],
            ['newNickname', function ($attribute, $params) {
                // 判断 nickname 是否允许更新
                $meta = Usermeta::query()
                    ->select('updatedAt')
                    ->where(['uid' => $this->user->uid, 'key' => 'nicknameChangedCounter'])
                    ->one();
                if ($meta && (time() - strtotime($meta['updatedAt'])) < 86400 * 30) {
                    $this->addError($attribute, '每 30 天才能更换一次昵称。');
                }
            }],

            ['newSlug', 'required'],
            ['newSlug', 'match', 'pattern' => '#^[\w\-]{1,64}$#'],
            ['newSlug', function ($attribute, $params) {
                if ($this->user->slug) {
                    $this->addError($attribute, 'Slug 设置后不可修改。');
                }
            }],
            ['newSlug', 'unique', 'targetClass' => User::class, 'targetAttribute' => 'slug'],

            ['newCellphone', 'required'],
            ['newCellphone', '\tsmd\base\yii\YiiCellphoneValidator'],
            ['newCellphone', 'match', 'pattern' => "#^{$this->user->cellphone}$#", 'not' => true,
                'message' => '新手机号码与当前号码一致，无须更换。'],

            ['newCellphoneCaptcha', 'required'],
            ['newCellphoneCaptcha', 'number', 'min' => '100000', 'max' => '999999'],

            ['newEmail', 'required'],
            ['newEmail', 'string', 'max' => 254],
            ['newEmail', 'email'],
            ['newEmail', 'match', 'pattern' => "#^{$this->user->email}$#", 'not' => true,
                'message' => '新邮箱与当前邮箱一致，无须更换。'],

            ['newEmailCaptcha', 'required'],
            ['newEmailCaptcha', 'number', 'min' => '100000', 'max' => '999999'],

            ['editFieldName', 'required'],
            ['editFieldName', 'in', 'range' => [
                'warehouseAddrid', 'ssWarehouseAddrids',
                'salemanTaobaoShop', 'salemanAlipayChgQrcode', 'salemanWeixinChgQrcode',
            ]],

            ['editFieldValue', 'required'],
            ['editFieldValue', 'string'],
        ];
    }

    /**
     * 修改字段值
     *
     * @return bool|false|int
     */
    public function editField()
    {
        if (!$this->validate(['editFieldName', 'editFieldValue'])) {
            return false;
        }
        $user = $this->user;
        $user->{$this->editFieldName} = $this->editFieldValue;
        return $user->update(false);
    }

    /**
     * 发送验证码
     * @param string $type
     * @return bool|void
     */
    public function sendCaptcha($type)
    {
        $capModel = Captcha::sendBy($this->user->cellphone, $type);
        return $capModel->hasErrors() ? $this->addErrors($capModel->firstErrors) : true;
    }

    /**
     * 验证验证码
     * @param $type
     * @return bool|void
     */
    public function validateCaptcha($type)
    {
        if (!$this->validate(['captcha'])) {
            return false;
        }
        $result = Captcha::validateBy($this->user->cellphone, $type, $this->captcha);
        return $result === true ?: $this->addError('captcha', $result);
    }

    /**
     * 重置密码
     * @return bool|int
     */
    public function resetPassword()
    {
        if (!$this->validateCaptcha(Captcha::TYPE_RESET_PASSWORD)) {
            return false;
        }
        if (!$this->validate(['password', 'newPassword'])) {
            return false;
        }
        $user = $this->user;

        // 验证新旧密码
        if ($this->password == $this->newPassword) {
            $this->addError('password', Yii::t('base', '新旧密码不能一样。'));
            return false;
        }
        // 验证旧密码
        if (!$user->validatePassword($this->password)) {
            $this->addError('password', Yii::t('base', '旧密码不正确。'));
            return false;
        }

        $this->log($user->cellphone, ['dataNew' => 'authKey=*&newPassword=*']);

        $user->setAuthKey();
        $user->setPassword($this->newPassword);
        $user->passwordChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 更换用户名
     * @return bool|int
     */
    public function changeUsername()
    {
        if (!$this->validateCaptcha(Captcha::TYPE_CHANGE_USERNAME)) {
            return false;
        }
        if (!$this->validate(['newUsername'])) {
            return false;
        }

        $this->log($this->newUsername, [
            'dataOld' => "username={$this->user->username}",
            'dataNew' => "username={$this->newUsername}",
        ]);

        $user = $this->user;
        $user->username = $this->newUsername;
        $user->usernameChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 更换姓名
     * @return bool|int
     */
    public function changeRealname()
    {
        if (!$this->validate(['newRealname'])) {
            return false;
        }

        $this->log($this->newRealname, [
            'dataOld' => "realname={$this->user->realname}",
            'dataNew' => "realname={$this->newRealname}",
        ]);

        $user = $this->user;
        $user->realname = $this->newRealname;
        $user->realnameLocked = 1;
        $user->realnameChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 更换昵称
     * @return bool|int
     */
    public function changeNickname()
    {
        if (!$this->validate(['newNickname'])) {
            return false;
        }

        $this->log($this->newNickname, [
            'dataOld' => "nickname={$this->user->nickname}",
            'dataNew' => "nickname={$this->newNickname}",
        ]);

        $user = $this->user;
        $user->nickname = $this->newNickname;
        $user->nicknameChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 更换 Slug
     * @return bool|int
     */
    public function changeSlug()
    {
        if (!$this->validate(['newSlug'])) {
            return false;
        }

        $this->log($this->newSlug, [
            'dataOld' => "slug={$this->user->slug}",
            'dataNew' => "slug={$this->newSlug}",
        ]);

        $user = $this->user;
        $user->slug = $this->newSlug;
        return $user->update(false);
    }

    /**
     * 发送新手机验证码
     * @return bool|void
     */
    public function sendNewCellphoneCaptcha()
    {
        if (!$this->validate(['newCellphone'])) {
            return false;
        }
        $capModel = Captcha::sendBy($this->newCellphone, Captcha::TYPE_CHANGE_CELLPHONE);
        return $capModel->hasErrors() ? $this->addErrors($capModel->firstErrors) : true;
    }

    /**
     * 验证新手机验证码
     * @return bool|void
     */
    public function validateNewCellphoneCaptcha()
    {
        if (!$this->validate(['newCellphone', 'newCellphoneCaptcha'])) {
            return false;
        }
        $result = Captcha::validateBy($this->newCellphone, Captcha::TYPE_CHANGE_CELLPHONE, $this->newCellphoneCaptcha);
        return $result === true ?: $this->addError('newCellphoneCaptcha', $result);
    }

    /**
     * 更换手机号
     * @return bool|int
     */
    public function changeCellphone()
    {
        if (!$this->validateCaptcha(Captcha::TYPE_CHANGE_CELLPHONE)) {
            return false;
        }
        if (!$this->validateNewCellphoneCaptcha()) {
            return false;
        }

        // 判断手机是否存在
        $counter = User::query()->where(['cellphone' => $this->newCellphone])->count();
        if ($counter) {
            $this->addError('newCellphone', Yii::t('app', 'New cellphone has already been taken.'));
            return false;
        }

        $this->log($this->newCellphone, [
            'dataOld' => "cellphone={$this->user->cellphone}",
            'dataNew' => "cellphone={$this->newCellphone}",
        ]);

        $user = $this->user;
        $user->cellphone = $this->newCellphone;
        $user->isCellphoneVerified = 1;
        $user->cellphoneChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 发送新邮箱验证码
     * @return bool|void
     */
    public function sendNewEmailCaptcha()
    {
        if (!$this->validate(['newEmail'])) {
            return false;
        }
        $capModel = Captcha::sendBy($this->newEmail, Captcha::TYPE_CHANGE_EMAIL);
        return $capModel->hasErrors() ? $this->addErrors($capModel->firstErrors) : true;
    }

    /**
     * 验证新邮箱验证码
     * @return bool|void
     */
    public function validateNewEmailCaptcha()
    {
        if (!$this->validate(['newEmail', 'newEmailCaptcha'])) {
            return false;
        }
        $result = Captcha::validateBy($this->newEmail, Captcha::TYPE_CHANGE_EMAIL, $this->newEmailCaptcha);
        return $result === true ?: $this->addError('newEmailCaptcha', $result);
    }

    /**
     * 更换邮箱
     * @return bool|int
     */
    public function changeEmail()
    {
        if (!$this->validateCaptcha(Captcha::TYPE_CHANGE_EMAIL)) {
            return false;
        }
        if (!$this->validateNewEmailCaptcha()) {
            return false;
        }

        // 判断邮箱是否存在
        $counter = User::query()->where(['email' => $this->newEmail])->count();
        if ($counter) {
            $this->addError('newEmail', Yii::t('app', 'New email has already been taken.'));
            return false;
        }

        $this->log($this->newEmail, [
            'dataOld' => "email={$this->user->email}",
            'dataNew' => "email={$this->newEmail}",
        ]);

        $user = $this->user;
        $user->email = $this->newEmail;
        $user->isEmailVerified = date('Y-m-d H:i:s');
        $user->emailChangedCounter += 1;
        return $user->update(false);
    }

    /**
     * 写日志
     * @param string $obj
     * @param array $extraData [successMsg, successData, errorMsg, errorData]
     */
    public function log($obj, $extraData = [])
    {
        $logPost = $_POST;
        unset($logPost['password'], $logPost['newPassword']);

        DynLog::createBy(array_merge([
            'uid'    => $this->user->uid,
            'object' => User::getLogObject($obj),
            'route'  => $this->logRoute,
            'action' => $this->logAction,
            'crud'   => DynLog::CRUD_UPDATE,
            'dataPost' => $logPost,
        ], $extraData));
    }
}
