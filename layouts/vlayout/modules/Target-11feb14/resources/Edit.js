/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var target_msg = "";
Vtiger_Edit_Js("Targets_Edit_Js",{

},{
	
	//Container which stores the line item elements
	lineItemContentsContainer : false,
	//Container which stores line item result details
	lineItemResultContainer : false,
	//contains edit view form element
	editViewForm : false,

	//a variable which will be used to hold the sequence of the row
	rowSequenceHolder : false,

	//holds the element which has basic hidden row which we can clone to add rows
	basicRow : false,

	//will be having class which is used to identify the rows
	rowClass : 'lineItemRow',

// Start code for duplicate check **********	
	 //Stored history of account name and duplicate check result
	duplicateCheckCache : {},
		
	/**
	 * This function will return the current form
	 */
	getForm : function(){
		if(this.editViewForm == false) {
			this.editViewForm = jQuery('#EditView');
		}
		return this.editViewForm;
	},
	
	/**
	 * This function will return the account name
	 */
	getStartDate : function(container){
		return jQuery('input[name="start_date"]',container).val();
	},
      
	 getEndDate : function(container){
		return jQuery('input[name="end_date"]',container).val();
	},  
	/**
	 * This function will return the current RecordId
	 */
	getRecordId : function(container){
		return jQuery('input[name="record"]',container).val();
	},
	
	getAssigned_To : function(container){
		return jQuery('input[name="salesperson"]',container).val();
	},  					
// End code for duplicate check **********	
	/**
	 * Function that is used to get the line item container
	 * @return : jQuery object
	 */
	getLineItemContentsContainer : function() {
		if(this.lineItemContentsContainer == false) {
			this.setLineItemContainer(jQuery('#lineItemTab'));
		}
		return this.lineItemContentsContainer;
	},

	/**
	 * Function to set line item container
	 * @params : element - jQuery object which represents line item container
	 * @return : current instance ;
	 */
	setLineItemContainer : function(element) {
		this.lineItemContentsContainer = element;
		return this;
	},
	
	/**
	 * Function to set line item result container
	 * @param : element - jQuery object which represents line item result container
	 * @result : current instance
	 */
	setLinteItemResultContainer : function(element) {
		this.lineItemResultContainer = element;
		return this;
	},

	/**
	 * Function which will give the closest line item row element
	 * @return : jQuery object
	 */
	getClosestLineItemRow : function(element){
		return element.closest('tr.'+this.rowClass);
	},

    loadRowSequenceNumber: function() {
		if(this.rowSequenceHolder == false) {
			this.rowSequenceHolder = jQuery('.' + this.rowClass, this.getLineItemContentsContainer()).length;
		}
		return this;
    },

	getNextLineItemRowNumber : function() {
		if(this.rowSequenceHolder == false){
			this.loadRowSequenceNumber();
		}
		return ++this.rowSequenceHolder;
	},

	/**
	 * Function which will return the basic row which can be used to add new rows
	 * @return jQuery object which you can use to
	 */
	getBasicRow : function() {
		if(this.basicRow == false){
			var lineItemTable = this.getLineItemContentsContainer();
			this.basicRow = jQuery('.lineItemCloneCopy',lineItemTable)
		}
		var newRow = this.basicRow.clone(true,true);		
		return newRow.removeClass('hide lineItemCloneCopy');
	},

    registerAddingNewProductsAndServices: function(){
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		jQuery('#addProduct').on('click',function(){									
			var newRow = thisInstance.getBasicRow().addClass(thisInstance.rowClass)			
			jQuery('.lineItemPopup[data-module-name="Services"]',newRow).remove();			
			var sequenceNumber = thisInstance.getNextLineItemRowNumber();					
			newRow = newRow.appendTo(lineItemTable);				
			thisInstance.checkLineItemRow();			
			newRow.find('input.rowNumber').val(sequenceNumber);			
			thisInstance.updateLineItemsElementWithSequenceNumber(newRow,sequenceNumber);
			newRow.find('input.productName').addClass('autoComplete');
			thisInstance.registerLineItemAutoComplete(newRow);
			//alert(sequenceNumber);
		});		
    },
	
	 registerDeleteLineItemEvent : function(){
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();

		lineItemTable.on('click','.deleteRow',function(e){					
			var element = jQuery(e.currentTarget);			
			//removing the row
			element.closest('tr.'+ thisInstance.rowClass).remove();			
			thisInstance.checkLineItemRow();
			thisInstance.lineItemDeleteActions();
			
			
			
		});
	 },

	 checkLineItemRow : function(){
				
		var lineItemTable = this.getLineItemContentsContainer();
		var noRow = lineItemTable.find('.lineItemRow').length;				
		if(noRow >0){
			this.showLineItemsDeleteIcon();
		}else{
			this.hideLineItemsDeleteIcon();
		}					
	},

	showLineItemsDeleteIcon : function(){
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.find('.deleteRow').show();
	},

	hideLineItemsDeleteIcon : function(){
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.find('.deleteRow').hide();
	},

    lineItemActions: function() {
		var lineItemTable = this.getLineItemContentsContainer();				
		this.registerDeleteLineItemEvent();			
    },

	/***
	 * Function which will update the line item row elements with the sequence number
	 * @params : lineItemRow - tr line item row for which the sequence need to be updated
	 *			 currentSequenceNUmber - existing sequence number that the elments is having
	 *			 expectedSequenceNumber - sequence number to which it has to update
	 *
	 * @return : row element after changes
	 */
	 
	
	 
	updateLineItemsElementWithSequenceNumber : function(lineItemRow,expectedSequenceNumber , currentSequenceNumber){		
		if(typeof currentSequenceNumber == 'undefined') {
			//by default there will zero current sequence number
			currentSequenceNumber = 0;
		}
		/* Code modified by jitendra singh[TECHFOUR] */
		var idFields = new Array('target','salesperson','start_date','end_date','salesperson_display','Targets_editView_fieldName_salesperson_select','revenue');
						
		var nameFields = new Array('discount');
		var classFields = new Array('taxPercentage');
		//To handle variable tax ids
		for(var classIndex in classFields) {
			var className = classFields[classIndex];
			jQuery('.'+className,lineItemRow).each(function(index, domElement){
				var idString = domElement.id
				//remove last character which will be the row number
				idFields.push(idString.slice(0,(idString.length-1)));
			});
		}

		var expectedRowId = 'row'+expectedSequenceNumber;
		for(var idIndex in idFields ) {			
			var elementId = idFields[idIndex];
			var actualElementId = elementId + currentSequenceNumber;
			var expectedElementId = elementId + expectedSequenceNumber;
			
/* Start added by ajay [TECHFOUR]*/			
			if(elementId == "salesperson_display") {											
				expectedElementId = "salesperson"+expectedSequenceNumber+"_display";
				actualElementId = "salesperson0_display";				
			}
			
			if(elementId == "Targets_editView_fieldName_salesperson_select") {											
				expectedElementId = "Targets_editView_fieldName_salesperson"+expectedSequenceNumber+"_select";
				actualElementId = "Targets_editView_fieldName_salesperson0_select";				
			}
			
/* Start added by ajay [TECHFOUR]*/			
				
			lineItemRow.find('#'+actualElementId).attr('id',expectedElementId)
					   .filter('[name="'+actualElementId+'"]').attr('name',expectedElementId);
		}

		for(var nameIndex in nameFields) {
			var elementName = nameFields[nameIndex];
			var actualElementName = elementName + currentSequenceNumber;
			var expectedElementName = elementName + expectedSequenceNumber;
			lineItemRow.find('[name="'+actualElementName+'"]').attr('name',expectedElementName);
		}


		return lineItemRow.attr('id',expectedRowId);
	},


	registerLineItemAutoComplete : function(container) {
		var thisInstance = this;
		if(typeof container == 'undefined') {
			container = thisInstance.getLineItemContentsContainer();
		}
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = tdElement.find('.lineItemPopup').data('moduleName');
				params.search_module = searchModule
				params.search_value = searchValue;
				thisInstance.searchModuleNames(params).then(function(data){
					var reponseDataList = new Array();
					var serverDataFormat = data.result
					if(serverDataFormat.length <= 0) {
						serverDataFormat = new Array({
							//TODO : client translation
							'label' : 'No Results Found',
							'type'  : 'no results'
						});
					}
					for(var id in serverDataFormat){
						var responseData = serverDataFormat[id];
						reponseDataList.push(responseData);
					}
					response(reponseDataList);
				});
			},
			'select' : function(event, ui ){
				var selectedItemData = ui.item;
				//To stop selection if no results is selected
				if(typeof selectedItemData.type != 'undefined' && selectedItemData.type=="no results"){
					return false;
				}
				var element = jQuery(this);
				element.attr('disabled','disabled');
				var tdElement = element.closest('td');
				var selectedModule = tdElement.find('.lineItemPopup').data('moduleName');
				var popupElement = tdElement.find('.lineItemPopup');
				var dataUrl = "index.php?module=Targets&action=GetTaxes&record="+selectedItemData.id+"&currency_id="+jQuery('#currency_id option:selected').val();
				AppConnector.request(dataUrl).then(
					function(data){
						for(var id in data){
							if(typeof data[id] == "object"){
							var recordData = data[id];
							thisInstance.mapResultsToFields(selectedModule, popupElement, recordData);
							}
						}
					},
					function(error,err){

					}
				);
			},
			'change' : function(event, ui) {
				var element = jQuery(this);


				//if you dont have disabled attribute means the user didnt select the item
				if(element.attr('disabled')== undefined) {
					element.closest('td').find('.clearLineItem').trigger('click');
				}
			}
		});
	},

	registerClearLineItemSelection : function() {
		var thisInstance = this;
		var lineItemTable = this.getLineItemContentsContainer();
		lineItemTable.on('click','.clearLineItem',function(e){
			var elem = jQuery(e.currentTarget);
			var parentElem = elem.closest('td');
			thisInstance.clearLineItemDetails(parentElem);
			parentElem.find('input.productName').removeAttr('disabled').val('');
			e.preventDefault();
		});
	},

	registerRecordPreSaveEvent : function(form) {		
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}	
	
		
		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {															
			var accountName = thisInstance.getStartDate(form);
			var recordId = thisInstance.getRecordId(form);
			var end_date = thisInstance.getEndDate(form);			
			var assigned_to = thisInstance.getAssigned_To(form);						
							
			var params = {};						
            if(!(accountName in thisInstance.duplicateCheckCache)) {
                Vtiger_Helper_Js.checkDuplicateName({
                    'accountName' : accountName,
					'assigned_to' : assigned_to, 
					'end_date' : end_date,
                    'recordId' : recordId					
                }).then(
                    function(data){										
                        thisInstance.duplicateCheckCache[accountName] = data['success'];
                        form.submit();
                    },
                    function(data, err){						
                        thisInstance.duplicateCheckCache[accountName] = data['success'];
                        thisInstance.duplicateCheckCache['message'] = data['message'];						
						target_msg = data['alert'];																	
						var message = app.vtranslate('JS_DUPLICTAE_CREATION_CONFIRMATION_EXIT');												
						delete thisInstance.duplicateCheckCache[accountName];
						Vtiger_Helper_Js.showConfirmationBoxTargets({'message' : message}).then(
							function(e) {								
								thisInstance.duplicateCheckCache[accountName] = false;
								form.submit();
							},
							function(error, err) {
								
							}
						);
                    }
				);
            }
           
			else {				
				if(thisInstance.duplicateCheckCache[accountName] == true){
					var message = app.vtranslate('JS_DUPLICTAE_CREATION_CONFIRMATION');					
					delete thisInstance.duplicateCheckCache[accountName];
					Vtiger_Helper_Js.showConfirmationBoxTargets({'message' : message}).then(
						function(e) {							
							thisInstance.duplicateCheckCache[accountName] = false;
							form.submit();
						},
						function(error, err) {
							
						}
					);
				} else {
					delete thisInstance.duplicateCheckCache[accountName];
					return true;
				}
			}
            e.preventDefault();
		})
	
	},

    registerEvents: function(){
		this._super();		
		this.registerAddingNewProductsAndServices();
		this.lineItemActions();				
    },
	
	registerBasicEvents : function(container) {
		this._super(container);
		this.registerRecordPreSaveEvent(container);		
		
			//container.trigger(Vtiger_Edit_Js.recordPreSave, {'value': 'edit'});
	}
});
