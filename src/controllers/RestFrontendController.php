<?php

namespace tsmd\base\controllers;

use tsmd\base\user\models\User;

/**
 * @author Haisen <thirsight@gmail.com>
 * @since 1.0
 */
abstract class RestFrontendController extends RestController
{
    /**
     * @var array [200, 201] 认证用户角色
     */
    protected $authUserStatus = [User::STATUS_OK, User::STATUS_INACTIVE];
    /**
     * @var array [0, 9] 认证用户角色
     */
    protected $authUserRole = [User::ROLE_MEMBER];
}
