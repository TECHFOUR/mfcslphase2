/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/

jQuery.Class("Vtiger_Helper_Js",{

	checkServerConfigResponseCache : '',
	/*
	 * Function to get the instance of Mass edit of Email
	 */
	getEmailMassEditInstance : function(){

		var className = 'Emails_MassEdit_Js';
		var emailMassEditInstance = new window[className]();
		return emailMassEditInstance
	},
    /*
	 * function to check server Configuration
	 * returns boolean true or false
	 */

	checkServerConfig : function(module){
		var aDeferred = jQuery.Deferred();
		var actionParams = {
			"action": 'CheckServerInfo',
			'module' : module
		};
		AppConnector.request(actionParams).then(
			function(data) {
				var state = false;
				if(data.result){
					state = true;
				} else {
					state = false;
				}
				aDeferred.resolve(state);
			}
		);
		return aDeferred.promise();
	},
	/*
	 * Function to get Date Instance
	 * @params date---this is the field value
	 * @params dateFormat---user date format
	 * @return date object
	 */

	getDateInstance : function(dateTime,dateFormat){
		var dateTimeComponents = dateTime.split(" ");
		var dateComponent = dateTimeComponents[0];
		var timeComponent = dateTimeComponents[1];
        var seconds = '00';

		var splittedDate = dateComponent.split("-");
		var splittedDateFormat = dateFormat.split("-");
		var year = splittedDate[splittedDateFormat.indexOf("yyyy")];
		var month = splittedDate[splittedDateFormat.indexOf("mm")];
		var date = splittedDate[splittedDateFormat.indexOf("dd")];
		if((year.length > 4) || (month.length > 2) || (date.length > 2)){
				var errorMsg = app.vtranslate("JS_INVALID_DATE");
				throw errorMsg;
		}

		//Before creating date object time is set to 00
		//because as while calculating date object it depends system timezone
		if(typeof timeComponent == "undefined"){
			timeComponent = '00:00:00';
		}

        var timeSections = timeComponent.split(':');
        if(typeof timeSections[2] != 'undefined'){
            seconds = timeSections[2];
        }

        //Am/Pm component exits
		if(typeof dateTimeComponents[2] != 'undefined') {
			timeComponent += ' ' + dateTimeComponents[2];
            if(dateTimeComponents[2].toLowerCase() == 'pm' && timeSections[0] != '12') {
                timeSections[0] = parseInt(timeSections[0], 10) + 12;
            }

            if(dateTimeComponents[2].toLowerCase() == 'am' && timeSections[0] == '12') {
                timeSections[0] = '00';
            }
		}

        month = month-1;
		var dateInstance = new Date(year,month,date,timeSections[0],timeSections[1],seconds);
        return dateInstance;
	},
	requestToShowComposeEmailForm : function(selectedId,fieldname){
		var selectedFields = new Array();
		selectedFields.push(fieldname);
		var selectedIds =  new Array();
		selectedIds.push(selectedId);
		var params = {
			'module' : 'Emails',
			'selectedFields' : selectedFields,
			'selected_ids' : selectedIds,
			'view' : 'ComposeEmail'
		}
		var emailsMassEditInstance = Vtiger_Helper_Js.getEmailMassEditInstance();
		emailsMassEditInstance.showComposeEmailForm(params);
	},

	/*
	 * Function to get the compose email popup
	 */
	getInternalMailer  : function(selectedId,fieldname){
		var module = 'Emails';
		var cacheResponse = Vtiger_Helper_Js.checkServerConfigResponseCache;
		var  checkServerConfigPostOperations = function (data) {
			if(data == true){
				Vtiger_Helper_Js.requestToShowComposeEmailForm(selectedId,fieldname);
			} else {
				alert(app.vtranslate('JS_EMAIL_SERVER_CONFIGURATION'));
			}
		}
		if(cacheResponse === ''){
			var checkServerConfig = Vtiger_Helper_Js.checkServerConfig(module);
			checkServerConfig.then(function(data){
				Vtiger_Helper_Js.checkServerConfigResponseCache = data;
				checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
			});
		} else {
			checkServerConfigPostOperations(Vtiger_Helper_Js.checkServerConfigResponseCache);
		}
	},

	showConfirmationBoxTargets : function(data){
		var aDeferred = jQuery.Deferred();		
		var bootBoxModal = bootbox.alert(target_msg);					 							
		aDeferred.reject();	

        bootBoxModal.on('hidden',function(e){
            //In Case of multiple modal. like mass edit and quick create, if bootbox is shown and hidden , it will remove
            // modal open
            if(jQuery('#globalmodal').length > 0) {
                // Mimic bootstrap modal action body state change
                jQuery('body').addClass('modal-open');
            }
        })
		return aDeferred.promise();
	},
	
	/*
	 * Function to show the confirmation messagebox
	 */
	 showConfirmationBoxLeads : function(data){
		 var aDeferred = jQuery.Deferred();
		
		 if(other_outlet_flag == 1) {
		 	//var bootBoxModal = bootbox.alert(data['message']+" "+lead_entity_no+".");
			/*var bootBoxModal = bootbox.confirm(data['message']+" "+lead_entity_no+".",app.vtranslate('LBL_NO'),app.vtranslate('LBL_YES'), function(result) {
			if(result){			
				aDeferred.resolve();
			} else{										 
						$.ajax({url:"test_ajax.php",success:function(result_data){
						  alert(result_data);
						}});					 							
				aDeferred.reject();
			}
			});	*/
			
			
			var bootBoxModal = bootbox.alert(data['message']+" "+lead_entity_no+".");					 							
				aDeferred.reject();			
			
		 }
		 
		 else if(other_outlet_flag == 2) {
			 var bootBoxModal = bootbox.alert(data['message']+" "+lead_entity_no+".");					 							
				aDeferred.resolve();		
		 	/*var bootBoxModal = bootbox.confirm(data['message']+" "+lead_entity_no+".",app.vtranslate('LBL_NO'),app.vtranslate('LBL_YES'), function(result) {			
				if(result){
					alert("Ok");
				aDeferred.resolve();
				} else{
					alert("No");
					aDeferred.reject();
				}			
			});*/										
		 }
		 else if(jQuery.type(lead_entity_no) === "string")
		 	var bootBoxModal = bootbox.alert(data['message']+" "+lead_entity_no+".");
		else
			var bootBoxModal = bootbox.alert(data['message']);
		//alert(lead_entity_no+"+++");
		aDeferred.reject();
			
		bootBoxModal.on('hidden',function(e){
            //In Case of multiple modal. like mass edit and quick create, if bootbox is shown and hidden , it will remove
            // modal open
            if(jQuery('#globalmodal').length > 0) {
                // Mimic bootstrap modal action body state change
                jQuery('body').addClass('modal-open');
            }
        })
		return aDeferred.promise();
	 },
	 
	showConfirmationBox : function(data){
		var aDeferred = jQuery.Deferred();		
		var bootBoxModal = bootbox.confirm(data['message'],app.vtranslate('LBL_NO'),app.vtranslate('LBL_YES'), function(result) {
			if(result){
				aDeferred.resolve();
			} else{
				aDeferred.reject();
			}
		});		

        bootBoxModal.on('hidden',function(e){
            //In Case of multiple modal. like mass edit and quick create, if bootbox is shown and hidden , it will remove
            // modal open
            if(jQuery('#globalmodal').length > 0) {
                // Mimic bootstrap modal action body state change
                jQuery('body').addClass('modal-open');
            }
        })
		return aDeferred.promise();
	},

	/*
	 * Function to check Duplication of Account Name
	 * returns boolean true or false
	 */
	checkDuplicateName : function(details) {
		var accountName = details.accountName;
		var recordId = details.recordId;
		var end_date = details.end_date; // for Targets module
		var actual_tag = details.tag; // For Campaigns
		var campaign_status = details.campaign_status; // For Campaigns
		var actual_send_hub = details.actual_send_hub; // For Campaigns
		var actual_checkup = details.actual_checkup; // For Campaigns
		var actual_leaflet = details.actual_leaflet; // For Campaigns
		var actual_poster = details.actual_poster; // For Campaigns
		var mobile = details.mobile;
		var assigned_to = details.assigned_to;		
		var campaignid = details.campaignid;
		var outlet = details.outlet;		
		var customer_type = details.customer_type;
		var lastname = details.lastname;
		var email = details.email;
		var secondaryemail = details.secondaryemail;			
		var dateofbirth = details.dateofbirth;
		var occupation = details.occupation;
		var orgname = details.orgname;
		var designation = details.designation;
		var homeaddone = details.homeaddone;			
		var society_name = details.society_name;						
		var homeaddtwo = details.homeaddtwo;
		var homeaddthree = details.homeaddthree;
		var home_state = details.home_state;
		var home_city = details.home_city;			
		var homepincode = details.homepincode;			
		var company_name = details.company_name;
		var officeaddtwo = details.officeaddtwo;
		var officeaddthree = details.officeaddthree;
		var office_state = details.office_state;			
		var office_city = details.office_city;					
		var office_pincode = details.office_pincode;
		var make = details.make;
		var model = details.model;
		var ownership = details.ownership;			
		var odometer = details.odometer;
		var dateofsale = details.dateofsale;
		var lastservicedate = details.lastservicedate;
		var insurancedate = details.insurancedate;
		var insurancecompany = details.insurancecompany;			
		var leadactivitydate = details.leadactivitydate;
		var description = details.description;
		var aDeferred = jQuery.Deferred();
		var moduleName = details.moduleName;				
		if(typeof moduleName == "undefined"){
			moduleName = app.getModuleName();
		}
		if(moduleName == "Accounts") {
			var params = {
			'module' : moduleName,
			'action' : "CheckDuplicate",
			'accountname' : accountName,			
			'record' : recordId
			}
		}
		
		if(moduleName == "Targets") {
			var params = {
			'module' : moduleName,
			'assigned_to' : assigned_to,
			'action' : "CheckDuplicate",
			'end_date' : end_date,
			'start_date' : accountName,
			'record' : recordId
			}
		}
		
		if(moduleName == "Campaigns") {			
			var params = {
			'module' : moduleName,
			'assigned_to' : assigned_to,
			'action' : "CheckDuplicate",
			'actualtag' : actual_tag,
			'actualbudget' : accountName,
			'campaign_status' : campaign_status,
			'actual_send_hub' : actual_send_hub,
			'actual_checkup' : actual_checkup,
			'actual_leaflet' : actual_leaflet,
			'actual_poster' : actual_poster,			
			'record' : recordId
			}
		}
		
		if(moduleName == "Leads") {				
			var params = {
			'module' : moduleName,
			'action' : "CheckDuplicate",
			'mobile' : mobile,
			'registrationno' : accountName,
			'assigned_to' : assigned_to,			
			'campaignid' : campaignid,
			'outlet' : outlet,
			'lastname' : lastname,
			'email' : email,
			'secondaryemail' : secondaryemail,
			'dateofbirth' : dateofbirth,
			'industry' : occupation, 
			'company' : orgname,
			'designation' : designation,
			'lane' : homeaddone,
			'society_name' : society_name,
			'homeaddtwo' : homeaddtwo, 
			'homeaddthree' : homeaddthree,
			'home_state' : home_state,
			'secondaryemail' : secondaryemail,
			'city' : home_city,
			'code' : homepincode,					
			'company_name' : company_name, 
			'officeaddtwo' : officeaddtwo,
			'officeaddthree' : officeaddthree,
			'office_state' : office_state,
			'country' : office_city,
			'state' : office_pincode, 
			'make' : make,
			'model' : model,
			'rating' : ownership,
			'odometer' : odometer,
			'dateofsale' : dateofsale, 
			'lastservicedate' : lastservicedate,
			'insurancedate' : insurancedate,
			'insurancecompany' : insurancecompany,
			'leadactivitydate' : leadactivitydate,
			'description' : description,
			'record' : recordId
			}
		}
		
		AppConnector.request(params).then(
			function(data) {				
				//alert(JSON.stringify(data));
				//alert(data['alert']);				
				var response = data['result'];
				var result = response['success'];															
				if(result == true) {
					aDeferred.reject(response);
				} else {
					aDeferred.resolve(response);
				}
			},
			function(error,err){
				aDeferred.reject();
			}
		);
		return aDeferred.promise();
	},

	showMessage : function(params){
		if(typeof params.type == "undefined"){
			params.type = 'info';
		}
		params.animation = "show";
		params.title = app.vtranslate('JS_MESSAGE'),
		Vtiger_Helper_Js.showPnotify(params);
	},

	/*
	 * Function to show pnotify message
	 */
	showPnotify : function(customParams) {

		var userParams = customParams;
		if(typeof customParams == 'string') {
			var userParams = {};
			userParams.text = customParams;
		}

		var params = {
			sticker: false,
			delay: '3000',
			type: 'error',
			pnotify_history: false
		}

		if(typeof userParams != 'undefined'){
			var params = jQuery.extend(params,userParams);
		}
		return jQuery.pnotify(params);
	},
    
    /* 
    * Function to add clickoutside event on the element - By using outside events plugin 
    * @params element---On which element you want to apply the click outside event 
    * @params callbackFunction---This function will contain the actions triggered after clickoutside event 
    */ 
    addClickOutSideEvent : function(element, callbackFunction) { 
        element.one('clickoutside',callbackFunction); 
    },
	
	/*
	 * Function to show horizontal top scroll bar 
	 */
	showHorizontalTopScrollBar : function() {
		var container = jQuery('.contentsDiv');
		var topScroll = jQuery('.contents-topscroll',container);
		var bottomScroll = jQuery('.contents-bottomscroll', container);
		
		jQuery('.topscroll-div', container).css('width', jQuery('.bottomscroll-div', container).outerWidth());
		
		topScroll.scroll(function(){
			bottomScroll.scrollLeft(topScroll.scrollLeft());
		});
		
		bottomScroll.scroll(function(){
			topScroll.scrollLeft(bottomScroll.scrollLeft());
		});
	}

},{});