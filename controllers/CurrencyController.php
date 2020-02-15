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
		$get = Yii::$app->request->queryParams;
		$where = ['date' => date('Y-m-d')];
		if (isset($get['filter'])) {
			if (isset($get['filter']['char_code'])) {
				$where['char_code'] = (string) $get['filter']['char_code'];
			}
			if (isset($get['filter']['date'])) {
				$where['date'] = (string) $get['filter']['date'];
			}
		}

		return Currency::find()
			->where($where)
			->all();
	}
}