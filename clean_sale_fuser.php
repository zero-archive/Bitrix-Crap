<?
$link = mysql_connect('localhost', 'login', 'pass');
mysql_select_db('database');
for ($i = 1; $i < 7000; $i++) {
    $result = mysql_query('DELETE FROM b_sale_fuser WHERE b_sale_fuser.USER_ID IS NULL LIMIT 3000;');
    if ($result) echo $i . PHP_EOL;
}
mysql_close($link);
