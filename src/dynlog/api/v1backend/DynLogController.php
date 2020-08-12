<?php

namespace tsmd\base\dynlog\api\v1backend;

use tsmd\base\models\TsmdResult;
use tsmd\base\dynlog\models\DynLog;

/**
 * DynLogController implements the CRUD actions for DynLog model.
 */
class DynLogController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * Lists all Log models.
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/dynlog/v1backend/dyn-log/index`
     *
     * @return mixed
     */
    public function actionIndex()
    {
        return TsmdResult::formatSuc('list', []);

        /*$options = [
            'using' => 'Query',
            'expressionAttributeNames' => [
                '#o' => 'uid',
            ],
        ];
        $logs = DynLog::find($options)
            ->where(['#u' => 2])
            ->orderBy('microtime DESC')
            ->limit(99)
            ->asArray()
            ->all(DynLog::getDb());

        array_walk($logs, function(&$item) {
            $item['microtime'] = (string) $item['microtime'];
        });
        return TsmdResult::formatSuc('list', $logs, ['pageSize' => 99]);*/
    }

    /**
     * 查看订单或包裹的日志
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/dynlog/v1backend/dyn-log/search`
     *
     * Argument     | Type | Required | Description
     * ------------ | ---- | -------- | -----------
     * type         | [[string]] | Yes | 订单 order 或 包裹 parcel
     * object       | [[string]] | Yes | 订单 oid 或 包裹 pclid
     *
     * @param string $type eg. order, parcel
     * @param string $object eg. J2820809466215
     * @return array
     */
    public function actionSearch($type, $object)
    {
        $types = [
            'jyorder' => Jyorder::class,
            'parcel' => Parcel::class,
        ];
        $object = isset($types[$type]) ? $types[$type]::getLogObject($object) : $object;

        $options = [
            'using' => 'Query',
            'expressionAttributeNames' => [
                '#o' => 'object',
            ],
        ];
        $logs = DynLog::find($options)
            ->indexBy('object-index')
            ->where(['#o' => $object])
            //->orderBy(['microtime' => 'DESC'])
            ->limit(99)
            ->asArray()
            ->all(DynLog::getDb());
        usort($logs, function ($a, $b) {
            return $a['microtime'] <=> $b['microtime'];
        });
        array_walk($logs, function(&$item) {
            unset($item['_response']);
            $item['microtime'] = (string) $item['microtime'];
        });
        return TsmdResult::formatSuc('list', $logs, ['pageSize' => 99]);
    }

    /**
     * 查看某一条日志的详情
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/dynlog/v1backend/dyn-log/view`
     *
     * Argument     | Type | Required | Description
     * ------------ | ---- | -------- | -----------
     * uid          | [[string]] | Yes | 用户 ID
     * microtime    | [[string]] | Yes | 时间戳
     *
     * @param $uid
     * @param $microtime
     * @return array
     */
    public function actionView($uid, $microtime)
    {
        $options = [
            'using' => 'Query',
        ];
        $log = DynLog::find($options)
            ->where(['uid' => (string) $uid, 'microtime' => (int) $microtime])
            ->asArray()
            ->one(DynLog::getDb());
        if ($log) {
            unset($log['_response']);
            $log['microtime'] = (string) $log['microtime'];
        }
        return TsmdResult::formatSuc('model', $log);
    }
}
