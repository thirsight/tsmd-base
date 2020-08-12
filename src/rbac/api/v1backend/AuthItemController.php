<?php

namespace tsmd\base\rbac\api\v1backend;

use Yii;
use yii\rbac\Item;
use tsmd\base\rbac\models\AuthItem;
use tsmd\base\rbac\models\Route;

/**
 * 提供角色和权限的管理接口（添加、查看、修改、删除等）
 *
 * `auth_item` Field | Description
 * ----------------- | -----------
 * name        | 名称
 * type        | 类型，1 角色 2 权限
 * description | 描述
 * rule_name   | 规则
 * data        | 额外数据
 * created_at  | 添加时间
 * updated_at  | 修改时间
 *
 * `auth_item_child` Field | Description
 * ----------------------- | -----------
 * parent | 父级名称
 * child  | 子级名称
 */
class AuthItemController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 获取角色
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/roles`
     *
     * 响应数据示例如下：
     *
     * ```
     * [
     *   {
     *     "type": "1",
     *     "name": "Admin",
     *     "description": "管理员",
     *     "ruleName": null,
     *     "data": null,
     *     "createdAt": "1524713359",
     *     "updatedAt": "1524713359"
     *   },
     *   ...
     * ]
     * ```
     *
     * @return array
     */
    public function actionRoles()
    {
        return array_values(Yii::$app->authManager->getRoles());
    }

    /**
     * 获取已添加的权限，以及未添加为权限的路由
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/permissions`
     *
     * 响应数据示例如下：
     *
     * ```
     * {
     *   "permissions": [
     *     "v1coNavDashboard",
     *     ...
     *   ],
     *   "routes": [
     *     "/*",
     *     "/captcha/v1frontend/captcha/delete",
     *     ...
     *   ],
     *   "routesAvailable": [
     *     "/rbac/v1backend/auth-item/update",
     *     "/rbac/*",
     *     ...
     *   ]
     * }
     * ```
     *
     * @return array
     */
    public function actionPermissions()
    {
        $appRoutes = (new Route())->getAppRoutes();
        $items = array_keys(Yii::$app->authManager->getPermissions());
        $routesAvailable = array_values(array_diff($appRoutes, $items));

        $routes = [];
        $permissions = [];
        foreach ($items as $item) {
            if (stripos($item, '/') === 0) {
                $routes[] = $item;
            } else {
                $permissions[] = $item;
            }
        }

        return [
            'permissions' => $permissions,
            'routes' => $routes,
            'routesAvailable' => $routesAvailable,
        ];
    }

    /**
     * 批量将路由（接口 `/rbac/v1backend/auth-item/permissions` 中的 `routesAvailable`）添加为权限
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/create-routes-available`
     *
     * @return array
     */
    public function actionCreateRoutesAvailable()
    {
        $routes = $this->actionPermissions()['routesAvailable'];
        foreach ($routes as $r) {
            $permission = new \yii\rbac\Permission(['name' => $r]);
            Yii::$app->authManager->add($permission);
        }
        return ['routesAvailable' => $routes];
    }

    /**
     * 创建角色或权限
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/create`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * name     | [[string]]      | Yes | 名称
     * type     | [[string]]      | Yes | 类型，1 角色 2 权限
     * description | [[string]]   | No  | 描述
     * ruleName    | [[string]]   | No  | 规则
     * data        | [[string]]   | No  | 额外数据
     *
     * @return array|AuthItem
     */
    public function actionCreate()
    {
        return AuthItem::createBy(Yii::$app->request->post());
    }

    /**
     * 查看角色或权限
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/view`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * name     | [[string]]      | Yes | 名称
     * type     | [[string]]      | Yes | 类型，1 角色 2 权限
     *
     * 响应数据示例如下：
     *
     * ```
     * {
     *   "type": "1",
     *   "name": "Admin",
     *   "description": "管理员",
     *   "ruleName": null,
     *   "data": null,
     *
     *   // 非路由时增加参数 children，即已分配给当前角色的角色和权限
     *   "children": {
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
     *   // 非路由时增加参数 available，即可用于分配给当前角色的角色和权限
     *   "available": {
     *     "roles": [...],
     *     "permissions": [...],
     *     "routes": [...]
     *   }
     * }
     * ```
     *
     * @param $name
     * @param $type
     * @return null|\yii\rbac\Permission|\yii\rbac\Role
     */
    public function actionView($name, $type)
    {
        $item = AuthItem::findOne($name, $type);

        // 非路由
        if ($item && stripos($item->name, '/') === false) {
            $roles = Yii::$app->authManager->getRoles();
            unset($roles[$name]);

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

            $types = [1 => 'roles', 2 => 'permissions'];
            $children = [];
            foreach (Yii::$app->authManager->getChildren($name) as $child) {
                if (stripos($child->name, '/') === 0) {
                    $children['routes'][] = $child;
                } else {
                    $children[$types[$child->type]][] = $child;
                }

                unset($roles[$child->name]);
                unset($routes[$child->name]);
                unset($permissions[$child->name]);
            }
            $item = array_merge((array) $item, [
                'children' => $children,
                'available' => [
                    'roles' => array_values($roles),
                    'permissions' => array_values($permissions),
                    'routes' => array_values($routes),
                ],
            ]);
        }
        return $item;
    }

    /**
     * 给角色分配其它角色或权限
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/assign-child`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * parentName | [[string]]    | Yes | 父级角色名称
     * childName  | [[string]]    | Yes | 子级名称，多个名称用半角逗号隔开，eg: abc, def
     * action     | [[string]]    | Yes | 动作，add 添加 remove 删除
     */
    public function actionAssignChild()
    {
        $post = Yii::$app->request->post();
        $parent = new Item(['name' => $post['parentName']]);

        foreach (array_filter(explode(',', $post['childName'])) as $name) {
            if ($post['action'] == 'add') {
                Yii::$app->authManager->addChild($parent, new Item(['name' => $name]));

            } elseif ($post['action'] == 'remove') {
                Yii::$app->authManager->removeChild($parent, new Item(['name' => $name]));

            } else {
                return $this->error('Error param "action".');
            }
        }
        return $this->success();
    }

    /**
     * 修改角色或权限
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/update`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * oldName  | [[string]]      | Yes | 旧名称
     * name     | [[string]]      | Yes | 名称
     * type     | [[string]]      | Yes | 类型，1 角色 2 权限
     * description | [[string]]   | No  | 描述
     * ruleName    | [[string]]   | No  | 规则
     * data        | [[string]]   | No  | 额外数据
     *
     * @return AuthItem
     */
    public function actionUpdate()
    {
        return AuthItem::updateBy(Yii::$app->request->post());
    }

    /**
     * 删除角色或权限
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/rbac/v1backend/auth-item/delete`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * name     | [[string]]      | Yes | 名称，多个名称用半角逗号隔开，eg: abc, def
     *
     * @return array
     */
    public function actionDelete()
    {
        $counter = 0;
        $names = array_filter(explode(',', Yii::$app->request->post('name')));

        foreach ($names as $name) {
            $item = new Item(['name' => $name]);
            Yii::$app->authManager->remove($item);
            $counter++;
        }
        return $this->success("成功删除 {$counter} 条记录。");
    }
}
