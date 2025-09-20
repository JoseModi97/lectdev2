<?php

namespace app\modules\v1\controllers;

use yii\rest\Controller;
use yii\filters\ContentNegotiator;
use yii\web\Response;

class BaseController extends Controller
{
    public function behaviors()
    {
        $behaviors = parent::behaviors();
        $behaviors['contentNegotiator'] = [
            'class' => ContentNegotiator::class,
            'formats' => [
                'application/json' => Response::FORMAT_JSON,
            ],
        ];
        return $behaviors;
    }
}
