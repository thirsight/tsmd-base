<?php

namespace tsmd\base\captcha\components;

use yii\base\BaseObject;
use GuzzleHttp\Client;

/**
 * Class of Google Recaptcha
 * https://www.google.com/recaptcha/admin/site/346269216
 * thirsight@gmail.com
 * Thirsight, v2 隐形
 *
 * @package tsmd\base\captcha\components
 */
class Recaptcha extends BaseObject
{
    /**
     * @var string 在您的网站提供给用户的 HTML 代码中使用此网站密钥，eg: 6LcgpqMUAAAA...
     */
    public $siteKey;

    /**
     * @var string 此密钥用于您的网站和 reCAPTCHA 之间的通信，eg: 6LcgpqMUAAAA...
     */
    public $secretKey;

    /**
     * 验证从前端提交上来的 g-recaptcha-response
     *
     * @see https://developers.google.com/recaptcha/docs/verify
     * @param string $gRecaptchaResponse
     * @return bool
     */
    public function validate($gRecaptchaResponse)
    {
        $resp = (new Client())->post('https://www.recaptcha.net/recaptcha/api/siteverify', [
            'form_params' => [
                'secret' => $this->secretKey,
                'response' => $gRecaptchaResponse,
            ],
        ]);
        $cont = json_decode($resp->getBody()->getContents(), true);
        return $cont['success'];
    }
}
