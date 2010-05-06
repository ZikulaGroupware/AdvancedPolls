<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Mark West <mark@markwest.me.uk>
 * @copyright (C) 2002-2010 by Mark West
 * @link http://code.zikula.org/advancedpolls
 * @version $Id$
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

function smarty_function_sortordertypes($params, &$smarty)
{
    $dom = ZLanguage::getModuleDomain('advanced_polls');

    extract($params);

    unset($params['name']);
    unset($params['id']);
    unset($params['selected']);
    unset($params['type']);

    $options = array('0' => __('Ascending', $dom), 
                     '1' => __('Descending', $dom));

    // we'll make use of the html_options plugin to simplfiy this plugin
    require_once $smarty->_get_plugin_filepath('function','html_options');

    // get the formatted list
    $output = smarty_function_html_options(array('options'   => $options,
                                                 'selected'  => isset($selected) ? $selected : null,
                                                 'name'      => $name,
                                                 'id'        => isset($id) ? $id : null),
                                                 $smarty);

    if (isset($assign)) {
        $smarty->assign($assign, $output);
    } else {
        return $output;
    }
}
