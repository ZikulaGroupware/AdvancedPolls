<?php
/**
 * Advanced Polls module for Zikula
 *
 * @author Advanced Polls Development Team
 * @copyright (C) 2002-2011 by Advanced Polls Development Team
 * @link https://github.com/zikula-modules/AdvancedPolls
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 */

class AdvancedPolls_Controller_Admin extends Zikula_AbstractController {

    /**
    * The main administration function
    */
    public function main($args)
    {
        return $this->view($args);
    }

  
    /**
    * Modify a Poll
    *
    * @param 'pollid' the id of the item to be modified
    */
    public function modify($args)
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('admin/modify.tpl', new AdvancedPolls_Handler_Modify());
    }


    /**
    * Delete a poll
    *
    * @param 'pollid' the id of the item to be deleted
    * @param 'confirmation' confirmation that this item can be deleted
    */
    public function delete($args)
    {

        $pollid       = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
        $objectid     = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $pollid = $objectid;
        }

        // Get the poll
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

        if ($item == false) {
            return LogUtil::registerError ($this->__('Error! No such poll found.'), 404);
        }

        // Security check.
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_DELETE)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user

            // Assign hidden form value for pollid
            $this->view->assign('pollid', $pollid);

            // Return the output that has been generated by this function
            return $this->view->fetch('admin/delete.tpl');
        }

        // If we get here it means that the user has confirmed the action

        // Confirm authorisation code.
        if (!SecurityUtil::generateCsrfToken()) {
            return LogUtil::registerPermissionError (ModUtil::url($this->name, 'admin', 'view'));
        }

        // The API function is called.
        if (ModUtil::apiFunc($this->name, 'admin', 'delete', array('pollid' => $pollid))) {
            LogUtil::registerStatus( $this->__('Done! Poll deleted.'));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'view'));
    }

    /**
    * Main admin function to view a full list of polls
    */
    public function view($args)
    {
        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters from whatever input we need.
        $startnum = (int)FormUtil::getPassedValue('startnum', isset($args['startnum']) ? $args['startnum'] : null, 'GET');
        $property = FormUtil::getPassedValue('advanced_polls_property', isset($args['advanced_polls_property']) ? $args['advanced_polls_property'] : null, 'GETPOST');
        $category = FormUtil::getPassedValue("advanced_polls_{$property}_category", isset($args["advanced_polls_{$property}_category"]) ? $args["advanced_polls_{$property}_category"] : null, 'GETPOST');
        $clear    = FormUtil::getPassedValue('clear', false, 'POST');
        if ($clear) {
            $property = null;
            $category = null;
        }

        // get module vars for later use
        $modvars = $this->getVars();

        
        
        
        if ($modvars['enablecategorization']) {
            // load the category registry util
            /*if (!($class = Loader::loadClass('CategoryRegistryUtil'))) {
                pn_exit (__f('Error! Unable to load class [%s]', array('s' => 'CategoryRegistryUtil'), $dom));
            } TODO */
            $catregistry  = CategoryRegistryUtil::getRegisteredModuleCategories('advanced_polls', 'advanced_polls_desc');
            $properties = array_keys($catregistry);

            // Validate and build the category filter - mateo
            if (!empty($property) && in_array($property, $properties) && !empty($category)) {
                $catFilter = array($property => $category);
            }

            // Assign a default property - mateo
            if (empty($property) || !in_array($property, $properties)) {
                $property = $properties[0];
            }

            // plan ahead for ML features
            $propArray = array();
            foreach ($properties as $prop) {
                $propArray[$prop] = $prop;
            }
        }

        // get all matching polls
        $items = ModUtil::apiFunc($this->name, 'user', 'getall', array('checkml' => false,
                                                                        'startnum' => $startnum,
                                                                        'numitems' => ModUtil::getVar('advanced_polls', 'adminitemsperpage'),
                                                                        'category' => isset($catFilter) ? $catFilter : null,
                                                                        'catregistry'  => isset($catregistry) ? $catregistry : null));

        if (!$items)
        $items = array();

        foreach ($items as $key => $item) {
            // check if poll is open
            $items[$key]['isopen'] = ModUtil::apiFunc($this->name, 'user', 'isopen', array('pollid' => $item['pollid']));
            $options = array();
            if (SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$item[pollid]", ACCESS_EDIT)) {
                $options[] = array('url' => ModUtil::url($this->name, 'admin', 'modify', array('pollid' => $item['pollid'])),
                                'image' => 'xedit.png',
                                'title' => $this->__('Edit'));
                if (SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$item[pollid]", ACCESS_DELETE)) {
                    $options[] = array('url' => ModUtil::url($this->name, 'admin', 'delete', array('pollid' => $item['pollid'])),
                                'image' => '14_layer_deletelayer.png',
                                'title' => $this->__('Delete'));
                }
                $options[] = array('url' => ModUtil::url($this->name, 'admin', 'resetvotes', array('pollid' => $item['pollid'])),
                                'image' => 'editclear.png',
                                'title' => $this->__('Reset votes'));
                $options[] = array('url' => ModUtil::url($this->name, 'admin', 'duplicate', array('pollid' => $item['pollid'])),
                                'image' => 'editcopy.png',
                                'title' => $this->__('Duplicate poll'));
                $options[] = array('url' => ModUtil::url($this->name, 'admin', 'adminstats', array('pollid' => $item['pollid'])),
                                'image' => 'vcalendar.png',
                                'title' => $this->__('Voting statistics'));
            }
            $items[$key]['options'] = $options;
        }


        // Assign the items to the template
        $this->view->assign('polls', $items);
        $this->view->assign($modvars);

        // Assign the default language
        $this->view->assign('lang', ZLanguage::getLanguageCode());

        // Assign the categories information if enabled
        if ($modvars['enablecategorization']) {
            $this->view->assign('catregistry', $catregistry);
            $this->view->assign('numproperties', count($propArray));
            $this->view->assign('properties', $propArray);
            $this->view->assign('property', $property);
            $this->view->assign('category', $category);
        }

        // Assign the values for the smarty plugin to produce a pager in case of there
        // being many items to display.
        $this->view->assign('pager', array('numitems'          => ModUtil::apiFunc('advanced_polls', 'user', 'countitems', array('category' => isset($catFilter) ? $catFilter : null)),
                                        'adminitemsperpage' => $modvars['adminitemsperpage']));


        // Return the output that has been generated by this function
        return $this->view->fetch('admin/view.tpl');
    }

    /**
    * Modify module configuration
    */
    public function modifyconfig()
    {
        $form = FormUtil::newForm($this->name, $this);
        return $form->execute('admin/modifyconfig.tpl', new AdvancedPolls_Handler_ModifyConfig());
        
        
    }


    /**
    * Reset the votes on a poll
    */
    public function resetvotes()
    {

        $pollid       = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
        $objectid     = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $pollid = $objectid;
        }

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', '::', ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation
        if (empty($confirmation)) {
            // No confirmation yet - get one

            $this->view->assign('pollid', $pollid);
            // Return the output that has been generated by this function
            return $this->view->fetch('admin/resetvotes.tpl');
        }

        // Confirm authorisation code
        if (!SecurityUtil::generateCsrfToken()) {
            return LogUtil::registerpermissionError (ModUtil::url($this->name, 'admin', 'view'));
        }

        // Pass to API
        if (ModUtil::apiFunc($this->name, 'admin', 'resetvotes', array('pollid' => $pollid))) {
            LogUtil::registerStatus ($this->__('Done! Votes reset.'));
        }

        return System::redirect(ModUtil::url($this->name, 'admin', 'view'));
    }

    /**
    * Display voting statistics to admin
    */
    public function adminstats()
    {
        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::', '::', ACCESS_ADMIN)) {
            return LogUtil::registerPermissionError();
        }

        // Get parameters
        $pollid    = FormUtil::getPassedValue('pollid');
        $sortorder = FormUtil::getPassedValue('sortorder');
        $sortby    = FormUtil::getPassedValue('sortby');
        $startnum  = FormUtil::getPassedValue('startnum');

        // set default sort order
        if (!isset($sortorder)) {
            $sortorder = 0;
        }
        // set default sort by
        if (!isset($sortby)) {
            $sortby = 1;
        }

        // get all votes for this poll from api
        $votes = ModUtil::apiFunc($this->name, 'admin', 'getvotes',
        array('pollid' => $pollid,
            'sortorder' => $sortorder,
            'sortby' => $sortby,
            'startnum' => $startnum,
            'numitems' => ModUtil::getVar($this->name, 'adminitemsperpage')));

        // get all votes for this poll from api
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

        $this->view->assign('item', $item);
        $this->view->assign('pollid', $pollid);
        $votecountarray = ModUtil::apiFunc($this->name, 'user', 'pollvotecount', array('pollid'=>$pollid));
        $votecount = $votecountarray['totalvotecount'];
        $this->view->assign('votecount', $votecount);
        $this->view->assign('sortby', $sortby);
        $this->view->assign('sortorder', $sortorder);

        if ($votes == true ) {
            foreach ($votes as $key => $vote) {
                if (ModUtil::getVar($this->name, 'usereversedns')) {
                    $host = gethostbyaddr($vote['ip']) . ' - ' . $vote['ip'];
                } else {
                    $host = $vote['ip'];
                }
                $voteoffset = $vote['optionid']-1;
                $votes[$key]['user'] = UserUtil::getVar('uname',$vote['uid']);
                $votes[$key]['optiontext'] = $item['options'][$voteoffset]['optiontext'];
            }
        }
        $this->view->assign('votes', $votes);

        // Assign the values for the smarty plugin to produce a pager in case of there
        // being many items to display.
        $this->view->assign('pager', array('numitems'          => $votecount,
                                        'adminitemsperpage' => ModUtil::getVar('advanced_polls', 'adminitemsperpage')));

        return $this->view->fetch('admin/adminstats.tpl');
    }

    /**
    * Duplicate poll
    */
    public function duplicate()
    {

        $pollid       = FormUtil::getPassedValue('pollid', isset($args['pollid']) ? $args['pollid'] : null, 'REQUEST');
        $objectid     = FormUtil::getPassedValue('objectid', isset($args['objectid']) ? $args['objectid'] : null, 'REQUEST');
        $confirmation = FormUtil::getPassedValue('confirmation', null, 'POST');
        if (!empty($objectid)) {
            $pollid = $objectid;
        }

        // The user API function is called.
        $item = ModUtil::apiFunc($this->name, 'user', 'get', array('pollid' => $pollid));

        if ($item == false) {
            return LogUtil::registerError($this->__('Error! No such poll found.'));
        }

        // Security check
        if (!SecurityUtil::checkPermission('AdvancedPolls::item', "$item[title]::$pollid", ACCESS_EDIT)) {
            return LogUtil::registerPermissionError();
        }

        // Check for confirmation.
        if (empty($confirmation)) {
            // No confirmation yet - display a suitable form to obtain confirmation
            // of this action from the user

            // Assign a hidden form value for the poll id
            $this->view->assign('pollid', $pollid);

            // Return the output that has been generated by this function
            return $this->view->fetch('admin/duplicate.tpl');
        }

        // If we get here it means that the user has confirmed the action

        // Confirm authorisation code.
        if (!SecurityUtil::generateCsrfToken()) {
            return LogUtil::registerPermissionError (ModUtil::url($this->name, 'admin', 'view'));
        }

        // The API function is called
        if (ModUtil::apiFunc($this->name, 'admin', 'duplicate', array('pollid' => $pollid))) {
            LogUtil::registerStatus( $this->__('Done! Poll duplicated.'));
        }

        // redirect the user to an appropriate page
        return System::redirect(ModUtil::url($this->name, 'admin', 'view'));
    }
}