<?php

use yii\helpers\Html;

$formatter = Yii::$app->formatter;
$image_path =  $product['image_filename'] ? Yii::getAlias('@web/images/products') . '/' . $product['image_filename'] : Yii::getAlias('@web/images/products') . '/' . 'no-image.png';
?>
<div class="site-index productPage">
    <h1 class="page_title"><?= $product->getAttribute('name') ?></h1>
    <div class="row">
        <div class="col-12 mb-3">
            <div class="breadcrumbs">
                <?php
                echo Html::a(
                    'Главная',
                    [Yii::$app->homeUrl]
                );
                if(isset($parents) && count($parents)){
                    $parents = array_reverse($parents);
                    foreach ($parents as $parent) {
                        echo Html::tag('span', '>');
                        echo Html::a(
                            mb_convert_case(Html::encode($parent['name']), MB_CASE_TITLE, "UTF-8"),
                            ['/category/view', 'id' => $parent['id']]
                        );
                    }
                }
                ?>
            </div>
        </div>
        <div class="col-md-8 col-sm-12">
            <div class="productImage" style='background-image: url( <?= $image_path; ?> )'>
            </div>
        </div>
        <div class="col-md-4 col-sm-12">
            <div class="productArticle">
                Артикул: <span><?= $product->getAttribute('article') ?></span>
            </div>
            <div class="productPrice">
                Цена: <span><?= $formatter->asInteger($product->getAttribute('price')) ?> ₽</span>
            </div>
        </div>
        <div class="col-12 mt-5 productDescription">
            <?= $product->getAttribute('description') ; ?>
        </div>
    </div>
</div>