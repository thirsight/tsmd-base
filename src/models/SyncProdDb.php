<?php

namespace tsmd\base\models;

use Yii;
use yii\helpers\Html;

/**
 * 从生产环境同步数据当前环境
 *
 * @package tsmd\base
 */
class SyncProdDb extends \yii\base\BaseObject
{
    /**
     * @var string
     */
    public $dbName;
    /**
     * @var string
     */
    public $table;
    /**
     * @var string
     */
    public $orderBy = '';

    /**
     * @var string
     */
    public $dateField;
    /**
     * @var string
     */
    public $dateStart;
    /**
     * @var string
     */
    public $dateStartEnd;
    /**
     * @var string
     */
    public $dateStartNext;
    /**
     * @var string
     */
    public $dateEnd;
    /**
     * @var string
     */
    public $interval = 0;
    /**
     * @var string
     */
    public $isTimestamp = false;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        // 设置日期
        if (strtotime($this->dateStart)) {
            $this->dateStart = date('Y-m-d 00:00:00', strtotime($this->dateStart));
            $this->dateStartEnd = date('Y-m-d 23:59:59', strtotime("{$this->dateStart} +{$this->interval} Day"));
            $this->dateStartNext = date('Y-m-d 00:00:00', strtotime("{$this->dateStartEnd} +1 Day"));;
        }
        $this->dateEnd = date('Y-m-d 23:59:59', strtotime($this->dateEnd) ?: time());
    }

    /**
     * 从生产环境同步数据当前环境（本地或沙箱），数据库配置参见 _db.php 中的 prodXxx, localXxx, sandboxXxx
     *
     * @param \Closure|null $prefilter
     * @return int
     */
    public function syncFromProdDb(\Closure $prefilter = null)
    {
        ini_set('memory_limit','512M');
        set_time_limit(0);

        if (YII_ENV_DEV) {
            $localPrefix = 'local';
        } elseif (YII_ENV_TEST) {
            $localPrefix = 'sandbox';
        } else {
            return 0;
        }
        $dbName = ucfirst($this->dbName);

        /* @var $prodDb \yii\db\Connection */
        $prodDb = Yii::$app->get("prod{$dbName}");
        /* @var $localDb \yii\db\Connection */
        $localDb = Yii::$app->get("{$localPrefix}{$dbName}");

        // 从生产环境服务器获取数据
        $dateStart = $this->isTimestamp ? strtotime($this->dateStart) : $this->dateStart;
        $dateStartEnd = $this->isTimestamp ? strtotime($this->dateStartEnd) : $this->dateStartEnd;
        $where = $this->dateField
            ? ['between', $this->dateField, $dateStart, $dateStartEnd]
            : [];
        $prodRows = (new \yii\db\Query())
            ->from($this->table)
            ->where($where)
            ->orderBy($this->orderBy)
            ->all($prodDb);
        if (empty($prodRows)) {
            return 0;
        }
        if ($prefilter) {
            array_walk($prodRows, $prefilter);
        }

        $fields = array_keys($prodRows[0]);
        foreach (array_chunk($prodRows, 500) as $chunk) {
            // 将数据更新到本地环境
            $sql = $localDb->createCommand()->batchInsert($this->table, $fields, $chunk)->getRawSql();
            $sql = str_ireplace('INSERT INTO', 'REPLACE INTO', $sql);
            $sql = "SET FOREIGN_KEY_CHECKS=0; {$sql}; SET FOREIGN_KEY_CHECKS=1;";
            $localDb->createCommand($sql)->execute();

            //echo "{$sql};\n";
        }
        return count($prodRows);
    }

    /**
     * @param $url
     * @param $result
     * @return string
     */
    public function redirect($url, $result)
    {
        Yii::$app->response->format = 'html';

        if (is_array($result)) {
            $result = '<pre>' . json_encode($result, JSON_PRETTY_PRINT) . '</pre>';
        }

        $html  = "<h2>{$this->dbName}.{$this->table}</h2>";
        $html .= "<p>Date Field: {$this->dateField}</p>";
        $html .= "<p>Date Start: {$this->dateStart}</p>";
        $html .= "<p>Date Next: {$this->dateStartNext}</p>";
        $html .= "<p>Result: {$result}</p>";

        if ($this->dateStart && $this->dateEnd &&
            strtotime($this->dateStart) < strtotime($this->dateEnd)) {

            $gets = Yii::$app->request->get();
            $gets['dateStart'] = $this->dateStartNext;
            $gets = http_build_query($gets);

            $html .= "<p>Redirecting...</p>";
            $html .= Html::script("window.location.href = '{$url}?{$gets}';");
        }
        return $this->render($html);
    }

    /**
     * @param $body
     * @return string
     */
    protected function render($body)
    {
        return <<<HTML
<!doctype html>
<html lang="en">
  <head>
    <title>SyncProdDb - TSMD</title>
  </head>
  <body>{$body}</body>
</html>
HTML;
    }
}
