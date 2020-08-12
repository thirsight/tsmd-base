<?php

namespace tsmd\base\user\models;

/**
 * This is the model class for table "{{%usermbr}}".
 *
 * @property int $mbrUid
 * @property int $isMobile 使用手機
 * @property int $isTablet 使用平板
 * @property int $isDesktop 使用電腦
 * @property int $defaultWhsAddrid 默認倉庫地址 ID
 */
class Usermbr extends \tsmd\base\models\ArModel
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return '{{%usermbr}}';
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'mbrUid' => 'Mbr Uid',
            'isMobile' => '使用手機',
            'isTablet' => '使用平板',
            'isDesktop' => '使用電腦',
            'defaultWhsAddrid' => '默認倉庫地址 ID',
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['mbrUid'], 'required'],
            [['mbrUid', 'isMobile', 'isTablet', 'isDesktop', 'defaultWhsAddrid'], 'integer'],
        ];
    }

    /**
     * @param array $data
     * @return static
     */
    public static function saveBy(array $data)
    {
        $model = new static();
        $model->load($data, '');
        if (!$model->validate()) {
            return $model;
        }
        $data = $model->toArray(array_keys($data));

        $model = static::findOne(['mbrUid' => $model->mbrUid]) ?: $model;
        $model->load($data, '');
        $model->save(false);
        return $model;
    }
}
