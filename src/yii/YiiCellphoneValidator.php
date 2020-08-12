<?php

namespace tsmd\base\yii;

use Yii;
use yii\base\InvalidConfigException;

/**
 * YiiCellphoneValidator validates that the attribute value is a valid cellphone.
 *
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class YiiCellphoneValidator extends \yii\validators\Validator
{
    /**
     * @var string
     */
    public $alpha2;

    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();

        if ($this->message === null) {
            $this->message = Yii::t('base', '{attribute} is not a valid cellphone.');
        }
    }

    /**
     * @param \yii\base\Model $model
     * @param string $attribute
     * @throws InvalidConfigException
     */
    public function validateAttribute($model, $attribute)
    {
        if (empty($this->alpha2)) {
            if ($model->hasProperty('alpha2') && $model->alpha2) {
                $this->alpha2 = $model->alpha2;
            } else {
                throw new InvalidConfigException('Alpha2 must be set.');
            }
        }

        parent::validateAttribute($model, $attribute);
    }

    /**
     * @inheritdoc
     */
    protected function validateValue($value)
    {
        $patterns = [
            'CN' => '#^1\d{10}$#',
            'HK' => '#^[69]{7}$#',
            'TW' => '#^(?:09\d{8}|007\d{7})$#',
            'MO' => '#^6{7}$#',
        ];

        $valid = isset($patterns[$this->alpha2]) ?
            preg_match($patterns[$this->alpha2], $value) : false;
        return $valid ? null : [$this->message, []];
    }
}
