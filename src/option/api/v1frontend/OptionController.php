<?php

namespace tsmd\base\option\api\v1frontend;

use tsmd\base\models\TsmdResult;
use tsmd\base\option\models\OptionQuery;

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
     * @param integer $init
     * @return array
     */
    public function actionSite()
    {
        $rows = (new OptionQuery)
            ->select('optKey, optValue, optData')
            ->andWhereIn('optGroup', 'site')
            ->allWithFormat();
        $kvs = [];
        foreach ($rows as $r) {
            $kvs[$r['optKey']] = $r['optData'] ?: $r['optValue'];
        }
        return TsmdResult::responseModel($kvs);
    }
}
