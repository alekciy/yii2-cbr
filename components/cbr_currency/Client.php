<?php

namespace app\components\cbr_currency;

use app\components\cbr_currency\models\Currency;
use DateTime;
use DateTimeZone;
use DOMDocument;
use DOMNode;
use DOMXPath;
use Exception;
use GuzzleHttp\Psr7\Uri;
use Yii;

class Client
{
	/** @var \GuzzleHttp\Client */
	protected $client;

	function __construct()
	{
		$this->client = new \GuzzleHttp\Client([
			'base_uri' => 'http://www.cbr.ru/scripts/XML_daily.asp',
		]);
	}

	/**
	 * Вернет ассоциативный массив: ключ - код валюты, значение - курс валюты на дату $date.
	 *
	 * @param DateTime|null $date
	 * @return Currency[]
	 * @throws Exception
	 */
	function load(DateTime $date = null)
	{
		$result = [];
		$date = $date instanceof DateTime
			? clone $date
			: new DateTime();
		$date->setTimezone(new DateTimeZone('Europe/Moscow'));

		$response = $this->client->get('http://www.cbr.ru/scripts/XML_daily.asp?date_req=' . $date->format('d.m.Y'));
		$content = $response->getBody()->getContents();
		if ($response->getStatusCode() != 200) {
			throw new Exception(sprintf('Не удалось получить курс валют из ЦБ: статус - %s, тело - %s',
				$response->getStatusCode(), $content
			));
		}
		$domDoc = new DOMDocument();
		if ($domDoc->loadXML($content) === false) {
			throw new Exception(sprintf('Не удалось загрузить XML документ: %s', $content));
		}
		$xPath = new DOMXPath($domDoc);

		$dateResponse = $xPath->query('//ValCurs/@Date')[0]->textContent;
		if ($dateResponse != $date->format('d.m.Y')) {
			Yii::info(sprintf('Запрашивали %s дату, но ЦБ вернул %s',
				$date->format('d.m.Y'), $dateResponse
			));
		}

		$charCodeNodeList = $xPath->query('//ValCurs/Valute/CharCode');
		$valueNodeList = $xPath->query('//ValCurs/Valute/Value');
		/** @var DOMNode  $charCodeNode */
		foreach ($charCodeNodeList as $index => $charCodeNode) {
			$code = $charCodeNode->textContent;
			$value = floatval(str_replace(',', '.', $valueNodeList[$index]->textContent));

			$currency = new Currency();
			$currency->date = $date->format('Y-m-d');
			$currency->char_code = $code;
			$currency->value = $value;
			if ($currency->validate()) {
				$result[$code] = $currency;
			} else {
				$error = 'Ответ ЦБ, ошибки в модели ' . Currency::class . ': ';
				foreach ($currency->errors as $property => $errorList) {
					$error .= sprintf('(%s: %s) ', $property, implode(';', $errorList));
				}
				throw new Exception($error);
			}
		}

		return $result;
	}

}