<?
use \Bitrix\Main\SystemException;
class CAsproProrsGroup extends CBitrixComponent
{
	//public $iblockId = null;
	//public $arProps = [];
	public $moduleId = null;
	public $noGroupName = "NO_GROUP";
	public $noGroupCode = "no-group";

	public function getFormatResult(){

		$data = [];

		return $data;
	}

	public function getFilePath($iblockId){
		return $_SERVER["DOCUMENT_ROOT"].'/bitrix/modules/'.$this->moduleId.'/admin/propertygroups/json/prop_groups_iblock_'.$iblockId.'.json';
	}

	public function getPropsFromFile($filePath){
		$propsFromFile = [];
		try{
			$propsFromFile = file_get_contents($filePath);
			$propsFromFile = \Bitrix\Main\Web\Json::decode($propsFromFile);
		} catch(SystemException $e){
			$propsFromFile = [];
		}
		
		return $propsFromFile;
	}

	public function arResultSet(){
		global $APPLICATION;
		$iblockId = $this->arParams["IBLOCK_ID"];
		$this->moduleId = $this->arParams["MODULE_ID"];
		$arDisplayProps = $this->arParams["DISPLAY_PROPERTIES"];

		$filePath = $this->getFilePath($iblockId);
		$tmpResult = [];
		$arResult["GROUPS"] = [];
		
		if(!empty($arDisplayProps)){
			if( file_exists($filePath) ){
				$arGroupProps = $this->getPropsFromFile($filePath);
	
				foreach($arGroupProps as $keyGroup => $arGroup){
					foreach($arGroup["PROPS"] as $propCode){					
						if(isset($arDisplayProps[$propCode])){
							$tmpResult[$keyGroup]["NAME"] = $arGroup["NAME"];
							$tmpResult[$keyGroup]["CODE"] = $arGroup["CODE"];
							$tmpResult[$keyGroup]["DISPLAY_PROPERTIES"][$propCode] = $arDisplayProps[$propCode];
						}
					}
				}
				
				if(count($tmpResult) > 1 && $tmpResult[0]["NAME"] === $this->noGroupName ){
					$noGroup = array_shift($tmpResult);
					array_push($tmpResult, $noGroup);
				}
			}
	
			if(count($tmpResult) > 0){
				$arResult["GROUPS"] = array_values($tmpResult);
			} else {
				$arResult["GROUPS"][] = ["NAME" => $this->noGroupName, "CODE" => $this->noGroupCode, "DISPLAY_PROPERTIES" => $arDisplayProps];
			}
		}
		

		/*offers block*/
		$offersIblockId = $this->arParams["OFFERS_IBLOCK_ID"];
		$arDisplayPropsOffer = $this->arParams["OFFER_DISPLAY_PROPERTIES"];

		$filePathOffer = $this->getFilePath($offersIblockId);
		$tmpResultOffer = [];
		
		$arGroupCodes = array_column($arResult["GROUPS"], "CODE");
		if($offersIblockId && !empty($arDisplayPropsOffer)){
			$countGroups = count($arResult["GROUPS"]);
			if(file_exists($filePathOffer) ){
				$arGroupPropsOffer = $this->getPropsFromFile($filePathOffer);
				
				foreach($arGroupPropsOffer as $keyGroupOffer => $arGroupOffer){
					$keyGroup = array_search($arGroupOffer["CODE"], $arGroupCodes, true);
					foreach($arGroupOffer["PROPS"] as $propCode){					
						if(isset($arDisplayPropsOffer[$propCode])){
							if($keyGroup !== false){
								$arResult["GROUPS"][$keyGroup]["DISPLAY_PROPERTIES"][$propCode] = $arDisplayPropsOffer[$propCode];
								$arResult["GROUPS"][$keyGroup]["DISPLAY_PROPERTIES"][$propCode]["IS_OFFER"] = true;
							} else {
								$tmpResultOffer[$keyGroupOffer]["NAME"] = $arGroupOffer["NAME"];
								$tmpResultOffer[$keyGroupOffer]["CODE"] = $arGroupOffer["CODE"];
								$tmpResultOffer[$keyGroupOffer]["DISPLAY_PROPERTIES"][$propCode] = $arDisplayPropsOffer[$propCode];
								$tmpResultOffer[$keyGroupOffer]["DISPLAY_PROPERTIES"][$propCode]["IS_OFFER"] = true;
								$tmpResultOffer[$keyGroupOffer]["OFFER_GROUP"] = true;
							}						
						}
					}
				}
				
				if(count($tmpResultOffer) > 1 && $tmpResultOffer[0]["NAME"] === $this->noGroupName ){
					$noGroupOffer = array_shift($tmpResultOffer);
					array_push($tmpResultOffer, $noGroupOffer);
				}

				if(count($tmpResultOffer) > 0){
					if( $countGroups>0 && $arResult["GROUPS"][$countGroups - 1]["NAME"] === $this->noGroupName){
						$arNoGroup = array_pop($arResult["GROUPS"]);
						$arResult["GROUPS"] = array_merge($arResult["GROUPS"], $tmpResultOffer, [$arNoGroup]);
					} else {
						$arResult["GROUPS"] = array_merge($arResult["GROUPS"], $tmpResultOffer);
					}
					
				}
			} else {
				foreach($arDisplayPropsOffer as $offerPropKey => $offerProp){
					$arDisplayPropsOffer[$offerPropKey]["IS_OFFER"] = true;
				}
				if( $countGroups>0 && $arResult["GROUPS"][$countGroups - 1]["NAME"] === $this->noGroupName){
					$arResult["GROUPS"][$countGroups - 1]["DISPLAY_PROPERTIES"] = array_merge($arResult["GROUPS"][$countGroups - 1]["DISPLAY_PROPERTIES"], $arDisplayPropsOffer);
				} else {
					$arResult["GROUPS"][] = ["NAME" => $this->noGroupName, "CODE" => $this->noGroupCode, "OFFER_GROUP" => true, "DISPLAY_PROPERTIES" => $arDisplayPropsOffer];
				}
			}
		}
		
		/**/

		return $arResult;
	}

    public function executeComponent()
    {
        //if($this->startResultCache())
        //{
            $this->arResult = $this->arResultSet();
            $this->includeComponentTemplate();
        //}
        return $this->arResult;
    }
};
?>