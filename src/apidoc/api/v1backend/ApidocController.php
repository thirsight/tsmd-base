<?php

namespace tsmd\base\apidoc\api\v1backend;

use tsmd\base\models\TsmdResult;

/**
 * API 文档处理接口
 *
 * @author Haisen <thirsight@gmail.com>
 */
class ApidocController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * API 文档列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/apidoc/v1backend/apidoc/index`
     *
     * ```json
     * {
     *     "tsmdResult": "SUCCESS",
     *     ...
     *     "list": [
     *         {
     *             "name": "doc-base-be",
     *             "url": "https://tsmd.thirsight.com/[[dirname]]"
     *         },
     *         ...
     *     ]
     * }
     * ```
     *
     * @return array
     */
    public function actionIndex()
    {
        return TsmdResult::formatSuc('list', $this->module->getApidocs());
    }

    /**
     * 生成 API 文档并部署至 S3
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/apidoc/v1backend/apidoc/deploy-s3`
     *
     * @param int $isDeployS3
     * @return array
     */
    public function actionDeployS3($isDeployS3 = 1)
    {
        return $this->module->generateApidoc((bool) $isDeployS3);
    }
}
