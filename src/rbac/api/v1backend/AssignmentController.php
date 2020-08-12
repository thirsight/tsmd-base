<?php

namespace tsmd\base\rbac\api\v1backend;

use Yii;
use yii\rbac\Item;

/**
 * 为用户分配角色和权限的接口（查看、查看等）
 *
 * `auth_assignment` Field | Description
 * ----------------------- | -----------
 * item_name  | 角色或权限名称
 * user_id    | 用户 UID
 * created_at | 添加时间
 */
class AssignmentController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 查看已分配给用户的角色和权限
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/assignment/assignments`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * uid      | [[string]]      | Yes | 用户 UID
     *
     * 响应数据示例如下：
     *
     * ```
     * {
     *   // 已分配给用户的角色和权限
     *   "assignments": {
     *     "roles": [
     *       {
     *         "type": "1",
     *         "name": "SuperAdmin",
     *       },
     *       ...
     *     ],
     *     "permissions": [
     *       {
     *         "type": "1",
     *         "name": "SuperAdmin",
     *       },
     *       ...
     *     ],
     *     "routes": [
     *       {
     *         "type": "2",
     *         "name": "/rbac/v1backend/auth-item/*",
     *       },
     *       ...
     *     ]
     *   },
     *
     *   // 可分配给用户的角色和权限
     *   "available": {
     *     "roles": [...],
     *     "permissions": [...],
     *     "routes": [...]
     *   }
     * }
     * ```
     *
     * @param $uid
     * @return array
     */
    public function actionAssignments($uid)
    {
        $assignments = Yii::$app->authManager->getAssignments($uid);
        $roles = Yii::$app->authManager->getRoles();

        $items = Yii::$app->authManager->getPermissions();
        $routes = [];
        $permissions = [];
        foreach ($items as $item) {
            if (stripos($item->name, '/') === 0) {
                $routes[$item->name] = $item;
            } else {
                $permissions[$item->name] = $item;
            }
        }

        $assignRoutes = [];
        $assignRoles = [];
        foreach ($assignments as $ass) {
            $ass = (array) $ass;
            $ass['name'] = $ass['roleName'];

            if (stripos($ass['name'], '/') === 0) {
                $assignRoutes[] = $ass;
            } else {
                $assignRoles[] = $ass;
            }
            unset($roles[$ass['name']]);
            unset($routes[$ass['name']]);
            unset($permissions[$ass['name']]);
        }

        return [
            'assignments' => [
                'roles' => $assignRoles,
                'permissions' => [],
                'routes' => $assignRoutes,
            ],
            'available' => [
                'roles' => array_values($roles),
                'permissions' => array_values($permissions),
                'routes' => array_values($routes),
            ],
        ];
    }

    /**
     * 为用户分配角色或权限
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/assignment/assign-item`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * uid      | [[string]]      | Yes | 用户 UID
     * itemName | [[string]]      | Yes | 角色或权限名称，多个名称用半角逗号隔开，eg: abc, def
     * action     | [[string]]    | Yes | 动作，assign 分配 revoke 移除
     *
     * @return array
     */
    public function actionAssignItem()
    {
        $post = Yii::$app->request->post();

        $itemNames = array_filter(explode(',', $post['itemName']));
        foreach ($itemNames as $name) {
            $item = new Item(['name' => $name]);
            if ($post['action'] == 'assign') {
                Yii::$app->authManager->assign($item, $post['uid']);

            } elseif ($post['action'] == 'revoke') {
                Yii::$app->authManager->revoke($item, $post['uid']);

            } else {
                return $this->error('Error param "action".');
            }
        }
        return $this->success();
    }
}
