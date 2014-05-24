/*+***********************************************************************************
 * The contents of this file are subject to the vtiger CRM Public License Version 1.0
 * ("License"); You may not use this file except in compliance with the License
 * The Original Code is:  vtiger CRM Open Source
 * The Initial Developer of the Original Code is vtiger.
 * Portions created by vtiger are Copyright (C) vtiger.
 * All Rights Reserved.
 *************************************************************************************/
var lead_entity_no = "";
var other_outlet_flag = 0;
Vtiger_Edit_Js("Leads_Edit_Js",{
   
},{
   
    //Stored history of account name and duplicate check result
	duplicateCheckCache : {},
	
	//This will store the editview form
	editViewForm : false,
   	 
	
								
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
	getRegisterNo : function(container){
		return jQuery('input[name="registrationno"]',container).val();
	},
      
	 getMobileNo : function(container){
		return jQuery('input[name="mobile"]',container).val();
	},  
	/**
	 * This function will return the current RecordId
	 */
	getRecordId : function(container){
		return jQuery('input[name="record"]',container).val();
	},
	
	getAssigned_To : function(container){
		return jQuery('select[name="assigned_user_id"]',container).val();
	},  
	/**
	 * This function will return the current RecordId
	 */	
	getCampaignId : function(container){
		return jQuery('input[name="campaignid"]',container).val();
	},
	
	getOutletId : function(container){
		return jQuery('input[name="outlet"]',container).val();
	},
       
	 getCustomerType : function(container){
		return jQuery('select[name="leadsource"]',container).val();
	},  
	 
	  getLastname : function(container){
		return jQuery('input[name="lastname"]',container).val();
	},  
	  
	  getEmail : function(container){
		return jQuery('input[name="email"]',container).val();
	},    
	  
	  getSecondaryemail : function(container){
		return jQuery('input[name="secondaryemail"]',container).val();
	}, 
	
	 getDateofbirth : function(container){
		return jQuery('input[name="dateofbirth"]',container).val();
	}, 
	
	getOccupation : function(container){
		return jQuery('select[name="industry"]',container).val();
	},  
	  
	 getOrgName : function(container){
		return jQuery('input[name="company"]',container).val();
	}, 
	
	getDesignation : function(container){
		return jQuery('input[name="designation"]',container).val();
	},  
	
	getHomeAdd1 : function(container){
		return jQuery('textarea[name="lane"]',container).val();
	}, 
	
	getSocietyName : function(container){
		return jQuery('input[name="society_name"]',container).val();
	},
	
	getHomeAdd2 : function(container){
		return jQuery('textarea[name="homeaddtwo"]',container).val();
	},
	getHomeAdd3 : function(container){
		return jQuery('textarea[name="homeaddthree"]',container).val();
	},
	
	getHomeState : function(container){
		return jQuery('input[name="home_state"]',container).val();
	},
	
	getHomeCity : function(container){
		return jQuery('input[name="city"]',container).val();
	},
	
	getHomePinCode : function(container){
		return jQuery('input[name="code"]',container).val();
	},
	
	getCompanyName : function(container){
		return jQuery('textarea[name="company_name"]',container).val();
	},
	
	getOfficeAdd2 : function(container){
		return jQuery('textarea[name="officeaddtwo"]',container).val();
	},
	
	getOfficeAdd3 : function(container){
		return jQuery('textarea[name="officeaddthree"]',container).val();
	},
	
	getOfficeState : function(container){
		return jQuery('input[name="office_state"]',container).val();
	},
	
	getOfficeCity : function(container){
		return jQuery('input[name="country"]',container).val();
	},
	
	getOfficePincode : function(container){
		return jQuery('input[name="state"]',container).val();
	},
	
	getMake : function(container){
		return jQuery('input[name="make"]',container).val();
	},
	
	getModel : function(container){
		return jQuery('input[name="model"]',container).val();
	},
	
	getOwnership : function(container){
		return jQuery('select[name="rating"]',container).val();
	},  
	  
	getOdometer : function(container){
		return jQuery('input[name="odometer"]',container).val();
	},
	
	getDateofsale : function(container){
		return jQuery('input[name="dateofsale"]',container).val();
	},
	
	getLastServiceDate : function(container){
		return jQuery('input[name="lastservicedate"]',container).val();
	}, 
	
	getInsuranceDate : function(container){
		return jQuery('input[name="insurancedate"]',container).val();
	}, 
	getInsuranceCompany : function(container){
		return jQuery('input[name="insurancecompany"]',container).val();
	},
	
	getLeadActivityDate : function(container){
		return jQuery('input[name="leadactivitydate"]',container).val();
	}, 
	
	getDescription : function(container){
		return jQuery('textarea[name="description"]',container).val();
	}, 
	/**
	 * This function will register before saving any record
	 */
	 
	registerRecordPreSaveEvent : function(form) {
		var thisInstance = this;
		if(typeof form == 'undefined') {
			form = this.getForm();
		}		
		form.on(Vtiger_Edit_Js.recordPreSave, function(e, data) {															
			var accountName = thisInstance.getRegisterNo(form);
			var recordId = thisInstance.getRecordId(form);
			var mobile = thisInstance.getMobileNo(form);
			var assigned_to = thisInstance.getAssigned_To(form);			
			var campaignid = thisInstance.getCampaignId(form);
			var outlet = thisInstance.getOutletId(form);						
			var customer_type = thisInstance.getCustomerType(form);
			var lastname = thisInstance.getLastname(form);
			var email = thisInstance.getEmail(form);
			var secondaryemail = thisInstance.getSecondaryemail(form);			
			var dateofbirth = thisInstance.getDateofbirth(form);
			var occupation = thisInstance.getOccupation(form);
			var orgname = thisInstance.getOrgName(form);
			var designation = thisInstance.getDesignation(form);
			var homeaddone = thisInstance.getHomeAdd1(form);			
			var society_name = thisInstance.getSocietyName(form);						
			var homeaddtwo = thisInstance.getHomeAdd2(form);
			var homeaddthree = thisInstance.getHomeAdd3(form);
			var home_state = thisInstance.getHomeState(form);
			var home_city = thisInstance.getHomeCity(form);			
			var homepincode = thisInstance.getHomePinCode(form);			
			var company_name = thisInstance.getCompanyName(form);
			var officeaddtwo = thisInstance.getOfficeAdd2(form);
			var officeaddthree = thisInstance.getOfficeAdd3(form);
			var office_state = thisInstance.getOfficeState(form);			
			var office_city = thisInstance.getOfficeCity(form);						
			var office_pincode = thisInstance.getOfficePincode(form);
			var make = thisInstance.getMake(form);
			var model = thisInstance.getModel(form);			
			var ownership = thisInstance.getOwnership(form);			
			var odometer = thisInstance.getOdometer(form);
			var dateofsale = thisInstance.getDateofsale(form);
			var lastservicedate = thisInstance.getLastServiceDate(form);
			var insurancedate = thisInstance.getInsuranceDate(form);
			var insurancecompany = thisInstance.getInsuranceCompany(form);			
			var leadactivitydate = thisInstance.getLeadActivityDate(form);
			var description = thisInstance.getDescription(form);
							
			var params = {};						
            if(!(accountName in thisInstance.duplicateCheckCache)) {
                Vtiger_Helper_Js.checkDuplicateName({
                    'accountName' : accountName, 
                    'recordId' : recordId,
					'mobile' : mobile,
                    'moduleName' : 'Leads',
					'assigned_to' : assigned_to,
					'customer_type' : customer_type, 
                    'lastname' : lastname,
					'email' : email,
                    'secondaryemail' : secondaryemail,
					'dateofbirth' : dateofbirth,
					'occupation' : occupation, 
                    'orgname' : orgname,
					'designation' : designation,
                    'homeaddone' : homeaddone,
					'society_name' : society_name,
					'homeaddtwo' : homeaddtwo, 
                    'homeaddthree' : homeaddthree,
					'home_state' : home_state,
                    'secondaryemail' : secondaryemail,
					'home_city' : home_city,
					'homepincode' : homepincode,					
					'company_name' : company_name, 
                    'officeaddtwo' : officeaddtwo,
					'officeaddthree' : officeaddthree,
                    'office_state' : office_state,
					'office_city' : office_city,
					'office_pincode' : office_pincode, 
                    'make' : make,
					'model' : model,
                    'ownership' : ownership,
					'odometer' : odometer,
					'dateofsale' : dateofsale, 
                    'lastservicedate' : lastservicedate,
					'insurancedate' : insurancedate,
                    'insurancecompany' : insurancecompany,
					'leadactivitydate' : leadactivitydate,
					'description' : description,   
					'outlet' : outlet,                
					'campaignid' : campaignid
                }).then(
                    function(data){										
                        thisInstance.duplicateCheckCache[accountName] = data['success'];
                        form.submit();
                    },
                    function(data, err){						
                        thisInstance.duplicateCheckCache[accountName] = data['success'];
                        thisInstance.duplicateCheckCache['message'] = data['message'];						
						lead_entity_no = data['alert'];
						other_outlet_flag = data['outlet_flag']												
						var message = app.vtranslate('JS_DUPLICTAE_CREATION_CONFIRMATION_LEADS_EXIT');												
						delete thisInstance.duplicateCheckCache[accountName];
						Vtiger_Helper_Js.showConfirmationBoxLeads({'message' : message}).then(
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
					var message = app.vtranslate('JS_DUPLICTAE_CREATION_CONFIRMATION_LEADS');					
					delete thisInstance.duplicateCheckCache[accountName];
					Vtiger_Helper_Js.showConfirmationBoxLeads({'message' : message}).then(
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
	
	
	
	
	
	
	
	/**
	 * Function which will register basic events which will be used in quick create as well
	 *
	 */
	registerBasicEvents : function(container) {
		this._super(container);
		this.registerRecordPreSaveEvent(container);		
		
			//container.trigger(Vtiger_Edit_Js.recordPreSave, {'value': 'edit'});
	}
});