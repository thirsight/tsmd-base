<?php

namespace tsmd\base\yii;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
class YiiSerializer extends \yii\rest\Serializer
{
    /**
     * Serializes the validation errors in a model.
     * @param \yii\base\Model $model
     * @return array the array representation of the errors
     */
    protected function serializeModelErrors($model)
    {
        //$this->response->setStatusCode(422, 'Data Validation Failed.');
        return ['error' => array_values($model->getFirstErrors())[0]];
    }
}
