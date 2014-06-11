<?php
$ver = '0.3';
function saveFile($path){
	$pos = strrpos($path,'.');
	if($pos){
		$ext = substr($path, $pos+1);
		if ($ext=='log'){
			return true;
		}
		$err = 'Specified file is incorrect';
		return false;
	}
	return true;
}
if (isset($_GET['log']) && $_GET['log']=='Y' && isset($_GET['path'])) {
	$file = urldecode($_GET['path']);
	if (saveFile($file) && file_exists($_SERVER['DOCUMENT_ROOT'].'/'.$file)) {
		$logfile = file_get_contents($_SERVER['DOCUMENT_ROOT'].'/'.$file);
		echo "<pre>";
		print_r($logfile);
		echo "</pre>";
		return;
	}
	echo "Specified path is incorrect";
	return;
}
use Bitrix\Main\Config as Conf;
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/lib/loader.php');
$configuration = Conf\Configuration::getInstance();
$exception_handling = $configuration->get('exception_handling');
$ac_reset = $configuration->get('no_accelerator_reset');
$status = $configuration->get('http_status');
$cache = $configuration->get('cache');
$cachettl = $configuration->get('cache_flags');
$cookies = $configuration->get('cookies');
if(!empty($_POST)){
$exception_handling['debug'] = ($_POST['debug']=='true') ? true : false;
$exception_handling['exception_errors_types'] = (!empty($_POST['ex_e_t'])) ? intval($_POST['ex_e_t']) : $exception_handling['exception_errors_types'];
$exception_handling['handled_errors_types'] = (!empty($_POST['h_e_t'])) ? intval($_POST['h_e_t']) : $exception_handling['handled_errors_types'];
$exception_handling['log']['settings']['file'] = ($_POST['log_path'] && saveFile($_POST['log_path'])) ? $_POST['log_path'] : $exception_handling['log']['settings']['file'];
$exception_handling['log']['settings']['log_size'] = ($_POST['log_size']) ? $_POST['log_size'] : $exception_handling['log']['settings']['log_size'];
$configuration->add('exception_handling', $exception_handling);
$cachettl['config_options'] = (!empty($_POST['config_options'])) ? intval($_POST['config_options']) : $cachettl['config_options'];
$cachettl['site_domain'] = (!empty($_POST['site_domain'])) ? intval($_POST['site_domain']) : $cachettl['site_domain'];
$configuration->add('cache_flags', $cachettl);
$cache['type'] = $_POST['cache_type'];
if ($_POST['cache_type']!=='files' && $_POST['cache_type']!=='none') {
	if(!empty($_POST['cache_sid']))
		$cache['sid']=$_POST['cache_sid'];
	else
		unset($cache['sid']);
	if ($_POST['cache_type']=='memcache') {
		if (!empty($_POST['mem_host'])) {
			$cache["memcache"]["host"] = $_POST['mem_host'];
			if(!empty($_POST['mem_port']))
				$cache["memcache"]["port"]=$_POST['mem_port'];
			else
				unset($cache['memcache']['port']);
		}
		else {
			$cache['type'] = 'files';
			unset($cache['memcache']);
			unset($cache['sid']);
		}
	}
}
else {
	unset($cache['memcache']);
	unset($cache['sid']);
}
$configuration->add('cache', $cache);
$configuration->saveConfiguration();
}
header('Content-Type: text/html; charset=windows-1251');
?>
<!DOCTYPE html>
<html>
<head>
	<title>.settings configuration script</title>
	<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin.css">
	<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
	<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/popup.css">
	<link rel="stylesheet" type="text/css" href="/bitrix/panel/main/admin-public.css">
	<script type="text/javascript" src="/bitrix/js/main/core/core.js"></script>
	<script type="text/javascript" src="/bitrix/js/main/core/core_window.js"></script>
	<script type="text/javascript" src="/bitrix/js/main/core/core_ajax.js"></script>
