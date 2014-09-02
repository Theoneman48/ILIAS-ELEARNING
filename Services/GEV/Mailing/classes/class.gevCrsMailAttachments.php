<?php
/* Copyright (c) 1998-2010 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
* Class gevMailingAttachments.
*
* @author Richard Klees <richard.klees@concepts-and-training>
*/

require_once("Services/Mailing/classes/class.ilMailAttachments.php");

class gevCrsMailAttachments extends ilMailAttachments {
	protected $crs;
	protected $generated_files;
	
	const LIST_FOR_TRAINER_NAME = "Teilnehmerliste_Trainer.xls";
	const LIST_FOR_HOTEL_NAME = "Teilnehmerliste_Hotel.xls";
	const LIST_FOR_PARTICIPANT_NAME = "Teilnehmerliste_Teilnehmer.xls";
	
	public function __construct($a_obj_id) {
		parent::__construct($a_obj_id);
		$this->crs = null;
		$this->crs_utils = null;
		
		$this->generated_files = array( self::LIST_FOR_TRAINER_NAME
									  , self::LIST_FOR_HOTEL_NAME
									  , self::LIST_FOR_PARTICIPANT_NAME
									  );
	}
	
	public function isAutogeneratedFile($a_filename) {
		return in_array($a_filename, $this->generated_files);
	}
	
	public function getList() {
		$ret = parent::getList();
		// This makes sure, that generated files are listed exactly once.
		return array_merge(array_diff($ret, $this->generated_files), $this->generated_files);
	}

	public function isAttachment($a_filename) {
		if ($this->isAutogeneratedFile($a_filename)) {
			return true;
		}
		else {
			return parent::isAttachment($a_filename);
		}
	}

	public function addAttachment($a_filename, $a_tmp_path) {
		if ($this->isAutogeneratedFile($a_filename)) {
			throw new Exception($a_filename." is an autogenerated file and therefore can't be overridden.");
		}
		return parent::addAttachment($a_filename, $a_tmp_path);
	}

	public function getPathTo($a_filename) {
		if (!$this->isAutogeneratedFile($a_filename)) {
			return parent::getPathTo($a_filename);
		}

		// Since everyone who wants to get an autogenerated file needs to 
		// ask for the path in advance, this is a good moment to (re)build that
		// file.
		$this->generateFile($a_filename);
		
		return parent::getPathTo($a_filename);
	}

/*	public function getInfoList() {
		$ret = parent::getInfoList();
		
		//Remove the old entries and recreate the files.
		array_pop($ret);
		
		$this->createMemberList();
		$ret[] = array( "name" => $this->memberListName()
					  , "size" => filesize($this->memberListPath())
					  , "last_modified" => time()
					  );
		return $ret;
	}*/

	public function lock($a_filename) {
		if ($this->isAutogeneratedFile($a_filename)) {
			return;
		}
		
		return parent::lock($a_filename);
	}

	public function unlock($a_filename) {
		if ($this->isAutogeneratedFile($a_filename)) {
			return;
		}
		
		return parent::unlock($a_filename);
	}
	
	public function isLocked($a_filename) {
		if ($this->isAutogeneratedFile($a_filename)) {
			return true;
		}
		
		return parent::isLocked($a_filename);
	}

	protected function getCourse() {
		if ($this->crs === null) {
			$this->crs = new ilObjCourse($this->obj_id, false);
		}

		return $this->crs;
	}
	
	protected function getCourseUtils() {
		require_once("Services/GEV/Utils/classes/class.gevCourseUtils.php");
		
		if ($this->crs_utils === null) {
			$this->crs_utils = gevCourseUtils::getInstanceByObj($this->getCourse());
		}
	
		return $this->crs_utils;
	}
	
	protected function generateFile($a_filename) {
		$path = parent::getPathTo($a_filename);
		$this->create();
		
		switch($a_filename) {
			case self::LIST_FOR_TRAINER_NAME:
				$this->getCourseUtils()->buildMemberList(false, $path, gevCourseUtils::MEMBERLIST_TRAINER);
				return;
			case self::LIST_FOR_HOTEL_NAME:
				$this->getCourseUtils()->buildMemberList(false, $path, gevCourseUtils::MEMBERLIST_HOTEL);
				return;
			case self::LIST_FOR_PARTICIPANT_NAME:
				$this->getCourseUtils()->buildMemberList(false, $path, gevCourseUtils::MEMBERLIST_PARTICIPANT);
				return;
		}
		throw new Exception("Don't know how to generate file '".$a_filename."'.");
	}
	
	// reimplemented from ilMailAttachments. Does not try to copy auto generated files.
	public function copyTo($a_obj_id) {
		$other = new gevCrsMailAttachments($a_obj_id);

		foreach (parent::getList() as $att) {
			$other->addAttachment($att, $this->getAbsolutePath() . "/" . $att);
		}
	}
}

?>