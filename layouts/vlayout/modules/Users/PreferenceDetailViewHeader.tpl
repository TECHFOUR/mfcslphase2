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
    {assign var="MODULE_NAME" value=$MODULE_MODEL->get('name')}
    <input id="recordId" type="hidden" value="{$RECORD->getId()}" />
   <div class="detailViewContainer">
       <div class="detailViewTitle" id="prefPageHeader">
            <div class="row-fluid">
                <div class="span8">
                    <span class="row-fluid marginLeftZero">
						<span class="logo span2">
							{foreach key=ITER item=IMAGE_INFO from=$RECORD->getImageDetails()}
								{if !empty($IMAGE_INFO.path) && !empty($IMAGE_INFO.orgname)}
									<img src="{$IMAGE_INFO.path}_{$IMAGE_INFO.orgname}" alt="{$IMAGE_INFO.orgname}" title="{$IMAGE_INFO.orgname}" data-image-id="{$IMAGE_INFO.id}">
								{/if}
							{/foreach}
						</span>
						<span class="span9">
							<span id="myPrefHeading" class="row-fluid">
								<h3>{vtranslate('LBL_MY_PREFERENCES', $MODULE_NAME)} </h3>
							</span>
							<span class="row-fluid">
								{vtranslate('LBL_USERDETAIL_INFO', $MODULE_NAME)}&nbsp;&nbsp;"<b>{$RECORD->getName()}</b>"
							</span>
						</span>
					</span>
                </div>
                <div class="span4">
                    <div class="row-fluid pull-right detailViewButtoncontainer">
						<div class="btn-toolbar pull-right">
								
                                <!-- Start comment to hide User header button by jitendra singh on 31 jan14 --> 
                              <!--  
                               <div class='btn-group' title="{vtranslate('LBL_DISPLAY_TYPE', 'Vtiger')}">
									<a class='btn dropdown-toggle' data-toggle='dropdown' href='#'>
										<span id='currentWidthType'><i class='icon-th-list'></i></span>&nbsp;<span class='caret'></span>
									</a>
									<ul class='dropdown-menu pull-right' id='widthType'>
										<li class="cursorPointer" data-class='wideWidthType' title="{vtranslate('LBL_DISPLAY_WIDETYPE', 'Vtiger')}">
											<i class='icon-th-list'></i>  {vtranslate('LBL_DISPLAY_WIDETYPE', 'Vtiger')}
										</li>
										<li class="cursorPointer" data-class='mediumWidthType' title="{vtranslate('LBL_DISPLAY_MEDIUMTYPE', 'Vtiger')}">
											<i class='icon-list'></i>  {vtranslate('LBL_DISPLAY_MEDIUMTYPE', 'Vtiger')}
										</li>
										<li class="cursorPointer" data-class='narrowWidthType' title="{vtranslate('LBL_DISPLAY_NARROWTYPE', 'Vtiger')}">
											<i class='icon-list-alt'></i>  {vtranslate('LBL_DISPLAY_NARROWTYPE', 'Vtiger')}
										</li>
									</ul>
								</div>-->
							{assign var="a" value=$RECORD->getName()} 
                            
                            {foreach item=DETAIL_VIEW_BASIC_LINK from=$DETAILVIEW_LINKS['DETAILVIEWPREFERENCE']}
                           {if $DETAIL_VIEW_BASIC_LINK->getLabel() eq 'LBL_EDIT' && $a neq ' Administrator'}
                           {else}  
                            <div class="btn-group">
                                    <button class="btn"
                                            {if $DETAIL_VIEW_BASIC_LINK->isPageLoadLink()}
                                                onclick="window.location.href='{$DETAIL_VIEW_BASIC_LINK->getUrl()}'"
                                            {else}
                                                onclick={$DETAIL_VIEW_BASIC_LINK->getUrl()}
                                            {/if}>
                                        <strong>{vtranslate($DETAIL_VIEW_BASIC_LINK->getLabel(), $MODULE_NAME)}</strong>
                                    </button>
                                </div>
                             {/if}
                            {/foreach}
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="detailViewInfo row-fluid">
            <div class="details span12">
                <form id="detailView" data-name-fields='{ZEND_JSON::encode($MODULE_MODEL->getNameFields())}'>
                    <div class="contents">
                    {/strip}