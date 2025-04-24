<?php

namespace app\controllers;

use app\models\Category;
use app\models\Product;
use yii\web\NotFoundHttpException;

class ProductController extends AppController
{
    public function actionView($id){
        $product = Product::findOne(['id' => $id, 'is_deleted'=>0]);
        if(empty($product)){
            throw new NotFoundHttpException('Такого продукта не сущетвует...');
        }
        $parents = Category::getParents($product->getAttribute('category_id'));
        return $this->render('view', compact('product', 'parents'));

    }
}