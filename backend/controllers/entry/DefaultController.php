<?php
/**
 * 控制器层Demo
 */

namespace backend\controllers\entry;

use common\cores\BackendController;

class DefaultController extends BackendController
{
    public function actionIndex()
    {
        echo 'in backend module';
        return $this->render("default");
    }
}