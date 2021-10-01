<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Importer class for forums
 *
 * @author Stefan Meyer <meyer@leifos.com>
 * @version $Id: $
 * @ingroup ModulesForum
 */
class ilForumImporter extends ilXmlImporter
{
    /**
     * Import XML
     * @param
     * @return void
     */
    public function importXmlRepresentation(string $a_entity, string $a_id, string $a_xml, ilImportMapping $a_mapping) : void
    {
        // case i container
        if ($new_id = $a_mapping->getMapping('Services/Container', 'objs', $a_id)) {
            $newObj = ilObjectFactory::getInstanceByObjId($new_id, false);
        } else {	// case ii, non container
            $newObj = new ilObjForum();
            $newObj->setType('frm');
            $newObj->create();
        }

        $parser = new ilForumXMLParser($newObj, $a_xml);
        $parser->setImportDirectory($this->getImportDirectory());
        $parser->setImportInstallId($this->getInstallId());
        $parser->setSchemaVersion($this->getSchemaVersion());
        $parser->startParsing();

        $a_mapping->addMapping("Modules/Forum", "frm", $a_id, $newObj->getId());
    }
}
