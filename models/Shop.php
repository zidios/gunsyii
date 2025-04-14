<?php
namespace app\models;

use yii\db\ActiveRecord;

class Shop extends ActiveRecord{
    /**
     * @var mixed|null
     */
    public $id;

    public static function tableName(){
        //На всякий случай
        return '{{shop}}';
    }
}