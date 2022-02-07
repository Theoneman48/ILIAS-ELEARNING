<?php declare(strict_types=1);

/******************************************************************************
 *
 * This file is part of ILIAS, a powerful learning management system.
 *
 * ILIAS is licensed with the GPL-3.0, you should have received a copy
 * of said license along with the source code.
 *
 * If this is not the case or you just want to try ILIAS, you'll find
 * us at:
 *      https://www.ilias.de
 *      https://github.com/ILIAS-eLearning
 *
 *****************************************************************************/

/**
*
* @author Stefan Meyer <meyer@leifos.com>
*
*
* @ingroup ModulesRemoteCategory
*/
class ilObjRemoteCategoryListGUI extends ilRemoteObjectBaseListGUI
{
    /**
     * Constructor
     *
     * @access public
     *
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * init
     *
     * @access public
     */
    public function init()
    {
        $this->copy_enabled = false;
        $this->static_link_enabled = true;
        $this->delete_enabled = true;
        $this->cut_enabled = true;
        $this->subscribe_enabled = true;
        $this->link_enabled = true;
        $this->info_screen_enabled = true;
        $this->type = 'rcat';
        $this->gui_class_name = 'ilobjremotecategorygui';

        $this->substitutions = ilAdvancedMDSubstitution::_getInstanceByObjectType($this->type);
        if ($this->substitutions->isActive()) {
            $this->substitutions_enabled = true;
        }

        // general commands array
        $this->commands = ilObjRemoteCategoryAccess::_getCommands();
    }


    /**
     * get properties (offline)
     *
     * @access public
     * @param
     *
     */
    public function getProperties()
    {
        if ($org = $this->_lookupOrganization(ilObjRemoteCategory::DB_TABLE_NAME, $this->obj_id)) {
            $this->addCustomProperty($this->lng->txt('organization'), $org, false, true);
        }

        return array();
    }

    /**
     * get command frame
     *
     * @access public
     * @param
     * @return
     */
    public function getCommandFrame($a_cmd)
    {
        switch ($a_cmd) {
            case 'show':
                if (ilECSExportManager::getInstance()->_isRemote(
                    ilECSImportManager::getInstance()->lookupServerId($this->obj_id),
                    ilECSImportManager::getInstance()->_lookupEContentId($this->obj_id)
                )) {
                    return '_blank';
                }

                // no break
            default:
                return parent::getCommandFrame($a_cmd);
        }
    }
} // END class.ilObjRemoteCategoryListGUI
