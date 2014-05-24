<?php
/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

class Vtiger_RelationListView_Model extends Vtiger_Base_Model {

	protected $relationModel = false;
	protected $parentRecordModel = false;

	public function setRelationModel($relation){
		$this->relationModel = $relation;
		return $this;
	}

	public function getRelationModel() {
		return $this->relationModel;
	}

	public function setParentRecordModel($parentRecord){
		$this->parentRecordModel = $parentRecord;
		return $this;
	}

	public function getParentRecordModel(){
		return $this->parentRecordModel;
	}

	public function getCreateViewUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateEventRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateEventRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getCreateTaskRecordUrl(){
		$relationModel = $this->getRelationModel();
		$relatedModel = $relationModel->getRelationModuleModel();
		$parentRecordModule = $this->getParentRecordModel();
		$parentModule = $parentRecordModule->getModule();

		$createViewUrl = $relatedModel->getCreateTaskRecordUrl().'&sourceModule='.$parentModule->get('name').
								'&sourceRecord='.$parentRecordModule->getId().'&relationOperation=true';

		//To keep the reference fieldname and record value in the url if it is direct relation
		if($relationModel->isDirectRelation()) {
			$relationField = $relationModel->getRelationField();
			$createViewUrl .='&'.$relationField->getName().'='.$parentRecordModule->getId();
		}
		return $createViewUrl;
	}

	public function getLinks(){
		global $adb;		
		$userid = $_SESSION['authenticated_user_id'];
		$relationModel = $this->getRelationModel();
		$actions = $relationModel->getActions();		
		$selectLinks = $this->getSelectRelationLinks();
		foreach($selectLinks as $selectLinkModel) {
			$selectLinkModel->set('_selectRelation',true)->set('_module',$relationModel->getRelationModuleModel());
		}
		$addLinks = $this->getAddRelationLinks();

		$links = array_merge($selectLinks, $addLinks);
		$relatedLink = array();
		$relatedLink['LISTVIEWBASIC'] = $links;
		
		// Start added code by Ajay [TECHFOUR]				
		if($_REQUEST['relatedModule'] == "CampaignApproval" && $userid != 1) {
			$sub_query = " AND vtiger_users.id = ".$userid." ";
			$userDetails = getUserDetails($sub_query);
			$depth = $userDetails['depth'];
			$emailstatus = 'emailstatus'.$depth;
			$record = $_REQUEST['record'];
			$campaign_qry = $adb->query("select ".$emailstatus." from vtiger_campaign where campaignid = ".$record." ");		
			if($adb->num_rows($campaign_qry) > 0) {
				$row = $adb->fetch_array($campaign_qry);
				$emailstatus = $row[$emailstatus];
				if($emailstatus == '1')
					return $relatedLink_blank;
				else
					return $relatedLink;
			}
			else {
				return $relatedLink_blank;
			}
			
		}// End added code by Ajay [TECHFOUR]
		// Start added code by Ajay [TECHFOUR]				
		else if($_REQUEST['relatedModule'] == "Driver" && $userid != 1) {			
			$record = $_REQUEST['record'];
			$lead_qry = $adb->query("SELECT leadstatus, cf_903, driver_pickup FROM vtiger_leaddetails 
INNER JOIN vtiger_seactivityrel ON vtiger_seactivityrel.crmid = vtiger_leaddetails.leadid
INNER JOIN vtiger_activitycf ON vtiger_activitycf.activityid = vtiger_seactivityrel.activityid
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_activitycf.activityid
WHERE vtiger_crmentity.deleted = 0 AND leadid = $record ORDER BY vtiger_seactivityrel.activityid DESC ");		
			if($adb->num_rows($lead_qry) > 0) {
				$row = $adb->fetch_array($lead_qry);
				$leadstatus = $row['leadstatus'];
				$sub_disposition = $row['cf_903'];
				$driver_pickup = $row['driver_pickup'];
				if($leadstatus == 'Appointment Booked' && $sub_disposition == 'Confirm for Tomorrow' && $driver_pickup == 1)
					return $relatedLink;
				else
					return $relatedLink_blank;
			}
						
		}// End added code by Ajay [TECHFOUR]
		else
			return $relatedLink;
		
	}

	public function getSelectRelationLinks() {
		$relationModel = $this->getRelationModel();
		$selectLinkModel = array();

		if(!$relationModel->isSelectActionSupported()) {
			return $selectLinkModel;
		}

		$relatedModel = $relationModel->getRelationModuleModel();

		$selectLinkList = array(
			array(
				'linktype' => 'LISTVIEWBASIC',
				'linklabel' => vtranslate('LBL_SELECT')." ".vtranslate($relatedModel->get('label')),
				'linkurl' => '',
				'linkicon' => '',
			)
		);


		foreach($selectLinkList as $selectLink) {
			$selectLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($selectLink);
		}
		return $selectLinkModel;
	}

	public function getAddRelationLinks() {
		$relationModel = $this->getRelationModel();
		$addLinkModel = array();

		if(!$relationModel->isAddActionSupported()) {
			return $addLinkModel;
		}
		$relatedModel = $relationModel->getRelationModuleModel();

		if($relatedModel->get('label') == 'Calendar'){

			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_EVENT'),
					'linkurl' => $this->getCreateEventRecordUrl(),
					'linkicon' => '',
			);
			$addLinkList[] = array(
					'linktype' => 'LISTVIEWBASIC',
					'linklabel' => vtranslate('LBL_ADD_TASK'),
					'linkurl' => $this->getCreateTaskRecordUrl(),
					'linkicon' => '',
			);
		}else{
			$addLinkList = array(
				array(
					'linktype' => 'LISTVIEWBASIC',
					// NOTE: $relatedModel->get('label') assuming it to be a module name - we need singular label for Add action.
					'linklabel' => vtranslate('LBL_ADD')." ".vtranslate('SINGLE_' . $relatedModel->getName(), $relatedModel->getName()),
					'linkurl' => $this->getCreateViewUrl(),
					'linkicon' => '',
				)
			);
		}

		foreach($addLinkList as $addLink) {
			$addLinkModel[] = Vtiger_Link_Model::getInstanceFromValues($addLink);
		}
		
		return $addLinkModel;
	}

