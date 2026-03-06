<?php

namespace frontend\models\forms;

use yii\base\Model;

class PrecioItemForm extends Model
{
    public $term;

    public function rules()
    {
        return [
            [['term'], 'trim'],
            [['term'], 'required'],
            [['term'], 'string', 'max' => 100],
        ];
    }

    public function attributeLabels()
    {
        return [
            'term' => 'Buscar',
        ];
    }
}
