AddEventHandler("main", "OnEpilog", "Redirect404");
function Redirect404() {
    if(defined('ERROR_404')){
        @define("PATH_TO_404", "/404.php");
    }

    if(!defined('ADMIN_SECTION') &&  defined("ERROR_404") &&  defined("PATH_TO_404") &&  file_exists($_SERVER["DOCUMENT_ROOT"].PATH_TO_404))
    {
        //LocalRedirect("/404.php", "404 Not Found");
        global $APPLICATION;
        $APPLICATION->RestartBuffer();
        CHTTP::SetStatus("404 Not Found");
        include($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/header.php");
        include($_SERVER["DOCUMENT_ROOT"].PATH_TO_404);
        include($_SERVER["DOCUMENT_ROOT"].SITE_TEMPLATE_PATH."/footer.php");
    }
}
