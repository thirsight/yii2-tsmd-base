<?php

namespace tsmd\base\option\api\v1backend;

use tsmd\base\models\TsmdResult;
use tsmd\base\option\models\Option;
use tsmd\base\option\models\OptionSearch;
use tsmd\base\option\models\OptionSite;

/**
 * OptionController implements the CRUD actions for Option model.
 */
class OptionController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * Option 列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * optid    | [[integer]] | No | optid
     * optKey   | [[string]]  | No | 键
     * optGroup | [[string]]  | No | 组
     *
     * @return array
     */
    public function actionSearch()
    {
        list($opts, $count) = ($search = new OptionSearch)->search($this->getQueryParams(), true);
        return $search->hasErrors()
            ? TsmdResult::failed($search->firstErrors)
            : TsmdResult::response($opts, ['count' => $count]);
    }

    /**
     * 初始化 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/init-site`
     *
     * @return array
     */
    public function actionInitSite()
    {
        list($options, $error) = OptionSite::initBy();
        return $error ? TsmdResult::failed($error) : TsmdResult::response($options);
    }

    /**
     * 更新 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/update`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * optid    | [[integer]] | Yes | optid
     * optValue | [[string]]  | No  | 键值
     * optData  | [[array]]   | No  | 数组
     * optSort  | [[string]]  | No  | 排序
     *
     * @return array
     */
    public function actionUpdate()
    {
        $opt = $this->findModel($this->getBodyParams('optid'));
        $opt->load($this->getBodyParams(), '');
        $opt->update(true, ['optValue', 'optData', 'optSort', 'updatedTime']);
        return $opt->hasErrors()
            ? TsmdResult::failed($opt->firstErrors)
            : TsmdResult::response($opt->toArray());
    }

    /**
     * 删除 Option
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/option/v1backend/option/delete`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * optid    | [[integer]] | Yes | optid
     *
     * @return array
     */
    public function actionDelete()
    {
        $this->findModel($this->getBodyParams('optid'))->delete();
        return TsmdResult::response();
    }

    /**
     * @param string $id
     * @return Option the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($optid)
    {
        if (is_numeric($optid) && ($model = Option::findOne($optid)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `option` does not exist.');
        }
    }
}
