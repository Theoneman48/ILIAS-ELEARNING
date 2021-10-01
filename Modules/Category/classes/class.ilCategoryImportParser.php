<?php

/* Copyright (c) 1998-2021 ILIAS open source, GPLv3, see LICENSE */

/**
 * Category Import Parser
 *
 * @author Alexander Killing <killing@leifos.de>
 */
class ilCategoryImportParser extends ilSaxParser
{
    protected ilRbacAdmin $rbacadmin;
    protected ilRbacReview $rbacreview;
    protected ilRbacSystem $rbacsystem;
    /** @var int[] $parent */
    public array $parent;		// current parent ref id
    public int $parent_cnt;
    public int $withrol;
    protected ilLogger $cat_log;

    /**
     * ilCategoryImportParser constructor.
     * @param string $a_xml_file
     * @param int    $a_parent
     * @param int    $withrol   // must have value 1 when creating a hierarchy of local roles
     */
    public function __construct(
        string $a_xml_file,
        int $a_parent,
        int $withrol
    ) {
        /** @var \ILIAS\DI\Container $DIC */
        global $DIC;

        $this->rbacadmin = $DIC->rbac()->admin();
        $this->rbacreview = $DIC->rbac()->review();
        $this->rbacsystem = $DIC->rbac()->system();
        $this->parent_cnt = 0;
        $this->parent[$this->parent_cnt] = $a_parent;
        $this->parent_cnt++;
        $this->withrol = $withrol;
        
        parent::__construct($a_xml_file);
    }

    public function setHandlers($a_xml_parser)
    {
        xml_set_object($a_xml_parser, $this);
        xml_set_element_handler($a_xml_parser, 'handlerBeginTag', 'handlerEndTag');
        xml_set_character_data_handler($a_xml_parser, 'handlerCharacterData');
    }

    public function startParsing()
    {
        parent::startParsing();
    }

    // generate a tag with given name and attributes
    public function buildTag(
        string $type,
        string $name,
        array $attr = null
    ) {
        $tag = "<";

        if ($type == "end") {
            $tag .= "/";
        }

        $tag .= $name;

        if (is_array($attr)) {
            foreach ($attr as $k => $v) {
                $tag .= " " . $k . "=\"$v\"";
            }
        }

        $tag .= ">";

        return $tag;
    }

    public function handlerBeginTag($a_xml_parser, $a_name, $a_attribs)
    {
        switch ($a_name) {
            case "Category":
                $cur_parent = $this->parent[$this->parent_cnt - 1];
                $this->category = new ilObjCategory;
                $this->category->setImportId($a_attribs["Id"] . " (#" . $cur_parent . ")");
                $this->default_language = $a_attribs["DefaultLanguage"];
                $this->category->setTitle($a_attribs["Id"]);
                $this->category->create();
                $this->category->createReference();
                $this->category->putInTree($cur_parent);
                $this->parent[$this->parent_cnt++] = $this->category->getRefId();
                break;

        case "CategorySpec":
          $this->cur_spec_lang = $a_attribs["Language"];
          break;

        }
    }


    public function handlerEndTag($a_xml_parser, $a_name)
    {
        switch ($a_name) {
            case "Category":
                unset($this->category);
                unset($this->parent[$this->parent_cnt - 1]);
                $this->parent_cnt--;
                break;

            case "CategorySpec":
                $is_def = 0;
                if ($this->cur_spec_lang == $this->default_language) {
                    $this->category->setTitle($this->cur_title);
                    $this->category->setDescription($this->cur_description);
                    $this->category->update();
                    $is_def = 1;
                }
                $this->category->addTranslation(
                    $this->cur_title,
                    $this->cur_description,
                    $this->cur_spec_lang,
                    $is_def
                );
                break;

            case "Title":
                $this->cur_title = $this->cdata;
                break;

            case "Description":
                $this->cur_description = $this->cdata;
                break;
        }

        $this->cdata = "";
    }

    public function handlerCharacterData($a_xml_parser, $a_data)
    {
        // i don't know why this is necessary, but
        // the parser seems to convert "&gt;" to ">" and "&lt;" to "<"
        // in character data, but we don't want that, because it's the
        // way we mask user html in our content, so we convert back...
        $a_data = str_replace("<", "&lt;", $a_data);
        $a_data = str_replace(">", "&gt;", $a_data);

        // DELETE WHITESPACES AND NEWLINES OF CHARACTER DATA
        $a_data = preg_replace("/\n/", "", $a_data);
        $a_data = preg_replace("/\t+/", "", $a_data);
        if (!empty($a_data)) {
            $this->cdata .= $a_data;
        }
    }
}
