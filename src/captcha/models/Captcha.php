<?php

namespace tsmd\base\captcha\models;

use Yii;
use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "captcha".
 *
 * @property integer $id
 * @property integer $uid
 * @property string $target cellphone|email
 * @property string $type
 * @property string $captcha
 * @property integer $generateCounter
 * @property integer $generateAt
 * @property integer $sendCounter
 * @property integer $sendAt
 * @property integer $validateCounter
 * @property integer $validateAt
 * @property string $ip
 * @property integer $createdAt
 * @property integer $updatedAt
 */
class Captcha extends \tsmd\base\models\ArModel
{
    const OG_CAPTCHA_TYPE  = 'captchaType';
    const OG_CAPTCHA_PARAM = 'captchaParam';

    const TYPE_SIGNUP = 'signUp';
    const TYPE_LOGIN  = 'login';
    const TYPE_LOGIN_SIGNUP = 'loginSignUp';
    const TYPE_VALIDATE     = 'validate';
    const TYPE_RESET_PASSWORD    = 'resetPassword';
    const TYPE_CHANGE_CELLPHONE  = 'changeCellphone';
    const TYPE_CHANGE_EMAIL      = 'changeEmail';
    const TYPE_CHANGE_USERNAME   = 'changeUsername';

    // 24小时内同一手机/邮箱允许生成验证码的次数
    const PARAM_24H_TARGET_GENERATE = '24hTargetGenerate';

    // 24小时内同一IP只允许 N 个手机/邮箱生成验证码
    const PARAM_24H_IP_TARGET   = '24hIPTarget';

    // 24小时内同一IP允许生成验证码的次数
    const PARAM_24H_IP_GENERATE = '24hIPGenerate';

    // 验证码生成时间间隔
    const PARAM_GENERATE_INTERVAL = 'generateInterval';

    // 验证码发送时间间隔
    const PARAM_SEND_INTERVAL   = 'sendInterval';

    // 验证码每小时发送次数
    const PARAM_SEND_LIMIT_1H   = 'sendLimit1H';

    // 验证码验证次数限制
    const PARAM_VALIDATE_LIMIT  = 'validateLimit';

