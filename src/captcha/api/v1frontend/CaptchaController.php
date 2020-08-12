<?php

namespace tsmd\base\captcha\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\captcha\models\Captcha;

/**
 * CaptchaController implements the CRUD actions for Captcha model.
 */
class CaptchaController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * 发送手机验证的验证码 (type: validate)
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/captcha/v1frontend/captcha/send`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * cellphone | [[string]] | No  | 手机号码，默认为用户手机号码
     * ispPrefix | [[string]] | No  | 默认值为空，当值设为 `N` 时取消简讯前缀，即发送的简讯将不含前缀`【巧巧郎】`
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
     * @return array
     */
    public function actionSend()
    {
        $cellphone = $this->getBodyParams('cellphone') ?: $this->user->cellphone;
        if (!preg_match('#^09\d{8}$#', $cellphone)) {
            return TsmdResult::formatErr('Error cellphone.');
        }

        $model = Captcha::sendBy($cellphone, Captcha::TYPE_VALIDATE, 'ispSmsTw', $this->getBodyParams('ispPrefix'));
        return $model->hasErrors()
            ? TsmdResult::formatErr($model->firstErrors)
            : TsmdResult::formatSuc('model', ['cellphone' => $cellphone, 'ispPrefix' => $this->getBodyParams('ispPrefix')]);
    }

    /**
     * @return mixed
     */
    /*public function actionValidate($target, $type, $captcha)
    {
        $result = Captcha::validateBy($target, $type, $captcha);
        return $result === true ? $this->success() : $this->error($result);
    }*/
}
