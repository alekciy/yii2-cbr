<?php

namespace app\commands;

use app\components\cbr_currency\Client;
use app\components\cbr_currency\models\Currency;
use DateInterval;
use DatePeriod;
use DateTime;
use Yii;
use yii\console\Controller;
use yii\db\Exception;

class CbrController extends Controller
{
	/** @var array Список кодов валют с которыми работает компонент */
	public $currencyList = [];

	/** @var int Максимальное дневное падения курса рубля */
	public $maxDropDaily = 2;

	public function init()
	{
		parent::init();
		$this->maxDropDaily = (int) isset(Yii::$app->params['currency']['maxDropDaily'])
			? Yii::$app->params['currency']['maxDropDaily']
			: 2;
		$this->currencyList = (array) isset(Yii::$app->params['currency']['currencyList'])
			? Yii::$app->params['currency']['currencyList']
			: [];
	}

	/**
	 * Проверить уровень падения курса.
	 *
	 * @return void
	 */
	public function actionCheckCurrency()
	{
		$today = new DateTime();
		$yesterday = (clone $today)->modify('1 days ago');
		foreach ($this->currencyList as $currencyCode) {
			// Текущий курс
			$todayCurrency = Currency::find()->where([
				'date' => $today->format('Y-m-d'),
				'char_code' => $currencyCode,
			])->one();
			if (!$todayCurrency) {
				$msg = 'Не заружен текущий курс ' . $currencyCode;
				$this->adminNotify($msg, 'error');
				Yii::error($msg);
				continue;
			}
			// Курс за вчера
			$yesterdayCurrency = Currency::find()->where([
				'date' => $yesterday->format('Y-m-d'),
				'char_code' => $currencyCode,
			])->one();
			if (!$yesterdayCurrency) {
				$msg = 'Не заружен курс за вчера ' . $currencyCode;
				$this->adminNotify($msg, 'error');
				Yii::error($msg);
				continue;
			}

			$diff = round($todayCurrency->value, 4) - round($yesterdayCurrency->value, 4);
			if ($diff > $this->maxDropDaily) {
				// Поднимаем тревогу если вышли за границы
				$msg = sprintf('Для валюты %s курс упал более чем на %s рублей (с %.4f до %.4f)',
					$currencyCode, $this->maxDropDaily, $yesterdayCurrency->value, $todayCurrency->value
				);
				$this->adminNotify($msg, 'error');
				Yii::error($msg);
			} else {
				// Если все нормально просто уведомляем, что все ок
				$msg = sprintf('Изменение курса для %s не превысило %s рублей (с %.4f до %.4f)',
					$currencyCode, $this->maxDropDaily, $yesterdayCurrency->value, $todayCurrency->value
				);
				$this->adminNotify($msg, 'info');
				Yii::info($msg);
			}
		}
	}

	protected function adminNotify($message, $type)
	{
		// TODO логика уведомления админа
		echo 'Сообщение для админа: ' . $message . PHP_EOL;
	}

	/**
	 * Загружает курс валют за сегодня.
	 *
	 * @throws Exception
	 */
	public function actionIndex()
	{
		$currentDate = new DateTime();
		$this->loadByDate($currentDate);
	}

	/**
	 * Загружает курс валют за указанную дату.
	 *
	 * @return void
	 * @throws Exception
	 * @throws \Exception
	 */
	public function loadByDate(DateTime $date)
	{
		$currencyList = [];
		foreach ($this->currencyList as $currencyCode) {
			$currency = Currency::findOne([
				'date' => $date->format('Y-m-d'),
				'char_code' => $currencyCode
			]);
			// Если нужные валюты за дату еще не загрузили, то пытаемся загрузить
			if (!$currency) {
				if (empty($currencyList)) {
					// Загружаем курс валют с сайта ЦБ
					$cbrClient = Yii::$app->get('cbrClient');
					$currencyList = $cbrClient->load($date);
				}
				if (!isset($currencyList[$currencyCode])) {
					throw new \Exception('Неизвестный код валюты ' . $currencyCode);
				}
				$currency = $currencyList[$currencyCode];
				if (!$currency->save()) {
					throw new Exception('Не удалось сохранить курс валюты');
				}
				Yii::info(sprintf('Курс для %s на дату %s=%f сохранен',
					$currencyCode, $date->format('Y-m-d'), $currency->value
				));
			}
		}
	}

	/**
	 * Загрузить курсы за календарную неделю.
	 *
	 * @throws Exception
	 */
	public function actionWeekLoad()
	{
		$end = new DateTime();
		$begin = (clone $end)->modify('7 days ago');
		$period = new DatePeriod($begin, new DateInterval('P1D'), $end);
		/** @var DateTime $date */
		foreach ($period as $date) {
			$this->loadByDate($date);
		}
	}
}
