<?php

namespace tsmd\base\user\api\v1frontend;

use tsmd\base\user\models\Userdevice;
use Yii;
use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserLoginForm;
use tsmd\base\user\models\UserSignupForm;
use tsmd\base\user\models\UserRestPasswordForm;

/**
 * 提供用户登录、获取用户初始数据等接口
 *
 * 登录成功会返回 `accessToken`，调用其它接口时须提交 `accessToken`
 *
 * **认证方式 Query string（所有接口通用）**
 *
 * 在 url 里增加 `?accessToken=xxxx`，xxx 须 urlencode
 *
 * **请求接口 Header（所有接口通用）**
 *
 * - `device-udid` 设备 ID
 * - `device-type` 设备型号，如：`iPhone 11 Pro` `SM-G9550`（即用户不可修改的数据）
 * - `device-name` 设备名称，即用户可修改的数据
 *
 * **执行成功时返回的数据格式（所有接口通用）**
 *
 * `{"success":"SUCCESS"}` 或 `{"success":"..."}` 或相关的 JSON 数据
 *
 * **产生错误时返回的数据格式（所有接口通用）**
 *
 * 状态码为 2xx 返回的错误格式如下：
 *
 * ```json
 * {
 *   "error": {
 *     "username": "電郵、行動電話或密碼錯誤。"
 *   }
 * }
 * ```
 *
 * ```json
 * {
 *   "error": "Error data."
 * }
 * ```
 *
 * 状态码为 3xx 4xx 5xx 返回的错误格式如下：
 *
 * ```json
 * {
 *   "name": "Unauthorized",
 *   "message": "You are requesting with an invalid credential.",
 *   "code": 0,
 *   "status": 401,
 * }
 * ```
 */
