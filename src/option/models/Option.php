<?php

namespace tsmd\base\option\models;

use Yii;
use yii\base\InvalidArgumentException;

/**
 * This is the model class for table "{{%option}}".
 *
 * @property integer $id
 * @property string $group
 * @property string $key
 * @property string $value
 * @property integer $autoload
 * @property integer $sort
 * @property string $createdTime
 * @property string $updatedTime
 */
class Option extends \tsmd\base\models\ArModel
{
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        $this->on(static::EVENT_AFTER_INSERT, [$this, 'deleteCache']);
        $this->on(static::EVENT_AFTER_UPDATE, [$this, 'deleteCache']);
        $this->on(static::EVENT_AFTER_DELETE, [$this, 'deleteCache']);
    }

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%option}}';
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id'       => Yii::t('base', 'Option ID'),
            'group'    => Yii::t('base', 'Option Group'),
            'key'      => Yii::t('base', 'Option Key'),
            'value'    => Yii::t('base', 'Option Value'),
            'autoload' => Yii::t('base', 'Option Autoload'),
            'sort'     => Yii::t('base', 'Sort'),
            'createdTime' => Yii::t('base', 'Created Time'),
            'updatedTime' => Yii::t('base', 'Updated Time'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['group', 'required'],
            ['group', 'string', 'max' => 64],

            ['key', 'required'],
            ['key', 'string', 'max' => 64],
            ['key', 'unique', 'targetAttribute' => ['group', 'key']],

            ['value', 'safe'],

            ['autoload', 'default', 'value' => 0],
            ['autoload', 'in', 'range' => [0, 1]],

            ['sort', 'default', 'value' => 0],
            ['sort', 'integer'],
        ];
    }

    /**
     * beforeValidate 验证前的前置过滤器
     */
    protected function prefilter()
    {
        foreach ($this as $field => $value) {
            if ($field == 'value') {
                $this->{$field} = Yii::$app->formatter->mergeBlank($value);
            } elseif (is_string($value)) {
                $this->{$field} = Yii::$app->formatter->mergeBlank(strip_tags($value));
            }
        }
    }

    /**
     * 删除分组缓存
     */
    public function deleteCache()
    {
        static::deleteCacheBy($this->group);
    }

    /**
     * 删除分组缓存
     *
     * @param $group
     */
    public static function deleteCacheBy($group)
    {
        Yii::$app->cache->delete(static::getCacheKey($group));
    }

    /**
     * 通过 group 獲取 option 值
     *
     * @param string $key
     * @param string $group
     * @return mixed|null
     */
    public static function getValuesBy($group, $key = null)
    {
        $opts = Yii::$app->cache->get(static::getCacheKey($group));
        if (empty($opts)) {
            $opts = static::query()->where(['group' => $group])->orderBy('sort ASC')->all();
            $opts = \yii\helpers\ArrayHelper::map((array) $opts, 'key', 'value');

            Yii::$app->cache->set(static::getCacheKey($group), $opts, 3600);
        }
        return $key ? $opts[$key] : $opts;
    }

    // --------------------------------------------------

    /**
     * 初始化 Option
     * @param string $group
     * @return bool|Option
     * @throws \Throwable
     */
    public static function initBy($group)
    {
        switch ($group) {
            case OptionSite::OG_SITE:
                $keys = OptionSite::$presetKeys;
                break;
            default:
                throw new InvalidArgumentException("Invalid argument `group` (`{$group}`).");
        }
        foreach ($keys as $i => $key) {
            $opt = Option::findOne(['group' => $group, 'key' => $key]) ?: new Option();
            if ($opt->isNewRecord) {
                $opt->group = $group;
                $opt->key   = $key;
                $opt->value = '';
                $opt->autoload = 0;
                $opt->sort = ++$i;
                $opt->insert(false);
            } else {
                $opt->sort = ++$i;
                $opt->update(false, ['sort', 'updatedTime']);
            }
            if ($opt->hasErrors()) {
                return $opt;
            }
        }
        return true;
    }

    /**
     * @param string $group
     * @param array $kvdata
     * @return int
     */
    public static function updateBatchBy($group, array $kvdata)
    {
        $counter = 0;
        foreach ($kvdata as $key => &$value) {
            $value = trim(strip_tags($value, '<br>'));
            $counter += Option::updateAll(
                ['value' => $value],
                ['group' => $group, 'key' => $key]
            );
        }
        Option::deleteCacheBy($group);
        return $counter;
    }
}
