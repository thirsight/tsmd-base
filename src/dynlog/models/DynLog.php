<?php

namespace tsmd\base\dynlog\models;

use Yii;
use tsmd\base\helpers\DeviceDetectHelper;

/**
 * This is the model class for DynamoDb table "tsmd_log".
 *
 * 操作者操作对象才会产生日志，操作对象的归属可能是操作者也可能是其它人
 * 操作对象肯定有归属人？
 *
 * @property string $uid 日志是哪个用户产生的 主键
 * @property integer $microtime 1517197636+123456+000000 秒+微秒+mt_rand(100000, 999999) 排序键
 * @property string $object eg: db.table.pk cellphone|email|username|oid|post_id|... 全局索引（排序键 microtime）
 * @property string $createdAt 2018-01-01 00:00:01
 * @property string $createdAtGmt 2018-01-02 00:00:01
 * @property string $route
 * @property string $action
 * @property string $crud Create|Read|Update|Delete|Login
 * @property string $dataGet
 * @property string $dataPost
 * @property string $dataBody
 * @property string $dataFiles
 * @property string $dataOld
 * @property string $dataNew
 * @property string $user404 User is not exist
 * @property string $errorMsg
 * @property string $errorData
 * @property string $successMsg
 * @property string $successData
 * @property string $ip
 * @property string $device
 * @property string $os
 * @property string $browser
 * @property string $userAgent
 */
class DynLog extends \tsmd\base\aws\dynamodb\BaseDynamoDb
{
    const CRUD_CREATE = 'Create';
    const CRUD_READ   = 'Read';
    const CRUD_UPDATE = 'Update';
    const CRUD_DELETE = 'Delete';
    const CRUD_LOGIN  = 'Login';

    const TYPE_CHROME_CRX = 'chrome-crx';

    /**
     * @return string the table name
     */
    public static function tableName()
    {
        return 'tsmd_log';
    }

    /**
     * @return array the table name
     */
    public static function primaryKey()
    {
        return ['uid', 'microtime'];
    }

    /**
     * @return array
     */
    public function attributes()
    {
        return [
            'uid',
            'microtime',
            'object',
            'createdAt',
            'createdAtGmt',
            'route',
            'action',
            'crud',
            'dataGet',
            'dataPost',
            'dataBody',
            'dataFiles',
            'dataOld',
            'dataNew',
            'user404',
            'errorMsg',
            'errorData',
            'successMsg',
            'successData',
            'ip',
            'device',
            'os',
            'browser',
            'userAgent',
        ];
    }

    public function rules()
    {
        return [
            ['uid', 'default', 'value' => Yii::$app->user->id],
            ['uid', 'required'],
            ['uid', 'string', 'max' => 255],

            ['object', 'required'],
            ['object', 'string', 'max' => 255],
            ['object', 'filter', 'filter' => 'strtolower'],
            ['object', 'match', 'pattern' => '#(?:\w{1,}\.\w{1,}\.\w{1,}|chrome-crx)#'],

            ['route', 'required'],
            ['route', 'string', 'max' => 255],

            ['action', 'required'],
            ['action', 'string', 'max' => 255],

            ['crud', 'in', 'range' => [self::CRUD_CREATE, self::CRUD_READ,
                self::CRUD_UPDATE, self::CRUD_DELETE, self::CRUD_LOGIN]],

            ['dataGet', 'default', 'value' => $_GET],
            ['dataGet', 'safe'],

            ['dataPost', 'default', 'value' => $_POST],
            ['dataPost', 'safe'],

            ['dataBody', 'default', 'value' => Yii::$app->request->getRawBody()],
            ['dataBody', 'string'],

            ['dataFiles', 'default', 'value' => $_FILES],
            ['dataFiles', 'safe'],

            ['dataOld', 'safe'],
            ['dataNew', 'safe'],

            ['user404', 'in', 'range' => [0, 1]],

            ['errorMsg',  'string', 'max' => 255],
            ['errorData', 'string', 'max' => 255],
            ['successMsg',  'string', 'max' => 255],
            ['successData', 'string', 'max' => 255],
        ];
    }

