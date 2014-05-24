{*<!--
/*********************************************************************************
  ** The contents of this file are subject to the vtiger CRM Public License Version 1.0
   * ("License"); You may not use this file except in compliance with the License
   * The Original Code is:  vtiger CRM Open Source
   * The Initial Developer of the Original Code is vtiger.
   * Portions created by vtiger are Copyright (C) vtiger.
   * All Rights Reserved.
  *
 ********************************************************************************/
-->*}
{strip}
<script type="text/javascript">
function checkAssignedLimit() {
	var selected_ids = $("#selected_ids").val();
	var activity_start_date = $("#leadactivitystartdate").val();
	var assigned_user_id = $("#assigned_user_id").val();
	if(activity_start_date == "") {
		alert("Please select the Activity Start Date.");
		return false;
	}
	var QryString = "?selected_ids="+selected_ids+"&assigned_user_id="+assigned_user_id+"&activity_start_date="+activity_start_date+"&leadmassedit=leadmasseditbymo_superviser";
	 $.ajax({
		 url:"ManualAssignCalling.php"+QryString,
		 success:function(result_data){			 														  
			 if(result_data == '')
			 	$( "#massEdit" ).submit();
			if(result_data != '')
				alert(result_data);	
		}
	});
}
	
</script>
{foreach key=index item=jsModel from=$SCRIPTS}
	<script type="{$jsModel->getType()}" src="{$jsModel->getSrc()}"></script>
{/foreach}
<div id="massEditContainer" class='modelContainer'>
	<div class="modal-header">
		<button data-dismiss="modal" class="close" title="{vtranslate('LBL_CLOSE')}">x</button>
		<h3 id="massEditHeader">{vtranslate('LBL_MASS_EDITING', $MODULE)} {vtranslate($MODULE, $MODULE)}</h3>
	</div>
	<form class="form-horizontal contentsBackground" id="massEdit" name="MassEdit" method="post" action="index.php">
		{if !empty($PICKIST_DEPENDENCY_DATASOURCE)}
			<input type="hidden" name="picklistDependency" value='{Vtiger_Util_Helper::toSafeHTML($PICKIST_DEPENDENCY_DATASOURCE)}' />
		{/if}
		<input type="hidden" name="module" value="{$MODULE}" />
		<input type="hidden" name="action" value="MassSave" />
		<input type="hidden" name="viewname" value="{$CVID}" />
		<input type="hidden" name="selected_ids" id="selected_ids" value={ZEND_JSON::encode($SELECTED_IDS)}>
		<input type="hidden" name="excluded_ids" id="excluded_ids" value={ZEND_JSON::encode($EXCLUDED_IDS)}>
        <input type="hidden" name="search_key" value= "{$SEARCH_KEY}" />
        <input type="hidden" name="operator" value="{$OPERATOR}" />
        <input type="hidden" name="search_value" value="{$ALPHABET_VALUE}" />
        
        <input type="hidden" id="massEditFieldsNameList" data-value='{Vtiger_Util_Helper::toSafeHTML(ZEND_JSON::encode($MASS_EDIT_FIELD_DETAILS))}' />
		<div name='massEditContent'>
			<div class="modal-body tabbable">
				<ul class="nav nav-tabs massEditTabs">
					{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
					{if $BLOCK_FIELDS|@count gt 0}
					<li {if $smarty.foreach.blockIterator.iteration eq 1}class="active"{/if}><a href="#block_{$smarty.foreach.blockIterator.iteration}" data-toggle="tab"><strong>{vtranslate($BLOCK_LABEL, $MODULE)}</strong></a></li>
					{/if}
					{/foreach}
				</ul>
				<div class="tab-content massEditContent">
				{foreach key=BLOCK_LABEL item=BLOCK_FIELDS from=$RECORD_STRUCTURE name=blockIterator}
					{if $BLOCK_FIELDS|@count gt 0}
					<div class="tab-pane {if $smarty.foreach.blockIterator.iteration eq 1}active{/if}" id="block_{$smarty.foreach.blockIterator.iteration}">
						<table class="massEditTable table table-bordered">
							<tr>
							{assign var=COUNTER value=0}
							{foreach key=FIELD_NAME item=FIELD_MODEL from=$BLOCK_FIELDS name=blockfields}
								{assign var="isReferenceField" value=$FIELD_MODEL->getFieldDataType()}
								{assign var="refrenceList" value=$FIELD_MODEL->getReferenceList()}
								{assign var="refrenceListCount" value=count($refrenceList)}
								{if $FIELD_MODEL->isEditable() eq true}
									{if $FIELD_MODEL->get('uitype') eq "19"}
										{if $COUNTER eq '1'}
											<td></td><td></td></tr><tr>
											{assign var=COUNTER value=0}
										{/if}
									{/if}
									{if $COUNTER eq 2}
										</tr><tr>
										{assign var=COUNTER value=1}
									{else}
										{assign var=COUNTER value=$COUNTER+1}
									{/if}
									<td class="fieldLabel alignMiddle">
									{if $FIELD_MODEL->isMandatory() eq true} <span class="redColor">*</span> {/if}
									{if {$isReferenceField} eq "reference"}
										{if $refrenceListCount > 1}
											<select style="width: 150px;" class="chzn-select referenceModulesList" id="referenceModulesList">
												<optgroup>
													{foreach key=index item=value from=$refrenceList}
														<option value="{$value}">{vtranslate($value, $value)}</option>
													{/foreach}
												</optgroup>
											</select>
										{else}
											{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
										{/if}
									{else}
										{vtranslate($FIELD_MODEL->get('label'), $MODULE)}
									{/if}
									&nbsp;&nbsp;
								</td>
								<td class="fieldValue" {if $FIELD_MODEL->getFieldDataType() eq 'boolean'} style="width:25%" {/if} {if $FIELD_MODEL->get('uitype') eq '19'} colspan="3" {assign var=COUNTER value=$COUNTER+1} {/if}>
									{include file=vtemplate_path($FIELD_MODEL->getUITypeModel()->getTemplateName(),$MODULE)}
								</td>
							{/if}
							{/foreach}
							{*If their are odd number of fields in MassEdit then border top is missing so adding the check*}
							{if $COUNTER is odd}                            							
                                <td>
                                {if $MODULE eq 'Leads'}	
                                Activity Start Date &nbsp;&nbsp;&nbsp;<input type="text"  data-validation-engine="validate[funcCall[Vtiger_Base_Validator_Js.invokeValidation]]" value="" readonly="readonly" data-date-format="dd-mm-yyyy" name="leadactivitystartdate" class="span9 dateField" style="width:190px;" id="leadactivitystartdate"  onchange="ValidateleadAssignment(this.value,this.id,'{$date_format}');" >
                                <span class="add-on"><i class="icon-calendar"></i></span>
                                {/if}
                                </td>                                
								<td></td>
                               
							{/if}
							</tr>
						</table>
					</div>
					{/if}
				{/foreach}
				</div>
			</div>
		</div>
		{include file='ModalFooter.tpl'|@vtemplate_path:$MODULE}
	</form>
</div>
{/strip}