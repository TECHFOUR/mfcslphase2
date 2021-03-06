/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_Edit_Js",{

	//Event that will triggered when reference field is selected
	referenceSelectionEvent : 'Vtiger.Reference.Selection',

	//Event that will triggered when reference field is selected
	referenceDeSelectionEvent : 'Vtiger.Reference.DeSelection',

	//Event that will triggered before saving the record
	recordPreSave : 'Vtiger.Record.PreSave',

    refrenceMultiSelectionEvent : 'Vtiger.MultiReference.Selection',

    preReferencePopUpOpenEvent : 'Vtiger.Referece.Popup.Pre',

	editInstance : false,

	/**
	 * Function to get Instance by name
	 * @params moduleName:-- Name of the module to create instance
	 */
	getInstanceByModuleName : function(moduleName){
		if(typeof moduleName == "undefined"){
			moduleName = app.getModuleName();
		}
		var parentModule = app.getParentModuleName();
		if(parentModule == 'Settings'){
			var moduleClassName = parentModule+"_"+moduleName+"_Edit_Js";
			if(typeof window[moduleClassName] == 'undefined'){
				moduleClassName = moduleName+"_Edit_Js";
			}
			var fallbackClassName = parentModule+"_Vtiger_Edit_Js";
			if(typeof window[fallbackClassName] == 'undefined') {
				fallbackClassName = "Vtiger_Edit_Js";
			}
		} else {
			moduleClassName = moduleName+"_Edit_Js";
			fallbackClassName = "Vtiger_Edit_Js";
		}
		if(typeof window[moduleClassName] != 'undefined'){
			var instance = new window[moduleClassName]();
		}else{
			var instance = new window[fallbackClassName]();
		}
		return instance;
	},


	getInstance: function(){
		if(Vtiger_Edit_Js.editInstance == false){
			var instance = Vtiger_Edit_Js.getInstanceByModuleName();
			Vtiger_Edit_Js.editInstance = instance;
			return instance;
		}
		return Vtiger_Edit_Js.editInstance;
	}

},{

	formElement : false,

	getForm : function() {
		if(this.formElement == false){
			this.setForm(jQuery('#EditView'));
		}
		return this.formElement;
	},

	setForm : function(element){
		this.formElement = element;
		return this;
	},

    getPopUpParams : function(container) {
        var params = {};
        var sourceModule = app.getModuleName();
		var popupReferenceModule = jQuery('input[name="popupReferenceModule"]',container).val();
        var sourceFieldElement = jQuery('input[class="sourceField"]',container);
		var sourceField = sourceFieldElement.attr('name');
		var sourceRecordElement = jQuery('input[name="record"]');
		var sourceRecordId = '';
		if(sourceRecordElement.length > 0) {
            sourceRecordId = sourceRecordElement.val();
        }

        var isMultiple = false;
        if(sourceFieldElement.data('multiple') == true){
            isMultiple = true;
        }

		var params = {
			'module' : popupReferenceModule,
			'src_module' : sourceModule,
			'src_field' : sourceField,
			'src_record' : sourceRecordId
		}

        if(isMultiple) {
            params.multi_select = true ;
        }
        return params;
    },


	openPopUp : function(e){
		var thisInstance = this;
		var parentElem = jQuery(e.target).closest('td');

        var params = this.getPopUpParams(parentElem);

        var isMultiple = false;
        if(params.multi_select) {
            isMultiple = true;
        }

        var sourceFieldElement = jQuery('input[class="sourceField"]',parentElem);

        var prePopupOpenEvent = jQuery.Event(Vtiger_Edit_Js.preReferencePopUpOpenEvent);
        sourceFieldElement.trigger(prePopupOpenEvent);

        if(prePopupOpenEvent.isDefaultPrevented()) {
            return ;
        }

		var popupInstance =Vtiger_Popup_Js.getInstance();
		popupInstance.show(params,function(data){
				var responseData = JSON.parse(data);
                var dataList = new Array();
				for(var id in responseData){
					var data = {
						'name' : responseData[id].name,
						'id' : id
					}
                    dataList.push(data);
                    if(!isMultiple) {
                        thisInstance.setReferenceFieldValue(parentElem, data);
                    }
				}

                if(isMultiple) {
                    sourceFieldElement.trigger(Vtiger_Edit_Js.refrenceMultiSelectionEvent,{'data':dataList});
                }
			});
	},

	setReferenceFieldValue : function(container, params) {
		var sourceField = container.find('input[class="sourceField"]').attr('name');
		var fieldElement = container.find('input[name="'+sourceField+'"]');
		var sourceFieldDisplay = sourceField+"_display";
		var fieldDisplayElement = container.find('input[name="'+sourceFieldDisplay+'"]');
		var popupReferenceModule = container.find('input[name="popupReferenceModule"]').val();

		var selectedName = params.name;
		var id = params.id;

		fieldElement.val(id)
		fieldDisplayElement.val(selectedName).attr('readonly',true);
		fieldElement.trigger(Vtiger_Edit_Js.referenceSelectionEvent, {'source_module' : popupReferenceModule, 'record' : id, 'selectedName' : selectedName});

		fieldDisplayElement.validationEngine('closePrompt',fieldDisplayElement);
	},

	proceedRegisterEvents : function(){
		if(jQuery('.recordEditView').length > 0){
			return true;
		}else{
			return false;
		}
	},

	referenceModulePopupRegisterEvent : function(container){
		var thisInstance = this;
		container.find('.relatedPopup').on("click",function(e){
			thisInstance.openPopUp(e);
		});
		container.find('.referenceModulesList').chosen().change(function(e){
			var element = jQuery(e.currentTarget);
			var closestTD = element.closest('td').next();
			var popupReferenceModule = element.val();
			var referenceModuleElement = jQuery('input[name="popupReferenceModule"]', closestTD);
			var prevSelectedReferenceModule = referenceModuleElement.val();
			referenceModuleElement.val(popupReferenceModule);

			//If Reference module is changed then we should clear the previous value
			if(prevSelectedReferenceModule != popupReferenceModule) {
				closestTD.find('.clearReferenceSelection').trigger('click');
			}
		});
	},

	getReferencedModuleName : function(parenElement){
		return jQuery('input[name="popupReferenceModule"]',parenElement).val();
	},

	searchModuleNames : function(params) {
		var aDeferred = jQuery.Deferred();

		if(typeof params.module == 'undefined') {
			params.module = app.getModuleName();
		}

		if(typeof params.action == 'undefined') {
			params.action = 'BasicAjax';
		}
		AppConnector.request(params).then(
			function(data){
				aDeferred.resolve(data);
			},
			function(error){
				//TODO : Handle error
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},

	/**
	 * Function which will handle the reference auto complete event registrations
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerAutoCompleteFields : function(container) {
		var thisInstance = this;
		container.find('input.autoComplete').autocomplete({
			'minLength' : '3',
			'source' : function(request, response){
				//element will be array of dom elements
				//here this refers to auto complete instance
				var inputElement = jQuery(this.element[0]);
				var tdElement = inputElement.closest('td');
				var searchValue = request.term;
				var params = {};
				var searchModule = thisInstance.getReferencedModuleName(tdElement);
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
				selectedItemData.name = selectedItemData.value;
				var element = jQuery(this);
				var tdElement = element.closest('td');
				thisInstance.setReferenceFieldValue(tdElement, selectedItemData)
			},
			'change' : function(event, ui) {
				var element = jQuery(this);
				//if you dont have readonly attribute means the user didnt select the item
				if(element.attr('readonly')== undefined) {
					element.closest('td').find('.clearReferenceSelection').trigger('click');
				}
			},
			'open' : function(event,ui) {
				//To Make the menu come up in the case of quick create
				jQuery(this).data('autocomplete').menu.element.css('z-index','100001');

			}
		});
	},


	/**
	 * Function which will register reference field clear event
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerClearReferenceSelectionEvent : function(container) {
		container.find('.clearReferenceSelection').on('click', function(e){
			var element = jQuery(e.currentTarget);
			var parentTdElement = element.closest('td');
			var fieldNameElement = parentTdElement.find('.sourceField');
			var fieldName = fieldNameElement.attr('name');
			fieldNameElement.val('');
			parentTdElement.find('#'+fieldName+'_display').removeAttr('readonly').val('');
			element.trigger(Vtiger_Edit_Js.referenceDeSelectionEvent);
			e.preventDefault();
		})
	},

	/**
	 * Function which will register event to prevent form submission on pressing on enter
	 * @params - container <jQuery> - element in which auto complete fields needs to be searched
	 */
	registerPreventingEnterSubmitEvent : function(container) {
		container.on('keypress', function(e){
            //Stop the submit when enter is pressed in the form
            var currentElement = jQuery(e.target);
            if(e.which == 13 && (!currentElement.is('textarea'))) {
                e. preventDefault();
            }
		})
	},

	/**
	 * Function which will give you all details of the selected record
	 * @params - an Array of values like {'record' : recordId, 'source_module' : searchModule, 'selectedName' : selectedRecordName}
	 */
	getRecordDetails : function(params) {
		var aDeferred = jQuery.Deferred();
		var url = "index.php?module="+app.getModuleName()+"&action=GetData&record="+params['record']+"&source_module="+params['source_module'];
		AppConnector.request(url).then(
			function(data){
				if(data['success']) {
					aDeferred.resolve(data);
				} else {
					aDeferred.reject(data['message']);
				}
			},
			function(error){
				aDeferred.reject();
			}
		)
		return aDeferred.promise();
	},


	registerTimeFields : function(container) {
		app.registerEventForTimeFields(container);
	},

    referenceCreateHandler : function(container) {
        var thisInstance = this;
        var postQuickCreateSave  = function(data) {			
            var params = {};
            params.name = data.result._recordLabel;
            params.id = data.result._recordId;
            thisInstance.setReferenceFieldValue(container, params);
        }

        var referenceModuleName = this.getReferencedModuleName(container);
        var quickCreateNode = jQuery('#quickCreateModules').find('[data-name="'+ referenceModuleName +'"]');
        if(quickCreateNode.length <= 0) {
            Vtiger_Helper_Js.showPnotify(app.vtranslate('JS_NO_CREATE_OR_NOT_QUICK_CREATE_ENABLED'))
        }
        quickCreateNode.trigger('click',{'callbackFunction':postQuickCreateSave});
    },

	/**
	 * Function which will register event for create of reference record
	 * This will allow users to create reference record from edit view of other record
	 */
	registerReferenceCreate : function(container) {
		var thisInstance = this;
		container.find('.createReferenceRecord').on('click', function(e){
			var element = jQuery(e.currentTarget);
			var controlElementTd = element.closest('td');

			thisInstance.referenceCreateHandler(controlElementTd);
		})
	},

	/**
	 * Function to register the event status change event
	 */
	registerEventStatusChangeEvent : function(container){
		
		var followupContainer = container.find('.followUpContainer');
		container.find('select[name="eventstatus"]').on('change',function(e){
			var selectedOption = jQuery(e.currentTarget).val();
			if(selectedOption == 'Held'){
				document.getElementById('followup').checked='true'
				followupContainer.show();
			} else{
				followupContainer.hide();
			}
		});
		
		
		
		/*var ActivityTypeEvent = container.find('.ActivityTypeEvent');  // For activity type done by ishwar 20dec13
		container.find('select[name="eventstatus"]').on('change',function(e){
			var selectedOptionActivityTypeEvent = jQuery(e.currentTarget).val();
			if(selectedOptionActivityTypeEvent == 'Held'){
				ActivityTypeEvent.show();
			} else{
				ActivityTypeEvent.hide();
			}
		});*/
		},
		
		
		////////////////////////Start Code For validation done by ISHWAR  Date - 20-12-2013 ////////
		
		
		
			
		registerEventActivityTypeChangeEvent : function(container){
			var ActivityStatusEventdiv = container.find('.ActivityStatusEventdiv');
			container.find('select[name="cf_895"]').on('change',function(e){
			var selectedOptiont = jQuery(e.currentTarget).val();// ishwar
			if(selectedOptiont == 'Appointment Booked'){
			   ActivityStatusEventdiv.show();
			  	}else{
			    ActivityStatusEventdiv.hide();
			     }
			});
			
			
			
			
			
			
			var driverPickupevent = container.find('.driverPickupevent');
			container.find('select[name="cf_895"]').on('change',function(e){
			var selectedOptiont = jQuery(e.currentTarget).val();// ishwar
		    if(selectedOptiont == 'Appointment Booked'){
			   driverPickupevent.show();
			}else{
			    driverPickupevent.hide();
			     }
			});
			
			var AppbookingDate = container.find('.AppbookingDate');
			container.find('select[name="cf_895"]').on('change',function(e){
			var selectedOptiontAppbookingDate = jQuery(e.currentTarget).val();// ishwar
		    if(selectedOptiontAppbookingDate == 'Appointment Booked'){
			   AppbookingDate.show();
			}else{
			    AppbookingDate.hide();
			     }
			});
			
		   // For Sub Dispositions
		   
		    var SubDispositions = container.find('.SubDispositions');
			var followupContainer_custom = container.find('.followUpContainer');			
			container.find('select[name="cf_895"]').on('change',function(e){
			var selectedOptiontSubDispositions = jQuery(e.currentTarget).val();// ishwar
			
		    if(selectedOptiontSubDispositions == 'Appointment Booked'){
			   SubDispositions.show();
			   followupContainer_custom.hide();
			}
			else if(selectedOptiontSubDispositions == 'Call Back'){
				//var selZF0_chzn= container.find('.selZF0_chzn');
			   //$("#selZF0_chzn").value = "Held";
			followupContainer_custom.show();
          // $("#selIM2").value = "Held";
		     SubDispositions.hide();
			}
			
			
			else{
				followupContainer_custom.hide();
			    SubDispositions.hide();
			     }
			});
		   
		    // Whenever Activity status equalls Callback status automatically selected as Held
		   
		    
			var followupContainerOther = container.find('.followUpContainer');
		    container.find('select[name="cf_903"]').on('change',function(e){
			var selectedOptionDisposition = jQuery(e.currentTarget).val();
			if(selectedOptionDisposition == 'Rescheduled'){
				followupContainerOther.show();
			} else{
				followupContainerOther.hide();
			}
		});
		
		
		
	
	
	var ActivityStatusR = container.find('.ActivityStatusR');
		container.find('select[id="Events_editView_fieldName_parent_id_dropDown"]').on('change',function(e){
     		
			var CurrentModule = $('input[name=sourceModule]').val();
			var Currentmodul = $('input[name=module]').val();
			var CurrentView = $('input[id=view]').val();
			var recordIDNEW =  $('input[name=record]').val();
			
			/*if(CurrentModule == 'undefined' || CurrentModule == null || CurrentModule == '') {
				CurrentModule = Currentmodul;
			}*/
			
			var selectedDateFromDate = jQuery(e.currentTarget).val();
		    if(selectedDateFromDate == CurrentModule){
				
				ActivityStatusEventdiv.show();
				ActivityStatusR.show();
				SubDispositions.show();
				AppbookingDate.show();
				driverPickupevent.show();
    		}
			

    else   {	
	          //   alert('else');
				ActivityStatusEventdiv.hide();
				ActivityStatusR.hide();
				SubDispositions.hide();
				AppbookingDate.hide();
				driverPickupevent.hide();
          }
		  
	});
		
		// Code Start for date validation start date	
			
		},	
		
		
		
////////////////////////End  Code For validation done by ISHWAR  Date - 20-12-2013 ////////

		
		
		
		

	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents : function(container) {
		this.referenceModulePopupRegisterEvent(container);
		this.registerAutoCompleteFields(container);
		this.registerClearReferenceSelectionEvent(container);
		this.registerPreventingEnterSubmitEvent(container);
		this.registerTimeFields(container);
		//Added here instead of register basic event of calendar. because this should be registered all over the places like quick create, edit, list..
		this.registerEventStatusChangeEvent(container);
		this.registerRecordAccessCheckEvent(container);
		this.registerEventForPicklistDependencySetup(container);
		
		// Start Added by Ishwar 20-12-2013
		this.registerEventActivityTypeChangeEvent(container);
		// End Added by ishwar 
	},

	/**
	 * Function to register event for image delete
	 */
	registerEventForImageDelete : function(){
		var formElement = this.getForm();
		var recordId = formElement.find('input[name="record"]').val();
		formElement.find('.imageDelete').on('click',function(e){
			var element = jQuery(e.currentTarget);
			var imageData = element.closest('div').find('img').data();
			var params = {
				'module' : app.getModuleName(),
				'action' : 'DeleteImage',
				'imageid' : imageData.imageId,
				'record' : recordId

			}
			AppConnector.request(params).then(
				function(data){
					if(data.success ==  true){
						element.closest('div').remove();
					}
				},
				function(error){
					//TODO : Handle error
				}
			)
		});
	},

	triggerDisplayTypeEvent : function() {
		var widthType = app.cacheGet('widthType', 'narrowWidthType');
		if(widthType) {
			var elements = jQuery('#EditView').find('td');
			elements.addClass(widthType);
		}
	},

	registerSubmitEvent: function() {
		var editViewForm = this.getForm();

		editViewForm.submit(function(e){

			//Form should submit only once for multiple clicks also
			if(typeof editViewForm.data('submit') != "undefined") {
				return false;
			} else {
				var module = jQuery(e.currentTarget).find('[name="module"]').val();
				if(editViewForm.validationEngine('validate')) {
					//Once the form is submiting add data attribute to that form element					
					editViewForm.data('submit', 'true');
						//on submit form trigger the recordPreSave event
						var recordPreSaveEvent = jQuery.Event(Vtiger_Edit_Js.recordPreSave);
						editViewForm.trigger(recordPreSaveEvent, {'value' : 'edit'});
						if(recordPreSaveEvent.isDefaultPrevented()) {
							//If duplicate record validation fails, form should submit again
							editViewForm.removeData('submit');
							e.preventDefault();
						}
				} else {
					//If validation fails, form should submit again
					editViewForm.removeData('submit');
					// to avoid hiding of error message under the fixed nav bar
					app.formAlignmentAfterValidation(editViewForm);
				}
			}
		});
	},

	/*
	 * Function to check the view permission of a record after save
	 */

	registerRecordAccessCheckEvent : function(form) {

		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {
			var module = app.getModuleName();							
			var assignedToSelectElement = jQuery('[name="assigned_user_id"]',form);
			if(assignedToSelectElement.data('recordaccessconfirmation') == true) {
				return;
			}else{
				if(assignedToSelectElement.data('recordaccessconfirmationprogress') != true) {
					var recordAccess = assignedToSelectElement.find('option:selected').data('recordaccess');
					if(recordAccess == false) {
						var message = app.vtranslate('JS_NO_VIEW_PERMISSION_AFTER_SAVE');
						Vtiger_Helper_Js.showConfirmationBox({'message' : message}).then(
						function(e) {
							assignedToSelectElement.data('recordaccessconfirmation',true);
							assignedToSelectElement.removeData('recordaccessconfirmationprogress');
							form.append('<input type="hidden" name="returnToList" value="true" />');
							form.submit();							
						},
						function(error, err){
							assignedToSelectElement.removeData('recordaccessconfirmationprogress');
							e.preventDefault();
						});
						assignedToSelectElement.data('recordaccessconfirmationprogress',true);
					} else {
						//if(module == 'Leads')
							//location.reload();
						return true;
					}
				}
			}
			e.preventDefault();
		});
	},

	/**
	 * Function to register event for setting up picklistdependency
	 * for a module if exist on change of picklist value
	 */
	registerEventForPicklistDependencySetup : function(container){
        var picklistDependcyElemnt = jQuery('[name="picklistDependency"]',container);
        if(picklistDependcyElemnt.length <= 0) {
            return;
        }
		var picklistDependencyMapping = JSON.parse(picklistDependcyElemnt.val());

		var sourcePicklists = Object.keys(picklistDependencyMapping);
		if(sourcePicklists.length <= 0){
			return;
		}

		var sourcePickListNames = "";
		for(var i=0;i<sourcePicklists.length;i++){
			sourcePickListNames += '[name="'+sourcePicklists[i]+'"],';
		}
		var sourcePickListElements = container.find(sourcePickListNames);

		sourcePickListElements.on('change',function(e){
			var currentElement = jQuery(e.currentTarget);
			var sourcePicklistname = currentElement.attr('name');

			var configuredDependencyObject = picklistDependencyMapping[sourcePicklistname];
			var selectedValue = currentElement.val();
			var targetObjectForSelectedSourceValue = configuredDependencyObject[selectedValue];
			var picklistmap = configuredDependencyObject["__DEFAULT__"];

			if(typeof targetObjectForSelectedSourceValue == 'undefined'){
				targetObjectForSelectedSourceValue = picklistmap;
			}
			jQuery.each(picklistmap,function(targetPickListName,targetPickListValues){
				var targetPickListMap = targetObjectForSelectedSourceValue[targetPickListName];
				if(typeof targetPickListMap == "undefined"){
					targetPickListMap = targetPickListValues;
				}
				var targetPickList = jQuery('[name="'+targetPickListName+'"]',container);
				if(targetPickList.length <= 0){
					return;
				}

				var listOfAvailableOptions = targetPickList.data('availableOptions');
				if(typeof listOfAvailableOptions == "undefined"){
					listOfAvailableOptions = jQuery('option',targetPickList);
					targetPickList.data('available-options', listOfAvailableOptions);
				}

				var optionSelector = '';
				for(var i=0; i<targetPickListMap.length; i++){
					optionSelector += '[value="'+targetPickListMap[i]+'"],';
				}
				var targetOptions = listOfAvailableOptions.filter(optionSelector);
				//Before updating the list, selected option should be updated
				var targetPickListSelectedValue = '';
				jQuery.each(targetOptions, function(key, option) {
					if(jQuery(option).is(':selected')) {
						targetPickListSelectedValue = jQuery(option).val();
					}
				});
				targetPickList.html(targetOptions).val(targetPickListSelectedValue).trigger("liszt:updated");
			})
		});

		//To Trigger the change on load
		sourcePickListElements.trigger('change');
	},

	registerEvents: function(){
		var editViewForm = this.getForm();
		var statusToProceed = this.proceedRegisterEvents();
		if(!statusToProceed){
			return;
		}

		this.registerBasicEvents(this.getForm());
		this.registerEventForImageDelete();
		this.registerSubmitEvent();

		app.registerEventForDatePickerFields('#EditView');
		editViewForm.validationEngine(app.validationEngineOptions);

		this.registerReferenceCreate(editViewForm);
		this.triggerDisplayTypeEvent();
	}
});


  function ValidateHideDriverPickup(AppBookigDate,drivercheckbox,dateformat)  // This is for validaing the driver pickup - ISHWAR 24-dec-2013 
			  {   
			   	var today = new Date();
				var AppBoookingDate2 = new Date(AccessFormatedDate(AppBookigDate,dateformat));
				var TomorrowTime = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
				var Final= gettingFormatedDate(AppBoookingDate2) == gettingFormatedDate(TomorrowTime);
				var hrs = (new Date().getHours());
				if(gettingFormatedDate(AppBoookingDate2) == gettingFormatedDate(TomorrowTime)==true && hrs>=17 )
				{ 
				document.getElementById(drivercheckbox).disabled='true';
				} 
				else {
					 document.getElementById(drivercheckbox).disabled=false;
			}
		} 
		
