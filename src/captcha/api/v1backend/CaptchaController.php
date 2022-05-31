<?php

namespace tsmd\base\captcha\api\v1backend;

use tsmd\base\models\TsmdResult;
use tsmd\base\captcha\models\Captcha;
use tsmd\base\captcha\models\CaptchaQuery;

/**
 * 验证码管理接口
 */
class CaptchaController extends \tsmd\base\controllers\RestBackendController
{
    /**
     * 验证码列表
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/captcha/v1backend/captcha/search`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * target   | [[string]]  | No | 发送目标手机号或邮箱
     * type     | [[string]]  | No | 验证码类型
     * uid      | [[integer]] | No | 用户 UID
     * ip       | [[string]]  | No | IP
     *
     * @param string $target
     * @param string $type
     * @param string $uid
     * @param string $ip
     * @return array
     */
    public function actionSearch($uid = '', $target = '', $type = '', $ip = '')
    {
        $query = new CaptchaQuery();
        $query->where(['>', 'updatedTime', time() - 86400 * 7])
            ->andFilterWhere(['target' => $target])
            ->andFilterWhere(['type' => $type])
            ->andFilterWhere(['uid' => $uid])
            ->andFilterWhere(['ip' => $ip]);

        $count = $query->count();
        $rows  = $query->addPaging()->orderBy('capid DESC')->all();
        return TsmdResult::response($rows, ['count' => $count]);
    }

    /**
     * 重置验证码记录
     *
     * <kbd>API</kbd> <kbd>GET</kbd> <kbd>AUTH</kbd> `/captcha/v1backend/captcha/view`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * capid   | [[string]] | Yes | capid
     *
     * @param string $capid
     * @return array
     */
    public function actionView($capid)
    {
        $row = $this->findModel($capid)->toArray();
        unset($row['capcode']);
        return TsmdResult::responseModel($row);
    }

    /**
     * 重置验证码记录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/captcha/v1backend/captcha/reset`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * capid   | [[string]] | Yes | capid
     *
     * @return array
     */
    public function actionReset()
    {
        $model = $this->findModel($this->getBodyParams('capid'));
        $model->resetCapcode(true);
        $model->update();
        return TsmdResult::response();
    }

    /**
     * 删除验证码记录
     *
     * <kbd>API</kbd> <kbd>POST</kbd> <kbd>AUTH</kbd> `/captcha/v1backend/captcha/delete`
     *
     * Argument | Type | Required | Description
     * -------- | ---- | -------- | -----------
     * capid   | [[string]] | Yes | capid
     *
     * @return array
     */
    public function actionDelete()
    {
        $this->findModel($this->getBodyParams('capid'))->delete();
        return TsmdResult::response();
    }

    /**
     * @param string $capid
     * @return Captcha the loaded model
     * @throws \yii\web\NotFoundHttpException if the model cannot be found
     */
    protected function findModel($capid)
    {
        if (is_numeric($capid) && ($model = Captcha::findOne($capid)) !== null) {
            return $model;
        } else {
            throw new \yii\web\NotFoundHttpException('The requested `captcha` does not exist.');
        }
    }
}
