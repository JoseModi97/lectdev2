<?php
/**
 * @author Rufusy Idachi <idachirufus@gmail.com>
 */

/* @var View $this */
/* @var string $content */

use app\assets\AppAsset;
use app\assets\JSTreeAsset;
use kartik\growl\Growl;
use kartik\icons\Icon;
use raoul2000\bootswatch\BootswatchAsset;
use yii\base\InvalidConfigException;
use yii\bootstrap\Nav;
use yii\bootstrap\NavBar;
use yii\helpers\Html;
use yii\helpers\Url;
use yii\web\ServerErrorHttpException;
use yii\web\View;
use yii\widgets\Breadcrumbs;

BootswatchAsset::$theme = 'yeti';
AppAsset::register($this);

try {
    Icon::map($this);
} catch (InvalidConfigException $ex) {
    $message = $ex->getMessage();
    if(YII_ENV_DEV){
        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
    }
    throw new ServerErrorHttpException($message, 500);
}

if(!Yii::$app->user->isGuest){
    JSTreeAsset::register($this);
}

$roleItems = [];
foreach (Yii::$app->session->get('roles') as $role){
    $roleItems[] = ['label' => $role];
}
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
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php $this->head() ?>
</head>
<body>
<?php $this->beginBody() ?>

<div class="wrap">
    <?php
    NavBar::begin([
        'brandLabel' => '<span style="display: flex"><img src="'.Yii::getAlias('@web') .
            '/img/logo.png" alt="UoN" class="img-responsive" style="height:35.5px;" />&nbsp;<span style="margin: auto 0;"> ' .
            Yii::$app->params['sitename_short'].'</span></span>',
        'brandUrl' => Yii::$app->homeUrl,
        'brandOptions' => [
            'style' => 'padding: 5px 15px;',
        ],
        'options' => [
            'class' => 'navbar navbar-inverse navbar-expand-lg navbar-dark bg-primary',
        ],
    ]);

    try {
        echo Nav::widget([
            'encodeLabels' => false,
            'options' => ['class' => 'navbar-nav navbar-right'],
            'items' => [
                [
                    'label' => Icon::show('user-tie fa-lg') . ' ' . Yii::$app->user->identity->EMP_TITLE . ' ' .
                        Yii::$app->user->identity->SURNAME,
                ],
                [
                    'label' => '<i class="fas fa-cogs"></i> My roles',
                    'items' => $roleItems
                ],
                [
                    'label' => '<i class="fas fa-book"></i> Download user guide',
                    'url' => Url::to(['/site/download-manual']),
                    'linkOptions' => ['target' => '_blank']
                ],
                [
                    'label' => '<i class="fas fa-sign-out-alt"></i> Logout',
                    'url' => Url::to(['/site/logout'])
                ],
            ],
        ]);
    } catch (Exception $ex) {
        $message = $ex->getMessage();
        if(YII_ENV_DEV){
            $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
        }
        throw new ServerErrorHttpException($message, 500);
    }

    NavBar::end();
    ?>

    <div class="container">
        <?php try {
            echo Breadcrumbs::widget(['links' => $this->params['breadcrumbs'] ?? []]);
            echo $content;
        } catch (Exception $ex) {
            $message = $ex->getMessage();
            if(YII_ENV_DEV){
                $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
            }
            throw new ServerErrorHttpException($message, 500);
        }
        ?>
    </div>
</div>

<?php
$flashType = '';
$flashTitle = '';
$flashIcon = '';
$flashes = Yii::$app->session->getAllFlashes();
if(!empty($flashes)){
    if(!empty($flashes['new'])) {
        $flashMessage = $flashes['new']['message'];

        if ($flashes['new']['type'] === 'success') {
            $flashType = Growl::TYPE_SUCCESS;
            $flashTitle = 'Well done!';
            $flashIcon = 'fas fa-check-circle';
        }

        if ($flashes['new']['type'] === 'danger') {
            $flashType = Growl::TYPE_DANGER;
            $flashTitle = 'Oh snap!';
            $flashIcon = 'fas fa-times-circle';
        }

        try {
            echo Growl::widget([
                'type' => $flashType,
                'title' => $flashTitle,
                'icon' => $flashIcon,
                'body' => $flashMessage,
                'showSeparator' => true,
                'delay' => 0,
                'pluginOptions' => [
                    'showProgressbar' => false,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ]
            ]);
        } catch (Exception $e) {
        }
    }

    if(!empty($flashes['added'])){
        foreach ($flashes['added'] as $addedFlash){
            $flashMessage = $addedFlash['message'];
            if($addedFlash['type'] === 'success'){
                $flashType = Growl::TYPE_SUCCESS;
                $flashTitle = 'Well done!';
                $flashIcon = 'fas fa-check-circle';
            }
            if($addedFlash['type'] === 'danger'){
                $flashType = Growl::TYPE_DANGER;
                $flashTitle = 'Oh snap!';
                $flashIcon = 'fas fa-times-circle';
            }

            try {
                echo Growl::widget([
                    'type' => $flashType,
                    'title' => $flashTitle,
                    'icon' => $flashIcon,
                    'body' => $flashMessage,
                    'showSeparator' => true,
                    'delay' => 0,
                    'pluginOptions' => [
                        'showProgressbar' => false,
                        'placement' => [
                            'from' => 'top',
                            'align' => 'right',
                        ]
                    ]
                ]);
            } catch (Exception $e) {
            }
        }
    }
}
?>

<footer class="footer">
    <div class="container">
        <p class="pull-left">Need help? <a href="mailto:lec_support@uonbi.ac.ke">lec-support@uonbi.ac.ke</a></p>
        <p class="pull-right">&copy; ICT Centre <?= date('Y') ?></p>
    </div>
</footer>

<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
