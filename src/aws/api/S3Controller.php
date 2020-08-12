<?php

namespace tsmd\base\aws\api;

use Yii;
use yii\helpers\FileHelper;
use yii\web\UploadedFile;
use tsmd\base\models\TsmdResult;
use tsmd\base\option\models\Option;
use tsmd\base\option\models\OptionAppFe;

/**
 * 提供 AWS S3 前端自動部署接口
 */
class S3Controller extends \tsmd\base\controllers\RestController
{
    /**
     * @var array 无须认证的接口
     */
    protected $authExcept = ['fe-deploy'];

    /**
     * AWS S3 前端自動部署<br>
     * 上传 zip 压缩包，自动解压，自动同步压缩包中的文件至指定的 S3
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/aws/s3/fe-deploy`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * signKey  | [[string]]  | Yes | 认证字串
     * bucket   | [[string]]  | Yes | S3 储存桶，其值参见下表
     * file     | [[string]]  | Yes | `zip` 压缩包（最大 64M），压缩包中的目录结构应如下：<br>- `static`<br>- `index.html`
     *
     * `bucket` Values | Description
     * --------------- | -----------
     * `www.thirsight.com` | PC 用户端
     * `m.thirsight.com`   | H5 用户端
     *
     * 响应数据示例如下：
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "model": {
     *         "fileCounter": 1,
     *         "uploadTimer": 0.071,
     *         "syncS3Timer": 0.098,
     *         "download": "https://tsmd.thirsight.com/_deploy/cc1016f0ae07a9303bed185b51ed47ac_202003272106.zip"
     *     }
     * }
     * ```
     *
     * @return array
     */
    public function actionFeDeploy()
    {
        $signKey = Yii::$app->request->post('signKey');
        $bucket = Yii::$app->request->post('bucket');

        if ($signKey != $this->module->s3ControllerSignKey) {
            return TsmdResult::formatErr('Error param 1.');
        }
        if (!in_array($bucket, ['www.thirsight.com', 'm.thirsight.com'])) {
            return TsmdResult::formatErr('Error param 2.');
        }
        ini_set('memory_limit','256M');
        set_time_limit(0);

        try {
            $deployDir = Yii::getAlias('@runtime/s3fedeploy');
            $zipFilename = md5($bucket) . '.zip';
            $res = $this->zipExtract($deployDir, $zipFilename);
            if ($res !== true) return $res;

            // 计时，上传并解压耗时
            $uploadTimer = round(microtime(true) - YII_BEGIN_TIME, 3);

            /* @var $config \tsmd\base\aws\Config */
            $config = Yii::$app->get('AWSConfig');
            $s3 = (new \Aws\Sdk())->createS3($config->toArray([
                'endpoint' => 'https://s3-ap-northeast-2.amazonaws.com',
            ]));

            // 找到解压后的文件，上传至 S3
            $zipSize = bcdiv(filesize("{$deployDir}/{$zipFilename}"), 1024 * 1024, 2);
            $zFiles = FileHelper::findFiles($deployDir, ['recursive' => true]);
            $zipVer = '';
            $result = [];
            foreach ($zFiles as $zFile) {
                // S3 Key
                $s3key = str_ireplace("{$deployDir}/", '', $zFile);
                // S3 Key 压缩包布署路径
                if (stripos($zFile, '.zip') !== false) {
                    $s3key = "zipdeploy/{$zipFilename}";
                }
                // 过滤
                if (stripos($s3key, '.') === 0 || stripos($s3key, '/.') !== false ||
                    stripos($s3key, '_') === 0 || stripos($s3key, '/_') !== false) {
                    continue;
                }
                // 获取版本号
                if (stripos($s3key, 'version.html') !== false) {
                    $zipVer = file_get_contents($zFile);
                }
                // 上传至 S3
                $s3->putObject([
                    'Bucket' => $bucket,
                    'Key'  => $s3key,
                    'SourceFile' => $zFile,
                    'ACL' => 'public-read',
                ]);
                $result[] = "{$s3key}";
                @unlink($zFile);
            }
            // 删除上传和解压的文件
            //sleep(5);
            FileHelper::removeDirectory("{$deployDir}");

            // 计时，上传至 S3 时
            $syncS3Timer = round(microtime(true) - YII_BEGIN_TIME - $uploadTimer, 3);

            // 更新 option 参数
            $download = "https://{$bucket}/zipdeploy/{$zipFilename}";
            $this->updateOption($bucket, $zipVer, $zipSize, $download);

            return TsmdResult::formatSuc('model', [
                'fileCounter' => count($result),
                'uploadTimer' => $uploadTimer,
                'syncS3Timer' => $syncS3Timer,
                'zipVersion'  => $zipVer,
                'zipSize'     => $zipSize,
                'zipDownload' => $download,
            ]);
        } catch (\Exception $e) {
            $msg = trim(strrchr($e->getFile(), '/'), '/') . " ({$e->getLine()}): {$e->getMessage()}";
            return TsmdResult::formatErr($msg);
        }
    }

    /**
     * 解压 zip
     * @param string $extractDir
     * @param string $zipFilename
     * @return array|bool
     * @throws \yii\base\Exception
     */
    protected function zipExtract($extractDir, $zipFilename)
    {
        // 上传文件存储目录
        FileHelper::createDirectory($extractDir);

        // 上传文件， 最大 64M (.htaccess 中设置)
        $file = UploadedFile::getInstanceByName('file');
        if ($file->getExtension() != 'zip') {
            return TsmdResult::formatErr('Error param 3.');
        }
        $filepath = "{$extractDir}/{$zipFilename}";
        $file->saveAs($filepath);

        if ($file->getHasError()) {
            return TsmdResult::formatErr("File error: {$file->error}.");
        }

        // 打开 zip 文件并解压
        $zip = new \ZipArchive;
        $zip->open($filepath);
        $zip->extractTo($extractDir);
        $zip->close();
        return true;
    }

    /**
     * @param string $version
     * @param string $download
     */
    protected function updateOption($bucket, $version, $size, $download)
    {
        $bucketOpts = [
            'tsmd.thirsight.com' => [
                'fe_h5_ver' => $version,
                'fe_h5_size' => $size,
                'fe_h5_download' => $download,
            ]
        ];
        if (isset($bucketOpts[$bucket])) {
            foreach ($bucketOpts[$bucket] as $key => $value) {
                Option::updateAll(['value' => $value], ['key' => $key]);
            }
            Option::deleteCacheBy(OptionAppFe::OG_APPFE);
        }
    }
}