class LoginController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var array 无须 accessToken 认证的接口
     */
    protected $authExcept = [
        'login',
        'login-pwd',
        'signup-send-captcha',
        'signup',
        'send-reset-password-captcha',
        'reset-password',
    ];

    /**
     * 「已棄用」商戶账号登入
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/login`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * username     | [[string]]          | Yes | 手机号码
     * password     | [[string]] *{6,32}* | Yes | 密码
     *
     * @return array|UserLoginForm
     */
    public function actionLogin()
    {
        return TsmdResult::formatErr('集運系統維護中…… 開放時間另行公告。');

        /*$model = new UserLoginForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->role = User::ROLE_MERCHANT;
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;

        return !$model->login() ? $model : [
            'accessToken' => $model->getUser()->generateAccessToken(),

            // 商户返回加密 Key
            'merchantSecurityKey' => $model->getUser()->role == User::ROLE_MERCHANT
                ? $model->getUser()->merchantSecurityKey : '',

            // 2019-03-03 先注释，如无问题，再删除
            //'mchUid' => empty(Yii::$app->request->post('mchUid'))
            //    ? ''
            //    : base64_encode(Yii::$app->security->encryptByKey(Yii::$app->request->post('mchUid'), $model->getUser()->merchantSecurityKey)),
        ];*/
    }

    /**
     * 賬號用戶名、密碼登入
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/login-pwd`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * username     | [[string]]          | Yes | 手机号码或 Facebook Access Token 或 Apple Identity Token
     * password     | [[string]] *{6,32}* | Yes | 密码
     *
     * 登入成功响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "model": {
     *         "uid": "1000",
     *         "accessToken": "dBB1V5oty37bgApLPuS172VkYjAwZ...",
     *     }
     * }
     * ```
     *
     * Facebook Access Token 或 Apple identityToken 登錄失敗响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "ERROR",
     *     ...
     *     "name": "fbidNotExists",
     *     "message": "Facebook 尚未關聯您的巧巧郞賬號。"
     * }
     * ```
     *
     * ```json
     * {
     *     "tsmdResult": "ERROR",
     *     ...
     *     "name": "appleUserNotExists",
     *     "message": "Apple 尚未關聯您的巧巧郞賬號。"
     * }
     * ```
     *
     * @return array|UserLoginForm
     */
    public function actionLoginPwd()
    {
        $model = new UserLoginForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->role = User::ROLE_MEMBER;
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;
        $model->login();

        if ($model->hasErrors()) {
            if ($model->getFirstError('appleUserNotExists')) {
                return TsmdResult::formatSuc('model', ['uid' => '', 'accessToken' => ''], 'appleUserNotExists');
            }
            if ($model->getFirstError('fbidNotExists')) {
                return TsmdResult::formatSuc('model', ['uid' => '', 'accessToken' => ''], 'fbidNotExists');
            }
            return TsmdResult::formatErr($model->firstErrors);
        }
        $model->migrateMchUid();

        Userdevice::createBy($model->getUser()->uid);

        return TsmdResult::formatSuc('model', [
            'uid' => $model->getUser()->uid,
            'accessToken' => $model->getUser()->generateAccessToken(),
        ]);
    }

    /**
     * 簡訊註冊或登入，发送登入或注册手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/signup-send-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * cellphone          | [[string]] | Yes | 手机号码，正則式：`/^09\d{8}$/`
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>用於 Captcha::canSend()
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "ERROR",
     *     "name": "gRecaptchaResponseEmpty",
     *     "type": "list",
     *     ...
     *     "list": [
     *         {
     *             "name": "gRecaptchaResponseEmpty",
     *             "message": "請先進行人機驗證，再發送驗證碼。"
     *         }
     *     ]
     * }
     * ```
     *
     * @return array|UserSignupForm
     */
    public function actionSignupSendCaptcha()
    {
        $model = new UserSignupForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->alpha2 = 'TW';
        return $model->sendCaptcha()
            ? TsmdResult::formatSuc('message', TsmdResult::SUC)
            : TsmdResult::formatErr($model->firstErrors);
    }

    /**
     * 簡訊註冊或登入，如果账号不存在，先註冊後登入，如果賬號已存在，則直接登入
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/signup`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * site             | [[string]] | No  | 站点，`KK` 巧巧郞（默认）
     * cellphone        | [[string]] | Yes | 手机号码
     * password         | [[string]] | No  | 密码（如果沒有提交密碼，生成一個隨機密碼）
     * captcha          | [[string]] | Yes | 手机验证码
     * signupReferer    | [[string]] | No  | 注册来源
     * signupDeviceID   | [[string]] | No  | 注册设备ID
     * fbAccessToken    | [[string]] | No  | Facebook Access Token
     * appleIdentityToken | [[string]] | No  | Apple identityToken
     *
     * @return array|UserSignupForm
     */
    public function actionSignup()
    {
        $post = Yii::$app->request->post();

        $model = new UserSignupForm();
        $model->load($post, '');
        $model->alpha2 = 'TW';
        $model->role = User::ROLE_MEMBER;
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;

        if (!$model->signup()) {
            // 如果手機號已存在，將用戶登錄
            if ($model->hasErrors('cellphoneExist')) {
                $model->clearErrors('cellphoneExist');
                $model->getUser()->setPassword($model->password);
                $model->getUser()->update(false, ['passwordHash', 'updatedAt']);
            }
            if ($model->hasErrors()) {
                return TsmdResult::formatErr($model->firstErrors);
            }
        }

        // 更新真实姓名
        if ($this->getBodyParams('realname')) {
            $user = $model->getUser();
            $user->realname = $this->getBodyParams('realname');
            $user->realnameLocked = 1;
            $user->realnameChangedCounter += 1;
            $user->update(false, ['realname', 'realnameLocked', 'realnameChangedCounter']);
        }
        // 綁定 facebook
        if ($this->getBodyParams('fbAccessToken')) {
            $model->getUser()->updateFbid($this->getBodyParams('fbAccessToken'));
        }
        // 綁定 apple
        if ($this->getBodyParams('appleIdentityToken')) {
            $model->getUser()->updateAppleUser($this->getBodyParams('appleIdentityToken'));
        }
        // 注册成功一并登录
        Yii::$app->request->setBodyParams([
            'username' => $model->getUser()->cellphone,
            'password' => $model->getUser()->getPassword(),
        ]);
        return $this->actionLoginPwd();
    }

    // ----------------------------------------

    /**
     * 重置密码，发送手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/send-reset-password-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * cellphone | [[string]]  | Yes | 手机号码，正則式： /^09\d{8}$/
     *
     * @return array
     */
    public function actionSendResetPasswordCaptcha()
    {
        $model = new UserRestPasswordForm();
        $model->load(Yii::$app->request->bodyParams, '');
        return $model->sendCaptcha()
            ? TsmdResult::formatSuc()
            : TsmdResult::formatErr($model->firstErrors);
    }

    /**
     * 重置密码，輸入新密码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/login/reset-password`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * cellphone        | [[string]] | Yes | 手机号码
     * captcha          | [[string]] | Yes | 手机验证码，6 位數字
     * newPassword      | [[string]] | Yes | 新密码，6-32 位字符
     *
     * 重置密码成功响应数据示例如下（须使用新的 `accessToken` 替换旧的，否则将被登出）：
     *
     * ```json
     * {
     *     "tsmdResult": "ERROR",
     *     ...
     *     "model": {
     *         "uid": "1000",
     *         "accessToken": "dBB1V5oty37bgApLPuS172VkYjAwZ...",
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function actionResetPassword()
    {
        $model = new UserRestPasswordForm();
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;
        $model->load(Yii::$app->request->bodyParams, '');

        if (!$model->resetPassword()) {
            return TsmdResult::formatErr($model->firstErrors);
        }
        return TsmdResult::formatSuc('model', [
            'uid' => $model->getUser()->uid,
            'accessToken' => $model->getUser()->generateAccessToken(),
        ]);
    }
}
