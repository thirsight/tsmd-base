<?php

namespace tsmd\base\controllers;

use tsmd\base\user\models\User;
use tsmd\address\models\Address;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class RestConsolidatorController extends RestController
{
    /**
     * @var array [200, 201] 认证用户角色
     */
    protected $authUserStatus = [User::STATUS_OK];
    /**
     * @var array [0, 9] 认证用户角色
     */
    protected $authUserRole = [User::ROLE_CONSOLIDATOR, User::ROLE_WAREHOUSE, User::ROLE_SALEMAN, User::ROLE_AC_AGENT, User::ROLE_CC_CO, User::ROLE_DELIVERY];

    /**
     * @var array
     */
    protected $warehouseAddr;
    /**
     * @var integer
     */
    protected $warehouseAddrid;
    /**
     * @var array
     */
    protected $ssWarehouseAddrids;

    /**
     * @var array ACFBehavior except property
     */
    protected $acfExcept = [];

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => 'tsmd\base\rbac\behaviors\ACFBehavior',
            'except' => $this->acfExcept,
        ];
        return $behaviors;
    }

    /**
     * @inheritdoc
     */
    protected function prepare()
    {
        parent::prepare();

        if ($this->user) {
            $addr = Address::query()
                ->select('addrid, address.uid, country, province, city, district, street, desc, desc2')
                ->addSelect('postcode, consignee, address.mobile, address.phone, address.email, memo, label, tag')
                ->addSelect('isPrimary, isLocked, isInvalid')
                ->addSelect('user.role')
                ->addSelect('user.uid AS ownerUid, role AS ownerRole')
                ->leftJoin(User::tableName(), 'user.uid = address.uid')
                ->where(['addrid' => $this->user->warehouseAddrid, 'tag' => Address::TAG_WAREHOUSE])
                ->one();
            if (empty($addr)) {
                throw new \yii\web\UnauthorizedHttpException("仓储管理员 {$this->user->uid} 暂未设置可用仓库，请与管理员联系。");
            }
            $addr['ownerSelfRun'] = (int) ($addr['ownerUid'] == 2);

            $this->warehouseAddr = $addr;
            $this->warehouseAddrid = $addr['addrid'];
            $this->ssWarehouseAddrids = array_filter(explode(',', "{$this->warehouseAddrid},{$this->user->ssWarehouseAddrids}"));
        }
    }
}