function ValidateHideTarrgetStartDate(AppBookigDate,drivercheckbox,dateformat)  // This is for validaing the driver pickup - ISHWAR 24-dec-2013 
			  {   
			   	var today = new Date();
				var AppBoookingDate2 = new Date(AccessFormatedDate(AppBookigDate,dateformat));
				var TomorrowTime = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
				var Final= gettingFormatedDate(AppBoookingDate2) == gettingFormatedDate(TomorrowTime);
				var hrs = (new Date().getHours());
				if(gettingFormatedDate(AppBoookingDate2) == gettingFormatedDate(TomorrowTime)==true && hrs>=17 )
				{ 
				document.getElementById(drivercheckbox).disabled='true';
				} 
				else {
					 document.getElementById(drivercheckbox).disabled=false;
			}
		} 
	 
	
  function validateDriverPickupdate(driverPdate,dateformat,id){   // Ajax code for validating the validating the driver pickup date

	  var recordId = $('#recordId').val();
	  
	  var sourceRecord = $('#sourceRecord').val();
	  
	  if(recordId == 'undefined' || recordId == '' || recordId == null) {
		  recordId = sourceRecord;
	  }

	   if(driverPdate=='' || driverPdate=='undefined') {  
	   alert('Enter Pickup Date');
	   return 0;
	    }
	     var QryString = "?driverdate="+driverPdate+"&id="+recordId+"&dateformat="+dateformat;
	     $.ajax({url:"driver_ajax.php"+QryString,success:function(result_data){
			//alert(result_data);													  
	     if(result_data!=1) { //alert(result_data);
		 alert('Pickup Date should be less than one day or equal to Appointment Booking Date('+result_data+')');	 
		 document.getElementById(id).value=''; 
        }
		else {  }
		 
      }});
	  }	 
	 
	 
