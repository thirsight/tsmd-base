<?php

namespace tsmd\base\user\models;

use Yii;

/**
 * This is the model class for table "userdevice".
 *
 * @property int $udUid
 * @property string $udUdid
 * @property string $udType
 * @property string $udName
 * @property string $udPlatform
 * @property string $udBrowser
 * @property string $udIP
 * @property string|null $udRSAPubkey
 * @property int $createdTime
 * @property int $updatedTime
 */
class Userdevice extends \tsmd\base\models\ArModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%userdevice}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'udUid'  => 'UID',
            'udUdid' => '设备 UDID',
            'udType' => '设备型号（不可变的）',
            'udName' => '设备名称（用户自定义的）',
            'udPlatform'  => '设备平台',
            'udBrowser'   => '设备浏览器',
            'udIP'        => '设备 IP',
            'udRSAPubkey' => '生物识别公钥',
            'createdTime' => 'Created Time',
            'updatedTime' => 'Updated Time',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['udUid', 'udUdid'], 'required'],
            ['udUid', 'integer'],
            ['udUdid', 'string', 'max' => 64],

            [['udType', 'udName'], 'string', 'max' => 64],
            [['udRSAPubkey'], 'string'],

            //[['udUdid'], 'unique', 'targetAttribute' => ['udUid', 'udUdid']],
        ];
    }

    /**
     * 插入或者更新 userdevice 表记录
     *
     * @param integer $uid 用户 UID
     * @param array $data 提交需要验证的数据，如：['udUdid' => '000000-00000-...', 'udType' => 'iPhone 11 Pro', 'udName' => 'My iPhone']
     * @return Userdevice
     */
    public static function saveBy($uid, array $data = [])
    {
        $data = array_merge([
            'udUdid' => Yii::$app->request->headers->get('device-udid'),
            'udType' => Yii::$app->request->headers->get('device-type'),
            'udName' => Yii::$app->request->headers->get('device-name'),
        ], $data);
        $device = self::findOne(['udUid' => $uid, 'udUdid' => $data['udUdid']]) ?: new Userdevice(['udUid' => $uid]);

        if ($device->load($data, '') && $device->validate()) {
            // 获取 udPlatform udBrowser IP
            $browser = get_browser(null, true);
            $device->udPlatform = $browser['platform'];
            $device->udBrowser = $browser['browser'];
            $device->udIP = Yii::$app->request->userIP;
            $device->save();
            return $device;
        }
        return $device;
    }
}
