<?php

namespace tsmd\base\user\api\v1backend;

use Yii;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserLoginForm;
use tsmd\taxonomy\models\Term;

/**
 * LoginController implements the login action for User model.
 */
class LoginController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * @var array 无须认证的接口
     */
    protected $authExcept = [
        'login',
    ];

    /**
     * @var array 无须授权的接口
     */
    protected $acfExcept = [
        'login',
    ];

    /**
     * 登录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> `/user/v1backend/login/login`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * username   | [[string]] | Yes | 用户名
     * password   | [[string]] | Yes | 密码
     * rememberMe | [[string]] | No  | 记住我
     *
     * @return array|UserLoginForm
     */
    public function actionLogin()
    {
        $model = new UserLoginForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->role = User::ROLE_ADMIN;
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;

        return !$model->login() ? $model : [
            'accessToken' => $model->getUser()->generateAccessToken(),
        ];
    }

    /**
     * @return array
     */
    public function actionAuthInit()
    {
        $user = array_merge($this->user->toArray(), $this->user->getMetaProperties());
        $sideMenus = Term::getTreeBy(Term::ADMIN_SIDE_MENU, ['name', 'route', 'icon', 'rbac']);
        $sideMenus = Term::formatTreeForJson($sideMenus);

        // RBAC
        $rbacAssignments = Yii::$app->authManager->getAssignments($this->user->uid);
        $rbacAssignments = array_column(array_values($rbacAssignments), 'roleName');
        $rbacPermissions = Yii::$app->authManager->getPermissionsByUser($this->user->uid);
        $rbacPermissions = array_column(array_values($rbacPermissions), 'name');

        // 菜单权限过滤
        $sideMenus = array_filter($sideMenus, function ($item) {
           return $item['rbac'] && $this->checkUserRbac($item['rbac']);
        });

        return [
            'user' => $user,
            'rbacAssignments' => $rbacAssignments,
            'rbacPermissions' => $rbacPermissions,
            'sideMenus' => $sideMenus,
        ];
    }

    /**
     * @param $route
     * @return bool
     */
    private function checkUserRbac($route)
    {
        $routes[] = $route;
        do {
            $route = preg_replace('#^(.*)/[^/]+$#', '$1', $route);
            $routes[] = '/' . ltrim($route . '/*', '/');
        } while ($route);

        $user = Yii::$app->user;
        foreach ($routes as $route) {
            if ($user->can($route)) {
                return true;
            }
        }
        return false;
    }
}
