<?
namespace Aspro\Max\CRM;

use Bitrix\Main\Localization\Loc,
    Bitrix\Main\Loader,
    Bitrix\Main\Config\Option,
    Bitrix\Sale,
    Aspro\Max\CRM,
    CMax as Solution;

Loc::loadMessages(__FILE__);

class Helper {
    const FORM_LEAD_REF_VALUE = 'form:aspro-max';
    const ORDER_LEAD_REF_VALUE = 'order:aspro-max';

    public static function getFormsFieldsMap() {
        $result = [];

        foreach ([
            'FORM_NAME',
            'FORM_SID',
            'SITE_ID',
            'SERVER_NAME',
            'RESULT_ID',
            'FORM_ALL',
            'FORM_ALL_HTML',
        ] as $field
        ) {
            $result[$field] = Loc::getMessage('CRM_FORM_FIELD_'.$field);
        }

        return $result;
    }

    public static function getFormsQuestionsMap(int $formId) {
        $result = [];

        $arQuestions = static::getFormQuestions($formId);
        foreach ($arQuestions as $questionId => $arQuestion) {
            $result[$questionId] = $arQuestion['TITLE'].' ('.$arQuestion['SID'].')';
        }

        return $result;
    }

    public static function getForms(string $siteId) :array {
        $forms = [];

        if (Loader::includeModule('form')) {
            $dbRes = \CForm::GetList(
                $by = 's_id', 
                $order = 'ASC', 
                [
                    'ACTIVE' => 'Y', 
                    'SITE' => [$siteId],
                ],
                $isFiltered
            );
            while($arForm = $dbRes->Fetch()){
                $forms[$arForm['ID']] = $arForm;
            }
        }

        return $forms;
    } 

    public static function getFormQuestions(int $formId) :array {
        $questions = [];

        if (Loader::includeModule('form')) {
            $dbRes = \CFormField::GetList(
                $formId,
                'ALL',
                $by = 'sort',
                $order = 'asc',
                [
                    'ACTIVE' => 'Y',
                ],
                $isFiltered
            );
            while($question = $dbRes->Fetch()){
                $questions[$question['ID']] = $question;
            }
        }

        return $questions;
    }

    public static function getFormResults(array $order = ['s_id' => 'desc'], int $formId) {
        $results = null;

        if (Loader::includeModule('form')) {
            $order = $order ?: ['s_id' => 'desc'];

            $results = \CFormResult::GetList(
                $formId, 
                key($order),
                current($order), 
                [], 
                $isFiltered, 
                'N', 
                false
            );
        }

        return $results;
    }

    public static function setSendingFormResult(int $resultId, string $siteId, array $result) {
        if (is_array($result)) {
            $value = [];
            foreach (CRM\Type::getAvailable() as $type) {
                if ($result[$type]) {
                    $value[$type] = $result[$type];
                }
            }

            Option::set(Solution::moduleID, 'CRM_SEND_FORM_'.$resultId, serialize($result), $siteId);
        }
    }

    public static function getSendingFormResult(int $resultId, string $siteId) :array {
        $value = Option::get(Solution::moduleID, 'CRM_SEND_FORM_'.$resultId, 'a:0:{}', $siteId);
        $value = Solution::unserialize($value);
        if (!is_array($value)) {
            $value = [];
        }

        $result = [];
        foreach (CRM\Type::getAvailable() as $type) {
            $result[$type] = $value[$type] ?? [];
        }

        return $result;
    }