</head>
<body class="adm-workarea">
<div align="center" id="bx-admin-prefix">
<script type="text/javascript">
var mem = document.getElementsByClassName('mem');
var sid = document.getElementsByClassName('sid');
var type_current = '<?=$cache["type"];?>';
function hide (type) {
		var length = mem.length;
		for (var i = 0; i < length; i++) {
  			mem[i].style.display = 'none';
		}
		if (type=='files'||type=='none') {
			sid[0].style.display = 'none';
		}
}
function show (type) {
	if(type!=='files'&&type!=='none') {
		sid[0].style.display = '';
		if (type=='memcache') {
			var length = mem.length;
			for (var i = 0; i < length; i++) {
  				mem[i].style.display = '';
			}
		}
	}
	else {
		hide(type);
	}
}
function disbtn(id) {
	document.getElementById(id).disabled = true;
}
function showLog(){
var dialog = new BX.CDialog({

    resizable: true,
    draggable: true,
    height: 500,
    width: 1000,
    content_url: document.URL+'?log=Y&path=<?=urlencode($exception_handling['log']['settings']['file'])?>',
    buttons: [
		BX.CDialog.prototype.btnClose
	]
});
dialog.Show();
}
</script>
<div style="width: 800px;" class="adm-detail-content-item-block">
<div>Version <?=$ver;?></div>
<?if (isset($_POST['log_path']) && !saveFile($_POST['log_path'])){
	echo '<div class="adm-info-message-wrap adm-info-message-red"><div class="adm-info-message"><div class="adm-info-message-title">Ошибка</div>Был указан неверный путь к файлу лога<br /><br /><div class="adm-info-message-icon"></div></div></div>';
}
?>
<form method="POST">
	 <table class="adm-detail-content-table">
	 	<tr class="heading">
	 		<td colspan="2">Exception Handling</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Вывод ошибок на экран">DEBUG</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 		<input type="checkbox" name="debug" <?if($exception_handling['debug']) echo 'checked'?> value="true"/>
	 		</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Ошибки, которые будут записываться в лог">handled_errors_types</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
   				<select name="h_e_t" style="width:300px">
	 			<option <?if($exception_handling['handled_errors_types']==29045) echo 'selected'?> value="20853">E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_WARNING & ~E_COMPILE_WARNING & ~E_DEPRECATED</option>
    			<option <?if($exception_handling['handled_errors_types']==30709) echo 'selected'?> value="30709">E_ALL & ~E_NOTICE & ~E_STRICT & ~E_WARNING</option>
    			<option <?if($exception_handling['handled_errors_types']==30711) echo 'selected'?> value="30711">E_ALL & ~E_NOTICE & ~E_STRICT</option>
    			<option <?if($exception_handling['handled_errors_types']==32759) echo 'selected'?> value="32759">E_ALL & ~E_NOTICE</option>
	 		</select>
	 		</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Ошибки, которые будут обрабатываться через исключение">exception_errors_types</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
   				<select name="ex_e_t" style="width:300px">
	 			<option <?if($exception_handling['exception_errors_types']==29045) echo 'selected'?> value="20853">E_ALL & ~E_NOTICE & ~E_WARNING & ~E_STRICT & ~E_USER_NOTICE & ~E_USER_WARNING & ~E_COMPILE_WARNING & ~E_DEPRECATED</option>
    			<option <?if($exception_handling['exception_errors_types']==22519) echo 'selected'?> value="22519">E_ALL & ~E_NOTICE & ~E_STRICT & ~E_DEPRECATED</option>
    			<option <?if($exception_handling['exception_errors_types']==30711) echo 'selected'?> value="30711">E_ALL & ~E_NOTICE & ~E_STRICT</option>
    			<option <?if($exception_handling['exception_errors_types']==32759) echo 'selected'?> value="32759">E_ALL & ~E_NOTICE</option>
	 		</select>
	 		</td>
	 	</tr>
	 	<tr class="heading">
	 		<td colspan="2">Log</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Путь к файлу лога">Path</td>
	 		<td width="20%"  class="adm-detail-content-cell-r">
	 			<input onchange="disbtn('log_btn');" type="text" name="log_path" value="<?=$exception_handling['log']['settings']['file']?>">    <input id="log_btn" type="button"  value="Open" onclick="showLog();" />
	 		</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Максимальный размер лога">Size</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 			<input type="number" min="0" name="log_size" value="<?=$exception_handling['log']['settings']['log_size']?>">
	 		</td>
	 	</tr>
	 	<tr class="heading">
	 		<td colspan="2">
	 			Cache settings
	 		</td>
	 	</tr>
	 	<tr>
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Тип хранения кеша">Cache_type</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 		<select name="cache_type" onchange="show(this.value);">
	 			<option <?if($cache['type']=='none') echo 'selected'?> value="none">none</option>
    			<option <?if($cache['type']=='files') echo 'selected'?> value="files">files</option>
    			<option <?if($cache['type']=='xcache') echo 'selected'?> value="xcache">xcache</option>
    			<option <?if($cache['type']=='apc') echo 'selected'?> value="apc">apc</option>
    			<option <?if($cache['type']=='eaccelerator') echo 'selected'?> value="eaccelerator">eaccelerator</option>
    			<option <?if($cache['type']=='memcache') echo 'selected'?> value="memcache">memcache</option>
	 			</select>
	 		</td>
	 	</tr>
	 	<tr class="sid" title="">
	 		<td width="50%"  class="adm-detail-content-cell-l">
	 			SID
	 		</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 			<input name="cache_sid" type="text" value="<?=(isset($cache['sid'])) ? $cache['sid'] :''?>">
	 		</td>
	 	</tr>
	 	<tr class="heading mem" title="Настройки для memcache">
	 		<td colspan="2">
	 			Memcache
	 		</td>
	 	</tr>
	 	<tr class="mem">
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Адрес сервера memcache">Host</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 			<input name="mem_host" type="text" value="<?=(isset($cache["memcache"])) ? $cache["memcache"]["host"] : ''?>">
	 		</td>
	 	</tr>
	 	<tr class="mem">
	 		<td width="50%"  class="adm-detail-content-cell-l" title="Порт memcache">Port</td>
	 		<td width="50%"  class="adm-detail-content-cell-r">
	 			<input name="mem_port" type="number" value="<?=(isset($cache["memcache"])) ? $cache['memcache']['port'] : ''?>">
	 		</td>
	 	</tr>
<tr class="heading">
	 		<td colspan="2">

	 		</td>
	 	</tr>
<tr>
	<td colspan="2" align="center">
		<input type="submit" class="adm-btn-save" value="Сохранить" />
	</td>
</tr>
	 </table>
</form>
</div>
</div>
<script type="text/javascript">
	if (type_current!=='memcache') {hide(type_current);}
</script>
</body>
</html>