	public function getEntries($pagingModel) {
		$db = PearDatabase::getInstance();
		$parentModule = $this->getParentRecordModel()->getModule();
		$relationModule = $this->getRelationModel()->getRelationModuleModel();
				
		$relatedColumnFields = $relationModule->getConfigureRelatedListFields();
		if(count($relatedColumnFields) <= 0){
			$relatedColumnFields = $relationModule->getRelatedListFields();
		}
		$query = $this->getRelationQuery();
		
		if($_REQUEST['relatedModule'] == "Calendar" && $_REQUEST['mode'] == "showRelatedList" && $_REQUEST['tab_label'] == "Activities") {
			$query .= " and ((vtiger_activity.activitytype='Task' and
			vtiger_activity.status not in ('Completed','Deferred')) or
			(vtiger_activity.activitytype NOT in ('Emails','Task') and
			vtiger_activity.eventstatus not in ('','Held'))) ";
		}
		
		if($_REQUEST['relatedModule'] == "Calendar" && $_REQUEST['mode'] == "showRelatedList" && $_REQUEST['tab_label'] == "Activity History") {
			$query = str_replace("vtiger_activity.activityid, vtiger_activity.subject, vtiger_activity.status, vtiger_activity.eventstatus, vtiger_activity.activitytype,vtiger_activity.date_start, vtiger_activity.due_date,vtiger_activity.time_start,vtiger_activity.time_end, vtiger_crmentity.modifiedtime,vtiger_crmentity.createdtime, vtiger_crmentity.description, CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) as user_name,vtiger_groups.groupname","CASE WHEN (vtiger_users.user_name not like '') THEN CONCAT(vtiger_users.first_name,' ',vtiger_users.last_name) ELSE vtiger_groups.groupname END AS user_name, vtiger_crmentity.*, vtiger_activity.*, vtiger_seactivityrel.crmid AS parent_id, CASE WHEN (vtiger_activity.activitytype = 'Task') THEN vtiger_activity.status ELSE vtiger_activity.eventstatus END AS status",$query);
		}
		
		if ($this->get('whereCondition')) {
			$query = $this->updateQueryWithWhereCondition($query);
		}

		$startIndex = $pagingModel->getStartIndex();
		$pageLimit = $pagingModel->getPageLimit();

		$orderBy = $this->getForSql('orderby');
		$sortOrder = $this->getForSql('sortorder');
		if($orderBy) {
			if($orderBy === 'assigned_user_id' || $orderBy == 'smownerid') {
				$orderBy = 'user_name';
			}
			// Qualify the the column name with table to remove ambugity
			$qualifiedOrderBy = $orderBy;
			$orderByField = $relationModule->getFieldByColumn($orderBy);
			if ($orderByField) {
				$qualifiedOrderBy = $orderByField->get('table') . '.' . $qualifiedOrderBy;
			}
			$query = "$query ORDER BY $qualifiedOrderBy $sortOrder";
		}

		$limitQuery = $query .' LIMIT '.$startIndex.','.$pageLimit;
		$result = $db->pquery($limitQuery, array());
		$relatedRecordList = array();

		for($i=0; $i< $db->num_rows($result); $i++ ) {
			$row = $db->fetch_row($result,$i);
			$newRow = array();
			foreach($row as $col=>$val){				
				if(array_key_exists($col,$relatedColumnFields)){
                    $newRow[$relatedColumnFields[$col]] = $val;
                }
            }
			//To show the value of "Assigned to"
			$newRow['assigned_user_id'] = $row['smownerid'];
			$record = Vtiger_Record_Model::getCleanInstance($relationModule->get('name'));
            $record->setData($newRow)->setModuleFromInstance($relationModule);
            $record->setId($row['crmid']);
			$relatedRecordList[$row['crmid']] = $record;
		}
		
		//echo "<pre>";print_r($relatedRecordList);
		$pagingModel->calculatePageRange($relatedRecordList);

		$nextLimitQuery = $query. ' LIMIT '.($startIndex+$pageLimit).' , 1';
		/*echo $nextLimitQuery = "SELECT * from vtiger_modtracker_basic INNER JOIN vtiger_modtracker_detail ON vtiger_modtracker_detail.id = vtiger_modtracker_basic.id WHERE 
fieldname = 'assigned_user_id' AND vtiger_modtracker_basic.crmid = 2 ";*/
		$nextPageLimitResult = $db->pquery($nextLimitQuery, array());
		if($db->num_rows($nextPageLimitResult) > 0){
			$pagingModel->set('nextPageExists', true);
		}else{
			$pagingModel->set('nextPageExists', false);
		}	
		//echo "<pre>";print_r($relatedRecordList);//[TECHFOUR]	
		return $relatedRecordList;
	}

	public function getHeaders() {
		$relationModel = $this->getRelationModel();
		$relatedModuleModel = $relationModel->getRelationModuleModel();
		
		$summaryFieldsList = $relatedModuleModel->getSummaryViewFieldsList();
		$headerFieldNames = $relatedModuleModel->getRelatedListFields();		
		//print_r($summaryFieldsList);
		$headerFields = array();
		if(count($summaryFieldsList) > 0) {			
			foreach($summaryFieldsList as $fieldName => $fieldModel) {
				$headerFields[$fieldName] = $fieldModel;
			}
		} else {
			//echo "<pre>";print_r($headerFieldNames);//[TECHFOUR]			
			foreach($headerFieldNames as $fieldName) {
				$headerFields[$fieldName] = $relatedModuleModel->getField($fieldName);
			}
		}
		
		return $headerFields;
	}

	/**
	 * Function to get Relation query
	 * @return <String>
	 */
	public function getRelationQuery() {
		$relationModel = $this->getRelationModel();
		$recordModel = $this->getParentRecordModel();				
		$query = $relationModel->getQuery($recordModel);				
		return $query;
	}

	public static function getInstance($parentRecordModel, $relationModuleName, $label=false) {
		$parentModuleName = $parentRecordModel->getModule()->get('name');
		$className = Vtiger_Loader::getComponentClassName('Model', 'RelationListView', $parentModuleName);
		$instance = new $className();

		$parentModuleModel = $parentRecordModel->getModule();
		$relationModuleModel = Vtiger_Module_Model::getInstance($relationModuleName);

		$relationModel = Vtiger_Relation_Model::getInstance($parentModuleModel, $relationModuleModel, $label);
		$instance->setRelationModel($relationModel)->setParentRecordModel($parentRecordModel);
		return $instance;
	}

	/**
	 * Function to get Total number of record in this relation
	 * @return <Integer>
	 */
	public function getRelatedEntriesCount() {
		$db = PearDatabase::getInstance();
		$relationQuery = $this->getRelationQuery();
		$position = stripos($relationQuery, 'from');
		if ($position) {
			$split = spliti('from', $relationQuery);
			$splitCount = count($split);
			$relationQuery = 'SELECT count(*) AS count ';
			for ($i=1; $i<$splitCount; $i++) {
				$relationQuery = $relationQuery. ' FROM ' .$split[$i];
			}
		}		
		$result = $db->pquery($relationQuery, array());
		return $db->query_result($result, 0, 'count');
	}

	/**
	 * Function to update relation query
	 * @param <String> $relationQuery
	 * @return <String> $updatedQuery
	 */
	public function updateQueryWithWhereCondition($relationQuery) {
		$condition = '';

		$whereCondition = $this->get("whereCondition");
		$count = count($whereCondition);
		if ($count > 1) {
			$appendAndCondition = true;
		}

		$i = 1;
		foreach ($whereCondition as $fieldName => $fieldValue) {
			$condition .= " $fieldName = '$fieldValue' ";
			if ($appendAndCondition && ($i++ != $count)) {
				$condition .= " AND ";
			}
		}

		$pos = stripos($relationQuery, 'where');
		if ($pos) {
			$split = spliti('where', $relationQuery);
			$updatedQuery = $split[0] . ' WHERE ' . $split[1] . ' AND ' . $condition;
		} else {
			$updatedQuery = $relationQuery . ' WHERE ' . $condition;
		}
		return $updatedQuery;
	}

}