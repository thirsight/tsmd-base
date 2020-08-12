<?php

/**
 * TSMD 模块配置文件
 *
 * @link https://tsmd.thirsight.com/
 * @copyright Copyright (c) 2008 thirsight
 * @license https://tsmd.thirsight.com/license/
 */

return [
    'adminEmail' => 'admin@thirsight.com',
    'supportEmail' => 'support@thirsight.com',
    'userRememberMe' => 86400 * 30,

    'tsmd\base\controllers\RestController' => [
        'corsOrigin' => [
            'http://localhost:80',
            'http://thirsight.com',
            'https://thirsight.com',
            'http://www.thirsight.com',
            'https://www.thirsight.com',
            'http://m.thirsight.com',
            'https://m.thirsight.com',
        ]
    ],

    // 生成接口文档的配置
    'tsmd\base\apidoc\Module' => [
        'sourceControllers' => [
            'doc-base-be' => [
                "@tsmd/base/option/api/v1backend",
                "@tsmd/base/user/api/v1backend",
                "@tsmd/base/rbac/api/v1backend",
                "@tsmd/base/aws/api",
                "@tsmd/base/dynlog/api/v1backend",
                "@tsmd/base/apidoc/api/v1backend",
                "@tsmd/base/captcha/api/v1backend",
            ],
            'doc-base-beyo' => [
                "@tsmd/base/user/api/v1backend",
            ],
            'doc-base-co' => [
                "@tsmd/base/option/api/v1consolidator",
                "@tsmd/base/aws/api",
            ],
            'doc-base-fe' => [
                "@tsmd/base/option/api/v1frontend",
                "@tsmd/base/user/api/v1frontend",
                "@tsmd/base/captcha/api/v1frontend",
            ],
        ],
    ],
];