function AccessFormatedDate(val,dateFormat){   // Function defined for getting the formated data
			 
		var Datevalues = new Array();
		Datevalues=val.split('-');
		if(dateFormat=='mm-dd-yyyy'){
		var CrmDate = Datevalues[2]+'-'+Datevalues[0]+'-'+Datevalues[1];
		}
		if(dateFormat=='dd-mm-yyyy'){
		var CrmDate = Datevalues[2]+'-'+Datevalues[1]+'-'+Datevalues[0];
		}
		
		if(dateFormat=='yyyy-mm-dd'){
		var CrmDate = Datevalues[0]+'-'+Datevalues[1]+'-'+Datevalues[2];
		}
		
		if(dateFormat=='yyyy-dd-mm'){
		var CrmDate = Datevalues[0]+'-'+Datevalues[2]+'-'+Datevalues[1];
		}
		
		//alert("CrmDate"+CrmDate);
		return CrmDate;
			
}	

 function ValidateDateForAppBook(val,id,dateformat,profile,campaign_type){ // Appointment booking date
 
				var today = new Date();				
				var current_date = new Date(new Date().getTime() - 24 * 60 * 60 * 1000);
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				var tomorrow = new Date(new Date().getTime() + 24 * 60 * 60 * 1000);
				var date = tomorrow.getDate();
				var month = tomorrow.getMonth()+1;
				var year = tomorrow.getFullYear(); 
				switch(month){
					case 1:
					month =  '01' ;
					break;
					case 2:
					month =  '02';
					break;
					case  3:
					month =  '03' ;
					break;
					case  4:
					month =  '04' ;
					break;
					case  5:
					month =  '05' ;
					break;
					case  6:
					month =  '06' ;
					break;
					case  7:
					month =  '07' ;
					break;
					case  8:
					month =  '08' ;
					break;
					case  9:
					month =  '09' ;
					break;
					}
					if(dateformat=='mm-dd-yyyy')
						tommorowdate = month+'-'+date+'-'+year;
					if(dateformat=='dd-mm-yyyy')
						tommorowdate = date+'-'+month+'-'+year;
					if(dateformat=='yyyy-mm-dd')
						tommorowdate = year+'-'+month+'-'+date;
					
				if(campaign_type == 'Walk In' && profile == '5') {
					if(Caldate > current_date){
						}
					else{
					alert('Appointment Booking Date should be greater than or equal to current date.');
					document.getElementById(id).value = tommorowdate;
					}
				}
				else if(Caldate <= today ){	
					alert('Appointment Booking Date should be greater than current date.');
					document.getElementById(id).value = tommorowdate;					 
				}
				else{
					
					}
			 }
			  
