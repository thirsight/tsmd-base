<?php

namespace tsmd\base\controllers;

/**
 * Site controller
 */
class SiteController extends RestController
{
    /**
     * @var array
     */
    protected $authExcept = ['index'];

    /**
     * @return array
     */
    public function actionIndex()
    {
        return ['success' => 'SUCCESS'];
    }
}
