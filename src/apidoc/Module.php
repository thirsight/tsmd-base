<?php

namespace tsmd\base\apidoc;

use Yii;
use yii\helpers\ArrayHelper;
use yii\helpers\FileHelper;
use GuzzleHttp\Client;

class Module extends \yii\base\Module
{
    /**
     * @var string
     */
    public $controllerNamespace = 'tsmd\base\apidoc\api';

    // --------------------------------------------------

    /**
     * 可通过 Yii::$app->params 参数进行配置
     *
     * ```
     * $sourceControllers = [
     *     'backend' => [
     *         '@tsmd/base/apidoc/api/v1backend',
     *         '@tsmd/base/option/api/v1backend',
     *     ],
     *     'frontend' => [
     *         '@tsmd/base/option/api/v1frontend',
     *     ],
     * ];
     * ```
     *
     * @var array
     */
    public $sourceControllers = [];

    /**
     * 文档链接地址
     *
     * @var string eg. https://docs.example.com
     */
    public $baseUrl;

    /**
     * 生成的文档输出的文件夹
     *
     * @var string eg. W5fE6Fs...
     */
    public $outputDir;

    /**
     * {@inheritDoc}
     */
    public function init()
    {
        parent::init();

        $this->sourceControllers = ArrayHelper::merge($this->sourceControllers, Yii::$app->params[self::class]['sourceControllers']);
    }

    /**
     * apidoc 生成的文档列表
     *
     * @return array
     */
    public function getApidocs()
    {
        $docs = [];
        foreach ($this->sourceControllers as $key => $controllers) {
            $docs[] = [
                'name' => $key,
                'url' => $this->baseUrl . '/' . $this->outputDir . '/' . $key,
            ];
        }
        return $docs;
    }

    /**
     * 使用 apidoc 命令生成文档
     *
     * @param bool $isDeployS3
     * @return array|string
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \yii\base\ErrorException
     * @throws \yii\base\Exception
     */
    public function generateApidoc($isDeployS3 = true)
    {
        $tsmdBasePath = Yii::getAlias('@tsmd/base');
        $outputPath = "{$tsmdBasePath}/apidoc/dist/{$this->outputDir}";

        FileHelper::removeDirectory($outputPath);
        FileHelper::createDirectory("{$tsmdBasePath}/apidoc/dist");

        // 文档生成命令
        $cmd = Yii::getAlias('@vendor/bin/apidoc');
        $bat = '';
        foreach ($this->sourceControllers as $key => $controllers) {
            $controllers = array_reduce($controllers, function ($carry, $ctrl) {
                $carry .= ($carry ? ',' : '') . Yii::getAlias($ctrl);
                return $carry;
            });
            $bat .= "{$cmd} api {$controllers} {$outputPath}/{$key}\n";
        }
        // 文档压缩命令
        $zipFile = "{$tsmdBasePath}/apidoc/dist/dist.zip";
        $bat .= "cd {$tsmdBasePath}/apidoc/dist\n";
        $bat .= "zip -r {$zipFile} *\n";

        // 执行命令
        $res = exec($bat);

        // 是否自动部署至 S3
        if ($isDeployS3) {
            try {
                // 自动部署至 S3
                $res = $this->deployS3($zipFile);
            } finally {
                // 删除压缩文件及生成的文档
                //sleep(2);
                @unlink($zipFile);
                FileHelper::removeDirectory($outputPath);
            }
        }
        return $res;
    }

    /**
     * 将文档部署至 S3
     *
     * @param string $zipFile
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function deployS3($zipFile)
    {
        // 调用线上 AWS S3 前端自動部署接口 https://api.thirsight.com/aws/s3/fe-deploy
        // http://localhost:8888/aws/s3/fe-deploy
        $client = new Client();
        $res = $client->request('POST', 'https://api.tsmd.thirsight.com/aws/s3/fe-deploy', [
            'multipart' => [
                [
                    'name'     => 'signKey',
                    'contents' => Yii::$app->getModule('aws')->s3ControllerSignKey,
                ],
                [
                    'name'     => 'bucket',
                    'contents' => 'tsmd.thirsight.com-apidoc',
                ],
                [
                    'name'     => 'file',
                    'contents' => fopen($zipFile, 'r'),
                ]
            ]
        ]);
        return json_decode($res->getBody()->getContents(), true);
    }
}