function validateFollowupdate(val,dateformat,id){ // Added by jitendra singh on 5 feb 2014 for Followup Date
				var yesterday = new Date(new Date().getTime() - 24 * 60 * 60 * 1000);
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				if(Caldate>yesterday){
				
				} else {				
				alert('Followup Date should be greater than or equal to current date.');
				document.getElementById(id).value='';
				           }
			  }			  
			  

    function getDaysInMonth(aDate){
   // returns the last day of a given month
    var m = new Number(aDate.getMonth());
    var y = new Number(aDate.getYear());

    var tmpDate = new Date(y, m, 28);
    var checkMonth = tmpDate.getMonth();
    var lastDay = 27;

    while(lastDay <= 31){
        temp = tmpDate.setDate(lastDay + 1);
        if(checkMonth != tmpDate.getMonth())
            break;
        lastDay++
    }
    return lastDay;
}

/*Add code for target will be greter than current date and end date will be last date of current month by jitendra singh on 18 jan 2014*/
			  
function TargetStartDate(val,id,dateformat){ // Target Start date
				
				var date = new Date();
				var yesterday = new Date(date.getFullYear(), date.getMonth(), 1);

				//var yesterday = new Date(new Date().getTime() - 24 * 60 * 60 * 1000);
				
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				if(dateformat == 'yyyy-mm-dd'){
					var fields = val.split(/-/);
					var year = fields[0];
					var month = fields[1];
					var date1 = fields[2];
					date1 = (new Date((new Date(year, month,1))-1)).getDate();
					end_date = year+"-"+month+"-"+date1;
				}
				if(dateformat == 'dd-mm-yyyy'){
					var fields = val.split(/-/);
					var year = fields[2];
					var month = fields[1];
					var date1 = fields[0];
					date1 = (new Date((new Date(year, month,1))-1)).getDate();
					end_date = date1+"-"+month+"-"+year;
					}
				
				if(dateformat == 'mm-dd-yyyy'){
					var fields = val.split(/-/);
					var year = fields[2];
					var month = fields[0];
					var date1 = fields[1];
					date1 = (new Date((new Date(year, month,1))-1)).getDate();
					end_date = month+"-"+date1+"-"+year;
					}
				
				if(Caldate > yesterday){
				document.getElementById('Targets_editView_fieldName_end_date').value = end_date;
				} 
				if(yesterday > Caldate){			
				alert('Target Start Date should not be less than Current Month Date.');
				document.getElementById(id).value='';
				           }
			  }
			  
			  
			function TargetEndDate(val,id,dateformat){ // Target End date
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				var startdate1 = document.getElementById('Targets_editView_fieldName_start_date').value;
				
				var today1 = new Date(AccessFormatedDate(startdate1,dateformat));
				var yesterday = new Date(today1.getTime() - 24 * 60 * 60 * 1000);
				var startdate = new Date(AccessFormatedDate(startdate1,dateformat));
				if(yesterday < Caldate){
				
				} else {
				alert('Target End Date should not be less than Target Start Date.');
				document.getElementById(id).value='';
				           }
			  }
