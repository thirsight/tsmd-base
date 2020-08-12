<?php

namespace tsmd\base\rbac\api\v1backend;

use Yii;
use tsmd\base\models\SyncProdDb;

/**
 * 提供从生产环境同步数据至当前环境（本地或沙箱）的接口
 */
class SyncProdDbController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 从生产环境同步数据（权限相关数据）至当前环境（本地或沙箱）
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/sync-prod-db/sync-from`
     *
     * @return array
     */
    public function actionSyncFrom()
    {
        /* @var $authManager \yii\rbac\DbManager */
        $authManager = Yii::$app->authManager;

        $spd = new SyncProdDb([
            'dbName' => 'db',
        ]);

        $tables[] = $spd->table = $authManager->itemTable;
        $success[$spd->table] = $spd->syncFromProdDb();

        $tables[] = $spd->table = $authManager->itemChildTable;
        $success[$spd->table] = $spd->syncFromProdDb();

        $tables[] = $spd->table = $authManager->assignmentTable;
        $success[$spd->table] = $spd->syncFromProdDb();

        $tables[] = $spd->table = $authManager->ruleTable;
        $success[$spd->table] = $spd->syncFromProdDb();

        $spd->table = implode(', ', $tables);
        return $spd->redirect('/rbac/v1backend/sync-prod-db/sync-from', $success);
    }
}
