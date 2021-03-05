<?php

namespace frontend\controllers\demo;

use common\cores\FrontendController;

class DefaultController extends FrontendController
{
    public function actionIndex()
    {
        echo 'in demo';
    }
}