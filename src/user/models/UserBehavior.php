<?php

namespace tsmd\base\user\models;

use Yii;

/**
 * 将 Usermeta Meta Key 作为属性添加到 User 对象
 */
class UserBehavior extends \tsmd\base\models\ArModelMetaBehavior
{
    // 预定义的 meta key ----------------------------------------

    /**
     * @var string 注册来源
     */
    public $signupReferer;
    /**
     * @var string 注册IP IPv4|IPv6
     */
    public $signupIP;
    /**
     * @var string 注册使用系统
     */
    public $signupOS;
    /**
     * @var string 注册使用浏览器
     */
    public $signupBrowser;
    /**
     * @var string 注册使用设备标识
     */
    public $signupDeviceID;
    /**
     * @var string 登入次数，次数更新时，updatedAt 即为登入时间
     */
    public $loginCounter;
    /**
     * @var integer 0|1 是否使用过手机访问
     */
    public $isMobile;
    /**
     * @var integer 0|1 是否使用过平板访问
     */
    public $isTablet;
    /**
     * @var integer 0|1 是否使用过PC访问
     */
    public $isDesktop;
    /**
     * @var integer 0|1 行动电话是否验证
     */
    public $isCellphoneVerified;
    /**
     * @var number 行动电话更换次数
     */
    public $cellphoneChangedCounter;
    /**
     * @var integer 0|1 邮箱是否验证
     */
    public $isEmailVerified;
    /**
     * @var number 邮箱更换次数
     */
    public $emailChangedCounter;
    /**
     * @var number 用户名更换次数
     */
    public $usernameChangedCounter;
    /**
     * @var number 真实姓名更换次数
     */
    public $realnameLocked;
    public $realnameChangedCounter;
    /**
     * @var number 昵称更换次数
     */
    public $nicknameChangedCounter;
    /**
     * @var number 密码更换次数
     */
    public $passwordChangedCounter;
    /**
     * @var string 支付密码
     */
    public $payPasswordHash;
    /**
     * @var string 支付密码更换次数
     */
    public $payPasswordChangedCounter;

    /**
     * @var string 商户用户/集运商加密字串，如：加密商户/集运商旗下的用户ID、地址ID
     */
    public $merchantSecurityKey;

    /**
     * @var string 可进入的仓库地址ID
     */
    public $warehouseAddrid;

    /**
     * @var string ShareShipmentWarehouseAddrids 出货共享仓库地址IDs
     */
    public $ssWarehouseAddrids;

    /**
     * @var string 业务员淘宝店地址、支付宝收款码、微信收款码
     */
    public $salemanTaobaoShop;
    public $salemanAlipayChgQrcode;
    public $salemanWeixinChgQrcode;

    /**
     * @inheritdoc
     */
    protected function getMetaClass()
    {
        return Usermeta::class;
    }

    /**
     * @inheritdoc
     */
    protected function getMetaLink()
    {
        return ['uid' => 'uid'];
    }

    /**
     * @inheritdoc
     */
    protected function getAutoloadKeys()
    {
        return [
            'isCellphoneVerified', 'isEmailVerified', 'payPasswordHash',
        ];
    }

    /**
     * @inherit
     */
    protected function setExtraRules()
    {
        $this->owner->extraRules = array_merge($this->owner->extraRules, [
            ['signupReferer', 'string'],
            ['signupIP', 'string'],
            ['signupOS', 'string'],
            ['signupBrowser', 'string'],
            ['signupDeviceID', 'string'],

            ['loginCounter', 'number'],

            ['isMobile',  'in', 'range' => [0, 1]],
            ['isTablet',  'in', 'range' => [0, 1]],
            ['isDesktop', 'in', 'range' => [0, 1]],

            ['isCellphoneVerified', 'in', 'range' => [0, 1]],
            ['cellphoneChangedCounter', 'number'],

            ['isEmailVerified', 'in', 'range' => [0, 1]],
            ['emailChangedCounter', 'number'],

            ['usernameChangedCounter', 'number'],

            ['realnameLocked', 'in', 'range' => [0, 1]],
            ['realnameChangedCounter', 'number'],

            ['nicknameChangedCounter', 'number'],

            ['passwordChangedCounter', 'number'],

            ['payPasswordHash', 'string'],
            ['payPasswordChangedCounter', 'number'],

            ['merchantSecurityKey', 'string'],

            ['warehouseAddrid', 'integer'],
            ['ssWarehouseAddrids', 'string'],

            ['salemanTaobaoShop', 'string'],
            ['salemanAlipayChgQrcode', 'string'],
            ['salemanWeixinChgQrcode', 'string'],
        ]);
    }

    /**
     * 設置执行密碼
     *
     * @param $pwd
     */
    public function setExecPassword($pwd)
    {
        if ($pwd) {
            $this->payPasswordHash = Yii::$app->security->generatePasswordHash($pwd);
        }
    }

    /**
     * 驗證执行密碼
     *
     * @param $pwd
     * @return bool
     */
    public function validateExecPassword($pwd)
    {
        return Yii::$app->security->validatePassword($pwd, $this->payPasswordHash);
    }

    /**
     * Generates merchant security key
     */
    public function setMerchantSecurityKey()
    {
        $this->merchantSecurityKey = time() . '-' . Yii::$app->security->generateRandomString();
    }

    // ----------------------------------------

    /**
     * 登记集运商
     *
     * @param integer $jyUid
     * @return bool
     */
    public function regConsolidator($jyUid)
    {
        $data = [
            'rmUid' => $this->owner->uid,
            'rmType' => Userrelmap::TYPE_CONSOLIDATOR,
            'rmJyUid' => $jyUid,
            'rmRelUid' => $jyUid,
        ];
        $relmap = Userrelmap::createBy($data, '');
        if ($relmap->hasErrors()) {
            $this->owner->addErrors($relmap->firstErrors);
            return false;
        }
        return true;
    }

    /**
     * 登记集運商业务员
     *
     * @param $jyUid
     * @param $salemanUid
     * @return bool
     */
    public function regSaleman($jyUid, $salemanUid)
    {
        $data = [
            'rmUid' => $this->owner->uid,
            'rmType' => Userrelmap::TYPE_SALEMAN,
            'rmJyUid' => $jyUid,
            'rmRelUid' => $salemanUid,
        ];
        $relmap = Userrelmap::createBy($data, '');
        if ($relmap->hasErrors()) {
            $this->owner->addErrors($relmap->firstErrors);
            return false;
        }
        return true;
    }

    /**
     * 用户关系地图
     *
     * @param null $jyUid
     * @param null $type
     * @return array|mixed
     */
    public function getRelmapKv($jyUid = null, $type = null)
    {
        $relmap = $this->owner->relmap;
        $relmap = \yii\helpers\ArrayHelper::map($relmap, 'rmType', 'rmRelUid', 'rmJyUid');

        if ($jyUid) {
            if (isset($relmap[$jyUid])) {
                $relmap = $relmap[$jyUid];
            } else {
                return null;
            }
            if ($type) {
                return $relmap[$type] ?? null;
            }
        }
        return $relmap;
    }

    /**
     * 通过用户关系地图获取集运商业务员UID
     *
     * @param $jyUid
     * @return integer
     */
    public function getSalemanUid($jyUid)
    {
        return $this->getRelmapKv($jyUid, Userrelmap::TYPE_SALEMAN) ?: 0;
    }
}
