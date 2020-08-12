<?php

namespace tsmd\base\dynlog\api\v1frontend;

use Yii;
use yii\helpers\Html;
use tsmd\base\models\TsmdResult;
use tsmd\base\dynlog\models\DynLog;

/**
 * 用戶日誌記錄控制器
 */
class DynlogController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * 新增用戶日誌记录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/dynlog/v1frontend/dynlog/create`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * object | [[string]] | Yes  | 日志对象，值可为 `chrome-crx`
     * data   | [[mixed]]  | Yes  | 对象、数组、字符等任意数据
     *
     * 请求数据示例如下：
     *
     * ```json
     * {
     *     "object": "chrome-crx",
     *     "data": {
     *         "extVer": "...",
     *         "name": "...",
     *         "message": "...",
     *         "position": "...",
     *         "metaData": "...",
     *     }
     * }
     * ```
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "message": "SUCCESS"
     * }
     * ```
     *
     * @return array
     */
    public function actionCreate()
    {
        $params = Yii::$app->request->bodyParams;
        if (empty($params['object']) || empty($params['data'])) {
            return TsmdResult::formatErr();
        }
        if ($params['object'] != DynLog::TYPE_CHROME_CRX) {
            return TsmdResult::formatErr('Error `object`.');
        }
        if (is_array($params['data'])) {
            array_walk($params['data'], function (&$val) {
                $val = Html::encode($val);
            });
        } else {
            $params['data'] = Html::encode($params['data']);
        }
        $log = DynLog::createBy([
            'uid'    => $this->user->uid,
            'object' => $params['object'],
            'route'  => $this->getRoute(),
            'action' => __METHOD__,
            'dataPost' => $params['data'],
            'dataBody' => '-',
        ]);
        return $log->hasErrors() ? TsmdResult::formatErr($log->firstErrors) : TsmdResult::formatSuc('message');
    }
}
