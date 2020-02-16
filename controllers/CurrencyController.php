<?php
namespace app\controllers;

use app\components\cbr_currency\models\Currency;
use Yii;
use yii\rest\ActiveController;

class CurrencyController extends ActiveController
{
	public $modelClass = Currency::class;

	/**
	 * @inheritDoc
	 */
	public function actions()
	{
		$actions = parent::actions();
		unset($actions['view'], $actions['delete'], $actions['create'], $actions['update'], $actions['options']);
		$actions['index']['prepareDataProvider'] = [$this, 'prepareDataProvider'];

		return $actions;
	}

	/**
	 * @return Currency[]
	 */
	public function prepareDataProvider()
	{
		$where = ['date' => date('Y-m-d')];
		$filter = Yii::$app->request->get('filter');
		if ($filter) {
			if (isset($filter['char_code'])) {
				$where['char_code'] = (string) $filter['char_code'];
			}
			if (isset($filter['date'])) {
				$where['date'] = (string) $filter['date'];
			}
		}

		return Currency::find()
			->where($where)
			->all();
	}
}