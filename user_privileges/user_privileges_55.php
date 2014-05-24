<?php


//This is the access privilege file
$is_admin=false;

$current_user_roles='H2';

$current_user_parent_role_seq='H1::H2';

$current_user_profiles=array(1,);

$profileGlobalPermission=array('1'=>0,'2'=>0,);

$profileTabsPermission=array('1'=>0,'2'=>0,'3'=>0,'4'=>1,'6'=>1,'7'=>0,'8'=>0,'9'=>0,'10'=>0,'13'=>0,'14'=>0,'15'=>0,'16'=>0,'18'=>0,'19'=>0,'20'=>0,'21'=>0,'22'=>1,'23'=>0,'24'=>1,'25'=>0,'26'=>0,'27'=>0,'30'=>0,'31'=>0,'32'=>0,'33'=>0,'34'=>0,'35'=>0,'36'=>0,'37'=>0,'38'=>0,'39'=>0,'40'=>0,'41'=>1,'42'=>1,'43'=>1,'44'=>1,'45'=>1,'46'=>1,'47'=>0,'48'=>0,'49'=>0,'50'=>0,'51'=>0,'52'=>0,'53'=>0,'28'=>0,);

$profileActionPermission=array(2=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,10=>0,),4=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,8=>0,10=>0,),6=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,8=>0,10=>0,),7=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,8=>0,9=>0,10=>0,),8=>array(0=>0,1=>0,2=>1,3=>0,4=>0,6=>0,),9=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,),13=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>0,10=>0,),14=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,10=>0,),15=>array(0=>0,1=>0,2=>1,3=>0,4=>0,),16=>array(0=>0,1=>0,2=>0,3=>0,4=>0,),18=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,10=>0,),19=>array(0=>0,1=>0,2=>0,3=>0,4=>0,),20=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,),21=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,),22=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,),23=>array(0=>0,1=>0,2=>0,3=>0,4=>0,5=>0,6=>0,),26=>array(0=>0,1=>0,2=>1,3=>0,4=>0,),33=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,10=>0,),34=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>1,10=>1,),36=>array(0=>0,1=>0,2=>1,3=>0,4=>0,),40=>array(0=>0,1=>0,2=>1,3=>0,4=>0,),41=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,10=>0,),42=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,10=>0,),43=>array(0=>1,1=>1,2=>1,3=>0,4=>1,5=>0,6=>0,10=>0,),45=>array(0=>0,1=>0,2=>0,3=>0,4=>0,),47=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),48=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),49=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),50=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),51=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),52=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),53=>array(0=>0,1=>0,2=>1,3=>0,4=>0,5=>0,6=>0,8=>1,),);

$current_user_groups=array();

$subordinate_roles=array('H17','H20','H21','H23','H24','H25','H26','H22','H3','H18','H4','H5','H10','H16','H7','H19','H9','H30','H8','H15','H29','H6','H11','H27','H28','H13','H12','H14','H31','H33','H35','H36','H32','H34','H37','H38',);

$parent_roles=array('H1',);

$subordinate_roles_users=array('H17'=>array(),'H20'=>array(),'H21'=>array(),'H23'=>array(),'H24'=>array(),'H25'=>array(),'H26'=>array(),'H22'=>array(),'H3'=>array(49,),'H18'=>array(48,),'H4'=>array(),'H5'=>array(),'H10'=>array(),'H16'=>array(),'H7'=>array(),'H19'=>array(),'H9'=>array(),'H30'=>array(),'H8'=>array(),'H15'=>array(),'H29'=>array(),'H6'=>array(47,),'H11'=>array(46,),'H27'=>array(35,50,),'H28'=>array(51,),'H13'=>array(32,),'H12'=>array(36,),'H14'=>array(39,41,),'H31'=>array(52,),'H33'=>array(33,),'H35'=>array(42,43,57,),'H36'=>array(37,),'H32'=>array(53,),'H34'=>array(34,),'H37'=>array(38,),'H38'=>array(40,44,45,56,),);

$user_info=array('user_name'=>'BCCO_TARUNG','is_admin'=>'off','user_password'=>'$1$BC$U07LoxwYQBrCvQEvXdsMr/','confirm_password'=>'$1$BC$U07LoxwYQBrCvQEvXdsMr/','first_name'=>'Tarun','last_name'=>'Gupta','roleid'=>'H2','email1'=>'gupta.tarun2@mahindra.com','status'=>'Active','activity_view'=>'Today','lead_view'=>'Today','hour_format'=>'12','end_hour'=>'','start_hour'=>'00:00','title'=>'','phone_work'=>'','department'=>'','phone_mobile'=>'','reports_to_id'=>'','phone_other'=>'','email2'=>'','phone_fax'=>'','secondaryemail'=>'','phone_home'=>'','date_format'=>'dd-mm-yyyy','signature'=>'','description'=>'','address_street'=>'','address_city'=>'','address_state'=>'','address_postalcode'=>'','address_country'=>'','accesskey'=>'TDSL5w2NSdOwEa8W','time_zone'=>'Asia/Kolkata','currency_id'=>'2','currency_grouping_pattern'=>'123,456,789','currency_decimal_separator'=>'.','currency_grouping_separator'=>'.','currency_symbol_placement'=>'$1.0','imagename'=>'','internal_mailer'=>'0','theme'=>'woodspice','language'=>'en_us','reminder_interval'=>'1 Minute','no_of_currency_decimals'=>'2','truncate_trailing_zeros'=>'0','dayoftheweek'=>'Sunday','callduration'=>'5','othereventduration'=>'5','calendarsharedtype'=>'public','default_record_view'=>'Summary','cf_775'=>'54','ccurrency_name'=>'','currency_code'=>'INR','currency_symbol'=>'INR','conv_rate'=>'1.00000','record_id'=>'','record_module'=>'','currency_name'=>'India, Rupees','id'=>'55');
?>