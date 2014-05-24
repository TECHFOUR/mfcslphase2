<?php
/*********************************************************************************
 * The contents of this file are subject to the SugarCRM Public License Version 1.1.2
 * ("License"); You may not use this file except in compliance with the
 * License. You may obtain a copy of the License at http://www.sugarcrm.com/SPL
 * Software distributed under the License is distributed on an  "AS IS"  basis,
 * WITHOUT WARRANTY OF ANY KIND, either express or implied. See the License for
 * the specific language governing rights and limitations under the License.
 * The Original Code is:  SugarCRM Open Source
 * The Initial Developer of the Original Code is SugarCRM, Inc.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.;
 * All Rights Reserved.
 * Contributor(s): ______________________________________.
 ********************************************************************************/
/*********************************************************************************
 * $Header$
 * Description:  Defines the Account SugarBean Account entity with the necessary
 * methods and variables.
 * Portions created by SugarCRM are Copyright (C) SugarCRM, Inc.
 * All Rights Reserved.
 * Contributor(s): ______________________________________..
 ********************************************************************************/

include_once('config.php');
require_once('include/logging.php');
require_once('include/utils/utils.php');

	global $empty_string;
// Faq is used to store vtiger_faq information.
class Faq extends CRMEntity {
	var $log;
	var $db;
	var $table_name = "vtiger_faq";
	var $table_index= 'id';
	var $tab_name = Array('vtiger_crmentity','vtiger_faq');
	var $tab_name_index = Array('vtiger_crmentity'=>'crmid','vtiger_faq'=>'id');
				
	var $entity_table = "vtiger_crmentity";
	
	var $column_fields = Array();
		
	var $sortby_fields = Array('id');		

	// This is the list of vtiger_fields that are in the lists.
	var $list_fields = Array(
				'Entity No'=>Array('faq'=>'entity_no'),
				'Campaign No'=>Array('faq'=>'campid'),
				'Changed By'=>Array('faq'=>'changed_by'),
				'Pre Assigned To'=>Array('faq'=>'pre_assigned_to'),
				'Post Assigned To'=>Array('faq'=>'post_assigned_to'),
				'Out Let'=>Array('faq'=>'outlet'), 
				'Created Time'=>Array('crmentity'=>'createdtime'), 
				'Modified Time'=>Array('crmentity'=>'modifiedtime') 
				);
	
	var $list_fields_name = Array(
				'Entity No'=>'entity_no',
				'Campaign No'=>'campid',
				'Changed By'=>'changed_by',
				'Pre Assigned To'=>'pre_assigned_to',
				'Post Assigned To'=>'post_assigned_to',
				'Out Let'=>'outlet',
				'Created Time'=>'createdtime',
				'Modified Time'=>'modifiedtime' 
			  );
	var $list_link_field= 'faq_no';

	var $search_fields = Array(
				'Entity No'=>Array('faq'=>'entity_no'),
				'Campaign No'=>Array('faq'=>'campid'),
				'Changed By'=>Array('faq'=>'changed_by'),
				'Pre Assigned To'=>Array('faq'=>'pre_assigned_to'),
				'Post Assigned To'=>Array('faq'=>'post_assigned_to'),
				'Out Let'=>Array('faq'=>'outlet'), 
				'Created Time'=>Array('crmentity'=>'createdtime'), 
				'Modified Time'=>Array('crmentity'=>'modifiedtime')  
				);
	
	var $search_fields_name = Array(
				'Entity No'=>'entity_no',
				'Campaign No'=>'campid',
				'Changed By'=>'changed_by',
				'Pre Assigned To'=>'pre_assigned_to',
				'Post Assigned To'=>'post_assigned_to',
				'Out Let'=>'outlet',
				'Created Time'=>'createdtime',
				'Modified Time'=>'modifiedtime'
			  );

	//Added these variables which are used as default order by and sortorder in ListView
	var $default_order_by = 'id';
	var $default_sort_order = 'DESC';

	var $mandatory_fields = Array('faq_no','createdtime' ,'modifiedtime');

	// For Alphabetical search
	var $def_basicsearch_col = 'faq_no';
	
	/**	Constructor which will set the column_fields in this object
	 */
	function Faq() {
		$this->log =LoggerManager::getLogger('faq');
		$this->log->debug("Entering Faq() method ...");
		$this->db = PearDatabase::getInstance();
		$this->column_fields = getColumnFields('Faq');
		$this->log->debug("Exiting Faq method ...");
	}

	function save_module($module)
	{
		//Inserting into Faq comment table
		//$this->insertIntoFAQCommentTable('vtiger_faqcomments', $module);
		
	}


	/** Function to insert values in vtiger_faqcomments table for the specified module,
  	  * @param $table_name -- table name:: Type varchar
  	  * @param $module -- module:: Type varchar
 	 */	
	function insertIntoFAQCommentTable($table_name, $module)
	{
		global $log;
		$log->info("in insertIntoFAQCommentTable  ".$table_name."    module is  ".$module);
        	global $adb;

        	$current_time = $adb->formatDate(date('Y-m-d H:i:s'), true);

		if($this->column_fields['comments'] != '')
			$comment = $this->column_fields['comments'];
		else
			$comment = $_REQUEST['comments'];

		if($comment != '')
		{
			$params = array('', $this->id, from_html($comment), $current_time);
			$sql = "insert into vtiger_faqcomments values(?, ?, ?, ?)";	
			$adb->pquery($sql, $params);
		}
	}	
	

	/*
	 * Function to get the primary query part of a report 
	 * @param - $module Primary module name
	 * returns the query string formed on fetching the related data for report for primary module
	 */
	function generateReportsQuery($module){
	 			$moduletable = $this->table_name;
	 			$moduleindex = $this->table_index;
	 			
	 			$query = "from $moduletable
					inner join vtiger_crmentity on vtiger_crmentity.crmid=$moduletable.$moduleindex
					left join vtiger_products as vtiger_products$module on vtiger_products$module.productid = vtiger_faq.product_id 
					left join vtiger_groups as vtiger_groups$module on vtiger_groups$module.groupid = vtiger_crmentity.smownerid 
					left join vtiger_users as vtiger_users$module on vtiger_users$module.id = vtiger_crmentity.smownerid 
					left join vtiger_groups on vtiger_groups.groupid = vtiger_crmentity.smownerid 
					left join vtiger_users on vtiger_users.id = vtiger_crmentity.smownerid
                    left join vtiger_users as vtiger_lastModifiedBy".$module." on vtiger_lastModifiedBy".$module.".id = vtiger_crmentity.modifiedby";
	            return $query;
	}

	/*
	 * Function to get the relation tables for related modules 
	 * @param - $secmodule secondary module name
	 * returns the array with table names and fieldnames storing relations between module and this module
	 */
	function setRelationTables($secmodule){
		$rel_tables = array (
			"Documents" => array("vtiger_senotesrel"=>array("crmid","notesid"),"vtiger_faq"=>"id"),
		);
		return $rel_tables[$secmodule];
	}

	function clearSingletonSaveFields() {
		$this->column_fields['comments'] = '';
	}
	
}
?>