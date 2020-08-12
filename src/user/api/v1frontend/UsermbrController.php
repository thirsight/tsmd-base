<?php

namespace tsmd\base\user\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\user\models\Usermbr;

/**
 * 用戶會員數據相關接口
 */
class UsermbrController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * 更新用戶選中的默認倉庫地址<br>
     * 如果仓库 `mobile` 和 `phone` 两者都为空，仓库不可使用（提示：該倉庫尚未啟用，敬請期待。）
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1frontend/usermbr/update`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * defaultWhsAddrid | [[string]] | Yes | 仓库地址 ID
     *
     * @return array
     */
    public function actionUpdate()
    {
        $mbr = Usermbr::saveBy([
            'mbrUid' => $this->user->uid,
            'defaultWhsAddrid' => $this->getBodyParams('defaultWhsAddrid'),
        ]);
        return $mbr->hasErrors()
            ? TsmdResult::formatErr($mbr->firstErrors)
            : TsmdResult::formatSuc('model', $mbr);
    }
}
