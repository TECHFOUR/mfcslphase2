<?php
// Turn on debugging level
$Vtiger_Utils_Log = true;
include_once('vtlib/Vtiger/Menu.php');
include_once('vtlib/Vtiger/Module.php');
// Create module instance and save it first
$module = new Vtiger_Module();
$module->name = 'CallingMaster';
$module->save();
// Initialize all the tables required
$module->initTables();
/**
* Creates the following table:
* vtiger_payslip (payslipid INTEGER)
* vtiger_payslipcf(payslipid INTEGER PRIMARY KEY)
* vtiger_payslipgrouprel((payslipid INTEGER PRIMARY KEY, groupname VARCHAR(100))
*/
// Add the module to the Menu (entry point from UI)
$menu = Vtiger_Menu::getInstance('Sales');
$menu->addModule($module);
// Add the basic module block
$block1 = new Vtiger_Block();
$block1->label = 'LBL_CALLING_MASTER_INFORMATION';
$module->addBlock($block1);
// Add custom block (required to support Custom Fields)
$block2 = new Vtiger_Block();
$block2->label = 'LBL_CUSTOM_INFORMATION';
$module->addBlock($block2);
/** Create required fields and add to the block */
$field1 = new Vtiger_Field();
$field1->name = 'callingmaster';
$field1->table = $module->basetable;
$field1->column = 'callingmaster';
$field1->columntype = 'VARCHAR(255)';
$field1->uitype = 2;
$field1->typeofdata = 'V~M';
$block1->addField($field1); /** Creates the field and adds to block */
// Set at-least one field to identifier of module record
$module->setEntityIdentifier($field1);
$field2 = new Vtiger_Field();
$field2->name = 'assigned_user_id';
$field2->label = 'Assigned To';
$field2->table = 'vtiger_crmentity';
$field2->column = 'smownerid';
$field2->uitype = 53;
$field2->typeofdata = 'V~M';
$block1->addField($field2);
$field3 = new Vtiger_Field();
$field3->name = 'CreatedTime';
$field3->label= 'Created Time';
$field3->table = 'vtiger_crmentity';
$field3->column = 'createdtime';
$field3->uitype = 70;
$field3->typeofdata = 'T~O';
$field3->displaytype= 2;
$block1->addField($field3);
$field4 = new Vtiger_Field();
$field4->name = 'ModifiedTime';
$field4->label= 'Modified Time';
$field4->table = 'vtiger_crmentity';
$field4->column = 'modifiedtime';
$field4->uitype = 70;
$field4->typeofdata = 'T~O';
$field4->displaytype= 2;
$block1->addField($field4);
/** END */
// Create default custom filter (mandatory)
$filter1 = new Vtiger_Filter();
$filter1->name = 'All';
$filter1->isdefault = true;
$module->addFilter($filter1);
// Add fields to the filter created
$filter1->addField($field1)->addField($field2, 1)->addField($field3, 2);
// Create one more filter
$filter2 = new Vtiger_Filter();
$filter2->name = 'All2';
$module->addFilter($filter2);
// Add fields to the filter
$filter2->addField($field1);
$filter2->addField($field2, 1);
// Add rule to the filter field
$filter2->addRule($field1, 'CONTAINS', 'Test');
/** Associate other modules to this module */
$module->setRelatedList(Vtiger_Module::getInstance('Accounts'), 'Accounts',
Array('ADD','SELECT'));
/** Set sharing access of this module */
$module->setDefaultSharing('Private');
/** Enable and Disable available tools */
$module->enableTools(Array('Import', 'Export'));
$module->disableTools('Merge');
?>
