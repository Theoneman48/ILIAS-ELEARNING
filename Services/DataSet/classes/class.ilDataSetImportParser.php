<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Manifest parser for ILIAS standard export files
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilDataSetImportParser extends ilSaxParser
{
    protected ?ilImport $import = null;				// import object
    protected array $entities = array();			// types array
    protected string $current_entity = "";			// current entity
    protected string $current_version = "";		// current version
    protected array $current_ftypes = array();	// current field types
    protected bool $entities_sent = false;		// sent entities to import class?
    protected bool $in_record = false;			// are we currently in a rec tag?
    protected string $current_field = "";			// current field
    protected array $current_field_values = array();	// current field values
    protected string $current_installation_id = "";
    protected string $chr_data = "";
    protected ilImportMapping $mapping;
    
    
    /**
     * Constructor
     *
     * @param
     * @return
     */
    public function __construct(
        string $a_top_entity,
        string $a_schema_version,
        string $a_xml,
        ilDataSet $a_ds,
        ilImportMapping $a_mapping
    ) {
        $this->ds = $a_ds;
        $this->mapping = $a_mapping;
        $this->top_entity = $a_top_entity;
        $this->schema_version = $a_schema_version;
        $this->dspref = ($this->ds->getDSPrefix() != "")
            ? $this->ds->getDSPrefix() . ":"
            : "";
        
        parent::__construct();
        $this->setXMLContent($a_xml);
        $this->startParsing();
    }

    /**
     * Get current installation id
     *
     * @param
     * @return
     */
    public function getCurrentInstallationId()
    {
        return $this->current_installation_id;
    }

        
    /**
     * Set event handlers
     *
     * @param	resource	reference to the xml parser
     * @access	private
     */
    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handleBeginTag', 'handleEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handleCharacterData');
    }

    
    /**
     * Start parser
     */
    public function startParsing()
    {
        parent::startParsing();
    }
    
    /**
     * Begin Tag
     */
    public function handleBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case $this->dspref . "DataSet":
//				$this->import->initDataset($this->ds_component, $a_attribs["top_entity"]);
                $this->current_installation_id = $a_attribs["InstallationId"];
                $this->ds->setCurrentInstallationId($a_attribs["InstallationId"]);
                break;
                
            case $this->dspref . "Types":
                $this->current_entity = $a_attribs["Entity"];
                $this->current_version = $a_attribs["Version"];
                break;
                
            case $this->dspref . "FieldType":
                $this->current_ftypes[$a_attribs["Name"]] =
                    $a_attribs["Type"];
                break;
                
            case $this->dspref . "Rec":
                $this->current_entity = $a_attribs["Entity"];
                $this->in_record = true;
                $this->current_field_values = array();
                break;
                
            default:
                if ($this->in_record) {
                    $field = explode(":", $a_name);		// remove namespace
                    $field = $field[count($field) - 1];
                    $this->current_field = $field;
                }
        }
    }
    
    /**
     * End Tag
     */
    public function handleEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case $this->dspref . "Types":
                $this->entities[$this->current_entity] =
                    array(
                        "version" => $this->current_version,
                        "types" => $this->current_ftypes
                        );
                $this->current_ftypes = array();
                $this->current_entity = "";
                $this->current_version = "";
                break;
                
            case $this->dspref . "Rec":
                $this->ds->importRecord(
                    $this->current_entity,
                    $this->entities[$this->current_entity]["types"] ?? [],
                    $this->current_field_values,
                    $this->mapping,
                    $this->schema_version
                );
                $this->in_record = false;
                $this->current_entity = "";
                $this->current_field_values = array();
                break;
                
            default:
                if ($this->in_record && $this->current_field != "") {
                    $this->current_field_values[$this->current_field] =
                        $this->chr_data;
                }
                $this->current_field = "";
                break;
        }
        
        $this->chr_data = "";
    }
    
    /**
     * End Tag
     */
    public function handleCharacterData($a_xml_parser, $a_data)
    {
        //$a_data = str_replace("<","&lt;",$a_data);
        //$a_data = str_replace(">","&gt;",$a_data);
        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        //$a_data = preg_replace("/\n/","",$a_data);
        //$a_data = preg_replace("/\t+/","",$a_data);

        $this->chr_data .= $a_data;
    }
}
