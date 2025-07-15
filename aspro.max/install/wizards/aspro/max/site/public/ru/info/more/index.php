<?
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle("Возможности");
?>

<h4>UI элементы решения</h4>
<p>В решении поддерживается множество UI элементов, которые Вы с легкостью можете использовать для развития сайта и добавления нового функционала.</p>

<div class="row"> 						
	<div class="col-md-3 col-sm-6 col-xs-12"> 							
		<div class="more_wrapper">
			<a href="#SITE_DIR#info/more/typograpy/"  data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/decoration.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Оформление
				</div>
			</a>
		</div>
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="#SITE_DIR#info/more/buttons/"  data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/buttons.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Кнопки
				</div>
			</a>
		</div>							
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="#SITE_DIR#info/more/icons/"  data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/icons.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Иконки
				</div>
			</a>
		</div>								
	</div>
	<div class="col-md-3 col-sm-6 col-xs-12"> 
		<div class="more_wrapper">
			<a href="#SITE_DIR#info/more/elements/"  data-toggle="tooltip" title="" data-original-title="Можно использовать Tooltip!">
				<?=CMax::showIconSvg("more_icon colored", SITE_TEMPLATE_PATH.'/images/svg/elements.svg', '', '', true, false);?>
				<div class="title color-theme-hover">
					Элементы
				</div>
			</a>
		</div>								
	</div>
</div>

<br/><br/>
<h4>Другие решения компании Аспро</h4>
<p>Готовые интернет-магазины, корпоративные сайты, отраслевые решения для быстрого запуска онлайн-бизнеса и облачные сервисы для управления проектом.</p>

<div class="row"> 						
	<div class="col-md-6 col-sm-6 col-xs-12"> 							
		<div><b>Готовые сайты на 1С-Битрикс</b></div>
		<ul>
			<li><a href="#SITE_DIR#info/more/ecommerce/">Интернет-магазины</a></li>
			<li><a href="#SITE_DIR#info/more/corp/">Корпоративные сайты</a></li>
			<li><a href="#SITE_DIR#info/more/themes/">Сайты по тематикам</a></li>
		</ul>

		<div><b>Решения</b></div>
		<ul>
			<li><a href="#SITE_DIR#info/more/dev/">Разработка сайтов</a></li>
			<li><a href="#SITE_DIR#info/more/licenses/">Лицензии 1С-Битрикс</a></li>
			<li><a href="#SITE_DIR#info/more/1c/">Интеграция сайта с 1С</a></li>
			<li><a href="#SITE_DIR#info/more/seo/">Оптимизация и продвижение</a></li>			
			<li><a href="#SITE_DIR#info/more/support/">Поддержка и сопровождение</a></li>			
		</ul>
	</div>
	<div class="col-md-6 col-sm-6 col-xs-12"> 							
		<div><b>Облачные продукты</b></div>
		<ul>
			<li><a href="#SITE_DIR#info/more/cloud/">Аспро.Cloud</a></li>
			<li><a href="#SITE_DIR#info/more/agile/">Аспро.Agile</a></li>
			<li><a href="#SITE_DIR#info/more/link/">Аспро.Link</a></li>
			<li><a href="#SITE_DIR#info/more/hr/">Аспро.HR</a></li>
			<li><a href="#SITE_DIR#info/more/sklad/">Аспро.Склад</a></li>
			<li><a href="#SITE_DIR#info/more/fin/">Аспро.Финансы</a></li>
			<li><a href="#SITE_DIR#info/more/kb/">Аспро.Знания</a></li>
			<li><a href="#SITE_DIR#info/more/edu/">Аспро.Обучение</a></li>
			<li><a href="#SITE_DIR#info/more/flowlu/">Flowlu</a></li>
			<li><a href="#SITE_DIR#info/more/flowlulink/">Flowlu.Link</a></li>
		</ul>
	</div>
</div>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");?>