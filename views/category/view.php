<?php


use app\assets\CategoryAsset;
use yii\helpers\Html;
use yii\i18n\Formatter;

CategoryAsset::register($this);

?>
<div class="site-index">
    <h1 class="page_title"><?= $categoryTitle ?></h1>
    <div class="row">
        <div class="col-md-3 col-sm-12">
            <div class="sidebar-categories">
                <ul class="category-list level-1">
                    <?php foreach ($tree as $category): ?>
                        <li class="category-item <?= ($activeCategoryId == $category['id'] || (isset($category['childIds']) && is_array($category['childIds']) && in_array($activeCategoryId, $category['childIds'])))  ? 'active' : '' ?>">
                            <div class="category-header">
                                <?= Html::a(
                                    $category['name'],
                                    ['/category/view', 'id' => $category['id']],
                                    ['class' => 'category-link']
                                ) ?>
                                <?php if (!empty($category['childrens'])): ?>
                                    <span class="category-toggle">
                            <i class="fas fa-chevron-down"></i>
                        </span>
                                <?php endif; ?>
                            </div>

                            <?php if (!empty($category['childrens'])): ?>
                                <ul class="subcategory-list level-2">
                                    <?php foreach ($category['childrens'] as $subcategory): ?>
                                        <li class="subcategory-item <?= ($activeCategoryId == $subcategory['id'] || (isset($subcategory['childIds']) && is_array($subcategory['childIds']) && in_array($activeCategoryId, $subcategory['childIds']))) ? 'active' : '' ?>">
                                            <div class="subcategory-header">
                                                <?= Html::a(
                                                    $subcategory['name'],
                                                    ['/category/view', 'id' => $subcategory['id']],
                                                    ['class' => 'subcategory-link']
                                                ) ?>
                                                <?php if (!empty($subcategory['childrens'])): ?>
                                                    <span class="subcategory-toggle">
                                            <i class="fas fa-chevron-down"></i>
                                        </span>
                                                <?php endif; ?>
                                            </div>

                                            <?php if (!empty($subcategory['childrens'])): ?>
                                                <ul class="nested-subcategory-list level-3">
                                                    <?php foreach ($subcategory['childrens'] as $nestedSubcategory): ?>
                                                        <li class="nested-subcategory-item <?= $activeCategoryId == $nestedSubcategory['id'] ? 'active' : '' ?>">
                                                            <?= Html::a(
                                                                $nestedSubcategory['name'],
                                                                ['/category/view', 'id' => $nestedSubcategory['id']],
                                                                ['class' => 'nested-subcategory-link']
                                                            ) ?>
                                                        </li>
                                                    <?php endforeach; ?>
                                                </ul>
                                            <?php endif; ?>
                                        </li>
                                    <?php endforeach; ?>
                                </ul>
                            <?php endif; ?>
                        </li>
                    <?php endforeach; ?>
                </ul>
            </div>
        </div>
        <div class="col-md-9 col-sm-12">
            <div class="row">
                <?php
                if(is_array($products) && count($products) > 0){
                    $formatter = Yii::$app->formatter;

                    foreach ($products as $product){
                        $image_path =  $product['image_filename'] ? Yii::getAlias('@web/images/products') . '/' . $product['image_filename'] : Yii::getAlias('@web/images/products') . '/' . 'no-image.png';
                        ?>
                        <div class="col-lg-3 col-md-6 col-sm-12 productBox">
                            <?= Html::a(
                                    Html::tag( 'div', '', ['style' => 'background-image: url('.$image_path.')', 'class' => 'productImage']) .
                                    '<div class="productTitle">'.$product['name'].'</div>
                                    <div class="productPrice">' . $formatter->asInteger($product['price']).' ₽</div>
                                    <div class="productText">В наличии</div>',
                                    ['product/view', 'id' => $product['id']],
                                    ['class' => 'productItem']) ?>
                        </div>
                        <?php
                    }
                   echo Html::tag('div', \yii\widgets\LinkPager::widget(['pagination' => $pages]), ['class' => 'col-12 mt-5']);
                } else {
                    ?>
                    <div class="col-12">
                        <p>
                            Товары в данной категории отсутствуют...
                        </p>
                    </div>
                    <?php
                }
                ?>
            </div>

        </div>
    </div>
</div>