    /**
     * @inheritdoc
     */
    public function saveInput()
    {
        if ($this->isNewRecord) {
            list($usec, $sec) = explode(" ", microtime());

            $this->microtime = (integer) ($sec . substr($usec, 2, 6) . mt_rand(100, 999));
            $this->createdAt = date('Y-m-d H:i:s');
            $this->createdAtGmt = gmdate('Y-m-d H:i:s');

            DeviceDetectHelper::init(Yii::$app->request->getUserAgent());

            $this->ip      = Yii::$app->request->getUserIP();
            $this->device  = DeviceDetectHelper::deviceType();
            $this->os      = DeviceDetectHelper::os();
            $this->browser = DeviceDetectHelper::browser();
            $this->userAgent = Yii::$app->request->getUserAgent();

            // 过滤掉 GET 中的 accessToken
            if ($this->dataGet && is_array($this->dataGet)) {
                $dataGet = $this->dataGet;
                unset($dataGet['accessToken']);
                $this->dataGet = $dataGet;
            }

            foreach ($this as $field => $value) {
                if (empty($value)) {
                    unset($this->{$field});
                }
            }
        }
    }

    /**
     * POST
     * uid, object, route, action, crud 必填
     * dataGet, dataPost, dataBody, dataFiles, dataOld, dataNew, brief 可选
     *
     * @param $data
     * @param null|string $formName
     * @param array $config
     * @return static
     */
    public static function createBy($data, $formName = '', $config = [])
    {
        return parent::createBy($data, $formName, $config);
    }

    /**
     * 判斷是否允許登錄
     *
     * @param $loginName
     * @return bool|string true|error
     */
    public static function canLogin($loginName)
    {
        return true;

        $ip = Yii::$app->request->getUserIP();
        $logs = static::findAll()
            // N 小时内登录失败的记录
            ->where(['>', 'createdAt', time() - 3600 * 1])
            // uid 为0表示登录失败的
            ->andWhere(['uid' => 0])
            // 登录记录
            ->andWhere(['type' => static::TYPE_LOGIN])
            // 登录名或IP
            ->andFilterWhere(['or', ['brief' => $loginName], ['ip' => $ip]])
            ->all(static::getDb());

        if (empty($logs)) {
            return true;
        }

        // 首次登錄失敗的時間
        $firstAt = 0;
        // 根據 ip 統計
        $ipStats = [];
        // 根據 loginName 登錄名統計
        $lnStats = [];

        foreach ($logs as $log) {
            if (!$firstAt) {
                $firstAt = $log['createdAt'];
            }

            if ($log['ip'] == $ip) {
                if (!isset($ipStats[$log['brief']])) {
                    $ipStats[$log['brief']] = 0;
                }
                $ipStats[$log['brief']] += 1;
            }

            if ($log['brief'] == $loginName) {
                if (!isset($lnStats[$log['ip']])) {
                    $lnStats[$log['ip']] = 0;
                }
                $lnStats[$log['ip']] += 1;
            }
        }

        // 該 loginName 登錄名登錄次數
        if (array_sum($lnStats) >= 5) {
            return '(ln-0105) 您登入次數太頻繁，請稍後再試。';
        }

        // 該 IP 嘗試登錄了多少個賬號
        if (count($ipStats) >= 3) {
            return '(0103) 您登入次數太頻繁，請稍後再試。';
        }
        // 該 IP 嘗試登錄了多少次
        if (array_sum($ipStats) >= 10) {
            return '(0110) 您登入次數太頻繁，請稍後再試。';
        }

        return true;
    }

    /**
     * 清除一个月以前 uid=0 的日志
     */
    public function clear()
    {
        // do something
    }
}
