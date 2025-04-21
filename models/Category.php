<?php

namespace app\models;

use yii\db\ActiveRecord;

class Category extends ActiveRecord
{
    public static function tableName(){
        //На всякий случай
        return '{{category}}';
    }
}