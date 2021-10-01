<?php

/* Copyright (c) 1998-2019 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * Portfolio definition
 *
 * Only for portfolio templates!
 *
 * @author Jörg Lützenkirchen <luetzenkirchen@leifos.com>
 */
class ilPortfolioExporter extends ilXmlExporter
{
    protected ilPortfolioDataSet $ds;
    
    public function init() : void
    {
        $this->ds = new ilPortfolioDataSet();
        $this->ds->setDSPrefix("ds");
    }
    
    public function getXmlExportTailDependencies(string $a_entity, string $a_target_release, array $a_ids) : array
    {
        $pg_ids = array();
        foreach ($a_ids as $id) {
            foreach (ilPortfolioTemplatePage::getAllPortfolioPages($id) as $p) {
                $pg_ids[] = "prtt:" . $p["id"];
            }
        }
        
        $deps[] =
            array(
                "component" => "Services/COPage",
                "entity" => "pg",
                "ids" => $pg_ids);

        // style
        $obj_ids = (is_array($a_ids))
            ? $a_ids
            : array($a_ids);
        $deps[] = array(
            "component" => "Services/Style",
            "entity" => "object_style",
            "ids" => $obj_ids
        );

        return $deps;
    }
    
    public function getXmlRepresentation(string $a_entity, string $a_schema_version, string $a_id) : string
    {
        $this->ds->setExportDirectories($this->dir_relative, $this->dir_absolute);
        return $this->ds->getXmlRepresentation($a_entity, $a_schema_version, [$a_id], "", true, true);
    }
    
    public function getValidSchemaVersions(string $a_entity) : array
    {
        return array(
                "4.4.0" => array(
                        "namespace" => "http://www.ilias.de/Modules/Portfolio/4_4",
                        "xsd_file" => "ilias_portfolio_4_4.xsd",
                        "uses_dataset" => true,
                        "min" => "4.4.0",
                        "max" => "4.9.9"),
                "5.0.0" => array(
                        "namespace" => "http://www.ilias.de/Modules/Portfolio/5_0",
                        "xsd_file" => "ilias_portfolio_5_0.xsd",
                        "uses_dataset" => true,
                        "min" => "5.0.0",
                        "max" => "")
        );
    }
}
