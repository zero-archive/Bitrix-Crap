<?php
/**
 * Файл result_modifier.php для перестроения плоского МЕНЮ
 * в иерархический массив $arResult.
 *
 * @package Bitrix-Crap
 * @author dZ <mail@dotzero.ru>
 * @link http://www.dotzero.ru
 * @link https://github.com/dotzero/Bitrix-Crap
 * @license MIT
 * @version 1.0 (30-mar-2014)
 */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

$menuList = array();
$depth = 0;
$lastInd = 0;
$parents = array();

foreach ($arResult as $arItem) {
    $depth = $arItem['DEPTH_LEVEL'];

    if ($arItem['IS_PARENT']) {
        $arItem['CHILDREN'] = array();
    }

    if ($depth == 1) {
        $menuList[] = $arItem;
        $lastInd = count($menuList) - 1;
        $parents[$depth] = & $menuList[$lastInd];
    } else {
        $parents[$depth - 1]['CHILDREN'][] = $arItem;
        $lastInd = count($parents[$depth - 1]['CHILDREN']) - 1;
        $parents[$depth] = & $parents[$depth - 1]['CHILDREN'][$lastInd];
    }
}

$arResult = $menuList;
