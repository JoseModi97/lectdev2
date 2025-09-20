<?php

/* @var $this \yii\web\View */
/* @var $content string */

use yii\helpers\Html;
use app\assets\AppAsset;
use kartik\icons\Icon;
use raoul2000\bootswatch\BootswatchAsset;

BootswatchAsset::$theme = 'yeti';
AppAsset::register($this);
Icon::map($this);
?>

<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>">
<head>
    <meta charset="<?= Yii::$app->charset ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link rel="shortcut icon" href="<?=Yii::getAlias('@web');?>/img/logo.png" type="image/x-icon">
    <link rel="icon" href="<?=Yii::getAlias('@web');?>/img/logo.png" type="image/x-icon">
    <?= Html::csrfMetaTags() ?>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <link href="<?=Yii::getAlias('@web');?>/css/login.css" rel="stylesheet">
</head>
<body>
<?php $this->beginBody() ?>

<div class="container">
    <?= $content ?>
</div>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
