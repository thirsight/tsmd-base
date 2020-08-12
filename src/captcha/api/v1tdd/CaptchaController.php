<?php

namespace tsmd\base\captcha\api\v1tdd;

use Yii;

/**
 * 验证码
 */
class CaptchaController extends \tsmd\base\controllers\RestTddController
{
    /**
     * <kbd>API</kbd> <kbd>GET</kbd> `/captcha/v1tdd/captcha/show-recaptcha`
     */
    public function actionShowRecaptcha()
    {
        if ($gRecaptchaResponse = Yii::$app->request->post('g-recaptcha-response')) {
            /*$resp = (new Client)->post('https://www.google.com/recaptcha/api/siteverify', [
                'form_params' => [
                    'secret' => '6LcgpqMUAAAAADp0hbG9-U4-YqTEkZNix1DSfNOh',
                    'response' => $gRecaptchaResponse,
                ],
            ]);
            return json_decode($resp->getBody()->getContents(), true);*/

            return Yii::$app->get('recaptcha')->validate($gRecaptchaResponse);
        }


        Yii::$app->response->format = 'html';

        $siteKey = Yii::$app->get('recaptcha')->siteKey;
        $html = <<<HTML
<html>
  <head>
    <title>reCAPTCHA demo: Simple page</title>
     <script src="//www.recaptcha.net/recaptcha/api.js" async defer></script>
  </head>
  <body>
    <form action="?" method="POST">
      <div class="g-recaptcha" data-sitekey="{$siteKey}"></div>
      <br/>
      <input type="submit" value="Submit">
    </form>
  </body>
</html>
HTML;

        $siteKey = Yii::$app->get('recaptcha')->siteKey;
        $html = <<<HTML
<html>
  <head>
    <title>reCAPTCHA demo: Simple page</title>
     <script src="//www.recaptcha.net/recaptcha/api.js" async defer></script>
     <script>
       function onSubmit(token) {
         document.getElementById("demo-form").submit();
       }
     </script>
  </head>
  <body>
    <form id='demo-form' action="?" method="POST">
      <button class="g-recaptcha" data-sitekey="{$siteKey}" data-callback='onSubmit'>Submit</button>
      <br/>
    </form>
  </body>
</html>
HTML;
        return $html;
    }

    public function actionValidateRecaptcha()
    {

    }
}
