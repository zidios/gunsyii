<?php

namespace app\controllers;

use app\models\Category;
use app\models\Product;
use app\models\Shop;
use Yii;
use yii\data\Pagination;
use yii\web\NotFoundHttpException;

class CategoryController extends AppController
{
    public function actionView($id = null) {
        $cookieName =!empty(Yii::$app->params['citySelect']['coockieName']) ? Yii::$app->params['citySelect']['coockieName'] : 'usercity';
        // Проверяем куки города, т.к. куки устанавливаются из JS, валидацию они не пройдут, вынимаем их напрямую
        $city = isset($_COOKIE[$cookieName]) ? $_COOKIE[$cookieName]: null;
        $shop_id = null;
        if(!empty($city)){
            $shopData = Shop::findOne(['name'=>$city]);
            if($shopData){
                $shop_id = intVal($shopData->getAttribute('id'));
            }
        }
        $activeCategoryId = $id;
        $activeCategoryIds = array();
        $categories = Category::find()
            ->where(['shop_id' => $shop_id])
            ->orderBy('name')
            ->asArray()
            ->all();
        $categoryTitle = 'Категории';
        $tree = [];
        //TODO: сделать рекурсивное построение дерева
        if(is_array($categories) && count($categories)){
            foreach($categories as $category){
                if($category['id'] == $activeCategoryId){
                    $categoryTitle = $category['name'];
                }
                if(empty($category['parent_ident'])){
                    if(!empty($activeCategoryId) && $activeCategoryId == $category['id']){
                        $activeCategoryIds[] = intval($category['id']);
                    }
                    $tree[$category['identifier']] = $category;
                }
            }
            foreach($categories as $category){
                if(!empty($category['parent_ident']) && isset($tree[$category['parent_ident']])){
                    if(!empty($activeCategoryId) && ($activeCategoryId == $category['id']) || $tree[$category['parent_ident']]['id'] == $activeCategoryId){
                        $activeCategoryIds[] = intval($category['id']);
                    }
                    $subCategory = $category;
                    foreach($categories as $category2){
                        if($category2['parent_ident'] == $category['identifier']){
                            if(!empty($activeCategoryId) &&
                                (
                                    $activeCategoryId == $category2['id'] ||
                                    in_array($subCategory['id'], $activeCategoryIds)
                                )
                            ){
                                $activeCategoryIds[] = intval($category2['id']);
                            }
                            $subCategory['childrens'][] = $category2;
                            $subCategory['childIds'][] = $category2['id'];
                            $tree[$category['parent_ident']]['childIds'][] = $category2['id'];
                        }
                    }
                    $tree[$category['parent_ident']]['childrens'][] = $subCategory;
                    $tree[$category['parent_ident']]['childIds'][] = $subCategory['id'];
                }
            }
        } else {
            throw new NotFoundHttpException('Категория не найдена...');
        }

        if(is_array($activeCategoryIds) && count($activeCategoryIds)){
            $query = Product::find()->where(['shop_id' => $shop_id, 'category_id' => $activeCategoryIds, 'is_deleted'=>0]);
        } else {
            $query = Product::find()->where(['shop_id' => $shop_id]);
        }

            $pages = new Pagination(['totalCount' => $query->count(), 'pageSize' => 20]);
            $products = $query->offset($pages->offset)->limit($pages->limit)->all();



        return $this->render('view', compact('activeCategoryId', 'tree', 'categoryTitle', 'products', 'pages'));
    }
}