/*End code for target will be greter than current date and end date will be last date of current month by jitendra singh on 18 jan 2014*/


/*Add code for Campaign will be greter than current date and end date will be last date of current month by jitendra singh on 18 jan 2014*/
			  
	function CampaignStartDate(val,id,dateformat){ // Campaign Start date

				var yesterday = new Date(new Date().getTime() - 24 * 60 * 60 * 1000);
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				
				if(Caldate > yesterday){
					document.getElementById('Campaigns_editView_fieldName_end_date').value = val;
				} else {				
				alert('Campaign Start Date should not be less than Current Date.');
				document.getElementById(id).value='';
				           }
	}
			  
			  
	function CampaignEndDate(val,id,dateformat){ // Campaign End date
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				var startdate1 = document.getElementById('Campaigns_editView_fieldName_closingdate').value;
				var today1 = new Date(AccessFormatedDate(startdate1,dateformat));
				var yesterday = new Date(today1.getTime() - 24 * 60 * 60 * 1000);
				if(yesterday < Caldate){
				
				} else {
				alert('Campaign End Date should not be less than Campaign Start Date.');
				document.getElementById(id).value='';
				           }
	}
/*End code for target will be greter than current date and end date will be last date of current month by jitendra singh on 18 jan 2014*/




/*Add code for Lead Date of Sale should be less than current date by jitendra singh on 18 jan 2014*/
			  
	function LeadDateofSale(val,id,dateformat){ // Lead Date of Sale

				var yesterday = new Date();
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				
				if(Caldate < yesterday){
				} else {				
				alert('Date of Sale should be less than Current Date.');
				document.getElementById(id).value='';
				           }
	}
	
	function LeadDateofBirth(val,id,dateformat){ // Lead Date of Sale

				var yesterday = new Date();
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				
				if(Caldate < yesterday){
				} else {				
				alert("Date of birth should be less than today's date.");
				document.getElementById(id).value='';
				           }
	}
			  
			  
	function Lead_Date_validation(val,id,dateformat){ // Lead Insurance Date
				var Caldate = new Date(AccessFormatedDate(val,dateformat));
				var saledate1 = document.getElementById('Leads_editView_fieldName_dateofsale').value;
				var saledate = new Date(AccessFormatedDate(saledate1,dateformat));
				var yesterday = new Date(saledate.getTime() - 24 * 60 * 60 * 1000);
				//alert(yesterday+"____"+Caldate)
				if(saledate1 == ''){
					alert('Select Date of Sale first.');
					document.getElementById(id).value = '';
					}
				else if(yesterday < Caldate){
				} else {
					if(id == 'Leads_editView_fieldName_lastservicedate')
					alert('Last Service Date should be greater than Date of Sale.');
					else
					alert('Insurance Date should be greater than Date of Sale.');
					document.getElementById(id).value='';
				}
	}
			  
/*End code for Lead Date of Sale should be less than current date by jitendra singh on 18 jan 2014*/
		  		  
			  
			  
function gettingFormatedDate(date){
	
	var currentDate = new Date(date);
var day = currentDate.getDate()
var month = currentDate.getMonth() + 1
var year = currentDate.getFullYear()
return year+'-'+month+'-'+day;
}