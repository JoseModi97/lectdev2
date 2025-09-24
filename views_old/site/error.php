<?php

/* @var $this yii\web\View */
/* @var $name string */
/* @var $message string */
/* @var $exception Exception */

use yii\helpers\Html;

$this->title = 'Error | ' . $name;

$this->params['breadcrumbs'][] = $this->title;

$exception = Yii::$app->errorHandler->exception;
?>

<div class="container-fluid">
    <div class="row-fluid">
        <div class="col-lg-12">
            <div class="centering text-center error-container">
                <div class="text-center">
                    <h2 class="text-primary"><?= Html::encode($name) ?></h2>
                    <br />
                    <br />
                    <p class="without-margin text-danger"><?= nl2br(Html::encode($message)) ?></p>
                </div>
                <br />
                <br />
                <div class="text-center">
                    <h3><small>The above error occurred while the Web server was processing your request.</small></h3>

                    <?php if (YII_ENV_DEV): ?>
                        <button type="button" class="btn btn-xs btn-warning" data-toggle="collapse" data-target="#error-trace">View Detailed Error Trace</button>
                        <div id="error-trace" class="collapse text-left bg-secondary">
                            <small class="without-margin text-warning">Error at line #<?= nl2br(Html::encode($exception->getLine())) ?> of
                                <?= nl2br(Html::encode($exception->getFile())) ?></small><br />
                            <small class="without-margin text-danger"><?= nl2br(Html::encode($exception->getTraceAsString())) ?></small>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>