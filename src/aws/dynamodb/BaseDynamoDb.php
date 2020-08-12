<?php

namespace tsmd\base\aws\dynamodb;

use Yii;

/**
 * Class Base DynamoDb for Aws
 */
class BaseDynamoDb extends \tsmd\base\aws\urbanindo\dynamodb\ActiveRecord
{
    /**
     * The name of the delete scenario.
     */
    const SCENARIO_CREATE = 'create';
    const SCENARIO_UPDATE = 'update';
    const SCENARIO_DELETE = 'delete';

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(static::EVENT_AFTER_FIND, [$this, 'findOutput']);

        $this->on(static::EVENT_BEFORE_VALIDATE, [$this, 'preFilter']);

        $this->on(static::EVENT_BEFORE_INSERT, [$this, 'saveInput']);

        $this->on(static::EVENT_BEFORE_UPDATE, [$this, 'saveInput']);
    }

    /**
     * 预过滤器
     */
    public function preFilter()
    {
        foreach ($this as $field => $value) {
            if (is_string($value)) {
                $this->{$field} = Yii::$app->formatter->mergeBlank($value);
            }
        }
    }

    /**
     * 输入处理
     */
    public function saveInput()
    {
        // do something
    }

    /**
     * 输出处理
     */
    public function findOutput()
    {
        foreach ($this as $field => $value) {
            // 格式化浮点数
            if (is_numeric($value) && preg_match('#\.\d*0+#', $value)) {
                $this->{$field} = rtrim(rtrim($value, '0'), '.');
            }
        }
    }

    /**
     * 创建一条记录
     *
     * @param $data
     * @param null|string $formName
     * @param array $config
     * @return static
     */
    public static function createBy($data, $formName = null, $config = [])
    {
        $model = new static($config);
        $model->load($data, $formName);
        $model->insert();
        return $model;
    }
}