    public static function sendFormResult(int $formId, int $resultId, CRM\Base\Connection $connection) {
        if (!$connection->active) {
            throw new \Exception('CRM integration is not active ('.$connection->type.')');
        }

        if (!Loader::includeModule('form')) {
            throw new \Exception('Module not installed (form)');
        }

        $siteId = $connection->siteId;
        $matches = (array)$connection->forms_matches[$formId];

        if($matches){
            $lead = CRM\Lead::newInstance($connection);

            // site
            $arSite = \CSite::GetByID($siteId)->Fetch();
            $serverName = ($GLOBALS['APPLICATION']->IsHTTPS() ? 'https://' : 'http://').preg_replace('/https?:\/\//i', '', $arSite['SERVER_NAME']);

            // get form
            \CFormResult::GetDataByID($resultId, array(), $arResultFields, $arAnswers);
            if (!$arResultFields) {
                throw new \Exception('Form`s result not found (form: '.$formId.'; result: '.$resultId.')');
            }

            // get fields
            \CForm::GetResultAnswerArray(
                $formId,
                $arrColumns,
                $arrAnswers,
                $arrAnswersVarname,
                [
                    'RESULT_ID' => $resultId,
                ]
            );

            // fill lead fieds
            foreach ($matches as $key => $ids) {
                foreach ((array)$ids as $id) {
                    if (strlen($id)) {
                        $value = '';

                        switch($id){
                            case 'FORM_NAME':
                                $value = $arResultFields['NAME'];

                                break;
                            case 'FORM_SID':
                                $value = $arResultFields['SID'];

                                break;
                            case 'SITE_ID':
                                $value = $siteId;

                                break;
                            case 'SERVER_NAME':
                                $value = $serverName;

                                break;
                            case 'RESULT_ID':
                                $value = $arResultFields['ID'];

                                break;
                            case 'FORM_ALL':
                                $value = self::getAllFormFields($formId, $resultId, $arAnswers);

                                break;
                            case 'FORM_ALL_HTML':
                                $value = self::getAllFormFieldsHTML($formId, $resultId, $arAnswers);

                                break;
                            default:
                                if($arrAnswers[$resultId][$id]){
                                    $value = (isset($arrAnswers[$resultId][$id][$id]['USER_TEXT']) && $arrAnswers[$resultId][$id][$id]['USER_TEXT'] ? $arrAnswers[$resultId][$id][$id]['USER_TEXT'] : $arrAnswers[$resultId][$id][$id]['ANSWER_TEXT']);
                                }

                                break;
                        }

                        if (strlen($value)) {
                            if (!array_key_exists($key, $lead->values)) {
                                $lead->values[$key] = [];
                            }

                            $lead->values[$key][] = $value;
                        }
                    }
                }
            }

            foreach ($lead->values as $key => &$value) {
                $value = implode(PHP_EOL.PHP_EOL, $value);
            }
            unset($value);

            if($lead->values){
                $leadClass = get_class($lead);

                $titleField = $leadClass::getTitleField();
                if (
                    strlen($titleField) &&
                    !strlen($lead->values[$titleField])
                ) {
                    $lead->values[$titleField] = $connection->forms_lead_title;
                    $lead->values[$titleField] = str_replace(
                        [
                            '#RESULT_ID#',
                            '#FORM_NAME#',
                            '#FORM_SID#',
                            '#DATE_CREATE#',
                            '#SITE_ID#',
                            '#SERVER_NAME#',
                        ],
                        [
                            $resultId,
                            $arResultFields['NAME'],
                            $arResultFields['SID'],
                            $arResultFields['DATE_CREATE'],
                            $siteId,
                            $serverName,
                        ],
                        $lead->values[$titleField]
                    );
                }

                if ($lead instanceof CRM\Base\Lead\iReferential) {
                    $refField = $leadClass::getRefField();
                    if (
                        strlen($refField) &&
                        !strlen($lead->values[$refField])
                    ) {
                        $lead->values[$refField] = static::FORM_LEAD_REF_VALUE;
                    }
    
                    $refIdField = $leadClass::getRefIdField();
                    if (
                        strlen($refIdField) &&
                        !strlen($lead->values[$refIdField])
                    ) {
                        $lead->values[$refIdField] = $formId.'_'.$resultId;
                    }
                }

                if ($connection instanceof CRM\Amocrm\Connection) {
                    if (!$lead->values['tags_leads']) {
                        $lead->values['tags_leads'] = $connection->tags_leads;
                    }
                }

                $lead->create();
                $leadId = $lead->id;
                if($leadId){
                    $result = self::getSendingFormResult($resultId, $siteId);
                    $result[$connection->type][$connection->domain] = $leadId;
                    self::setSendingFormResult($resultId, $siteId, $result);

                    return $leadId;
                }
            }
        }

        return false;
    }

