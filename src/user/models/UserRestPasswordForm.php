<?php

namespace tsmd\base\user\models;

use Yii;
use tsmd\base\captcha\models\Captcha;
use tsmd\base\dynlog\models\DynLog;

/**
 * User retrieve password form
 */
class UserRestPasswordForm extends \yii\base\Model
{
    public $cellphone;
    public $captcha;
    public $newPassword;

    public $logRoute;
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
            'cellphone'   => Yii::t('app', 'Cellphone'),
            'captcha'     => Yii::t('app', 'Captcha'),
            'newPassword' => Yii::t('app', 'New Password'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['cellphone', 'trim'],
            ['cellphone', 'required'],
            ['cellphone', 'match', 'pattern' => '#^09\d{8}$#'],

            ['captcha', 'trim'],
            ['captcha', 'required'],
            ['captcha', 'match', 'pattern' => '#^\d{6}$#', 'message' => '手機驗證碼為 6 位數字。'],

            ['newPassword', 'required'],
            ['newPassword', 'string', 'min' => 6, 'max' => '32'],

        ];
    }

    /**
     * @return User
     */
    public function getUser()
    {
        return $this->_user;
    }

    /**
     * 发送验证码
     * @return bool
     */
    public function sendCaptcha()
    {
        if (!$this->validate(['cellphone'])) {
            return false;
        }
        $capModel = Captcha::sendBy($this->cellphone, Captcha::TYPE_RESET_PASSWORD);
        if ($capModel->hasErrors()) {
            $this->addErrors($capModel->firstErrors);
            return false;
        }
        return true;
    }

    /**
     * 验证验证码
     * @return bool
     */
    public function validateCaptcha()
    {
        if (!$this->validate()) {
            return false;
        }
        $result = Captcha::validateBy($this->cellphone, Captcha::TYPE_RESET_PASSWORD, $this->captcha);
        if ($result !== true) {
            $this->addErrors($result);
            return false;
        }
        return true;
    }

    /**
     * 重置密码
     * @return bool|User
     */
    public function resetPassword()
    {
        if (!$this->validateCaptcha()) {
            return false;
        }

        // 查找用户，如果用戶不存在直接註冊呢？用戶會迷惑，進入後會發現這不是原來的賬號啊
        $this->_user = User::findOne(['cellphone' => $this->cellphone]);
        if (!$this->_user) {
            $this->addError('cellphoneNotExists', '您的手機號碼尚未註冊。');
            $this->log(0);
            return false;
        }
        // 用户状态、角色判断
        if ($this->_user->status !== User::STATUS_OK || $this->_user->role !== User::ROLE_MEMBER) {
            $this->addError('cellphone', '您的賬號異常無法重置密碼，如有疑問請與客服聯絡。');
            $this->log($this->_user->uid, ['errorData' => "status={$this->_user->status}&role={$this->_user->role}"]);
            return false;
        }

        // 设置新密码
        $this->_user->setAuthKey();
        $this->_user->setPassword($this->newPassword);
        $this->_user->update(false);

        $this->log($this->_user->uid, ['dataNew' => 'authKey=*&newPassword=*']);
        return true;
    }

    /**
     * 写日志
     *
     * @param $uid
     * @param array $extraData [successMsg, successData, errorMsg, errorData]
     */
    public function log($uid, $extraData = [])
    {
        $logPost = $_POST;
        unset($logPost['newPassword']);

        DynLog::createBy(array_merge([
            'uid'    => $uid,
            'object' => User::getLogObject($this->cellphone),
            'route'  => $this->logRoute,
            'action' => $this->logAction,
            'crud'   => DynLog::CRUD_UPDATE,
            'user404'  => $uid ? 1 : 0,
            'dataPost' => $logPost,
        ], $extraData));
    }
}
