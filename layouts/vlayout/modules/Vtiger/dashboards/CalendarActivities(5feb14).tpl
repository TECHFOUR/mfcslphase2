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

<div class="dashboardWidgetHeader">
	<table width="100%" cellspacing="0" cellpadding="0">
	<thead>
		<tr>
			<th class="span4">
				<div class="dashboardTitle"><b>&nbsp;&nbsp;{vtranslate($WIDGET->getTitle())}</b></div>
			</th>
			<th class="span2">
            {if $PROFILEID eq 1 || $PROFILEID eq 2 ||  $PROFILEID eq 4 ||  $PROFILEID eq 7 ||  $PROFILEID eq 8 ||  $PROFILEID eq 9 ||  $PROFILEID eq 10 || $PROFILEID eq 11 || $PROFILEID eq 12}
				<div>
					<select class="widgetFilter" name="type" style='width:100px;margin-bottom:0px'>
						<option value="{$CURRENTUSER->getId()}">{vtranslate('LBL_MINE')}</option>                        
						<option value="all">{vtranslate('LBL_ALL')}</option>
                        
					</select>
				</div>
               {/if}
			</th>
			<th class="refresh span1" align="right">
				<span style="position:relative;"></span>
			</th>
			<th class="widgeticons span5" align="right">
				{include file="dashboards/DashboardHeaderIcons.tpl"|@vtemplate_path:$MODULE_NAME}
			</th>
		</tr>
	</thead>
	</table>
</div>
<div name="history" class="dashboardWidgetContent">
	{include file="dashboards/CalendarActivitiesContents.tpl"|@vtemplate_path:$MODULE_NAME WIDGET=$WIDGET}
</div>



<script type='text/javascript'>
	$(document).ready(function(){
		jQuery('.dashboardWidgetContent').on('click', 'a[name="history_more"]', function(e) {
			var element = jQuery(e.currentTarget);
			var url = element.data('url')+'&content=true';
			AppConnector.request(url).then(function(data) {
				jQuery(element.parent().parent()).append(data);
				element.parent().remove();
			});
		});
	});
</script>