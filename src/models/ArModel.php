<?php

namespace tsmd\base\models;

use Yii;
use yii\db\Query;
use tsmd\base\dynlog\models\DynLog;

/**
 * This is the base model implements [[\yii\db\ActiveRecord]].
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class ArModel extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'prefilter']);
        $this->on(static::EVENT_AFTER_VALIDATE, [$this, 'postfilter']);

        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'saveInput']);
        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'saveInput']);
    }

    /**
     * beforeValidate 验证前的前置过滤器
     */
    protected function prefilter()
    {
        foreach ($this as $field => $value) {
            if (is_string($value)) {
                $this->{$field} = Yii::$app->formatter->mergeBlank(strip_tags($value));
            }
        }
    }

    /**
     * 验证后的后置过滤器
     */
    protected function postfilter()
    {
        // do something
    }

    /**
     * 添加、更新前的处理
     */
    protected function saveInput()
    {
        foreach ($this as $field => $value) {
            if (is_array($value)) {
                $this->{$field} = json_encode($value);
            }
            switch ($field) {
                case 'createdTime':
                    if ($this->isNewRecord) {
                        $this->createdTime = $this->createdTime ?: time();
                    }
                    break;
                case 'updatedTime':
                    $this->updatedTime = time();
                    break;
            }
        }
    }

    /**
     * 输出处理
     */
    public function findOutput()
    {
        // do something
    }

    // --------------------------------------------------

    /**
     * @return string
     */
    public static function getDbName()
    {
        preg_match('#dbname=(\w+)#', static::getDb()->dsn, $m);
        return $m[1];
    }

    /**
     * @return string
     */
    public static function getRawTableName()
    {
        return static::getDb()->getSchema()->getRawTableName(static::tableName());
    }

    /**
     * @param string $key
     * @return string
     */
    public static function getCacheKey($key)
    {
        return 'table.' . static::getRawTableName() . '.' . $key;
    }

    /**
     * @return Query
     */
    public static function query()
    {
        return (new Query())
            ->select('*')
            ->from(static::tableName());
    }

    /**
     * @param $where
     * @return array|bool
     */
    public static function queryOne($where)
    {
        return (new Query())
            ->select('*')
            ->from(static::tableName())
            ->where($where)
            ->one(static::getDb());
    }

    /**
     * @param mixed $where
     * @return array|bool
     */
    public static function queryAll($where = [])
    {
        return (new Query())
            ->select('*')
            ->from(static::tableName())
            ->filterWhere($where)
            ->all(static::getDb());
    }

    /**
     * 创建一条记录
     *
     * @param $data
     * @param null $formName
     * @param array $config
     * @return static
     * @throws \Throwable
     */
    public static function createBy($data, $config = [])
    {
        $model = new static($config);
        $model->load($data, '');
        $model->insert();
        return $model;
    }

    /**
     * 輸出格式化
     *
     * @param $row
     */
    public static function queryOutputBy(&$row)
    {
        foreach ($row as $field => $value) {
            // 格式化浮点数
            if (is_numeric($value) && preg_match('#\.\d*0+#', $value)) {
                $row[$field] = rtrim(rtrim($value, '0'), '.');
            }
            // 时间格式化
            switch ($field) {
                case 'createdTime':
                    $row['createdAt'] = date('Y-m-d H:i:s', $value);
                    break;
                case 'updatedTime':
                    $row['updatedAt'] = date('Y-m-d H:i:s', $value);
                    break;
            }
        }
    }

    // --------------------------------------------------

    /**
     * @var string 用於寫日誌 dynlog route
     */
    public $logRoute;

    /**
     * @var string 用於寫日誌 dynlog action
     */
    public $logAction;

    /**
     * @param $primaryKeyVal
     * @return string
     */
    public static function getLogObject($primaryKeyVal)
    {
        return strtolower(static::getDbName() . '.' . static::getRawTableName() . '.' . $primaryKeyVal);
    }

    /**
     * 写日志
     * @param integer $uid
     * @param integer|string $primaryKeyVal
     * @param array $extraData ['crud' => '', 'successMsg' => '', 'successData' => '', 'errorMsg' => '', 'errorData' => '']
     * @return DynLog|null
     */
    public function createDynlog($uid, $primaryKeyVal, $extraData = [])
    {
        if (!YII_ENV_PROD) return null;
        try {
            return DynLog::createBy(array_merge([
                'uid'    => $uid,
                'object' => static::getLogObject($primaryKeyVal),
                'route'  => $this->logRoute,
                'action' => $this->logAction,
                'crud'   => $this->isNewRecord ? DynLog::CRUD_CREATE : DynLog::CRUD_UPDATE,
            ], $extraData));
        } catch (\Exception $e) {
            return null;
        }
    }
}
