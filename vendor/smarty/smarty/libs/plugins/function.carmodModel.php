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
function smarty_function_carmodModel( $params, $template)
{

    $MSelect_Position = 'Left'; // Left/Right position on the page - important for mobile adaptability dropdown menu
    $MSelect_Template = 'default';
    $Selector_Template = 'rm_auto';

    include_once($_SERVER['DOCUMENT_ROOT'].'/carparts/add/selector/controller.php');
}
