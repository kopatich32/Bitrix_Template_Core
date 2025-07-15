<?php
namespace Aspro\Max\Stores;

use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	Bitrix\Highloadblock as HL,
	Bitrix\Main\Entity;

class HelperHL
{
	private static ?HelperHL $instance = null;
	private static string $dataClass;

	public static function getInstance(string $hlName) : HelperHL{
		if (self::$instance === null) {
			self::$instance = new self($hlName);
		}

		return self::$instance;
	}

	public function add(array $arFields) : string{
		$entityDataClass = $this->dataClass;

		$result = $entityDataClass::add($arFields);

		$newHLStoreID = '';
		if($result->isSuccess()){
			$newHLStoreID = $result->getId();
		} else {
			$errors = $result->getErrorMessages();
			$strErrors = implode('\n', $errors);
			throw new \Exception($strErrors);
		}

		return $newHLStoreID;
	}

	public function update(string $idElement, array $arFields) : bool{
		$entityDataClass = $this->dataClass;

		$result = $entityDataClass::update($idElement, $arFields);

		if(!$result->isSuccess()){
			$errors = $result->getErrorMessages();
			$strErrors = implode('\n', $errors);
			throw new \Exception($strErrors);
		}

		return $result->isSuccess();
	}

	public function get(array $arOptions) : array{
		$arSelect = $arOptions['select'] ?? [];
		$arOrder = $arOptions['order'] ?? ["ID"=>"DESC"];
		$arFilter = $arOptions['filter'] ?? [];

        $entityDataClass = $this->dataClass;

		$result = $entityDataClass::getList(array(
			"select" => $arSelect,
			"order" => $arOrder,
			"filter" => $arFilter,
		));

		$arStores = [];
		while ($arRow = $result->Fetch()){
			$arStores[] = $arRow;
		}

		return $arStores;
    }

	public function delete(string $idElement) : bool{
		$entityDataClass = $this->dataClass;

		$result = $entityDataClass::Delete($idElement);

		if(!$result->isSuccess()){
			$errors = $result->getErrorMessages();
			$strErrors = implode('\n', $errors);
			throw new \Exception($strErrors);
		}

		return $result->isSuccess();
	}

	protected function __construct(string $hlName){
		Loader::includeModule("highloadblock");

		$hlblock = HL\HighloadBlockTable::getList([
			'filter' => ['=NAME' => $hlName]
		])->fetch();

		if(!$hlblock){
			throw new \Exception('hl block not found');
		}

		$entity = HL\HighloadBlockTable::compileEntity($hlblock);
		$entityDataClass = $entity->getDataClass();

		$this->dataClass = $entityDataClass;
	}
	
}