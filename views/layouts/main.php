<?php

/** @var yii\web\View $this */
/** @var string $content */

use app\assets\AppAsset;
use app\models\Category;
use app\models\Shop;
use app\widgets\Alert;
use yii\bootstrap5\Breadcrumbs;
use yii\bootstrap5\Html;
use yii\bootstrap5\Nav;
use yii\bootstrap5\NavBar;
use yii\helpers\Url;

AppAsset::register($this);

$this->registerCsrfMetaTags();
$this->registerMetaTag(['charset' => Yii::$app->charset], 'charset');
$this->registerMetaTag(['name' => 'viewport', 'content' => 'width=device-width, initial-scale=1, shrink-to-fit=no']);
$this->registerMetaTag(['name' => 'description', 'content' => $this->params['meta_description'] ?? '']);
$this->registerMetaTag(['name' => 'keywords', 'content' => $this->params['meta_keywords'] ?? '']);
?>
<?php $this->beginPage() ?>
<!DOCTYPE html>
<html lang="<?= Yii::$app->language ?>" class="h-100">
<head>
    <title><?= Html::encode($this->title) ?: 'Оружейный салон "Охотник"' ?></title>
    <?php $this->head() ?>
    <link rel="icon" type="image/png" sizes="32x32" href="<?= Yii::getAlias('@web') ?>/cropped-favicon-2-32x32.png">
    <link rel="icon" type="image/png" sizes="192x192" href="<?= Yii::getAlias('@web') ?>/cropped-favicon-2-192x192.png">
    <link rel="apple-touch-icon" sizes="180x180" href="<?= Yii::getAlias('@web') ?>/cropped-favicon-2-180x180.png">
</head>
<body class="d-flex flex-column h-100">
<?php $this->beginBody() ?>

<header id="header">
    <?php
    $coockieName = !empty(Yii::$app->params['citySelect']['coockieName']) ? Yii::$app->params['citySelect']['coockieName'] : 'usercity';
    $city = isset($_COOKIE[$coockieName]) ? $_COOKIE[$coockieName]: null;
    $shop_id = null;
    if(!empty($city)){
        $shopData = Shop::findOne(['name'=>$city]);
        if($shopData){
            $shop_id = intVal($shopData->getAttribute('id'));
        }
    }
    NavBar::begin([
        'brandLabel' => Html::img('@web/customAssets/images/logo.png', [
            'alt' => Yii::$app->name,
            'style' => 'height:40px;'
        ]),
        'brandUrl' => Yii::$app->homeUrl,
        'options' => ['class' => 'navbar-expand-md navbar-dark sec-bg-color fixed-top']
    ]);
    $oruzhie = '#';
    $optika = '#';
    $teplovizory = '#';
    if($shop_id){
        $categories = Category::find()->where(['shop_id' => $shop_id])->andWhere(['is_deleted'=>0])->andWhere(['name' => ['Оружие', 'Оптика', 'Тепловизоры']])->asArray()->indexBy('name')->all();
        if(isset($categories['ОРУЖИЕ']) && isset($categories['ОРУЖИЕ']['id'])){
            $oruzhie = Url::to(['category/view', 'id' => $categories['ОРУЖИЕ']['id']]);
        }
        if(isset($categories['ОПТИКА']) && isset($categories['ОПТИКА']['id'])){
            $optika = Url::to(['category/view', 'id' => $categories['ОПТИКА']['id']]);
        }
        if(isset($categories['ТЕПЛОВИЗОРЫ']) && isset($categories['ТЕПЛОВИЗОРЫ']['id'])){
            $teplovizory = Url::to(['category/view', 'id' => $categories['ТЕПЛОВИЗОРЫ']['id']]);
        }
    }
    echo Nav::widget([
        'options' => ['class' => 'navbar-nav'],
        'items' => [
            ['label' => 'Оружие', 'url' => [$oruzhie]],
            ['label' => 'Стрелковый тир', 'url' => ['#']],
            ['label' => 'Оптика', 'url' => [$optika]],
            ['label' => 'Тепловизоры', 'url' => [$teplovizory]],
            ['label' => 'О компании', 'url' => [Url::to(['home/about'])]],
            ['label' => 'Контакты', 'url' => [Url::to(['home/contacts'])]]
        ]
    ]);

    echo Html::tag('div', 'Ваш город: ' . Html::tag('span', $city, ['class' => 'inner-span']), [
        'class' => 'ms-auto navbar-text text-white city-select'
    ]);
    NavBar::end();
    ?>
</header>

<main id="main" class="flex-shrink-0" role="main">
    <div class="container">
        <?php if (!empty($this->params['breadcrumbs'])): ?>
            <?= Breadcrumbs::widget(['links' => $this->params['breadcrumbs']]) ?>
        <?php endif ?>
        <?= Alert::widget() ?>
        <?= $content ?>
    </div>
</main>

<footer id="footer" class="mt-auto">
    <div class="footer-content py-3 sec-bg-color">
        <div class="container">
            <div class="row">
                <div class="col-lg-3 col-sm-6 mb-3">
                    <h4>Компания</h4>
                    <div class="links-wrapper d-grid">
                        <?=  Html::tag('a', 'О компании',['href'=>Url::to(['home/about'])]) ?>
                        <?=  Html::tag('a', 'Контакты',['href'=>Url::to(['home/contacts'])]) ?>
                        <?=  Html::tag('a', 'Политика конфиденцальности',['href'=>Url::to(['home/privacy'])]) ?>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <h4>Покупателям</h4>
                    <div class="links-wrapper d-grid">
                        <?=  Html::tag('a', 'Оплата',['href'=>Url::to(['home/payment'])]) ?>
                        <?=  Html::tag('a', 'Доставка',['href'=>Url::to(['home/delivery'])]) ?>
                        <?=  Html::tag('a', 'Подарочные сертификаты',['href'=>Url::to(['home/giftcards'])]) ?>
                    </div>
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <h4>Белгород</h4>
                    <p>ул. Преображенская 139<br>+7 4722 33 04 71<br>
                        ул. Преображенская 74<br>+7 4722 22 23 24</p>
                    <?=  Html::tag('a', 'belohotnik@mail.ru',['href'=>'mailto:belohotnik@mail.ru?subject=Сайт Охотник']) ?>
                </div>
                <div class="col-lg-3 col-sm-6 mb-3">
                    <h4>Воронеж</h4>
                    <p>ул. Южно-Моравская 27, стр.3<br>
                        +7 473 257 13 99<br>
                        +7 473 257 13 00</p>
                    <?=  Html::tag('a', 'belohotnik@mail.ru',['href'=>'mailto:belohotnik@mail.ru?subject=Сайт Охотник']) ?>
                </div>
            </div>
        </div>
    </div>
    <div class="after-footer bg-transparent py-3">
        <div class="container">
            <div class="row">
                <div class="col12 text-center">&copy; "Охотник" <?= date('Y') ?></div>
            </div>
        </div>
    </div>
</footer>
<?= \app\widgets\CitySelectorWidget::widget() ?>
<?php $this->endBody() ?>
</body>
</html>
<?php $this->endPage() ?>
