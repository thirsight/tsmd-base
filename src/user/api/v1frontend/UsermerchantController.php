<?php

namespace tsmd\base\user\api\v1frontend;

use Yii;
use tsmd\base\user\models\Usermerchant;

/**
 * 接口商戶用戶數據相關接口
 */
class UsermerchantController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * 更新用戶選中的默認倉庫地址<br>
     * 如果仓库 `mobile` 和 `phone` 两者都为空，仓库不可使用（提示：該倉庫尚未啟用，敬請期待。）
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/usermerchant/update-default-warehouse-addrid`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * addrid   | [[string]] | Yes | 仓库地址ID
     *
     * @param null $addrid
     * @return array|static
     */
    /*public function actionUpdateDefaultWarehouseAddrid($addrid = null)
    {
        $addrid = Yii::$app->request->post('addrid', $addrid);

        // 判断地址是否存在，是否已启用（手机或电话不为空）
        $counter = Address::query()
            ->where(['addrid' => $addrid])
            ->andWhere(['tag' => Address::TAG_WAREHOUSE])
            ->andWhere([
                'or',
                ['!=', 'mobile', ''],
                ['!=', 'phone', '']
            ])
            ->count('*', Address::getDb());
        if (empty($counter)) {
            return $this->error('您所选倉庫尚未啟用，盡請期待，請重新選擇其它倉庫。');
        }

        $mch = Usermerchant::saveBy(
            $this->user->uid,
            $this->mchUid,
            Usermerchant::KEY_DEFAULT_WHS_ADDRID,
            $addrid
        );
        // 新集運寶用戶贈送禮品，創建用戶贈品包裹
        if ($mch->notFind) {
            Parcel::createGiftBy($this->user->uid, $this->mchUid);
        }
        return $mch->hasErrors() ? $mch : $this->success();
    }*/
}
