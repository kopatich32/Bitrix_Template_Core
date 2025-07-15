<?$bFirst = true;?>
<?
$bGroups = is_array($arResult['GROUPS']) && !empty($arResult['GROUPS']);
$bOffersMode = $arParams['OFFERS_MODE'] === 'Y';
?>
<?if ($bGroups):?>
	<div class="properties-group properties-group--table js-offers-group-wrap js-scrolled">
		<?foreach ($arResult['GROUPS'] as $arGroup):?>
			<?
			$bNoGroup = $arGroup['CODE'] === 'no-group' || $arGroup['NAME'] === 'NO-GROUP';
			$bOfferGroup = $bOffersMode || (isset($arGroup['OFFER_GROUP']) && $arGroup['OFFER_GROUP']);
			?>
			<div class="properties-group__group<?=($bOfferGroup ? ' js-offers-group' : '')?>" data-group-code="<?=($arGroup['CODE'] ?? 'no-group')?>">
				<?if (
					!$bNoGroup &&
					!empty($arGroup['NAME'])
				):?>
					<div class="properties-group__group-name color_dark switcher-title<?=($bFirst ? ' properties-group__group-name--first' : '')?>">
						<?=$arGroup['NAME']?>
					</div>
				<?endif;?>

				<div class="properties-group__items js-offers-group__items-wrap">
					<?foreach ($arGroup['DISPLAY_PROPERTIES'] as $arProp):?>
						<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name muted"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_dark" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE'])):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
						</div>
					<?endforeach;?>
				</div>
			</div>
			<?$bFirst = false;?>
		<?endforeach;?>
	</div>
<?else:?>
	<?if ($arResult['DISPLAY_TYPE'] != 'TABLE'):?>
		<div class="properties-group properties-group--block js-offers-group-wrap js-scrolled">
			<div class="properties-group__group<?=($bOffersMode ? ' js-offers-group' : '')?>" data-group-code="no-group">
				<div class="properties-group__items js-offers-group__items-wrap">
					<?foreach ($arResult['DISPLAY_PROPERTIES'] as $arProp):?>
						<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name muted"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_dark" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE'])):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
						</div>
					<?endforeach;?>

					<?if ($arResult['OFFER_DISPLAY_PROPERTIES']):?>
						<?foreach ($arResult['OFFER_DISPLAY_PROPERTIES'] as $arProp):?>
							<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
							<div class="properties-group__item js-offers-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
								<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
									<span itemprop="name" class="properties-group__name muted"><?=$arProp['NAME']?></span>
									<?if ($bHint):?>
										<div class="hint hint--down">
											<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
											<div class="tooltip"><?=$arProp['HINT']?></div>
										</div>
									<?endif;?>
								</div>

								<div class="properties-group__value-wrap">
									<div class="properties-group__value color_dark" itemprop="value">
										<?if (is_array($arProp['DISPLAY_VALUE'])):?>
											<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
										<?else:?>
											<?=$arProp['DISPLAY_VALUE']?>
										<?endif;?>
									</div>
								</div>
							</div>
						<?endforeach;?>
					<?endif;?>
				</div>
			</div>
		</div>
	<?else:?>
		<div class="properties-group properties-group--table js-offers-group-wrap js-scrolled">
			<div class="properties-group__group<?=($bOffersMode ? ' js-offers-group' : '')?>" data-group-code="no-group">
				<div class="properties-group__items js-offers-group__items-wrap">
					<?foreach ($arResult['DISPLAY_PROPERTIES'] as $arProp):?>
						<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
						<div class="properties-group__item<?=($bOffersMode || $arProp['IS_OFFER'] ? ' js-offers-group__item' : '')?>" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
							<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
								<span itemprop="name" class="properties-group__name muted"><?=$arProp['NAME']?></span>
								<?if ($bHint):?>
									<div class="hint hint--down">
										<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
										<div class="tooltip"><?=$arProp['HINT']?></div>
									</div>
								<?endif;?>
							</div>

							<div class="properties-group__value-wrap">
								<div class="properties-group__value color_dark" itemprop="value">
									<?if (is_array($arProp['DISPLAY_VALUE'])):?>
										<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
									<?else:?>
										<?=$arProp['DISPLAY_VALUE']?>
									<?endif;?>
								</div>
							</div>
						</div>
					<?endforeach;?>
					<?if ($arResult['OFFER_DISPLAY_PROPERTIES']):?>
						<?foreach ($arResult['OFFER_DISPLAY_PROPERTIES'] as $arProp):?>
							<?$bHint = $arProp['HINT'] && $arParams['SHOW_HINTS'] == 'Y';?>
							<div class="properties-group__item js-offers-group__item" itemprop="additionalProperty" itemscope itemtype="http://schema.org/PropertyValue">
								<div class="properties-group__name-wrap<?=($bHint ? ' properties-group__name-wrap--whint' : '')?>">
									<span itemprop="name" class="properties-group__name muted"><?=$arProp['NAME']?></span>
									<?if ($bHint):?>
										<div class="hint hint--down">
											<span class="hint__icon rounded bg-theme-hover border-theme-hover bordered"><i>?</i></span>
											<div class="tooltip"><?=$arProp['HINT']?></div>
										</div>
									<?endif;?>
								</div>
								<div class="properties-group__value-wrap">
									<div class="properties-group__value color_dark" itemprop="value">
										<?if (is_array($arProp['DISPLAY_VALUE'])):?>
											<?=implode(', ', $arProp['DISPLAY_VALUE'])?>
										<?else:?>
											<?=$arProp['DISPLAY_VALUE']?>
										<?endif;?>
									</div>
								</div>
							</div>
						<?endforeach;?>
					<?endif;?>
				</div>
			</div>
		</div>
	<?endif;?>
<?endif;?>