    // 验证码有效时间，单位秒
    const PARAM_VALIDATE_TIME   = 'validateTime';

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%captcha}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return array_merge([
            'id'  => 'ID',
            'uid' => Yii::t('base', 'User ID'),
            'target'  => Yii::t('base', 'Target'),
            'type'    => Yii::t('base', 'Type'),
            'captcha' => Yii::t('base', 'Captcha'),
            'generateCounter' => Yii::t('base', 'Generate Counter'),
            'generateAt'      => Yii::t('base', 'Generate Time'),
            'sendCounter' => Yii::t('base', 'Send Counter'),
            'sendAt'      => Yii::t('base', 'Send Time'),
            'validateCounter' => Yii::t('base', 'Validate Counter'),
            'validateAt'      => Yii::t('base', 'Validate Time'),
            'ip' => 'IP',
        ], parent::attributeLabels());
    }

    /**
     * @param null $type
     * @param null $default
     * @return array|mixed
     */
    public static function presetTypes($type = null, $default = null)
    {
        $data = [
            self::TYPE_SIGNUP => ['name' => '註冊'],
            self::TYPE_LOGIN  => ['name' => '登入'],
            self::TYPE_LOGIN_SIGNUP => ['name' => '註冊登入'],
            self::TYPE_VALIDATE     => ['name' => '二次校驗'],
            self::TYPE_RESET_PASSWORD    => ['name' => '重置密碼'],
            self::TYPE_CHANGE_CELLPHONE  => ['name' => '更換手機'],
            self::TYPE_CHANGE_EMAIL      => ['name' => '更換郵箱'],
            self::TYPE_CHANGE_USERNAME   => ['name' => '更換用戶名'],
        ];
        return $type === null ? $data : ArrayHelper::getValue($data, $type, $default);
    }

    /**
     * @param null $param
     * @param null $default
     * @return array|mixed
     */
    public static function presetParams($param = null, $default = null)
    {
        $params = [
            self::PARAM_24H_TARGET_GENERATE => 5,
            self::PARAM_24H_IP_TARGET       => 5,
            self::PARAM_24H_IP_GENERATE     => 15,
            self::PARAM_GENERATE_INTERVAL   => 60 * 3,
            self::PARAM_SEND_INTERVAL       => 60,
            self::PARAM_VALIDATE_LIMIT      => 5,
            self::PARAM_VALIDATE_TIME       => 60 * 30,
        ];
        return empty($param) ? $params : \yii\helpers\ArrayHelper::getValue($params, $param, $default);
    }

    /**
     * @inheritdoc
     */
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CREATE] = $scenarios['default'];
        $scenarios[self::SCENARIO_UPDATE] = $scenarios['default'];
        return $scenarios;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        switch ($this->scenario) {
            case self::SCENARIO_CREATE:
                return $this->createRules();
            case self::SCENARIO_UPDATE:
                return $this->updateRules();
        }
        return parent::rules();
    }

    /**
     * @inheritdoc
     */
    protected function createRules()
    {
        return [
            ['uid', 'default', 'value' => Yii::$app->user->id ?: 0],
            ['uid', 'integer'],

            ['target', 'required'],
            ['target', 'string', 'length' => [8, 128]],

            ['type', 'required'],
            ['type', 'in', 'range' => array_keys(static::presetTypes())],

            ['captcha', 'required'],
            ['captcha', 'integer', 'min' => 100000, 'max' => 999999],

            ['generateCounter', 'integer'],
            ['generateAt', 'string'],

            ['sendCounter', 'integer'],
            ['sendAt', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    protected function updateRules()
    {
        return [
            ['captcha', 'integer', 'min' => 100000, 'max' => 999999],

            ['generateCounter', 'integer'],
            ['generateAt', 'string'],

            ['sendCounter', 'integer'],
            ['sendAt', 'string'],

            ['validateCounter', 'integer'],
            ['validateAt', 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        parent::saveInput();

        if ($this->isNewRecord) {
            $this->ip = Yii::$app->request->getUserIP();
        }
    }

    /**
     * 判断是否能够发送验证码
     *
     * @return bool
     */
    public function canSend()
    {
        // 通过一小时内验证码发送记录数，判断是否需要提交 gRecaptchaResponse (google 人机验证)
        $stats = (new CaptchaStats)->groupLastHourType($this->type);
        if ($stats && $stats['generateCounter'] > 60) {
            $gRecaptchaResponse = Yii::$app->request->post('gRecaptchaResponse') ?: Yii::$app->request->post('get');
            if (empty($gRecaptchaResponse)) {
                $this->addError('gRecaptchaResponseEmpty', "請先進行人機驗證，再發送驗證碼。");
                return false;
            }
            if (!Yii::$app->get('recaptcha')->validate($gRecaptchaResponse)) {
                $this->addError('gRecaptchaResponseFalse', '人機驗證失敗，請再驗證一次。');
                return false;
            }
        }

        // 验证码发送时间间隔
        $leftSec = $this->sendAt
            ? static::presetParams(self::PARAM_SEND_INTERVAL) - (time() - strtotime($this->sendAt))
            : 0;
        if ($leftSec > 0) {
            $this->addError('target', "驗證碼發送次數過於頻繁，請{$leftSec}秒後再次發送。");
            return false;
        }

        // 统计24小时内相同 target 生成验证码次数
        $sum = Captcha::query()
            ->where(['target' => $this->target])
            ->andWhere(['>=', 'generateAt', date('Y-m-d H:i:s', time() - 86400)])
            ->sum('generateCounter', Captcha::getDb());
        if ($sum > static::presetParams(self::PARAM_24H_TARGET_GENERATE)) {
            $this->addError('target', sprintf('驗證碼發送次數過於頻繁，發送失敗，請明日再試。(%s)', self::PARAM_24H_TARGET_GENERATE));
            return false;
        }

        // 统计24小时内相同 IP 生成验证码次数
        $stats = Captcha::query()
            ->select('target, generateCounter')
            ->where(['ip' => $this->ip ?: Yii::$app->request->getUserIP()])
            ->andWhere(['>=', 'generateAt', date('Y-m-d H:i:s', time() - 86400)])
            ->groupBy('target, generateCounter')
            ->all();
        if ($stats) {
            // 24小时内同一IP只允许 N 个手机/邮箱生成验证码
            $targetCounter = $this->isNewRecord ? count($stats) + 1 : count($stats);
            if ($targetCounter >= static::presetParams(self::PARAM_24H_IP_TARGET)) {
                $this->addError('target', sprintf('驗證碼發送次數過於頻繁，發送失敗，請明日再試。(%s)', self::PARAM_24H_IP_TARGET));
                return false;
            }

            $generateCounter = 0;
            foreach ($stats as $s) {
                $generateCounter += $s['generateCounter'];
            }

            // 24小时内同一IP只允许生成 N 次验证码
            if ($generateCounter >= static::presetParams(self::PARAM_24H_IP_GENERATE)) {
                $this->addError('target', sprintf('驗證碼發送次數過於頻繁，發送失敗，請明日再試。(%s)', self::PARAM_24H_IP_GENERATE));
                return false;
            }
        }

        return true;
    }

    /**
     * 發送驗證碼
     *
     * @param string $target 发送目标，手机号或邮箱
     * @param string $type 验证码类型
     * @param string $ispComponentId 预定义的简讯发送组件名
     *   即已添加到配置文件 /common/config/main.php 中的 components 键名
     *   如：ispMitake 或 ispFreecall 或 ispSmsTw （所有类均须继承自 AbstractIspSms）
     * @param string|null $ispPrefix 自定义简讯前缀，值为 `N` 发送简讯不带前缀
     * @return Captcha
     */
    public static function sendBy($target, $type, $ispComponentId = 'ispSmsTw', $ispPrefix = null)
    {
        /* @var $model Captcha */
        if ($model = Captcha::findOne(['target' => $target, 'type' => $type])) {
            $model->scenario = self::SCENARIO_UPDATE;
        } else {
            $model = new Captcha([
                'scenario' => self::SCENARIO_CREATE,
                'target' => $target,
                'type' => $type,
                'generateCounter' => 0,
                'sendCounter' => 0,
            ]);
        }

        if (!$model->canSend()) {
            return $model;
        }

        $typeName = self::presetTypes("{$type}.name");
        $captcha = $model->captcha && (time() - strtotime($model->generateAt) < static::presetParams(self::PARAM_GENERATE_INTERVAL))
            ? $model->captcha : mt_rand(100000, 999999);

        $isp = Yii::$app->get($ispComponentId);
        $isp->phone = $model->target;
        $isp->message = "親愛的用戶，您的{$typeName}驗證碼為：{$captcha}，請勿向任何人透露此驗證碼。";
        $isp->send($ispPrefix);

        if (!$isp->hasError()) {
            // 驗證碼相同，重新發送了驗證碼
            if ($model->captcha == $captcha) {
                $model->sendCounter = ++$model->sendCounter;
                $model->sendAt = date('Y-m-d H:i:s');
                $model->validateCounter = 0;
            }
            // 驗證碼不同，生成了新的驗證碼
            else {
                // 距離上次更新時間超過24小時，重置生成次數
                if ($model->updatedAt && time() - strtotime($model->updatedAt) > 86400) {
                    $model->generateCounter = 0;
                }

                $model->captcha = $captcha;
                $model->generateCounter = ++$model->generateCounter;
                $model->generateAt = date('Y-m-d H:i:s');
                $model->sendCounter = 1;
                $model->sendAt = date('Y-m-d H:i:s');
                $model->validateCounter = 0;
            }

            // 非生产环境，验证码设置为 999999
            if (!YII_ENV_PROD || $model->target == '0988881000') {
                $model->captcha = '999999';
            }

            $model->save();
        } else {
            $model->addError('target', $isp->getError());
        }
        return $model;
    }

    /**
     * Finds out if the captcha is valid or not
     *
     * @param string $captcha 用户提交的验证码
     * @return boolean
     */
    public function validateCaptcha($captcha)
    {
        if (empty($this->captcha)) {
            $this->addError('captcha', Yii::t('base', '您還未發送驗證碼。'));
            return false;
        }

        // 验证次数过多
        if ($this->validateCounter >= static::presetParams(self::PARAM_VALIDATE_LIMIT)) {
            $this->addError('validateCounter', Yii::t('base', '驗證次數過多，請重新發送驗證簡訊。'));
            return false;
        }

        // 验证码错误，验证次数+1
        if ($this->captcha != $captcha) {
            $this->validateCounter = $this->validateCounter + 1;
            $this->validateAt = date('Y-m-d H:i:s');
            $this->addError('validateCounter', Yii::t('base', '驗證碼不正確。'));
            return false;
        }

        // 验证码超时
        if (time() - strtotime($this->generateAt) > static::presetParams(self::PARAM_VALIDATE_TIME)) {
            $this->addError('validateCounter', Yii::t('base', '驗證碼超時，請重新發送驗證簡訊。'));
            return false;
        }

        return true;
    }

    /**
     * 校驗驗證碼
     *
     * @param $type
     * @param $target
     * @param $captcha
     * @return bool|array
     */
    public static function validateBy($target, $type, $captcha)
    {
        $captcha = Yii::$app->formatter->stripBlank($captcha);

        // 判斷驗證碼格式
        if (empty($captcha) || !preg_match('#^\d{6}$#', $captcha)) {
            return ['captcha' => '驗證碼為6位數字。'];
        }

        $model = Captcha::findOne(['target' => $target, 'type' => $type]);
        if (empty($model)) {
            return ['captcha' => '請先發送驗證碼。'];
        }

        if (!$model->validateCaptcha($captcha)) {
            $errors = $model->firstErrors;
            $model->update();
            return $errors;
        }

        $model->captcha = '';
        $model->sendCounter = 0;
        $model->validateCounter = 0;
        $model->validateAt = '';
        $model->update();

        return true;
    }
}
