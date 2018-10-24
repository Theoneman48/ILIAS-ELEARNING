<?php

/* Copyright (c) 1998-2018 ILIAS open source, Extended GPL, see docs/LICENSE */

/**
 * News service
 *
 * @author killing@leifos.de
 * @ingroup ServiceUI
 */
class ilUIFilterService
{
	/**
	 * @var ilUIService
	 */
	protected $service;

	/**
	 * @var ilUIServiceDependencies
	 */
	protected $_deps;

	/**
	 * Constructor
	 * @param ilUIService $service
	 * @param ilUIServiceDependencies $deps
	 */
	public function __construct(ilUIService $service, ilUIServiceDependencies $deps)
	{
		$this->service = $service;
		$this->_deps = $deps;
	}


	/**
	 * Get standard filter instance
	 *
	 * @param
	 * @return
	 */
	protected function standard($filter_id, $base_action, array $inputs, array $is_input_initially_rendered,
								$is_initially_activated = false, $is_initially_expanded = false): \ILIAS\UI\Component\Input\Container\Filter\Standard
	{
		global $DIC;
		$ui = $DIC->ui()->factory();

		$is_input_rendered = $is_input_initially_rendered;
		$is_activated = $is_initially_activated;
		$is_expanded = $is_initially_expanded;

		// read cmdFilter from request and update session data

		if ($_REQUEST["cmdFilter"] == "toggleOn") {
			ilSession::set("ui_service_filter_activated_".$filter_id, true);
		}

		if ($_REQUEST["cmdFilter"] == "toggleOff") {
			ilSession::set("ui_service_filter_activated_".$filter_id, false);
		}

		if ($_REQUEST["cmdFilter"] == "expand") {
			ilSession::set("ui_service_filter_expanded_".$filter_id, true);
		}

		if ($_REQUEST["cmdFilter"] == "collapse") {
			ilSession::set("ui_service_filter_expanded_".$filter_id, false);
		}


		$input_id = 0;

		if ($_REQUEST["cmdFilter"] == "apply") {
			foreach ($is_input_rendered as $i) {
				if ($i == true) {
					ilSession::set("ui_service_filter_is_input_rendered_" . $input_id . "_" . $filter_id, true);
				} else {
					ilSession::set("ui_service_filter_is_input_rendered_" . $input_id . "_" . $filter_id, false);
				}
				$input_id++;
			}
		}

		if ($_REQUEST["cmdFilter"] == "reset") {
			foreach ($is_input_rendered as $i) {
					ilSession::clear("ui_service_filter_is_input_rendered_" . $input_id . "_" . $filter_id);
				$input_id++;
			}
		}


		// get data from session

		$is_activated = ilSession::get("ui_service_filter_activated_".$filter_id);
		$is_expanded = ilSession::get("ui_service_filter_expanded_".$filter_id);

		//compose a new array because rendering of inputs has eventually changed
		if (ilSession::get("ui_service_filter_is_input_rendered_0" . "_" . $filter_id) != null) {
			$is_input_rendered = array();
			for ($i = 0; $i <= $input_id; $i++) {
				$is_input_rendered[] = ilSession::get("ui_service_filter_is_input_rendered_" . $i . "_" . $filter_id);
			}
		}


		// create the KS Filter

		$filter = $ui->input()->container()->filter()->standard(
			$base_action."&cmdFilter=toggleOn",
			$base_action."&cmdFilter=toggleOff",
			$base_action."&cmdFilter=expand",
			$base_action."&cmdFilter=collapse",
			$base_action."&cmdFilter=apply",
			$base_action."&cmdFilter=reset",
			$inputs,
			$is_input_rendered,
			$is_activated,
			$is_expanded);

		return $filter;

	}

}