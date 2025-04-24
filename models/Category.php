<?php

namespace app\models;

use Yii;
use yii\db\ActiveRecord;

class Category extends ActiveRecord
{

    public static function tableName(){
        //На всякий случай
        return '{{category}}';
    }

    public static function getParents($categoryId)
    {
        $cacheKey = "category_parents_{$categoryId}";
        $parents = Yii::$app->cache->get($cacheKey);

        if ($parents === false) {
            $parents = [];
            $current = self::findOne(['id' => $categoryId, 'is_deleted'=>0]);
            $parents[] = array(
                'id' => $current->getAttribute('id'),
                'name' => $current->getAttribute('name'),
            );

            while ($current && !empty($current->getAttribute('parent_ident'))) {
                $parent = self::findOne(['identifier' => $current->getAttribute('parent_ident'), 'is_deleted'=>0]);
                if ($parent) {
                    $parents[] = array(
                        'id' => $parent->getAttribute('id'),
                        'name' => $parent->getAttribute('name'),
                    );
                    $current = $parent;
                } else {
                    break;
                }
            }

            Yii::$app->cache->set($cacheKey, $parents, 3600);
        }

        return $parents;
    }
}