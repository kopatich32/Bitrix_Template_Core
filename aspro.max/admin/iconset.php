<?
use Bitrix\Main\Localization\Loc,
	Bitrix\Main\Loader,
	CMax as Solution,
	Aspro\Max\Iconset;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

$APPLICATION->RestartBuffer();
$GLOBALS['APPLICATION']->ShowAjaxHead();

Loc::loadMessages(__FILE__);

$errorMessage = '';
$arResult = array(
	'error' => &$errorMessage,
);

$action = isset($_REQUEST['action']) ? htmlspecialcharsbx($_REQUEST['action']) : '';
$code = isset($_REQUEST['code']) ? htmlspecialcharsbx($_REQUEST['code']) : '';
$value = isset($_REQUEST['value']) ? htmlspecialcharsbx($_REQUEST['value']) : '';
$id = isset($_REQUEST['id']) ? htmlspecialcharsbx($_REQUEST['id']) : '';
$icon_path = isset($_REQUEST['icon_path']) ? htmlspecialcharsbx($_REQUEST['icon_path']) : '';
$icon_file = $_FILES['icon_file'] ?? false;

$moduleId = Iconset::getModuleId();
$RIGHT = $APPLICATION->GetGroupRight($moduleID);
if($RIGHT >= 'R'){
	$bCanChange = $RIGHT > 'R';

	if(strlen($code)){
		try{
			$iconset = new Iconset($code);
		}
		catch(\Bitrix\Main\SystemException $exception){
			$errorMessage = $exception->getMessage();
		}

		if(!strlen($errorMessage)){
			// include required modules
			foreach(
				array(
					'fileman',
					$moduleId,
				) as $moduleId
			){
				if(!Loader::includeModule($moduleId)){
					$errorMessage = Loc::getMessage('ICONSET_ERROR_MODULE_NOT_INCLUDED', array('#MODULE_NAME#' => $moduleId));
				}
			}
		}

		$bCanAdd = $iconset->config['can_add'];
		$bCanDelete = $iconset->config['can_delete'];

		if(!strlen($errorMessage)){
			if(strlen($action)){
				// action request

				if($action === 'add_icon'){
					if($bCanChange){
						try{
							// mk file fields
							$arFields = array();
							if(strlen($icon_path)){
								$icon_path = str_replace($_SERVER['DOCUMENT_ROOT'], '', $icon_path);
								if (strpos($icon_path, '://') === false) {
									$icon_path = $_SERVER['DOCUMENT_ROOT'].$icon_path;
								}
								$arFields = \CFile::MakeFileArray($icon_path);
							}
							else{
								if(isset($icon_file)){
									$arFields = $icon_file;
								}
							}

							// check & save
							if($arFields){
								$fileId = $iconset->addItem($arFields);
								$arResult['id'] = $fileId;
							}
							else{
								$errorMessage = Loc::getMessage('ICONSET_ERROR_VALIDATION_FILE_EMPTY_VALUE');
							}
						}
						catch(\Bitrix\Main\SystemException $exception){
							$errorMessage = $exception->getMessage();
						}
					}
					else{
						$errorMessage = Loc::getMessage('ICONSET_ERROR_NO_RIGHTS_FOR_ADD');
					}
				}
				elseif($action === 'delete_icon'){
					if(strlen($id)){
						if($bCanChange){
							try{
								$iconset->deleteItem($id);
							}
							catch(\Bitrix\Main\SystemException $exception){
								$errorMessage = $exception->getMessage();
							}
						}
						else{
							$errorMessage = Loc::getMessage('ICONSET_ERROR_NO_RIGHTS_FOR_DELETE');
						}
					}
					else{
						$errorMessage = Loc::getMessage('ICONSET_ERROR_BAD_ICON_ID');
					}
				}
				elseif($action === 'get_icon'){
					if(strlen($id)){
						foreach($iconset->getItems() as $item){
							if($item['id'] == $id){
								$GLOBALS['APPLICATION']->RestartBuffer();
								$bSelected = $item['path'] === $value;
								?>
								<div class="iconset_item iconset_item--added <?=($bSelected ? 'iconset_item--selected' : '')?>" data-id="<?=$item['id']?>" data-value="<?=$item['path']?>" title="<?=$item['name']?>">
									<?if($bCanDelete && $bCanChange && !$item['default']):?>
										<div class="iconset_btn--delete" title="<?=Loc::getMessage('ICONSET_BUTTON_DELETE')?>"></div>
									<?endif;?>
									<div class="iconset_item_middle">
										<?=Iconset::showIcon($item['id'], true);?>
									</div>
								</div>
								<?
								die();
							}
						}

						$errorMessage = Loc::getMessage('ICONSET_ERROR_UNKNOWN_ICON_ID');
					}
					else{
						$errorMessage = Loc::getMessage('ICONSET_ERROR_BAD_ICON_ID');
					}
				}
				else{
					$errorMessage = Loc::getMessage('ICONSET_ERROR_BAD_PARAMS');
				}
			}
		}
	}
	else{
		$errorMessage = Loc::getMessage('ICONSET_ERROR_BAD_PARAMS');
	}
}
else{
	$errorMessage = Loc::getMessage('ICONSET_ERROR_NO_RIGHTS_FOR_VIEWING');
}
?>
<?if(strlen($action)):?>
	<?// action request?>
	<?
	$GLOBALS['APPLICATION']->RestartBuffer();
	header('Content-Type: application/json');
	echo \Bitrix\Main\Web\Json::encode($arResult);
	die();
	?>
