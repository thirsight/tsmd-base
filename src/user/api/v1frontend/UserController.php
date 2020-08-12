<?php

namespace tsmd\base\user\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserUpdateForm;

/**
 * UserController implements the CRUD actions for User model.
 */
class UserController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * 更新真实姓名
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/update-realname`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * newRealname | [[string]] | Yes  | 中文真实姓名
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "model": {
     *         "realname": "張三豐"
     *     }
     * }
     * ```
     *
     * @return array|UserUpdateForm
     */
    public function actionUpdateRealname()
    {
        $model = new UserUpdateForm($this->user);
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;
        $model->load($this->getBodyParams(), '');
        $model->changeRealname();
        return $model->hasErrors()
            ? TsmdResult::formatErr($model->firstErrors)
            : TsmdResult::formatSuc('model', ['realname' => $model->newRealname]);
    }

    /**
     * 绑定 Facebook
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/user/update-fbid`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * fbAccessToken | [[string]] | Yes  | Facebook Access Token
     */
    public function actionUpdateFbid()
    {
        $this->user->updateFbid($this->getBodyParams('fbAccessToken'));
        if ($this->user->hasErrors()) {
            return TsmdResult::formatErr($this->user->firstErrors);
        }
        return TsmdResult::formatSuc('model', [
            'fbPicture' => "https://graph.facebook.com/v6.0/{$this->user->fbid}/picture",
            'nickname' => $this->user->nickname,
        ]);
    }

    // ----------------------------------------

    /**
     * 查看用户信息
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/view`
     *
     * @return array
     */
    /*public function actionView()
    {
        $user = $this->user;
        $user->assignMetaProperties(1);
        return array_merge($user->toArray(), $user->getMetaProperties());
    }*/

    // ----------------------------------------

    /**
     * 重置密码，发送手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-reset-password-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendResetPasswordCaptcha()
    {
        $model = new UserUpdateForm($this->user, Yii::$app->request->bodyParams);
        return $model->sendCaptcha(Captcha::TYPE_RESET_PASSWORD) ? $this->success() : $model;
    }*/

    // ----------------------------------------

    /**
     * 修改用户名，发送手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-username-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendUsernameCaptcha()
    {
        $model = new UserUpdateForm($this->user, Yii::$app->request->bodyParams);
        return $model->sendCaptcha(Captcha::TYPE_CHANGE_USERNAME) ? $this->success() : $model;
    }*/

    // ----------------------------------------

    /**
     * 修改手机，发送旧手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-old-cellphone-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendOldCellphoneCaptcha()
    {
        $model = new UserUpdateForm($this->user, Yii::$app->request->bodyParams);
        return $model->sendCaptcha(Captcha::TYPE_CHANGE_CELLPHONE)
            ? TsmdResult::formatSuc('message', TsmdResult::SUC)
            : TsmdResult::formatErr($model->firstErrors);

    }*/

    /**
     * 修改手机，验证旧手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/validate-old-cellphone-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * captcha  | [[string]] *\d{6}*    | Yes | 手机验证码
     *
     * @return array|UserUpdateForm
     */
    /*public function actionValidateOldCellphoneCaptcha()
    {
        $model = new UserUpdateForm($this->user);
        $model->load(Yii::$app->request->bodyParams, '');
        return $model->validateCaptcha(Captcha::TYPE_CHANGE_CELLPHONE)
            ? TsmdResult::formatSuc('message', TsmdResult::SUC)
            : TsmdResult::formatErr($model->firstErrors);

    }*/

    /**
     * 修改手机，发送新手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-new-cellphone-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * newCellphone       | [[string]] | Yes | 手机号码，正則式：`/^09\d{8}$/`
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendNewCellphoneCaptcha()
    {
        $model = new UserUpdateForm($this->user);
        $model->load(Yii::$app->request->bodyParams, '');
        return $model->sendNewCellphoneCaptcha()
            ? TsmdResult::formatSuc('message', TsmdResult::SUC)
            : TsmdResult::formatErr($model->firstErrors);
    }*/

    // ----------------------------------------

    /**
     * 修改邮箱，发送手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-change-email-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendChangeEmailCaptcha()
    {
        $model = new UserUpdateForm($this->user, Yii::$app->request->bodyParams);
        return $model->sendCaptcha(Captcha::TYPE_CHANGE_EMAIL) ? $this->success() : $model;
    }*/

    /**
     * 修改邮箱，验证手机验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/validate-new-cellphone-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * captcha  | [[string]] *\d{6}*    | Yes | 手机验证码
     *
     * @return array|UserUpdateForm
     */
    /*public function actionValidateChangeEmailCaptcha()
    {
        $model = new UserUpdateForm($this->user);
        $model->load(Yii::$app->request->bodyParams, '');
        return $model->validateCaptcha(Captcha::TYPE_CHANGE_CELLPHONE) ? $this->success() : $model;
    }*/

    /**
     * 修改邮箱，发送新邮箱验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/send-new-email-captcha`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * newEmail | [[string]] | Yes | 新邮箱
     * gRecaptchaResponse | [[string]] | No  | 前端生成的 Google ReCaptcha g-recaptcha-response<br>当响应数据参数 `name` 值为 `gRecaptchaResponseEmpty` 则必填<br>Captcha::canSend() 中验证
     *
     * @return array|UserUpdateForm
     */
    /*public function actionSendNewEmailCaptcha()
    {
        $model = new UserUpdateForm($this->user);
        $model->load(Yii::$app->request->bodyParams, '');
        return $model->sendNewEmailCaptcha() ? $this->success() : $model;
    }*/

    // ----------------------------------------

    /**
     * 登記業務員（登記倉庫收貨人大陸手機門號），登记后须将加密后的 jyUid 写入 LocalStorage
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1frontend/user/reg-saleman`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * cellphone | [[string]] | Yes | 大陸手機門號
     *
     * 响应数据示例如下：
     *
     * ```
     * {
     *   "jyUid":"oe6cq/2yjDf82599tkY...hg=="
     * }
     * ```
     *
     * @return array|User
     */
    /*public function actionRegSaleman()
    {
        $cellphone = trim(Yii::$app->request->post('cellphone'));
        if (!preg_match('#^1\d{10}$#', $cellphone)) {
            return $this->error('請輸入正確的大陸手機門號。');
        }

        // 集運商旗下倉庫IDs
        /*$jyWhsAddrids = Address::query()
            ->select('addrid')
            ->where(['uid' => $this->jyUid])
            ->andWhere(['tag' => Address::TAG_WAREHOUSE])
            ->all(Address::getDb());
        $jyWhsAddrids = array_column($jyWhsAddrids, 'addrid');* /

        // 通過手機號查找業務員UID，業務員所屬倉庫須屬於該集運商
        $saleman = User::query()
            ->select('user.uid')

            // 业务员所属仓库地址 ID
            //->addSelect('usermeta.value AS warehouseAddrid')
            ->leftJoin('usermeta', 'usermeta.uid = user.uid AND usermeta.key = "warehouseAddrid"')

            // 仓库所属集运商 UID
            ->addSelect('address.uid AS jyUid')
            ->leftJoin('address', 'address.addrid = usermeta.value')

            ->where(['cellphone' => $cellphone])
            ->one(User::getDb());
        if (empty($saleman)) {
            return $this->error('查無此收貨人手機門號，請重新輸入。');
        }

        // 登記集運商
        $this->user->regConsolidator($saleman['jyUid']);
        // 登記業務員
        $this->user->regSaleman($saleman['jyUid'], $saleman['uid']);

        return [
            'jyUid' => base64_encode(Yii::$app->security->encryptByKey($saleman['jyUid'], $this->user->authKey))
        ];
    }*/
}
