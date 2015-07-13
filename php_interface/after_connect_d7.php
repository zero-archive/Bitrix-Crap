<?
$connection = \Bitrix\Main\Application::getConnection();
$connection->queryExecute("SET NAMES 'utf8'");
$connection->queryExecute("SET wait_timeout=28800");
$connection->queryExecute('SET collation_connection = "utf8_unicode_ci"');
$connection->queryExecute("SET optimizer_search_depth = 0");
