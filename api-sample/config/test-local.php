<?php
$appPath = dirname(__DIR__);

return yii\helpers\ArrayHelper::merge(
    require 'test.php',
    require $tsmdPath . '/yii2-tsmd-base/config/main-local.php'
);
