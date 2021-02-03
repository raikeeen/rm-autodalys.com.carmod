<?php
/**
 * Smarty plugin
 *
 * @package    Smarty
 * @subpackage PluginsFunction
 */
/**
 * Smarty {counter} function plugin
 * Type:     function
 * Name:     counter
 * Purpose:  print out a counter value
 *
 * @author Monte Ohrt <monte at ohrt dot com>
 * @link   http://www.smarty.net/manual/en/language.function.counter.php {counter}
 *         (Smarty online manual)
 *
 */
function smarty_function_carmodVin( $params, $template)
{
    $VinNum_Def_Lang = 'lt'; //Default language code
    $VinNum_Template = 'default'; //Addon template

    include_once($_SERVER['DOCUMENT_ROOT'].'/carparts/add/vinnum/controller.php');
}