    public static function getAllFormFields($WEB_FORM_ID, $RESULT_ID, $arAnswers) {
        global $APPLICATION;

        $strResult = "";

        $w = \CFormField::GetList($WEB_FORM_ID, "ALL", $by = 's_sort', $order = 'asc', array("ACTIVE" => "Y"), $is_filtered);
        while ($wr=$w->Fetch())
        {
            $answer = "";
            $answer_raw = '';
            if (is_array($arAnswers[$wr["SID"]]))
            {
                $bHasDiffTypes = false;
                $lastType = '';
                foreach ($arAnswers[$wr['SID']] as $arrA)
                {
                    if ($lastType == '') $lastType = $arrA['FIELD_TYPE'];
                    elseif ($arrA['FIELD_TYPE'] != $lastType)
                    {
                        $bHasDiffTypes = true;
                        break;
                    }
                }

                foreach($arAnswers[$wr["SID"]] as $arrA)
                {
                    if ($wr['ADDITIONAL'] == 'Y')
                    {
                        $arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
                    }

                    $USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"]))>0);
                    $ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"]))>0);
                    $ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"]))>0);
                    $USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"])>0);

                    if (
                        $bHasDiffTypes
                        && !$USER_TEXT_EXIST
                        && (
                            $arrA['FIELD_TYPE'] == 'text'
                            ||
                            $arrA['FIELD_TYPE'] == 'textarea'
                        )
                    )
                    {
                        continue;
                    }

                    if (strlen(trim($answer)) > 0)
                        $answer .= "\n";
                    if (strlen(trim($answer_raw)) > 0)
                        $answer_raw .= ",";

                    if ($ANSWER_TEXT_EXIST)
                        $answer .= $arrA["ANSWER_TEXT"].': ';

                    switch ($arrA['FIELD_TYPE'])
                    {
                        case 'text':
                        case 'textarea':
                        case 'email':
                        case 'url':
                        case 'hidden':
                        case 'date':
                        case 'password':

                            if ($USER_TEXT_EXIST)
                            {
                                $answer .= trim($arrA["USER_TEXT"]);
                                $answer_raw .= trim($arrA["USER_TEXT"]);
                            }

                        break;

                        case 'checkbox':
                        case 'multiselect':
                        case 'radio':
                        case 'dropdown':

                            if ($ANSWER_TEXT_EXIST)
                            {
                                $answer = substr($answer, 0, -2).' ';
                                $answer_raw .= $arrA['ANSWER_TEXT'];
                            }

                            if ($ANSWER_VALUE_EXIST)
                            {
                                $answer .= '('.$arrA['ANSWER_VALUE'].') ';
                                if (!$ANSWER_TEXT_EXIST)
                                {
                                    $answer_raw .= $arrA['ANSWER_VALUE'];
                                }
                            }

                            if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
                            {
                                $answer_raw .= $arrA['ANSWER_ID'];
                            }

                            $answer .= '['.$arrA['ANSWER_ID'].']';

                        break;

                        case 'file':
                        case 'image':

                            if ($USER_FILE_EXIST)
                            {
                                $f = \CFile::GetByID($arrA["USER_FILE_ID"]);
                                if ($fr = $f->Fetch())
                                {
                                    $file_size = \CFile::FormatSize($fr["FILE_SIZE"]);
                                    $url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&action=download&lang=".LANGUAGE_ID;

                                    if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
                                    {
                                        $answer .= $arrA["USER_FILE_NAME"]." [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")\n".$url;
                                    }
                                    else
                                    {
                                        $answer .= $arrA["USER_FILE_NAME"]." (".$file_size.")\n".$url."&action=download";
                                    }
                                }

                                $answer_raw .= $arrA['USER_FILE_NAME'];
                            }

                        break;
                    }
                }
            }

            $strResult .= $wr["TITLE"].":\r\n".(strlen($answer)<=0 ? " " : $answer)."\r\n\r\n";
        }

