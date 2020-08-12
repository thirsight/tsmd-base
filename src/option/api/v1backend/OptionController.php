<?php

namespace tsmd\base\option\api\v1backend;

use yii\helpers\ArrayHelper;
use tsmd\base\models\TsmdResult;
use tsmd\base\option\models\Option;
use tsmd\base\option\models\OptionSearch;

/**
 * OptionController implements the CRUD actions for Option model.
 */
class OptionController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * Option 列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/index`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * group    | [[integer]] | No | 组
     * key      | [[string]]  | No | 键
     * autoload | [[string]]  | No | 自动加载
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "type": "list",
     *     "list": {
     *         "site": [
     *             {
     *                 "id": "1",
     *                 "group": "site",
     *                 "key": "title",
     *                 "value": "",
     *                 "autoload": "0",
     *                 "sort": "1",
     *                 "createdTime": "1590932354",
     *                 "updatedTime": "1590932354"
     *             },
     *         ]
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function actionIndex()
    {
        $opts = (new OptionSearch)->search($this->getQueryParams());
        $opts = ArrayHelper::index($opts, null, 'group');

        return TsmdResult::formatSuc('list', $opts);
    }

    /**
     * 初始化 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/init`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * group    | [[string]] | Yes | 分组
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "type": "message",
     *     "message": "SUCCESS"
     * }
     * ```
     *
     * @return array
     */
    public function actionInit()
    {
        $res = Option::initBy($this->getBodyParams('group'));
        if ($res instanceof Option) {
            return TsmdResult::formatErr($res->firstErrors);
        }
        return TsmdResult::formatSuc();
    }

    /**
     * 更新 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/update`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * id       | [[integer]] | Yes | ID
     * value    | [[integer]] | No  | 值
     * autoload | [[string]]  | No  | 自动加载
     * sort     | [[string]]  | No  | 排序
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "type": "model",
     *     "model": {
     *         "id": 1,
     *         "group": "site",
     *         "key": "title",
     *         "value": "test",
     *         "autoload": 0,
     *         "sort": 1,
     *         "createdTime": 1590932354,
     *         "updatedTime": 1590932701
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function actionUpdate()
    {
        $opt = $this->findModel($this->getBodyParams('id'));
        $opt->load($this->getBodyParams(), '');
        $opt->update(true, ['value', 'autoload', 'sort', 'updatedTime']);
        return $opt->hasErrors()
            ? TsmdResult::formatErr($opt->firstErrors)
            : TsmdResult::formatSuc('model', $opt->toArray());
    }

    /**
     * 删除 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/delete`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * id       | [[integer]] | Yes | ID
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "type": "message",
     *     "message": "SUCCESS"
     * }
     * ```
     *
     * @return array
     */
    public function actionDelete()
    {
        $opt = $this->findModel($this->getBodyParams('id'));
        $opt->delete();
        return TsmdResult::formatSuc();
    }

    /**
     * Finds the Option model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     * @param string $id
     * @return Option the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($id)
    {
        if (is_numeric($id) && ($model = Option::findOne(['id' => $id])) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `option` does not exist.');
        }
    }
}