<?else:?>
	<?// normal request?>
	<?if(!strlen($errorMessage)):?>
		<?
		$GLOBALS['APPLICATION']->SetAdditionalCSS('/bitrix/css/aspro.max/iconset.css');
		$GLOBALS['APPLICATION']->AddHeadScript('/bitrix/js/aspro.max/iconset.js');

		$rand = random_int(1, 10000);
		$arTabs = array(
			array(
				'DIV' => 'iconset_items',
				'TAB' => Loc::getMessage('ICONSET_TAB_ITEMS'),
			),
		);
		if($bCanAdd && $bCanChange){
			$arTabs[] = array(
				'DIV' => 'iconset_add_icon',
				'TAB' => Loc::getMessage('ICONSET_TAB_ADD_ICON'),
			);
		}
		$varTabControl = 'tabControl_iconset_'.$rand;
		$$varTabControl = new \CAdminViewTabControl($varTabControl, $arTabs);
		?>
		<div id="iconset-<?=$rand?>" class="iconset<?=($bCanAdd && $bCanChange ? ' iconset--can_add' : '')?>" data-code="<?=$code?>" style="display:none;">
			<?$$varTabControl->Begin();?>
			<?$$varTabControl->BeginNextTab();?>
			<div class="iconset_wrap">
				<div class="iconset_item iconset_item--empty<?=(strlen($value) ? '' : ' iconset_item--selected')?>" data-id="" data-value="" title="<?=Loc::getMessage('ICONSET_EMPTY_ITEM')?>">
					<div class="iconset_item_middle"></div>
					<div class="iconset_item_text"><?=Loc::getMessage('ICONSET_EMPTY_ITEM')?></div>
				</div>

				<?foreach($iconset->getItems() as $item):?>
					<?$bSelected = $item['path'] === $value;?>
					<div class="iconset_item<?=($bSelected ? ' iconset_item--selected' : '')?>" data-id="<?=$item['id']?>" data-value="<?=$item['path']?>" title="<?=htmlspecialcharsbx($item['name'])?>">
						<?if($bCanDelete && $bCanChange && !$item['default']):?>
							<div class="iconset_btn--delete" title="<?=Loc::getMessage('ICONSET_BUTTON_DELETE')?>"></div>
						<?endif;?>
						<div class="iconset_item_middle">
							<?=Iconset::showIcon($item['id'], true);?>
						</div>
					</div>
				<?endforeach;?>
			</div>
			<?if($bCanAdd && $bCanChange):?>
				<?$$varTabControl->BeginNextTab();?>
				<form name="iconset_form" class="iconset_form" action="<?=$_SERVER['PHP_SELF']?>?lang=<?=LANGUAGE_ID?>" method="post" enctype="multipart/form-data">
					<?if(strlen($iconset->config['add_note'])):?>
						<div class="adm-info-message iconset_form_message">
							<?=$iconset->config['add_note']?>
						</div>
					<?endif;?>
					<input type="hidden" name="value" value="<?=$value?>" />
				    <?=\CFileInput::Show('iconset_new', false,
						array(),
						array(
							'upload' => true,
							'medialib' => true,
							'file_dialog' => true,
							'cloud' => false,
							'del' => false,
							'description' => false,
						)
					);?>
					<input type="button" value="<?=Loc::getMessage('ICONSET_BUTTON_LOAD')?>" class="adm-btn-save iconset_btn--load" />
				</form>
			<?endif;?>
			<?$$varTabControl->End();?>
			<script>
			new JIconset('<?=$rand?>', '<?=$code?>', <?=CUtil::PhpToJSObject($iconset->config, false, true)?>);
			</script>
		</div>
	<?else:?>
		<?\CAdminMessage::ShowMessage($errorMessage);?>
	<?endif;?>
<?endif;?>