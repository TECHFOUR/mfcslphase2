<?php

/* +***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 * *********************************************************************************** */

class Leads_Save_Action extends Vtiger_Save_Action {

	public function process(Vtiger_Request $request) {
	
// Start added by Ajay [TECHFOUR]			
	$mobile = str_replace('', '-', $request->get('mobile')); // Replaces all spaces with hyphens.
   	$mobile = substr(preg_replace('/[^0-9\-]/', '', $mobile),-10);
		
	$registrationno = str_replace('', '-', $request->get('registrationno')); // Replaces all spaces with hyphens.
   	$registrationno = strtoupper(substr(preg_replace('/[^a-zA-Z0-9\']/', '', $registrationno),-11)); // Removes special chars.
   
	$request->set('mobile', $mobile);
	$request->set('registrationno', $registrationno);
// End added by Ajay [TECHFOUR]	

		//To stop saveing the value of salutation as '--None--'
		$salutationType = $request->get('salutationtype');
		if ($salutationType === '--None--') {
			$request->set('salutationtype', '');
		}
		parent::process($request);
	}
}