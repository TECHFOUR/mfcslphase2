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
<div style='padding:1px'>
{if count($MODELS) > 0}
	<div class='row-fluid'>
		<div class='span12'>
			<div class='row-fluid'>
				
				<div class='span4'>
					<b>{vtranslate('Customer Name', $MODULE_NAME)}</b>
				</div>
                <!-- <div class='span4'>
					<b>{vtranslate('Last Name', $MODULE_NAME)}</b>
				</div>-->
				<div class='span4'>
					<b>{vtranslate('Mobile No', $MODULE_NAME)}</b>
				</div>
                <div class='span4'>
					<b>{vtranslate('Driver Name', $MODULE_NAME)}</b>
				</div>
			</div>
		</div>
		<hr>
		{foreach item=MODEL from=$MODELS}
		<div class='row-fluid'>
			
			<div class='span4'>
				<a href="index.php?module=Leads&relatedModule=Driver&view=Detail&record={$MODEL->get('leadid')}&mode=showRelatedList&tab_label=Driver" target="_blank">
                {$MODEL->getDisplayValue('firstname')} {$MODEL->getDisplayValue('lastname')}</a>
			</div>
            <!--<div class='span4'>
				{$MODEL->getDisplayValue('lastname')}
			</div>-->
			<div class='span4'>
				{$MODEL->getDisplayValue('mobile')}
			</div>
            
            <div class='span4'>
           
				<!--<a href="{$MODEL->getDetailViewUrl()}">{$MODEL->getName()}</a> -->
                <a href="driver_letter.php?driverid = {$MODEL->get('driverid')}">{$MODEL->get('fullname')}</a>
                
			</div>
		</div>
		{/foreach}
	</div>
{else}
	<span class="noDataMsg">
		{*vtranslate('LBL_NO')*} {*vtranslate($MODULE_NAME, $MODULE_NAME)*} {vtranslate('There are no driver assignments for today')}
	</span>
{/if}
</div>