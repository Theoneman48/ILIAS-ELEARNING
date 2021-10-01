<?php
/* Copyright (c) 1998-2009 ILIAS open source, Extended GPL, see docs/LICENSE */

include_once './Services/Export/classes/class.ilXmlExporter.php';

/**
 * Class for category export
 *
 * @author Stefan Meyer <smeyer.ilias@gmx.de>
 * $Id$
 */
abstract class ilContainerReferenceExporter extends ilXmlExporter
{
    /**
     * Get head dependencies
     * @param		string		entity
     * @param		string		target release
     * @param		array		ids
     * @return		array		array of array with keys "component", entity", "ids"
     */
    public function getXmlExportHeadDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        global $DIC;

        $log = $DIC->logger()->root();

        include_once './Services/Export/classes/class.ilExportOptions.php';
        $eo = ilExportOptions::getInstance();

        $obj_id = end($a_ids);

        $log->debug(__METHOD__ . ': ' . $obj_id);
        if ($eo->getOption(ilExportOptions::KEY_ROOT) != $obj_id) {
            return array();
        }
        if (count(ilExportOptions::getInstance()->getSubitemsForExport()) > 1) {
            return array(
                array(
                    'component' => 'Services/Container',
                    'entity' => 'struct',
                    'ids' => $a_ids
                )
            );
        }
        return array();
    }
    
    abstract protected function initWriter(ilContainerReference $ref);


    /**
     * Get xml
     * @param string $a_entity
     * @param string $a_schema_version
     * @param string $a_id
     * @return string
     */
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        global $DIC;

        $log = $DIC->logger()->root();

        $refs = ilObject::_getAllReferences($a_id);
        $ref_ref_id = end($refs);
        $ref = ilObjectFactory::getInstanceByRefId($ref_ref_id, false);

        if (!$ref instanceof ilContainerReference) {
            $log->debug(__METHOD__ . $a_id . ' is not instance of type category!');
            return '';
        }
        $writer = $this->initWriter($ref);
        $writer->setMode(ilContainerReferenceXmlWriter::MODE_EXPORT);
        $writer->export(false);
        return $writer->getXml();
    }

    /**
     * Returns schema versions that the component can export to.
     * ILIAS chooses the first one, that has min/max constraints which
     * fit to the target release. Please put the newest on top.
     * @return array
     */
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
            "4.3.0" => array(
                "namespace" => "http://www.ilias.de/Modules/CategoryReference/catr/4_3",
                "xsd_file" => "ilias_catr_4_3.xsd",
                "uses_dataset" => false,
                "min" => "4.3.0",
                "max" => "")
        );
    }

    /**
     * Init method
     */
    public function init() : void
    {
    }
}
