<?php

namespace tsmd\base\controllers;

use yii\web\Response;
use yii\filters\ContentNegotiator;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class RestTddController extends \yii\rest\Controller
{
    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            'contentNegotiator' => [
                'class' => ContentNegotiator::class,
                'formats' => [
                    'application/json' => Response::FORMAT_JSON,
                    'application/xml' => Response::FORMAT_XML,
                ],
            ],
        ];
    }

    /**
     * @throws \yii\web\NotFoundHttpException
     */
    public function init()
    {
        parent::init();

        if (!YII_ENV_DEV) {
            throw new \yii\web\NotFoundHttpException('The requested page (TDD) does not exist.');
        }
    }
}