        return $strResult;
    }

    public static function getAllFormFieldsHTML($WEB_FORM_ID, $RESULT_ID, $arAnswers) {
        global $APPLICATION;

        $strResult = "";

        $w = \CFormField::GetList($WEB_FORM_ID, "ALL", $by, $order, array("ACTIVE" => "Y"), $is_filtered);
        while ($wr=$w->Fetch())
        {
            $answer = "";
            $answer_raw = '';
            if (is_array($arAnswers[$wr["SID"]]))
            {
                $bHasDiffTypes = false;
                $lastType = '';
                foreach ($arAnswers[$wr['SID']] as $arrA)
                {
                    if ($lastType == '') $lastType = $arrA['FIELD_TYPE'];
                    elseif ($arrA['FIELD_TYPE'] != $lastType)
                    {
                        $bHasDiffTypes = true;
                        break;
                    }
                }

                foreach($arAnswers[$wr["SID"]] as $arrA)
                {
                    if ($wr['ADDITIONAL'] == 'Y')
                    {
                        $arrA['FIELD_TYPE'] = $wr['FIELD_TYPE'];
                    }

                    $USER_TEXT_EXIST = (strlen(trim($arrA["USER_TEXT"]))>0);
                    $ANSWER_TEXT_EXIST = (strlen(trim($arrA["ANSWER_TEXT"]))>0);
                    $ANSWER_VALUE_EXIST = (strlen(trim($arrA["ANSWER_VALUE"]))>0);
                    $USER_FILE_EXIST = (intval($arrA["USER_FILE_ID"])>0);

                    if (
                        $bHasDiffTypes
                        &&
                        !$USER_TEXT_EXIST
                        &&
                        (
                            $arrA['FIELD_TYPE'] == 'text'
                            ||
                            $arrA['FIELD_TYPE'] == 'textarea'
                        )
                    )
                        continue;

                    if (strlen(trim($answer))>0) $answer .= "<br />";
                    if (strlen(trim($answer_raw))>0) $answer_raw .= ",";

                    if ($ANSWER_TEXT_EXIST)
                        $answer .= $arrA["ANSWER_TEXT"].': ';

                    switch ($arrA['FIELD_TYPE'])
                    {
                        case 'text':
                        case 'textarea':
                        case 'hidden':
                        case 'date':
                        case 'password':

                            if ($USER_TEXT_EXIST)
                            {
                                $answer .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                                $answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                            }

                        break;

                        case 'email':
                        case 'url':

                            if ($USER_TEXT_EXIST)
                            {
                                $answer .= '<a href="'.($arrA['FIELD_TYPE'] == 'email' ? 'mailto:' : '').trim($arrA["USER_TEXT"]).'">'.htmlspecialcharsbx(trim($arrA["USER_TEXT"])).'</a>';
                                $answer_raw .= htmlspecialcharsbx(trim($arrA["USER_TEXT"]));
                            }

                        break;

                        case 'checkbox':
                        case 'multiselect':
                        case 'radio':
                        case 'dropdown':

                            if ($ANSWER_TEXT_EXIST)
                            {
                                $answer = htmlspecialcharsbx(substr($answer, 0, -2).' ');
                                $answer_raw .= htmlspecialcharsbx($arrA['ANSWER_TEXT']);
                            }

                            if ($ANSWER_VALUE_EXIST)
                            {
                                $answer .= '('.htmlspecialcharsbx($arrA['ANSWER_VALUE']).') ';
                                if (!$ANSWER_TEXT_EXIST)
                                    $answer_raw .= htmlspecialcharsbx($arrA['ANSWER_VALUE']);
                            }

                            if (!$ANSWER_VALUE_EXIST && !$ANSWER_TEXT_EXIST)
                                $answer_raw .= $arrA['ANSWER_ID'];

                            $answer .= '['.$arrA['ANSWER_ID'].']';

                        break;

                        case 'file':
                        case 'image':

                            if ($USER_FILE_EXIST)
                            {
                                $f = \CFile::GetByID($arrA["USER_FILE_ID"]);
                                if ($fr = $f->Fetch())
                                {
                                    $file_size = \CFile::FormatSize($fr["FILE_SIZE"]);
                                    $url = ($APPLICATION->IsHTTPS() ? "https://" : "http://").$_SERVER["HTTP_HOST"]. "/bitrix/tools/form_show_file.php?rid=".$RESULT_ID. "&hash=".$arrA["USER_FILE_HASH"]."&lang=".LANGUAGE_ID;

                                    if ($arrA["USER_FILE_IS_IMAGE"]=="Y")
                                    {
                                        $answer .= "<a href=\"$url\">".htmlspecialcharsbx($arrA["USER_FILE_NAME"])."</a> [".$fr["WIDTH"]." x ".$fr["HEIGHT"]."] (".$file_size.")";
                                    }
                                    else
                                    {
                                        $answer .= "<a href=\"$url&action=download\">".htmlspecialcharsbx($arrA["USER_FILE_NAME"])."</a> (".$file_size.")";
                                    }

                                    $answer_raw .= htmlspecialcharsbx($arrA['USER_FILE_NAME']);
                                }
                            }

                        break;
                    }
                }
            }

            $strResult .= $wr["TITLE"].":<br />".(strlen($answer)<=0 ? " " : $answer)."<br /><br />";
        }

        return $strResult;
    }

    public static function getOrdersFieldsMap() {
        $result = [];

        foreach ([
            'ID',
            'ACCOUNT_NUMBER',
            'SITE_ID',
            'SERVER_NAME',
            'PERSON_TYPE',
            'PRICE',
            'GOODS',
            'USER_DESCRIPTION',
            'COMMENTS',
        ] as $field
        ) {
            $result[$field] = Loc::getMessage('CRM_ORDER_FIELD_'.$field);
        }

        return $result;
    }

    public static function getOrdersPropertiesMap(int $personTypeId) {
        $result = [];

        $properties = static::getOrdersProperties();
        if ($properties) {
            $personTypes = static::getOrdersPersonTypes();

            foreach ($properties as $property) {
                if ($personTypeId == $property['PERSON_TYPE_ID']) {
                    $result[$property['ID']] = $property['NAME'].' ('.$property['CODE'].')';
                }
            }
        }

        return $result;
    }

    public static function getOrdersProperties() {
        $properties = [];

        if (Loader::includeModule('sale')) {
            $dbRes = Sale\Property::getList([
                'order' => ['sort' => 'asc'],
				'filter' => [
					'ACTIVE' => 'Y',
					'TYPE' => [
						'STRING',
						'NUMBER',
						'Y/N',
						// 'ENUM',
						'DATE',
						'LOCATION'
					],
				],
                'select' => [
                    'ID',
                    'PERSON_TYPE_ID',
                    'NAME',
                    'TYPE',
                    'REQUIRED',
                    'DEFAULT_VALUE',
                    'CODE',
                    'MULTIPLE',
                ],
            ]);
            while($property = $dbRes->Fetch()){
                $properties[] = $property;
            }
        }

        return $properties;
    }

    public static function getOrdersPersonTypes(string $siteId = '') {
        $persontypes = [];

        if (Loader::includeModule('sale')) {
            $filter = [
                'ACTIVE' => 'Y',
            ];

            if (strlen($siteId)) {
                $filter['LID'] = $siteId;
            }

            $dbRes = Sale\PersonType::getList([
                'order' => ['SORT' => 'asc'],
				'filter' => $filter,
            ]);
            while($persontype = $dbRes->Fetch()){
                $persontypes[$persontype['ID']] = $persontype;
            }
        }

        return $persontypes;
    }

    public static function getOrders(array $order = ['ID' => 'desc'], int $personTypeId, string $siteId) {
        $orders = null;

        if (Loader::includeModule('sale')) {
            $order = $order ?: ['ID' => 'desc'];

            $orders = Sale\Order::getList([
                'order' => $order,
                'filter' => [
                    'LID' => $siteId,
                    'PERSON_TYPE_ID' => $personTypeId,
                ],
                'select' => [
                    'ID',
                    'ACCOUNT_NUMBER',
                    'STATUS_ID',
                    'STATUS.NAME',
                    'PRICE',
                    'CURRENCY',
                    'PAYED',
                    'CANCELED',
                    'DATE_INSERT',
                ],
            ]);
        }

        return $orders;
    }

    public static function setSendingOrderResult(int $orderId, string $siteId, array $result) {
        if (is_array($result)) {
            $value = [];
            foreach (CRM\Type::getAvailable() as $type) {
                if ($result[$type]) {
                    $value[$type] = $result[$type];
                }
            }

            Option::set(Solution::moduleID, 'CRM_SEND_ORDER_'.$orderId, serialize($value), $siteId);
        }
    }

    public static function getSendingOrderResult(int $orderId, string $siteId) :array {
        $value = Option::get(Solution::moduleID, 'CRM_SEND_ORDER_'.$orderId, 'a:0:{}', $siteId);
        $value = Solution::unserialize($value);
        if (!is_array($value)) {
            $value = [];
        }

        $result = [];
        foreach (CRM\Type::getAvailable() as $type) {
            $result[$type] = $value[$type] ?? [];
        }

        return $result;
    }

    public static function getOrderBasketProductsLine(Sale\Order $order) {
        $lines = [];

        if (Loader::includeModule('sale')) {
            $basket = $order->getBasket();
            $basketItems = $basket->getBasketItems();
            foreach ($basketItems as $basketItem) {
                $productId = $basketItem->getField('PRODUCT_ID');
                $name = $basketItem->getField('NAME');
                $qnt = $basketItem->getField('QUANTITY');
                $measure = $basketItem->getField('MEASURE_NAME');
                $price = $basketItem->getField('PRICE');
                $currency = $basketItem->getField('CURRENCY');
                $lines[$productId] = $name.' (ID: '.$productId.')'.', '.$qnt.' '.$measure.', '.\CCurrencyLang::CurrencyFormat($price, $currency).';';
            }
        }

        return implode(PHP_EOL, $lines);
    }

    public static function sendOrder(int $orderId, CRM\Base\Connection $connection) {
        if (!$connection->active) {
            throw new \Exception('CRM integration is not active ('.$connection->type.')');
        }

        if (!Loader::includeModule('sale')) {
            throw new \Exception('Module not installed (sale)');
        }

        $order = Sale\Order::load($orderId);
        if (!$order) {
            throw new \Exception('Order not found ('.$orderId.')');
        }

        $siteId = $order->getSiteId();
        $personTypeId = $order->getPersonTypeId();
        $matches = (array)$connection->orders_matches[$personTypeId];

        if($matches){
            $lead = CRM\Lead::newInstance($connection);
            
            $arOrder = [
                'FIELDS' => [],
                'PROPERTIES' => [],
            ];

            // site
            $arSite = \CSite::GetByID($siteId)->Fetch();
            $serverName = ($GLOBALS['APPLICATION']->IsHTTPS() ? 'https://' : 'http://').preg_replace('/https?:\/\//i', '', $arSite['SERVER_NAME']);

            // person types
            $personTypes = static::getOrdersPersonTypes();

            // get order fields
            foreach ($order->getFields() as $fieldCode => $values) {
                $values = is_array($values) ? $values : [$values];                

                foreach ($values as &$value) {
                    if (
                        $value instanceof \Bitrix\Main\Type\Date ||
                        $value instanceof \Bitrix\Main\Type\DateTime
                    ) {
                        $value = $value->toString();
                    }
                }
                unset($value);

                $arOrder['FIELDS'][$fieldCode] = implode(', ', $values);
            }

            // get order properties
            $propertyCollection = $order->getPropertyCollection();
            foreach ($propertyCollection as $property) {
                $values = $property->getValue();
                $values = is_array($values) ? $values : [$values];

                foreach ($values as &$value) {
                    if (
                        $value instanceof \Bitrix\Main\Type\Date ||
                        $value instanceof \Bitrix\Main\Type\DateTime
                    ) {
                        $value = $value->toString();
                    }

                    $isLocaion = $property->getField('IS_LOCATION') == 'Y';
                    if (method_exists($property, 'getProperty')) {
                        $isLocaion = $property->getProperty()['IS_LOCATION'] === 'Y';
                    }

                    if (
                        $isLocaion &&
                        strlen($value)
                    ) {
                        $arLocation = [];

                        $res = Sale\Location\LocationTable::getList(array(
                            'filter' => array(
                                '=CODE' => $value, 
                                '=PARENTS.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                                '=PARENTS.TYPE.NAME.LANGUAGE_ID' => LANGUAGE_ID,
                            ),
                            'select' => array(
                                'I_ID' => 'PARENTS.ID',
                                'I_NAME_RU' => 'PARENTS.NAME.NAME',
                                'I_TYPE_CODE' => 'PARENTS.TYPE.CODE',
                                'I_TYPE_NAME_RU' => 'PARENTS.TYPE.NAME.NAME'
                            ),
                            'order' => array(
                                'PARENTS.DEPTH_LEVEL' => 'asc'
                            )
                        ));
                        while($item = $res->fetch()){
                            $arLocation[] = $item['I_NAME_RU'];
                        }

                        $value = $arLocation ? implode(', ', $arLocation) : $value;
                    }
                }
                unset($value);

                $arOrder['PROPERTIES'][$property->getPropertyId()] = implode(', ', $values);
            }

            // fill lead fieds
            foreach ($matches as $key => $ids) {
                foreach ((array)$ids as $id) {
                    if (strlen($id)) {
                        $value = '';

                        switch($id){
                            case 'PERSON_TYPE':
                                $personTypeId = $arOrder['FIELDS']['PERSON_TYPE_ID'];
                                $value = $personTypeId;
                                if ($personTypes[$personTypeId]) {
                                    $value = $personTypes[$personTypeId]['NAME'].' ('.$personTypeId.')';
                                }
        
                                break;
                            case 'SITE_ID':
                                $value = $order->getSiteId();
        
                                break;
                            case 'SERVER_NAME':
                                $value = $serverName;
        
                                break;
                            case 'GOODS':
                                $value = static::getOrderBasketProductsLine($order);
        
                                break;
                            default:
                                if (array_key_exists($id, $arOrder['FIELDS'])) {
                                    $value = $arOrder['FIELDS'][$id];
                                } elseif (array_key_exists($id, $arOrder['PROPERTIES'])) {
                                    $value = $arOrder['PROPERTIES'][$id];
                                }
        
                                break;
                        }

                        if (strlen($value)) {
                            if (!array_key_exists($key, $lead->values)) {
                                $lead->values[$key] = [];
                            }

                            $lead->values[$key][] = $value;
                        }
                    }
                }
            }

            foreach ($lead->values as $key => &$value) {
                $value = implode(PHP_EOL.PHP_EOL, $value);
            }
            unset($value);

            if($lead->values){
                $leadClass = get_class($lead);

                $titleField = $leadClass::getTitleField();
                if (
                    strlen($titleField) &&
                    !strlen($lead->values[$titleField])
                ) {
                    $lead->values[$titleField] = $connection->orders_lead_title;
                    $lead->values[$titleField] = str_replace(
                        [
                            '#ORDER_ID#',
                            '#ACCOUNT_NUMBER#',
                            '#DATE_INSERT#',
                            '#SITE_ID#',
                            '#SERVER_NAME#',
                        ],
                        [
                            $orderId,
                            $arOrder['FIELDS']['ACCOUNT_NUMBER'],
                            $arOrder['FIELDS']['DATE_INSERT'],
                            $siteId,
                            $serverName,
                        ],
                        $lead->values[$titleField]
                    );
                }

                if ($lead instanceof CRM\Base\Lead\iReferential) {
                    $refField = $leadClass::getRefField();
                    if (
                        strlen($refField) &&
                        !strlen($lead->values[$refField])
                    ) {
                        $lead->values[$refField] = static::ORDER_LEAD_REF_VALUE;
                    }
    
                    $refIdField = $leadClass::getRefIdField();
                    if (
                        strlen($refIdField) &&
                        !strlen($lead->values[$refIdField])
                    ) {
                        $lead->values[$refIdField] = $orderId;
                    }
                }

                if ($connection instanceof CRM\Amocrm\Connection) {
                    if (!$lead->values['tags_leads']) {
                        $lead->values['tags_leads'] = $connection->tags_leads;
                    }
                }

                $lead->create();
                $leadId = $lead->id;
                if($leadId){
                    $result = self::getSendingOrderResult($orderId, $siteId);
                    $result[$connection->type][$connection->domain] = $leadId;
                    self::setSendingOrderResult($orderId, $siteId, $result);

                    return $leadId;
                }
            }
        }

        return false;
    }
}
