<?php

/** @var yii\web\View $this */

use yii\bootstrap5\Carousel;
use yii\bootstrap5\Html;

$this->title = 'Оружейный салон "Охотник"';
?>
<div class="site-index">
    <section class="home-contacts">
        <div class="row">
            <div class="col-12">
                <div class="h-100 bg-image main-banner d-grid">
                    <div class="overlay"></div>
                    <div class="m-auto position-relative">
                        <div class="mb-3 text-center"><?= Html::img('@web/customAssets/images/logo_sq_trans.png', ['alt' => 'logo2']) ?></div>
                        <?php
                        $coockieName = !empty(Yii::$app->params['citySelect']['coockieName']) ? Yii::$app->params['citySelect']['coockieName'] : 'usercity';
                        $city = isset($_COOKIE[$coockieName]) ? $_COOKIE[$coockieName]: null;
                        switch ($city) {
                            case 'Белгород':
                                echo Html::tag('h2', 'Белгород', ['class'=>'display-4 mb-4']);
                                echo '<p class="lead">ул. Преображенская 139<br>+7 4722 33 04 71</p>';
                                echo '<p class="lead">ул. Преображенская 74<br>+7 4722 22 23 24</p>';
                                break;
                            case 'Воронеж':
                                echo Html::tag('h2', 'Воронеж', ['class'=>'display-4 mb-4']);
                                echo '<p class="lead">ул. Южно-Моравская, 27<br>+7 473 257 13 99</p>';
                                break;
                            default:
                                break;
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
        <div class="row">
            <div class="col-6 pe-0">
                <div class="h-100 bg-image d-grid secondary-left"">
                <div class="overlay"></div>
                <div class="m-auto position-relative text-center">
                    <h4>Наличие</h4>
                    <p>Интерактивный прайс-лист</p>
                    <a class="btn btn-outline-secondary px-5" href="#">Белгород</a>
                </div>
            </div>
        </div>
        <div class="col-6 ps-0">
            <div class="h-100 bg-image d-grid secondary-right">
                <div class="overlay"></div>
                <div class="m-auto position-relative text-center">
                    <h4>Наличие</h4>
                    <p>Интерактивный прайс-лист</p>
                    <a class="btn btn-outline-secondary px-5" href="#">Воронеж</a>
                </div>
            </div>
        </div>

    </section>
    <section class="home-slider slider-wrapper text-center bg-transparent mt-3 mb-3">
        <div class="row">
            <div class="col-12">
                <?php
                echo Carousel::widget([
                    'items' => [
                        [
                            'content' => Html::img('@web/customAssets/images/homeSlider/slide1.jpeg', [
                                'class' => 'carousel-image-contain'
                            ]),
                            'caption' => '<h4>Новинка!</h4><p>В ассортименте нашего магазина появились замечательные пневматические винтовки ЭДган Леший 2.</p><a class="btn btn-lg btn-success" href="#">Выбрать модель</a>',
                            'options' => ['alt' => 'Слайд 1'],
                        ],
                        [
                            'content' => Html::img('@web/customAssets/images/homeSlider/slide2.png', [
                                'class' => 'carousel-image-contain'
                            ]),
                            'options' => ['alt' => 'Слайд 2'],
                        ],
                        [
                            'content' => Html::img('@web/customAssets/images/homeSlider/slide3.jpg', [
                                'class' => 'carousel-image-contain'
                            ]),
                            'options' => ['alt' => 'Слайд 3'],
                        ],
                        [
                            'content' => Html::img('@web/customAssets/images/homeSlider/slide4.jpg', [
                                'class' => 'carousel-image-contain'
                            ]),
                            'options' => ['alt' => 'Слайд 4'],
                        ],
                        [
                            'content' => Html::img('@web/customAssets/images/homeSlider/slide5.png', [
                                'class' => 'carousel-image-contain'
                            ]),
                            'options' => ['alt' => 'Слайд 5'],
                        ],

                    ],
                    'options' => [
                        'class' => 'center-slider carousel-with-mask',
                    ],
                    'clientOptions' => [
                        'interval' => 3000,
                        'ride' => 'carousel',
                        'wrap' => true,
                        'keyboard' => true,
                        'touch' => true
                    ]
                ]);
                ?>
            </div>
        </div>

    </section>


    <section class="body-content">
        <div class="warning-block">
            <div class="row">
                <div class="col-12 text-center mb-3">
                    <h3>Обращаем ваше внимание, мы НЕ торгуем запасными частями для оружия.</h3>
                    <h3>Приобрести оружие или боеприпасы в наших магазинах, вы можете только при наличии лицензии и паспорта.</h3>
                </div>
            </div>
        </div>
        <div class="partners-block">
            <div class="row">
                <div class="col-12 text-center mb-3">
                    <h2>Наши партнёры</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner1.jpg', ['alt' => 'Калашников']),['href'=>'https://kalashnikovgroup.ru/']) ?>
                </div>
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner2.png', ['alt' => 'EDgun']),['href'=>'https://edgun.ru/']) ?>
                </div>
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner3.png', ['alt' => 'Левша']),['href'=>'https://www.levsha.spb.ru/']) ?>
                </div>
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner4.jpg', ['alt' => 'Арсенал']),['href'=>'https://arsenal-arms.ru/']) ?>
                </div>
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner5.png', ['alt' => 'Inf iRay']),['href'=>'https://infiray.ru/']) ?>
                </div>
                <div class="col-lg-4 col-sm-6 mb-3 text-center partners-item">
                    <?= Html::tag('a', Html::img('@web/customAssets/images/partners/partner6.jpg', ['alt' => 'Кольчуга']),['href'=>'https://www.kolchuga.ru/']) ?>
                </div>
            </div>
        </div>
        <div class="special-block">
            <div class="row">
                <div class="col-12 text-center mb-3">
                    <h2 class="my-3">Доставка спецсвязью</h2>
                    <?= Html::img('@web/customAssets/images/partners/spec.jpg', ['alt' => 'Спецсвязь']) ?>
                    <p class="mt-3">Обращаем внимание, приобретённую продукцию в наших магазинах можно доставить спецсвязью в любой регион на территории РФ.</p>
                </div>
            </div>
        </div>
    </section>
</div>
