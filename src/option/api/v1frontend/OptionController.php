<?php

namespace tsmd\base\option\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\option\models\Option;
use tsmd\base\option\models\OptionSite;

/**
 * OptionController implements the CRUD actions for Option model.
 */
class OptionController extends \tsmd\base\controllers\RestFrontendController
{
    /**
     * @var array
     */
    protected $authExcept = ['site'];

    /**
     * 站点配置参数
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/option/v1frontend/option/site`
     *
     * ```json
     * {
     *   "tsmdResult": "SUCCESS",
     *   "code": 200,
     *   "name": "app",
     *   "type": "model",
     *   "message": "",
     *   "model": {
     *   },
     *   "list": [],
     *   "listInfo": {}
     * }
     * ```
     *
     * @param integer $init
     * @return array
     */
    public function actionSite()
    {
        return TsmdResult::formatSuc('model', Option::getValuesBy(OptionSite::OG_SITE));
    }
}
