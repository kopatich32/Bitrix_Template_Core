<?php
/**
 * @var array $properties
 * @var int   $iblockID
 */
use Bitrix\Main\Localization\Loc;

IncludeModuleLangFile($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/'.self::partnerName.'.'.self::solutionName.'/lib/propertygroups.php');
?>
    <tr valign="top">
        <td class="props-sort-wrapper-outer">
        <div class="props-sort-wrapper-top">
			<?= BeginNote(); ?>
			<?= Loc::getMessage('ASPRO_PROPS_GROUP_NOTE'); ?>
			<?= EndNote(); ?>

            <div class="props-group-add">
                <div class="props-group-add__title"><?= Loc::getMessage('ASPRO_PROPS_GROUP_ADD'); ?></div>
                <input type="text" class="props-group-add__text" value="<?= Loc::getMessage('ASPRO_PROPS_GROUP_NEW'); ?>"/>
                <button type="button" class="props-group-add__button" name="add-group"><?= Loc::getMessage('ASPRO_PROPS_GROUP_ADD_BUTTON'); ?></button>
            </div>
        </div>

            <div class="props-sort-wrapper <?= self::$bModified ? 'props-sort--modified' : ''; ?>">
                <?foreach($properties as $keyGroup => $arPropGroup):?>
                    <?if ($arPropGroup["NAME"] !== "NO_GROUP"):?>
					<div class="prop-drag-item prop-drag-item--group">
						<div class="prop-drag-item__inner">
                            <div class="drag" title="<?=Loc::getMessage("ASPRO_PROPS_GROUP_DRAG_BUTTON")?>"></div>
							<div contenteditable="false" class="props-item__group-name"><?= $arPropGroup['NAME']; ?></div>
							<div class="props-item__group-select-note"><?= Loc::getMessage('ASPRO_PROPS_GROUP_SELECT_GROUP'); ?></div>
							<div class="props-item__group-action">
                                <div class="props-item__group-apply" title="<?=Loc::getMessage("ASPRO_PROPS_GROUP_APPLY_BUTTON")?>"></div>
                                <div class="props-item__group-edit" title="<?=Loc::getMessage("ASPRO_PROPS_GROUP_EDIT_BUTTON")?>"></div>
                                <div class="props-item__group-delete" title="<?=Loc::getMessage("ASPRO_PROPS_GROUP_DELETE_BUTTON")?>"></div>
							</div>
						</div>
					</div>
				<?endif;?>

                    <?foreach($arPropGroup["PROPS"] as $propKey => $propCode):?>
                        <?if(isset(self::$propsInfo[$propCode])):?>
                            <div class="prop-drag-item" data-prop-id="<?= self::$propsInfo[$propCode]['ID']; ?>" data-prop-code="<?= $propCode; ?>">
                                <div class="prop-drag-item__inner">
                                    <div class="drag" title="<?= Loc::getMessage('ASPRO_PROPS_GROUP_DRAG_BUTTON'); ?>"></div>
                                    <div class="props-item__name"><?= self::$propsInfo[$propCode]['NAME']; ?></div>
                                </div>
                            </div>
                        <?endif;?>
                    <?endforeach;?>
                <?endforeach;?>
            </div>
            <input type="hidden" class="props-group-json-count" name="props-group-json-count" value=""/>
            <input type="hidden" name="props-group-iblock-id" value="<?= self::$iblockId; ?>"/>
        </td>
    </tr>
    <script>
    let grupperCell = document.querySelector('.props-sort-wrapper-outer');

    if (grupperCell) {
	let observer = new IntersectionObserver(
		(entries, observer) => {
			entries.forEach(entry => {
				if (entry.isIntersecting) {
					observer.unobserve(entry.target);

					/* lang text */
				    BX.message({
                        'ASPRO_PROPS_GROUP_DRAG_BUTTON': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_DRAG_BUTTON'); ?>',
                        'ASPRO_PROPS_GROUP_APPLY_BUTTON': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_APPLY_BUTTON'); ?>',
                        'ASPRO_PROPS_GROUP_EDIT_BUTTON': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_EDIT_BUTTON'); ?>',
                        'ASPRO_PROPS_GROUP_DELETE_BUTTON': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_DELETE_BUTTON'); ?>',
                        'ASPRO_PROPS_GROUP_DELETE_GROUP_CONFIRM': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_DELETE_GROUP_CONFIRM'); ?>',
                        'ASPRO_PROPS_GROUP_SELECT_GROUP': '<?= Loc::getMessage('ASPRO_PROPS_GROUP_SELECT_GROUP'); ?>',
                         });
					/**/

					function asproPlaceCaretAtEnd(el) {
						if (typeof window.getSelection != "undefined"
								&& typeof document.createRange != "undefined") {
							var range = document.createRange();
							range.selectNodeContents(el);
							range.collapse(false);
							var sel = window.getSelection();
							sel.removeAllRanges();
							sel.addRange(range);
						} else if (typeof document.body.createTextRange != "undefined") {
							var textRange = document.body.createTextRange();
							textRange.moveToElementText(el);
							textRange.collapse(false);
							textRange.select();
						}
					}

					/* get all props sort */
					function asproCollectPropGroup(){
						let sortPropWrapOuter = document.querySelector('.props-sort-wrapper-outer');
						let arSortProp = document.querySelectorAll('.props-sort-wrapper .prop-drag-item');
						let isGroup = false;
						let itemPropKey = 0;
						let propItem = [];
						let currentGroup = 0;
						let currentGroupName = "NO_GROUP";
						let arGroups = {
							[currentGroup]: {
								'NAME': currentGroupName,
								'PROPS': []
							}
						};
						let inputForGroup;
						let tmpDiv = document.createElement("div");
						if (arSortProp.length){
							/* get all groups object */
							for(itemPropKey = 0; itemPropKey < arSortProp.length; itemPropKey++){
								propItem = arSortProp[itemPropKey];
								isGroup = propItem.classList.contains("prop-drag-item--group");
								if (isGroup){
									currentGroup++;
									currentGroupName = propItem.querySelector(".props-item__group-name").innerText;
									/* strip_tags */
									tmpDiv.innerHTML = currentGroupName;
									currentGroupName = tmpDiv.textContent || tmpDiv.innerText || "";
									/* end strip_tags */
									arGroups[currentGroup] = {
										'NAME': currentGroupName,
										'PROPS': []
									};
								} else {
									arGroups[currentGroup]['PROPS'].push(propItem.getAttribute("data-prop-code"));
								}
							}

							/* set inputs for every group */
							for(var keyGroup in arGroups){
								inputForGroup = document.createElement('input');
								inputForGroup.setAttribute("name", "props-group-json[" + keyGroup + "]");
								inputForGroup.setAttribute("type", "hidden");
								inputForGroup.value = encodeURIComponent(JSON.stringify(arGroups[keyGroup]));
								sortPropWrapOuter.insertAdjacentElement("beforeend", inputForGroup);
							}
						}

						/* count groups */
						let inputJson = document.querySelector('input.props-group-json-count');
						inputJson.value = currentGroup;
					}

					/* sort order for arrays */
					let sort_block = document.querySelector('.props-sort-wrapper');
					if (sort_block && ( typeof window.Sortable === "function" )) {
						var sortable = Sortable.create(sort_block, {
							handle: '.prop-drag-item',
							animation: 150,
							forceFallback: true,
							filter: '.no_drag, .props-item__group-action',
							multiDrag: true,
							avoidImplicitDeselect: false,
							onChoose: function (evt) {
								sort_block.classList.add('sortable-started');
							},
							onUnchoose: function(evt) {
								sort_block.classList.remove('sortable-started');
							},
							onStart: function(evt){
								sort_block.classList.add('sortable-started');
								window.getSelection().removeAllRanges();
							},
							onEnd: function(evt){
								sort_block.classList.remove('sortable-started');
							},
							onMove: function(evt){
								return evt.related.className.indexOf('no_drag') === -1;
							},
							onUpdate: function(evt){
								sort_block.classList.add('props-sort--modified');
							},
							onSelect: function(evt) {
								let selectedgroups = sort_block.querySelectorAll('.prop-drag-item--group.sortable-selected');
								let selecteditems = sort_block.querySelectorAll('.prop-drag-item:not(.prop-drag-item--group).sortable-selected');

								if (evt.item.classList.contains('prop-drag-item--group')) {
									selectedgroups.forEach((groupitem) => {
										if (groupitem !== evt.item) {
											Sortable.utils.deselect(groupitem);
										}
									});

									selecteditems.forEach((item) => {
										Sortable.utils.deselect(item);
										item.classList.remove('sortable-selected--withgroup');
									});

									this.options.selectgroupitems(evt.item);
								}
								else {
									let curgroupitem = this.options.finditemgroup(evt.item);

									selectedgroups.forEach((groupitem) => {
										if (groupitem !== curgroupitem) {
											Sortable.utils.deselect(groupitem);
										}
									});

									selecteditems.forEach((item) => {
										if (curgroupitem !== this.options.finditemgroup(item)) {
											Sortable.utils.deselect(item);
											item.classList.remove('sortable-selected--withgroup');
										}
									});
								}
							},
							onDeselect: function(evt) {
								if (evt.item.classList.contains('prop-drag-item--group')) {
									this.options.deselectgroupitems(evt.item);
								}
								else {
									let curgroupitem = this.options.finditemgroup(evt.item);
									if (
										curgroupitem &&
										curgroupitem.classList.contains('sortable-selected')
									) {
										Sortable.utils.select(evt.item);
									}
								}
							},
							finditemgroup: function(item) {
								if (item.classList.contains('prop-drag-item--group')) {
									return item;
								}
								else {
									let groupitem = null;
									let prevItem = item.previousElementSibling;
									while (prevItem) {
										if (prevItem.classList.contains('prop-drag-item--group')) {
											return prevItem;
										}

										prevItem = prevItem.previousElementSibling;
									}

									return groupitem;
								}
							},
							selectgroupitems: function(groupitem) {
								if (groupitem.classList.contains('prop-drag-item--group')) {
									let nextItem = groupitem.nextElementSibling;
									while (nextItem) {
										if (nextItem.classList.contains('prop-drag-item--group')) {
											break;
										}

										Sortable.utils.select(nextItem);
										nextItem.classList.add('sortable-selected--withgroup');
										nextItem = nextItem.nextElementSibling;
									}
								}
							},
							deselectgroupitems: function(groupitem) {
								if (groupitem.classList.contains('prop-drag-item--group')) {
									let nextItem = groupitem.nextElementSibling;
									while (nextItem) {
										if (nextItem.classList.contains('prop-drag-item--group')) {
											break;
										}

										nextItem.classList.remove('sortable-selected--withgroup');
										Sortable.utils.deselect(nextItem);
										nextItem = nextItem.nextElementSibling;
									}
								}
							}
						});
					}

                    let buttonAddPropsGroup = document.querySelector('.props-group-add__button');
                    if(buttonAddPropsGroup){
                        buttonAddPropsGroup.addEventListener("click", function(event) {
                            let buttonWrap = event.target.closest('.props-group-add');
                            let newGroupText = buttonWrap.querySelector(".props-group-add__text");
                            if(newGroupText){
                                let allPropsWrap = document.querySelector('.props-sort-wrapper');
                                allPropsWrap.classList.add("props-sort--modified");
                                allPropsWrap.insertAdjacentHTML('afterbegin',
                                    '<div class="prop-drag-item prop-drag-item--group">' +
                                        '<div class="prop-drag-item__inner">' +
                                            '<div class="drag" title="' + BX.message('ASPRO_PROPS_GROUP_DRAG_BUTTON') + '"></div>' +
                                            '<div contenteditable="false" class="props-item__group-name">' +
                                                newGroupText.value +
                                            '</div>' +
                                            '<div class="props-item__group-select-note">' + BX.message('ASPRO_PROPS_GROUP_SELECT_GROUP') + '</div>' +
                                            '<div class="props-item__group-action">' +
                                                '<div class="props-item__group-apply" title="' + BX.message('ASPRO_PROPS_GROUP_APPLY_BUTTON') + '"></div>' +
                                                '<div class="props-item__group-edit" title="' + BX.message('ASPRO_PROPS_GROUP_EDIT_BUTTON') + '"></div>' +
                                                '<div class="props-item__group-delete" title="' + BX.message('ASPRO_PROPS_GROUP_DELETE_BUTTON') + '"></div>' +
                                            '</div>' +
                                        '</div>' +
                                    '</div>'
                                );
                            }
                        });
                    }

					let sortPropWrap = document.querySelector('.props-sort-wrapper');
					if (sortPropWrap){
						sortPropWrap.addEventListener("click", function(event) {
							/* delete action */
							let buttonDeleteGroup = event.target.closest(".props-item__group-delete");
							if (buttonDeleteGroup) {
								let currentGroup = buttonDeleteGroup.closest(".prop-drag-item--group");
								if (currentGroup) {
									if (confirm(BX.message('ASPRO_PROPS_GROUP_DELETE_GROUP_CONFIRM'))) {
										currentGroup.remove();
										document.querySelector('.props-sort-wrapper').classList.add("props-sort--modified");
									}
								}
							}
							/**/

							/* edit action */
							let buttonEditGroup = event.target.closest(".props-item__group-edit");
							if (buttonEditGroup) {
								let currentGroup = buttonEditGroup.closest(".prop-drag-item--group");
								if (currentGroup) {
									let groupName = currentGroup.querySelector(".props-item__group-name");
									if (groupName) {
										groupName.setAttribute("contenteditable", "true");
										currentGroup.classList.add("prop-drag-item--editable");
										groupName.focus();
										asproPlaceCaretAtEnd(groupName);
										document.querySelector('.props-sort-wrapper').classList.add("props-sort--modified");
									}
								}
							}
							/**/

							/* apply action */
							let buttonApplyGroup = event.target.closest(".props-item__group-apply");
							if (buttonApplyGroup) {
								let currentGroup = buttonApplyGroup.closest(".prop-drag-item--group");
								if (currentGroup) {
									let groupName = currentGroup.querySelector(".props-item__group-name");
									if (groupName){
										groupName.blur();
									}
								}
							}
							/**/
						});

						sortPropWrap.addEventListener("dblclick", function(event) {
							/* edit action */
							let buttonEditGroup = event.target.closest(".props-item__group-name");
							if (buttonEditGroup){
								let currentGroup = buttonEditGroup.closest(".prop-drag-item--group");
								if (currentGroup){
									let groupName = currentGroup.querySelector(".props-item__group-name");
									if (groupName){
										groupName.setAttribute("contenteditable", "true");
										currentGroup.classList.add("prop-drag-item--editable");
										groupName.focus();
										//asproPlaceCaretAtEnd(groupName);
										document.querySelector('.props-sort-wrapper').classList.add("props-sort--modified");
									}
								}
							}
							/**/
						});

						sortPropWrap.addEventListener("focusout", function(event){
							let curElem = event.target;
							let editGroup = curElem.closest(".prop-drag-item--editable");
							if (editGroup){
								let isGroupName = curElem.classList.contains('props-item__group-name');
								if (isGroupName){
									curElem.setAttribute("contenteditable", "false");
									editGroup.classList.remove("prop-drag-item--editable");
								}
							}
						});

						window.addEventListener("keydown", function (e) {
							if (e.keyCode == 27) {
								let curGroup = document.querySelector('.props-sort-wrapper .prop-drag-item--editable .props-item__group-name');
								if (curGroup){
									curGroup.blur();
								}
							}
						});

						let iblockForm = sortPropWrap.closest("form");
						iblockForm.addEventListener("submit", function(event){
							if (document.querySelector(".props-sort--modified")){
								asproCollectPropGroup();
							}

						});
					}
				}
			});
		},
		{
			root: null,
			rootMargin: '0px',
			threshold: 0
		}
	);
	observer.observe(grupperCell);
}
</script>
