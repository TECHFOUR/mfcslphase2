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
{assign var="announcement" value=$ANNOUNCEMENT->get('announcement')}
{assign var='count' value=0}
{assign var="dateFormat" value=$USER_MODEL->get('date_format')}
<div class="navbar commonActionsContainer noprint">
    <div class="actionsContainer row-fluid">
		<div class="span2">
			<!--<span class="companyLogo"><img src="{$COMPANY_LOGO->get('imagepath')}" title="{$COMPANY_LOGO->get('title')}" alt="{$COMPANY_LOGO->get('alt')}"/>&nbsp;</span>-->
		</div>
		<div class="span10 marginLeftZero">
			<div class="row-fluid">
				<div class="searchElement span6 row-fluid">
					{*<div class="span4">
						<select class="chzn-select row-fluid" id="basicSearchModulesList">
							<option value="" class="globalSearch_module_All">{vtranslate('LBL_ALL_RECORDS', $MODULE_NAME)}</option>
							{foreach key=MODULE_NAME item=fieldObject from=$SEARCHABLE_MODULES}
								<option value="{$MODULE_NAME}" class="globalSearch_module_{$MODULE_NAME}">{vtranslate($MODULE_NAME,$MODULE_NAME)}</option>
							{/foreach}
						</select>
					</div>*}
					{*<div class="input-append row-fluid span8">
						<input type="text" class="span5" id="globalSearchValue" placeholder="{vtranslate('LBL_GLOBAL_SEARCH')}" results="10" />
						<span class="add-on" id="globalSearch" data-toggle="dropdown">
							<img class="alignMiddle" src="{vimage_path('downArrow.png')}" alt="{vtranslate('LBL_SEARCH_BUTTON',$MODULE)}" title="{vtranslate('LBL_SEARCH_BUTTON',$MODULE)}" id="globalSearch" />
						</span>
					</div>*}
				</div>
				<div class="notificationMessageHolder span3">

				</div>
				<div class="nav quickActions btn-toolbar span3 pull-right">
					<div class="pull-right commonActionsButtonContainer">
						{if !empty($announcement)}
							<div class="btn-group cursorPointer">
								<img class='alignMiddle' src="{vimage_path('btnAnnounceOff.png')}" alt="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" title="{vtranslate('LBL_ANNOUNCEMENT',$MODULE)}" id="announcementBtn" />
							</div>&nbsp;
						{/if}

						<div class="btn-group cursorPointer" id="guiderHandler">
							{*<!--<img src="{vimage_path('circle_question_mark.png')}" class="alignMiddle" alt="?" title="{vtranslate('LBL_GUIDER',$MODULE)}"/>-->*}
						</div>&nbsp;

						<div class="btn-group cursorPointer">
							<img id="menubar_quickCreate" src="{vimage_path('btnAdd.png')}" class="alignMiddle" alt="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" title="{vtranslate('LBL_QUICK_CREATE',$MODULE)}" data-toggle="dropdown" />
							<ul class="dropdown-menu dropdownStyles commonActionsButtonDropDown">
								<li class="title"><strong>{vtranslate('Quick Create',$MODULE)}</strong></li><hr/>
								<li id="quickCreateModules">
									<div class="row-fluid">
										<div class="span12">
											{foreach key=moduleName item=moduleModel from=$MENUS}
												{if $moduleModel->isPermitted('EditView')}
													{assign var='quickCreateModule' value=$moduleModel->isQuickCreateSupported()}
													{assign var='singularLabel' value=$moduleModel->getSingularLabelKey()}
													{if $quickCreateModule == '1'}
														{if $count % 3 == 0}
															<div class="row-fluid">
														{/if}
														<div class="span4">
                                                        {if $moduleName neq 'Documents'}
															<a id="menubar_quickCreate_{$moduleModel->getName()}" class="quickCreateModule" data-name="{$moduleModel->getName()}"
															   data-url="{$moduleModel->getQuickCreateUrl()}" href="javascript:void(0)">{vtranslate($singularLabel,$moduleName)}</a>
                                                               {/if}
														</div>
														{if $count % 3 == 2}
															</div>
														{/if}
														{assign var='count' value=$count+1}
													{/if}
												{/if}
											{/foreach}
										</div>
									</div>
								</li>
							</ul>
						</div>&nbsp;
					</div>
				</div>
			</div>
		</div>
    </div>
</div>
{/strip}