<?php

use yii\db\Migration;

/**
 * Class m200215_071851_addCbrCurrencyTable
 */
class m200215_071851_addCbrCurrencyTable extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
		$this->createTable('cbr_currency', [
			'id' => $this->primaryKey(),
			'date' => $this->date()->notNull()->comment('Дата'),
			'char_code' => $this->string(3)->notNull()->comment('Текстовой код'),
			'value' => $this->float(4)->notNull()->comment('Количество рублей'),
		]);
		$this->createIndex('natural_pk', 'cbr_currency', ['date', 'char_code'], true);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('cbr_currency');
    }
}
