<?php

namespace tsmd\base\captcha\api\v1backend;

use Yii;
use tsmd\base\captcha\models\Captcha;

/**
 * 验证码管理接口
 *
 * Table Field | Description
 * ----------- | -----------
 * id               | 唯一id
 * uid              | 用户ID
 * target           | 发送目标账号，手机号或邮箱
 * type             | 类型，详见表 `field type`
 * captcha          | 6位数字验证码
 * generateCounter  | 生成次数
 * generateAt       | 最后生成时间
 * sendCounter      | 发送次数
 * sendAt           | 发送时间
 * validateCounter  | 验证次数
 * validateAt       | 最后验证时间
 * ip               | ip
 * createdAt        | createdAt
 * updatedAt        | updatedAt
 *
 * field type | Description
 * -----------| -----------
 * signUp           | 注册
 * login            | 登录
 * validate         | 二次验证
 * retrievePassword | 找回密码
 * changeCellphone  | 更换手机
 * changeEmail      | 更换邮箱
 * changeUsername   | 更换用户名
 *
 */
class CaptchaController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 验证码列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/captcha/v1backend/captcha/index`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * uid      | [[integer]] | No | 用户ID
     * target   | [[string]]  | No | 发送目标手机号或邮箱
     * type     | [[string]]  | No | 验证码类型
     * ip       | [[string]]  | No | IP
     *
     * 响应数据示例如下：
     *
     * ```json
     * [
     *   {
     *     "id": 1,
     *     "captcha": "123456",
     *     ...
     *   },
     *   ...
     * ]
     * ```
     *
     * @param null $uid
     * @param null $target
     * @param null $type
     * @param null $ip
     * @return array
     */
    public function actionIndex($uid = null, $target = null, $type = null, $ip = null)
    {
        return Captcha::query()
            ->andFilterWhere(['uid' => $uid])
            ->andFilterWhere(['target' => $target])
            ->andFilterWhere(['type' => $type])
            ->andFilterWhere(['ip' => $ip])
            ->orderBy('updatedAt DESC')
            ->offset(Yii::$app->request->getPageOffset())
            ->limit(Yii::$app->request->getPageSize())
            ->all(Captcha::getDb());
    }

    /**
     * Displays a single Captcha model.
     * @param string $id
     * @return mixed
     */
    public function actionView($id)
    {
        return $this->findModel($id);
    }

    /**
     * Updates an existing Captcha model.
     * If update is successful, the browser will be redirected to the 'view' page.
     * @param string $id
     * @return mixed
     */
    public function actionUpdate($id)
    {
        $model = $this->findModel($id);
        $model->scenario = Captcha::SCENARIO_UPDATE;
        $model->load(Yii::$app->request->post(), '');
        $model->save();

        return $model;
    }

    /**
     * Deletes an existing Captcha model.
     * If deletion is successful, the browser will be redirected to the 'index' page.
     * @param string $id
     * @return mixed
     */
    public function actionDelete($id)
    {
        $this->findModel($id)->delete();

        return $this->success();
    }

    /**
     * Finds the Captcha model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Captcha the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (($model = Captcha::findOne($id)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested page does not exist.');
        }
    }
}
