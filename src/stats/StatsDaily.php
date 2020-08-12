<?php

namespace tsmd\base\stats;

use yii\helpers\ArrayHelper;

/**
 * This is the model class for table "stats_daily".
 *
 * @property string $sdTag
 * @property string $sdDate
 * @property string $sdGroup
 * @property string $sdKey
 * @property string $sdValue
 * @property string $sdMonth
 * @property string $updatedAt
 */
class StatsDaily extends \tsmd\base\models\ArModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%stats_daily}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'sdTag'         => '统计标签',
            'sdDate'        => '统计日期',
            'sdGroup'       => '统计分组',
            'sdKey'         => '字段名',
            'sdValue'       => '字段值',
            'sdMonth'       => '统计月份',
            'updatedAt'     => 'Updated At',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['sdTag', 'sdDate', 'sdKey', 'sdValue'], 'required'],

            ['sdDate', 'date', 'format' => 'Y-m-d'],
            ['sdDate', function ($attribute, $params) {
                $this->sdMonth = date('Y-m-00', strtotime($this->sdDate));
            }],

            ['sdGroup', 'string', 'max' => 64],
            ['sdTag', 'string', 'max' => 64],
            ['sdKey', 'string', 'max' => 64],

            ['sdValue', 'safe'],

            //[['sdTag'], 'unique', 'targetAttribute' => ['sdTag', 'sdDate', 'sdGroup', 'sdKey']],
        ];
    }

    /**
     * 获取统计记录（并以键值对格式返回）
     *
     * @param $sdTag
     * @param $dateStart
     * @param $dateEnd
     * @param $sdDateField
     * @return array
     */
    public static function batchGetKvBy($sdTag, $sdDateField, $dateStart, $dateEnd)
    {
        $rows = static::query()
            ->select('sdDate, sdKey, sdValue')
            ->where(['sdTag' => $sdTag])
            ->andWhere(['between', 'sdDate', $dateStart, $dateEnd])
            ->orderBy('sdDate ASC')
            ->all(static::getDb());
        $rows = ArrayHelper::map($rows, 'sdKey', 'sdValue', 'sdDate');

        array_walk($rows, function (&$v, $k) use (&$sdDateField) {
            $v[$sdDateField] = $k;
        });
        return array_values($rows);
    }

    /**
     * 批量添加或更新统计记录
     *
     * $rows example,
     *
     * ```php
     * $rows = [
     *   ['packedDate' => '2019-05-16', 'counter' => '153', 'sumWeight' => '1222550'],
     *   ['packedDate' => '2019-05-17', 'counter' => '186', 'sumWeight' => '1222550'],
     * ];
     * ```
     *
     * @param string $sdTag 1e874f518f4ff069e27e96b720df2380
     * @param string $rowDateField packedDate
     * @param string $rowGroupField
     * @param array $rows
     * @return array|bool
     */
    public static function batchSaveBy($sdTag, $rowDateField, $rowGroupField, $rows)
    {
        $errors = [];
        foreach ($rows as $row) {
            $date = $row[$rowDateField];
            $group = $row[$rowGroupField] ?? '';

            foreach ($row as $field => $val) {
                if (in_array($field, [$rowDateField, $rowGroupField])) {
                    continue;
                }

                $where = [
                    'sdTag'   => $sdTag,
                    'sdDate'  => $date,
                    'sdGroup' => $group,
                    'sdKey'   => $field,
                ];
                $sd = static::findOne($where);
                if ($sd) {
                    if ($sd->sdValue != $val) {
                        $sd->sdValue = $val;
                        $sd->update(false);
                    }
                } else {
                    $where['sdValue'] = $val;

                    $sd = new static();
                    $sd->load($where, '');
                    $sd->insert();
                }
                if ($sd->hasErrors()) {
                    $errors[] = $sd->firstErrors;
                }
            }
        }
        return $errors ?: true;
    }
}
