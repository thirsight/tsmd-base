<?php

namespace tsmd\base\controllers;

use tsmd\base\user\models\User;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class RestBackendController extends RestController
{
    /**
     * @var array [200, 201] 认证用户角色
     */
    protected $authUserStatus = [User::STATUS_OK];
    /**
     * @var array [0, 9] 认证用户角色
     */
    protected $authUserRole = [User::ROLE_ADMIN];

    /**
     * @var array ACFBehavior except property
     */
    protected $acfExcept = [];

    /**
     * @return array
     */
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['access'] = [
            'class' => 'tsmd\base\rbac\behaviors\ACFBehavior',
            'except' => $this->acfExcept,
        ];
        return $behaviors;
    }
}
