<?php

namespace tsmd\base\user\api\v1backend;

use Yii;
use tsmd\base\user\models\User;
use tsmd\base\user\models\UserSearch;
use tsmd\base\dynlog\models\DynLog;

/**
 * 提供用户管理相关接口
 *
 * Table Field | Description
 * ----------- | -----------
 * uid          | User ID
 * alpha2       | Country
 * cellphone    | Cellphone
 * email        | Email
 * authKey      | Auth Key
 * passwordHash | Password Hash
 * status       | Status
 * role         | Role
 * username     | Username
 * realname     | Real Name
 * nickname     | Nickname
 * gender       | Gender
 * slug         | Slug
 *
 * `status` value | Description
 * ---------------| -----------
 * 200    | OK
 * 201    | Inactive
 * 403    | Forbidden
 * 404    | Deleted
 * 423    | Locked
 *
 * `role` value | Description
 * -------------| -----------
 * 0    | Member
 * 1    | Merchant
 * 2    | Consolidator
 * 3    | Warehouse
 * 4    | Warehouse Saleman
 * 5    | Air Cargo Agent
 * 9    | Admin
 */
class UserController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 用户列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> `/user/v1backend/user/index`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * s        | [[string]] | No | 可为手机号、邮箱、UID、用户名
     * status   | [[string]] | No | 用户状态，参见表 "`status` value"
     * role     | [[string]] | No | 角色，参见表 "`role` value"
     * realname | [[string]] | No | 真实姓名
     * nickname | [[string]] | No | 昵称
     *
     * @return array|UserSearch
     */
    public function actionIndex()
    {
        $search = new UserSearch();
        $res = $search->search($this->getQueryParams());

        array_walk($res, function(&$item) {
            User::queryOutputBy($item);
        });
        return $res !== null ? $res : $search;
    }

    /**
     * @return array|UserSearch
     */
    public function actionIndexAdmin()
    {
        $roles = [User::ROLE_ADMIN, User::ROLE_CONSOLIDATOR, User::ROLE_WAREHOUSE, User::ROLE_SALEMAN, User::ROLE_AC_AGENT, User::ROLE_CC_CO, User::ROLE_DELIVERY];

        if (isset($_GET['role']) && in_array($_GET['role'], $roles)) {
            $roles = $_GET['role'];
        }
        $_GET = array_merge($_GET, ['role' => $roles]);

        return $this->actionIndex();
    }

    /**
     * @return array
     */
    public function actionPrepare()
    {
        $statuses = User::presetStatuses();
        array_walk($statuses, function(&$item, $key) {
            $item['value'] = $key;
        }, $statuses);

        $roles = User::presetRoles();
        array_walk($roles, function(&$item, $key) {
            $item['value'] = $key;
        }, $roles);

        $genders = User::presetGenders();
        array_walk($genders, function(&$item, $key) {
            $item['value'] = $key;
        }, $genders);

        return [
            'presetStatuses' => array_values($statuses),
            'presetRoles' => array_values($roles),
            'presetGenders' => array_values($genders),
        ];
    }

    /**
     * @return User|\tsmd\base\user\models\UserSignupForm
     */
    public function actionCreate()
    {
        $model = new \tsmd\base\user\models\UserSignupForm();
        $model->load(Yii::$app->request->bodyParams, '');
        $model->captcha = '000000';
        $model->password = $model->password ?: (string) mt_rand(11110000, 99990000);
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;

        return $model->signup(true) ? $model->getUser() : $model;
    }

    /**
     * @param integer $uid
     * @return User
     */
    public function actionView($uid)
    {
        $user = $this->findModel($uid);
        $user->assignMetaProperties();

        $userFull = array_merge($user->toArray(), $user->getMetaProperties());
        $userFull['password'] = $user->getPassword();
        $userFull['accessToken'] = $user->generateAccessToken();
        return $userFull;
    }

    /**
     * @return array|User
     */
    public function actionResetMerchantSecurityKey()
    {
        $user = $this->findModel(Yii::$app->request->post('uid'));
        $user->setMerchantSecurityKey();
        $user->update(false, ['merchantSecurityKey']);

        return $user->hasErrors() ? $user : $this->success();
    }

    /**
     * 重置密码，生成一个 8 位随机密码
     *
     * @return array|User
     */
    public function actionResetUserPassword()
    {
        $user = $this->findModel(Yii::$app->request->post('uid'));
        $user->setAuthKey();
        $user->setPassword((string) mt_rand(11110000, 99990000));
        $user->update(false, ['authKey', 'passwordHash']);

        DynLog::createBy([
            'uid'    => $user->uid,
            'object' => User::getLogObject($user->uid),
            'route'  => $this->getRoute(),
            'action' => __METHOD__,
            'crud'   => DynLog::CRUD_UPDATE,
        ]);

        return $user->hasErrors() ? $user : $this->success();
    }

    /**
     * 修改邮箱，发送新邮箱验证码
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/user/v1backend/user/update-by-action`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * action   | [[string]]  | Yes | 方法 eg. editField,resetPassword,changeUsername,changeRealname,changeNickname,changeSlug,changeCellphone,changeEmail
     * uid      | [[integer]] | Yes | Uid
     *
     * 如果 `action` 值为 `editField` 须提交以下额外数据：
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * editFieldName   | [[string]]  | Yes | 字段名，值可为 `warehouseAddrid` `ssWarehouseAddrids`
     * editFieldValue  | [[string]]  | Yes | 字段值
     *
     * @return array|\tsmd\base\user\models\UserUpdateForm
     */
    public function actionUpdateByAction()
    {
        $post = Yii::$app->request->post();
        $user = $this->findModel($post['uid']);

        $model = new \tsmd\base\user\models\UserUpdateForm($user);
        $model->logRoute = $this->getRoute();
        $model->logAction = __METHOD__;
        $model->load($post, '');

        if (isset($post['action']) && $model->hasMethod($post['action'])) {
            return $model->{$post['action']}() ? $this->success() : $model;
        }
        return $this->error('Action error.');
    }

    /**
     * Finds the Log model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param integer $uid
     * @return User the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($uid)
    {
        if (($model = User::findOne(['uid' => $uid])) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
