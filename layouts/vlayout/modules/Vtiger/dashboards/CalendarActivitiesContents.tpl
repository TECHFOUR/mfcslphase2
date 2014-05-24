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
		
        {if $INDEX eq 0 && ($PROFILEID_PERMISSION eq 1 ||  $PROFILEID_PERMISSION eq 2 ||  $PROFILEID_PERMISSION eq 3 )}
                <div>
			<div class='pull-left' style='margin-top:5px;font-size:9px;width:30%'>				
				<strong>{if $PROFILEID_PERMISSION eq 1} Region {elseif $PROFILEID_PERMISSION eq 2} Outlet Name {else} Agent{/if} </strong>
			</div>
            
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%' >	{*4 Col*}	
				&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
               <strong> {$daygreater2day}</strong>
                
			</div>
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%' >	{*3 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp;&nbsp; 
                <strong>{$daygreater1day}</strong>
			</div>
            
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%'  >	{*2 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
               <strong> {$currentDate}  </strong>          
			</div>
            
             <div class='pull-right' style='margin-top:5px; font-size:9px;width:17.5%' >	{*1 Col*}	
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
        {*<div class='pull-left' style='margin-top:5px'><strong style="font-size:9px;">{$INDEX + 1}-</strong>&nbsp;&nbsp;</div>*}
     {if $PROFILEID_PERMISSION eq 1 ||  $PROFILEID_PERMISSION eq 2 ||  $PROFILEID_PERMISSION eq 3} {* Start for Region, Manager and Mo Only *}         
         <div style="width:100%;">
			<div class='pull-left' style='margin-top:5px; font-size:9px;;width:30%'>				
				<a href="{$ACTIVITY->getDetailViewUrl()}">{*$ACTIVITY->get('subject')*}                
                	            
                </a>
                <strong style="font-size:9px;">{$INDEX + 1}-</strong>&nbsp;&nbsp;{$ACTIVITY->get('agent')} 
                <span style="color:#FF0D0D"> {if $ACTIVITY->get('totalpdpastcall')} ({$ACTIVITY->get('totalpdpastcall')}) {/if} </span>
			</div>
            
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%' >	{*4 Col*}	
				&nbsp; &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp; 
                <span style="color:#00F">{$ACTIVITY->get('ddd')}</span> / <span style="color:#008000">{$ACTIVITY->get('dddheld')} </span>
                
			</div>
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%' >	{*3 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp;&nbsp; 
                <span style="color:#00F">{$ACTIVITY->get('dd')}</span> / <span style="color:#008000">{$ACTIVITY->get('ddheld')} </span>
			</div>
            
            <div class='pull-right' style='margin-top:5px;font-size:9px;width:17.5%'  >	{*2 Col*}	
				&nbsp; &nbsp; &nbsp;&nbsp; &nbsp;
                <span style="color:#00F">{$ACTIVITY->get('d')}</span> / <span style="color:#008000">{$ACTIVITY->get('dheld')} </span>            
			</div>
            
             <div class='pull-right' style='margin-top:5px; font-size:9px;width:17.5%' >	{*1 Col*}	
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
				 <strong style="font-size:9px;">{$INDEX + 1}-</strong><a  href="{$ACTIVITY->getEditViewUrl()}">{*$ACTIVITY->get('subject')*}                
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
			{*<p class='pull-right muted' style='margin-top:5px;padding-right:5px;'><small title="{Vtiger_Util_Helper::formatDateTimeIntoDayString("$START_DATE $START_TIME")} {vtranslate('LBL_TO')} {Vtiger_Util_Helper::formatDateTimeIntoDayString("$DUE_DATE $DUE_TIME")}">{Vtiger_Util_Helper::formatDateDiffInStrings("$DUE_DATE $DUE_TIME")}</small></p>*}
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
            {vtranslate('There are no calls for today', $MODULE_NAME)}
        {else}
            {vtranslate('There are no followup on appointments for today', $MODULE_NAME)}
        {/if}
		</span>
	{/foreach}
</div>
{if $ACTIVITIES|@count eq 10}
	<div><a href="#" class="pull-right" name="history_more" data-url="{$WIDGET->getUrl()}&page={$PAGING->getNextPage()}">{vtranslate('LBL_MORE')}...</a></div>
{/if}