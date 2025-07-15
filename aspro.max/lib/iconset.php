<?
namespace Aspro\Max;
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\IO,
	CMax as Solution,
	Aspro\Max\Property\RegionPhone,
    Bitrix\Main\Application;

class Iconset {
	const ICONSET_DIR = 'iconset';
	protected $code;
	protected $config;

	public function __construct($code){
		if(strlen($code)){
			$this->code = $code;
			$this->config = self::getConfig($code);
			if(!$this->config){
				throw new \Bitrix\Main\ArgumentException(Loc::getMessage('ICONSET_ERROR_UNKNOWN_CODE', array('#CODE#' => $code)));
			}
		}
		else{
			throw new \Bitrix\Main\ArgumentException(Loc::getMessage('ICONSET_ERROR_BAD_CODE'));
		}
	}

	public function __get($name){
		switch($name) {
			case 'code':
			case 'config':
				return $this->{$name};
				break;

			return null;
		}
	}

	public function getItems(){
		$arIcons = array();

		$needle = self::getModuleId().'/'.self::ICONSET_DIR.'/'.$this->code.'/';
		$dbRes = \CFile::GetList(array('ID' => 'DESC'), array('MODULE_ID' => self::getModuleId()));
		while($arFile = $dbRes->Fetch()){
			$path = \CFile::GetPath($arFile['ID']);
			if(strpos($path, $needle) !== false){
				$arIcons[] = array(
					'id' => $arFile['ID'],
					'default' => false,
					'path' => str_replace($_SERVER['DOCUMENT_ROOT'], '', $path),
					'name' => $arFile['ORIGINAL_NAME'],
				);
			}
		}

		foreach($this->getDefaultItems() as $file){
			$arIcons[] = array(
				'id' => $path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $file),
				'default' => true,
				'path' => $path,
				'name' => basename($file),
			);
		}

