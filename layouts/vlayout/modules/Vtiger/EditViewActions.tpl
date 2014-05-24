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
        <div>
            <div class="pull-right">
            	{if $MODULE eq 'Events'}
                <button class="btn btn-success"  id="process_save_action_bottom" style="visibility:hidden;" ><strong>Processing...</strong></button>
                {/if}
				<button class="btn btn-success" type="submit" id="save_action_bottom" style="display:block;" {if $MODULE eq 'Events'}onclick="buttonShowHide(this.id);"{/if}><strong>{vtranslate('LBL_SAVE', $MODULE)}</strong></button>                
				<a class="cancelLink" type="reset" onclick="javascript:window.history.back();">{vtranslate('LBL_CANCEL', $MODULE)}</a>
			</div>
			<div class="clearfix"></div>
        </div>
    </form>
</div>
{/strip}