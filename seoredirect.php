<?

/**
 * Redirect file: $_SERVER['DOCUMENT_ROOT'] . '/urlredirect.php'
 *
 * <?php
 * return array(
 *     '/orl/url/' => '/new/url/',
 * );
 */

AddEventHandler(
    'main',
    'OnPageStart',
    function () {
        $url = rtrim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/') . '/';
        $file = $_SERVER['DOCUMENT_ROOT'] . '/urlredirect.php';
        $map = file_exists($file) ? include($file) : array();

        if (is_array($map) AND array_key_exists($url, $map)) {
            LocalRedirect($map[$url], false, '301 Moved permanently');
        }
    },
    10
);
