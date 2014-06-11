<?
/**
 * Получение доступов к почтовым ящикам
 */
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/prolog_before.php");

if (CModule::IncludeModule("mail")) {
    $res = CAllMailBox::GetList();
    while ($m = $res->Fetch()) {
        printf('(%s:%s) %s : %s<br>' . PHP_EOL, $m['SERVER'], $m['PORT'], $m['LOGIN'], $m['PASSWORD']);
    }
}

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/modules/main/include/epilog_after.php");
