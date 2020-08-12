<?php

namespace tsmd\base\option\api\v1backend;

use tsmd\base\models\SyncProdDb;
use tsmd\base\option\models\Option;
use tsmd\base\option\models\OptionSite;

/**
 * 提供从生产环境同步数据至当前环境（本地或沙箱）的接口
 */
class SyncProdDbController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 从生产环境同步数据（选项设置）至当前环境（本地或沙箱）
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/option/v1backend/sync-prod-db/sync-from`
     *
     * @return string
     */
    public function actionSyncFrom()
    {
        $spd = new SyncProdDb([
            'dbName' => 'db',
            'table' => Option::tableName(),
        ]);
        $counter = $spd->syncFromProdDb();

        $gps = [
            OptionSite::OG_SITE,
        ];
        foreach ($gps as $gp) {
            Option::deleteCacheBy($gp);
        }

        return $spd->redirect('/option/v1backend/sync-prod-db/sync-from', $counter);
    }
}
