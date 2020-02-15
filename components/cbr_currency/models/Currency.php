<?php

namespace app\components\cbr_currency\models;

/**
 * This is the model class for table "cbr_currency".
 *
 * @property int $id
 * @property string $date Дата
 * @property string $char_code Текстовой код
 * @property float $value Количество рублей
 */
class Currency extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'cbr_currency';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['date', 'char_code', 'value'], 'required'],
            [['date'], 'date', 'format' => 'php:Y-m-d'],
            [['value'], 'number'],
            [['char_code'], 'string', 'max' => 3],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'date' => 'Дата',
            'char_code' => 'Текстовой код',
            'value' => 'Количество рублей',
        ];
    }

    public function formName()
	{
		return '';
	}
}
