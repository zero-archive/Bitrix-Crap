<?
/**
 * Удаление убогой таблицы b_sale_fuser когда она выросла до невъебенных высот
 */
$_SERVER['DOCUMENT_ROOT'] = '/home/user_kolesatyt/data/www/kolesatyt.ru';

define('NO_KEEP_STATISTIC', true);
define('NOT_CHECK_PERMISSIONS', true);

require($_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_before.php');

@set_time_limit(0);

function DeleteOld($nDays)
{
    global $DB;

    if (CModule::IncludeModule('sale')) {
        $nDays = IntVal($nDays);
        $strSql =
            'SELECT f.ID ' .
            'FROM b_sale_fuser f ' .
            'LEFT JOIN b_sale_order o ON (o.USER_ID = f.USER_ID) ' .
            'WHERE ' .
            '   TO_DAYS(f.DATE_UPDATE)<(TO_DAYS(NOW())-' . $nDays . ') ' .
            '   AND o.ID is null ' .
            '   AND f.USER_ID is null ' .
            'LIMIT 1000';

        $db_res = $DB->Query($strSql, false, 'File: ' . __FILE__ . '<br>Line: ' . __LINE__);

        while ($ar_res = $db_res->Fetch()) {
            CSaleBasket::DeleteAll($ar_res['ID'], false);
            CSaleUser::Delete($ar_res['ID']);
        }
    }

    return true;
}

for ($i = 0; $i < 10000; $i++) {
    DeleteOld(7);
}