		return $arIcons;
	}

	private function getDefaultItems(){
		$arIcons = array();

		$path = $_SERVER['DOCUMENT_ROOT'].'/bitrix/images/'.self::getModuleId().'/'.self::ICONSET_DIR.'/'.$this->code.'/';
		foreach(
			(array)glob($path.$this->config['glob_pattern'], GLOB_BRACE)
			as $file
		){
			$arIcons[] = $file;
		}

		return $arIcons;
	}

	public function addItem($arFields){
		if($this->config['can_add']){
			if(
				$arFields &&
				is_array($arFields) &&
				strlen($arFields['name']) &&
				strlen($arFields['tmp_name'])
			){
				if(IO\File::isFileExists($arFields['tmp_name'])){
					if(is_dir($arFields['tmp_name'])){
						throw new IO\InvalidPathException($arFields['tmp_name']);
					}
					else{
						// validate
						if(strlen($this->config['validation_pattern'])){
							if(!preg_match('/'.$this->config['validation_pattern'].'/i', $arFields['name'])){
								throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_VALIDATION_FILE_BAD_NAME'));
							}
						}

						$arFields['MODULE_ID'] = self::getModuleId();
						return \CFile::SaveFile($arFields, self::getModuleId().'/'.self::ICONSET_DIR.'/'.$this->code);
					}
				}
				else{
					throw new IO\InvalidPathException($arFields['tmp_name']);
				}
			}
			else{
				throw new \Bitrix\Main\ArgumentException(Loc::getMessage('ICONSET_ERROR_BAD_FILE_FIELDS'));
			}
		}
		else{
			throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_ADD_ICON_NOT_AVAILABLE'));
		}

		return false;
	}

	public function deleteItem($id){
		if($this->config['can_delete']){
			if(strlen($id)){
				foreach($this->getItems() as $item){
					if($item['id'] == $id){
						if($item['default']){
							throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_DELETE_ICON_DEFAULT'));
						}
						else{
							$bByFileId = is_numeric($id);
							if ($bByFileId) {
								$fulllPath = \CFile::GetPath($id);
								$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fulllPath);
							}
							elseif (strpos($id, '://') === false) {
								$fulllPath = $_SERVER['DOCUMENT_ROOT'].$id;
								$path = $id;
							}
							else {
								$fullPath = $id;
								$path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $fullPath);
							}

							$bExternal = strpos($fullPath, '://') !== false;

							// search in module options
							$searchValue = $path;
							$connection = \Bitrix\Main\Application::getConnection();
							$options = $connection->query("(SELECT MODULE_ID,NAME,SITE_ID,VALUE FROM b_option WHERE MODULE_ID = '".self::getModuleId()."' AND VALUE LIKE '".$searchValue."%') UNION (SELECT MODULE_ID,NAME,SITE_ID,VALUE FROM b_option_site WHERE MODULE_ID = '".self::getModuleId()."' AND VALUE LIKE '".$searchValue."%')");
							while($arOption = $options->fetch()){
								throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_DELETE_ICON_IS_SAVED_AS_OPTION_VALUE'));
							}

							// search in iblock property default value && element values
							if(class_exists(get_class(new RegionPhone()))){
								if($arPropertyHandlers = RegionPhone::OnIBlockPropertyBuildList()){
									if($arPropertyHandlers['USER_TYPE']){
										$searchValue = basename($searchValue);

										$arElementFilter = array();
										$dbRes = \CIBlockProperty::GetList(
											array(),
											array('USER_TYPE' => $arPropertyHandlers['USER_TYPE'])
										);
										while($arProperty = $dbRes->Fetch()){
											if($arProperty['DEFAULT_VALUE'] && is_array($arProperty['DEFAULT_VALUE'])){
												if(preg_match('/\/'.$searchValue.'$/', $arProperty['DEFAULT_VALUE']['ICON'])){
													throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_DELETE_ICON_IS_SAVED_AS_OPTION_VALUE'));
												}
											}

											$arElementFilter[] = array('%PROPERTY_'.$arProperty['ID'] => $searchValue);
										}

										if($arElementFilter){
											$arElementFilter['LOGIC'] = 'OR';

											$dbRes = \CIBlockElement::GetList(
												array(),
												$arElementFilter,
												false,
												array('nTopCount' => 1),
												array('ID')
											);
											if($dbRes->Fetch()){
												throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_DELETE_ICON_IS_SAVED_AS_OPTION_VALUE'));
											}
										}
									}
								}
							}

							if ($bByFileId) {
								\CFile::Delete($id);
							}
							else {
								IO\File::deleteFile($fulllPath);
							}

							return true;
						}
					}
				}
			}
			else{
				throw new \Bitrix\Main\ArgumentException(Loc::getMessage('ICONSET_ERROR_BAD_ICON_ID'));
			}
		}
		else{
			throw new \Bitrix\Main\SystemException(Loc::getMessage('ICONSET_ERROR_DELETE_ICON_NOT_AVAILABLE'));
		}

		return false;
	}

	public static function getConfig($code){
		$arConfig = array();

		if(strlen($code)){
			// TODO: replace config to DB
			$arList = array(
				'header_phones' => array(
					'width' => 16,
					'height' => 16,
					'glob_pattern' => '{*.svg,*.png,*.jpg,*.bmp}',
					'validation_pattern' => '.+[.](svg|png|jpg|jpeg|bmp)$',
					'can_delete' => true,
					'can_add' => true,
					'add_note' => Loc::getMessage('ICONSET_ADD_NOTE_HEADER_PHONES'),
				),
			);

			$arConfig = $arList[$code] ?? array();
		}

		return $arConfig;
	}

	public static function getModuleId(){
		return Solution::moduleID;
	}

	public static function isSvgIcon($path){
		return preg_match('/\.svg$/i', $path);
	}

	public static function getCodeByIconPath($path){
		if(preg_match('/[\/]iconset[\/]([^\/]+)[\/]/i', $path, $arMatches)){
			return $arMatches[1];
		}

		return false;
	}

    public static function showIcon($id, $bSvgUrlReplace = false, $bInlineSvg = true)
    {
        if (!strlen($id)) {
            return '';
        }

        $documentRoot = Application::getDocumentRoot();
        $bByFileId = is_numeric($id);
        $path = '';
        $fullPath = '';

        if ($bByFileId) {
            $path = \CFile::GetPath($id);
            $fullPath = $documentRoot . $path;
        } elseif (strpos($id, '://') === false) {
            $fullPath = $documentRoot . $id;
            $path = $id;
        } else {
            $fullPath = $id;
            $path = str_replace($documentRoot, '', $fullPath);
        }

        $bExternal = strpos($fullPath, '://') !== false;
        $name = basename($path);

        $bNeedResize = false;
        $arConfig = [];
        if ($code = self::getCodeByIconPath($path)) {
            $arConfig = self::getConfig($code);
            $bNeedResize = $arConfig['width'] && $arConfig['height'];
        }

        $bSvg = self::isSvgIcon($path);
        if ($bSvg && $bInlineSvg && !$bExternal) {
            $style = $bNeedResize ? 'width:'.$arConfig['width'].'px;height:'.$arConfig['height'].'px;line-height:'.$arConfig['width'].'px;' : '';

            // set style attr of svg element using $title parameter of method
            $svgContent = Solution::showIconSvg('', $path, htmlspecialcharsbx($name).($style ? '" style="'.$style : ''), 'iconset_icon iconset_icon--svg light-ignore', true, false);

            // fix bug with styles dark-color:hover svg rect, dark-color:hover svg path{fill:theme_color;}
            if(preg_match('/<svg[^>]*>/i', $svgContent, $arMatches)){
                if(strpos($arMatches[0], ' class="') === false){
                    $svgContent = str_replace('<svg ', '<svg class="not_fill" ', $svgContent);
                }
                else{
                    $svgContent = str_replace(' class="', ' class="not_fill ', $svgContent);
                }
            }

            // fix bug with dublicate url(#id)
            if($bSvgUrlReplace){
                $svgContent = str_replace(array('svg_mask_', 'svg_paint_'), array('svg_mask_re_', 'svg_paint_re_'), $svgContent);
            }

            return $svgContent;
        }

        if (!IO\File::isFileExists($fullPath) && !$bExternal) {
            return '';
        }

        $style = $bNeedResize ? 'max-width:' . $arConfig['width'] . 'px;max-height:' . $arConfig['height'] . 'px;' : '';

        if ($bNeedResize && !$bSvg) {
            $resizeType = BX_RESIZE_IMAGE_PROPORTIONAL;
            $arSize = [
                'width' => $arConfig['width'],
                'height' => $arConfig['height']
            ];

            if ($bByFileId) {
                if ($arFile = \CFile::GetByID($id)->Fetch()) {
                    $name = $arFile['ORIGINAL_NAME'];
                    $arImage = \CFile::ResizeImageGet($id, $arSize, $resizeType, false);
                    $path = $arImage['src'];
                }
            } else {
                static $uploadDirName;
                if (!isset($uploadDirName)) {
                    $uploadDirName = \COption::GetOptionString('main', 'upload_dir', 'upload');
                }

                $dirName = preg_replace('/^\/' . $uploadDirName . '\//i', '', dirname($path));
                $resizeDestination = '/' . $uploadDirName . '/resize_cache/' . $dirName . '/' . $arConfig['width'] . '_' . $arConfig['height'] . '_' . $resizeType . '/' . $name;
                \CFile::ResizeImageFile($path, $resizeDestination, $arSize, $resizeType);

                if (IO\File::isFileExists($resizeDestination)) {
                    $path = $resizeDestination;
                }
            }
        }

        return '<img class="iconset_icon iconset_icon--img" src="' . str_replace($documentRoot, '', $path) . '" title="' . htmlspecialcharsbx($name) . '" ' . ($style ? 'style="' . $style . '"' : '') . ' />';
    }

}
?>
