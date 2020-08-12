<?php

namespace tsmd\base\user\api\v1backend;

use tsmd\base\models\SyncProdDb;
use tsmd\base\user\models\User;
use tsmd\base\user\models\Usermerchant;
use tsmd\base\user\models\Usermeta;
use tsmd\base\user\models\Userrelmap;

/**
 * 提供从生产环境同步数据至当前环境（本地或沙箱）的接口
 */
class SyncProdDbController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 从生产环境同步数据（用户相关数据）至当前环境（本地或沙箱）
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/user/v1backend/sync-prod-db/sync-from`
     *
     * @param string $dateStart
     * @param null $dateEnd
     * @return array
     */
    public function actionSyncFrom($dateStart, $dateEnd = null)
    {
        $spd = new SyncProdDb([
            'dbName' => 'db',
            'dateField' => 'updatedAt',
            'dateStart' => $dateStart,
            'dateEnd'   => $dateEnd,
            'interval'  => 5,
        ]);

        // User set authKey & password
        $user = new User();
        $user->setAuthKey();
        $user->setPassword('Tsmd190604');
        // 重置用户敏感数据
        $userPrefilter = function (&$row) use (&$user) {
            // uid 100000 以上的才重置
            if ($row['uid'] > 100000) {
                $cp = $row['cellphone'];
                $cp = substr($cp, 0, 8) . substr(preg_replace('#\D#', '', md5($cp)), -2);

                $row['cellphone'] = $cp;
                $row['authKey'] = $user->authKey;
                $row['passwordHash'] = $user->passwordHash;
            }
        };

        $tables[] = $spd->table = User::tableName();
        $success[$spd->table] = $spd->syncFromProdDb($userPrefilter);

        $spd->table = implode(', ', $tables);
        return $spd->redirect('/user/v1backend/sync-prod-db/sync-from', $success);
    }
}
