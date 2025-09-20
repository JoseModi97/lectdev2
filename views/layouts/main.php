<?php

/** @var yii\web\View $this */
/** @var string $content */

use yii\helpers\Url;
use app\widgets\Alert;
use kartik\icons\Icon;
use yii\bootstrap5\Nav;
use app\assets\AppAsset;
use yii\bootstrap5\Html;
use kartik\widgets\Growl;
use yii\bootstrap5\NavBar;
use app\helpers\GraduateHelper;
use yii\bootstrap5\Breadcrumbs;
use yii\web\ServerErrorHttpException;
use yii\bootstrap5\BootstrapIconAsset;


if (Yii::$app->user->isGuest) {
    return Yii::$app->response->redirect(['/site/login']);
}

AppAsset::register($this);
BootstrapIconAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
$this->registerLinkTag(['rel' => 'icon', 'type' => 'image/x-icon', 'href' => Yii::getAlias('@web/favicon.ico')]);



$roleItems = [];
foreach (Yii::$app->session->get('roles') ?? [] as $role) {
    $roleItems[] = ['label' => $role];
}
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">

<head>
    <title><?= Html::encode($this->title) ?></title>
    <?php $this->head() ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <?php
    $this->registerAssetBundle(\kartik\editable\EditableAsset::class);
    $this->registerAssetBundle(\kartik\popover\PopoverXAsset::class);
    ?>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.2.0/css/all.min.css" integrity="sha512-6c4nX2tn5KbzeBJo9Ywpa0Gkt+mzCzJBrE1RB6fmpcsoN+b/w/euwIMuQKNyUoU/nToKN3a8SgNOtPrbW12fug==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/pdf.js/2.6.347/pdf.min.js"></script>

    <style>
        #footer {
            /* Targeting the existing footer ID */
            position: fixed;
            bottom: 0;
            width: 100%;
            z-index: 1030;
            /* Bootstrap navbar z-index is 1030 */
            background-color: #f8f9fa;
            /* Using Bootstrap's bg-light color */
            box-shadow: 0 -2px 5px rgba(0, 0, 0, .1);
            /* Optional: subtle shadow */
        }

        body {
            padding-bottom: 60px;
            /* Adjust this value based on the actual footer height */
        }
    </style>
</head>

<body class="d-flex flex-column h-100 wrap">
    <?php $this->beginBody() ?>

    <div class="w-100">
        <header id="header">
            <div class="container-navbar">
                <?php
                NavBar::begin([
                    'brandLabel' => Html::img('@web/img/logo.png', [
                        'alt' => Yii::$app->name,
                        'style' => 'height: 25px; margin-right: 10px;  '
                    ]) . Yii::$app->params['sitename'],
                    'brandUrl' => Yii::$app->homeUrl,
                    'options' => [
                        'class' => 'navbar navbar-expand-md navbar-custom fixed-top',
                        'style' => 'max-height:45px;'
                    ],
                ]);

                try {
                    echo Nav::widget([
                        'encodeLabels' => false,
                        'options' => ['class' => 'navbar-nav ms-auto'],
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
                                'label' => '<i class="fas fa-sign-out-alt"></i> Logout',
                                'url' => Url::to(['/site/logout']),
                                'linkOptions' => [
                                    'data-method' => 'post',
                                    'data-confirm' => 'Are you sure you want to logout?'
                                ]
                            ],
                        ],
                    ]);
                } catch (Exception $ex) {
                    $message = $ex->getMessage();
                    if (YII_ENV_DEV) {
                        $message = $ex->getMessage() . ' File: ' . $ex->getFile() . ' Line: ' . $ex->getLine();
                    }
                    throw new ServerErrorHttpException($message, 500);
                }

                NavBar::end();
                ?>

            </div>
        </header>

        <?php
        if (Yii::$app->session->hasFlash('title')) {
            echo GraduateHelper::growl(Yii::$app->session->hasFlash('title'), Yii::$app->session->getFlash('body'));
        }


        ?>

        <?php if (Yii::$app->session->hasFlash('success')): ?>

            <?php

            echo Growl::widget([
                'type' => Growl::TYPE_SUCCESS,
                'title' => 'Well done!',
                'icon' => 'bi bi-check-lg',
                'iconOptions' => ['class' => 'img-circle pull-left'],
                'body' => Yii::$app->session->getFlash('success'),
                'showSeparator' => true,
                'delay' => 0,
                'pluginOptions' => [
                    'showProgressbar' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ]
            ]);
            ?>
        <?php elseif (Yii::$app->session->hasFlash('error')): ?>
            <?php
            echo Growl::widget([
                'type' => Growl::TYPE_DANGER,
                'title' => 'Oh snap!',
                'icon' => 'fas fa-times-circle',
                'body' => Yii::$app->session->getFlash('error'),
                'showSeparator' => true,
                'delay' => 4500,
                'pluginOptions' => [
                    'showProgressbar' => true,
                    'placement' => [
                        'from' => 'top',
                        'align' => 'right',
                    ]
                ]
            ]);
            ?>

        <?php endif; ?>
        <main id="main" role="main">
            <div class="container px-4">
                <div class="container-main">
                    <?= $content ?>
                </div>
            </div>
        </main>

    </div>

    <footer id="footer" class="py-3 bg-light">
        <div class="container">
            <div class="row text-muted">
                <div class="copyright text-center text-muted mt-1">
                    &copy; University of Nairobi <?= date('Y') ?>. All Rights Reserved.

                </div>
            </div>
        </div>
    </footer>

    <?php $this->endBody() ?>
</body>



</html>
<?php $this->endPage() ?>