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

<div style='padding:5px'>
	{foreach from=$ACTIVITIES key=INDEX item=ACTIVITY}
	<div>
		
        {if $INDEX eq 0 && ($PROFILEID eq 2 ||  $PROFILEID eq 4 ||  $PROFILEID eq 7 ||  $PROFILEID eq 8 ||  $PROFILEID eq 9 ||  $PROFILEID eq 10 || $PROFILEID eq 11 || $PROFILEID eq 12)}
                <div>
			<div class='pull-left' style='margin-top:5px'>				
				<strong>Agent </strong>
			</div>
            
            <div class='pull-right' style='margin-top:5px;' >	{*4 Col*}	
				&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
               <strong> {$daygreater2day}</strong>
                
			</div>
            <div class='pull-right' style='margin-top:5px;' >	{*3 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp;&nbsp; 
                <strong>{$daygreater1day}</strong>
			</div>
            
            <div class='pull-right' style='margin-top:5px;'  >	{*2 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
               <strong> {$currentDate}  </strong>          
			</div>
            
             <div class='pull-right' style='margin-top:5px; ' >	{*1 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
               <strong>{$dayless1day} </strong>        
			</div>
                         				
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
        {/if}
        <!--<div class='pull-left'>
			{if $ACTIVITY->get('activitytype') == 'Task'}
				<image src="{vimage_path('Tasks.png')}" width="24px"/>&nbsp;&nbsp;
			{else}
				<image src="{vimage_path('Calendar.png')}" width="24px" />&nbsp;&nbsp;
			{/if}            
		</div>-->
        <div class='pull-left' style='margin-top:5px'><strong>{$INDEX + 1}-</strong>&nbsp;&nbsp;</div>
     {if $PROFILEID eq 2 ||  $PROFILEID eq 4 ||  $PROFILEID eq 7 ||  $PROFILEID eq 8 ||  $PROFILEID eq 9 ||  $PROFILEID eq 10 || $PROFILEID eq 11 || $PROFILEID eq 12} {* Start for Mo Only *}         
         <div>
			<div class='pull-left' style='margin-top:5px'>				
				<a href="{$ACTIVITY->getDetailViewUrl()}">{*$ACTIVITY->get('subject')*}                
                	            
                </a>
                {$ACTIVITY->get('agent')}   
			</div>
            
            <div class='pull-right' style='margin-top:5px;' >	{*4 Col*}	
				&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                <span style="color:#00F">{$ACTIVITY->get('ddd')}</span> / <span style="color:#008000">{$ACTIVITY->get('dddheld')} </span>
                
			</div>
            <div class='pull-right' style='margin-top:5px;' >	{*3 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp;&nbsp; 
                <span style="color:#00F">{$ACTIVITY->get('dd')}</span> / <span style="color:#008000">{$ACTIVITY->get('ddheld')} </span>
			</div>
            
            <div class='pull-right' style='margin-top:5px;'  >	{*2 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
                <span style="color:#00F">{$ACTIVITY->get('d')}</span> / <span style="color:#008000">{$ACTIVITY->get('dheld')} </span>            
			</div>
            
             <div class='pull-right' style='margin-top:5px; ' >	{*1 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
                <span style="color:#00F">{$ACTIVITY->get('pd')}</span> / <span style="color:#008000">{$ACTIVITY->get('pdheld')} </span>          
			</div>
                         				
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
        
       {else}      {* End for Mo Only *}  		
		<div>
			<div class='pull-left' style='margin-top:5px'>
				{assign var=PARENT_ID value=$ACTIVITY->get('parent_id')}
				{assign var=CONTACT_ID value=$ACTIVITY->get('contact_id')}
				<a target="_blank" href="{$ACTIVITY->getEditViewUrl()}">{*$ACTIVITY->get('subject')*}                
                	{$ACTIVITY->getModuleDashboardValues($ACTIVITY->get('activityid'),'mobile')}                
                </a>{*if $PARENT_ID} {vtranslate('LBL_FOR')} {$ACTIVITY->getDisplayValue('parent_id')}{else if $CONTACT_ID} {vtranslate('LBL_FOR')} {$ACTIVITY->getDisplayValue('contact_id')}{/if*}
			</div>
            
            <div class='pull-left' style='margin-top:5px; ' >		
				&nbsp; &nbsp; {$ACTIVITY->getModuleDashboardValues($ACTIVITY->get('activityid'),'fullname')} &nbsp;                 
			</div>
             <div class='pull-right' style='margin-top:5px; ' >	            				
                {$ACTIVITY->get('date_start')} 
                {$ACTIVITY->get('time_start')}
               
			</div>
            
				{assign var=START_DATE value=$ACTIVITY->get('start_date')}
				{assign var=START_TIME value=$ACTIVITY->get('time_start')}
				
				{assign var=DUE_DATE value=$ACTIVITY->get('due_date')}
				{assign var=DUE_TIME value=$ACTIVITY->get('time_end')}
			<p class='pull-right muted' style='margin-top:5px;padding-right:5px;'><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")} {vtranslate('LBL_TO')} {Vtiger_Util_Helper::formatDateTimeIntoDayString("$DUE_DATE $DUE_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$DUE_DATE $DUE_TIME")}</small></p>
			<div class='clearfix'></div>
		</div>
		<div class='clearfix'></div>
{/if}  {* END *}      
	</div>
	{foreachelse}
		<span class="noDataMsg">
			 {*
       upcoming -> Appointment booked
       overdue ->  Followup Calling
       today - >   Today Calling        				
			*}                
        {if $smarty.request.name eq 'OverdueActivities'}
            {vtranslate('There are no followup calls for today', $MODULE_NAME)}
        {elseif $smarty.request.name eq 'CalendarTodayActivities'}
            {vtranslate('There are no today calling', $MODULE_NAME)}
        {else}
            {vtranslate('There are no followup on appointments for today', $MODULE_NAME)}
        {/if}
		</span>
	{/foreach}
</div>
{if $ACTIVITIES|@count eq 10}
	<div><a href="#" class="pull-right" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING->getNextPage()}">{vtranslate('LBL_MORE')}...</a></div>
{/if}