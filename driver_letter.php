<?php
require_once('include/utils/utils.php');
include('config.php');

global $adb;
require('fpdf/fpdf.php');

class PDF extends FPDF {
 
function Header() {
    $this->SetFont('Times','',15);
    $this->SetY(0.25);
	$heading = "SESAME WORKSHOP INITIATIVES (INDIA) PRIVATE LIMITED";

}
 
function Footer() {

}

function regi_table(){
	
	$this->SetFillColor(238);
	$this->SetFont('Times','',12);
	$this->Cell(1, .2, "v0212.002", 1, 0, 'L', true);
	$this->SetFont('Times','',10);
	$this->SetFillColor(1000);
	$this->Cell(6, .2, "", 1, 0, 'C', true);
	
	
	}
	
function company_name(){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','',12);
	$this->Image('logo.png', 3.35, 0, 2, 1);
	$this->Cell(6.7,.5, "AUTHORIZATION LETTER FOR PICK-UP", 0, 0, 'C', true);
	
	
	}
	


function table1($customer_name,$customer_address,$pickup_date){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->Cell(2, .4,"Customer Name/Address :", 0, 0, 'L', true);
	$this->SetFont('Times','I',10);
	$this->Cell(3.3, .4,$customer_name.",".$customer_address, 0, 0, 'L', true);
	$this->Cell(.5, .4, "Date:-", 0, 0, 'L', true);
	$this->Cell(0, .4, $pickup_date, 0, 0, 'L', true);
	
	
	
	}
	
function table2($registrationno,$model){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->Cell(2, .4,"Vehicle Reg Nos", 0, 0, 'L', true);
	$this->Cell(2.8, .4,$registrationno, 0, 0, 'L', true);
	$this->Cell(1, .4, "Model:", 0, 0, 'C', true);
	$this->Cell(0, .4, $model, 0, 0, 'L', true);
	}


function table3(){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->Cell(2, .4,"Dear Sir/Madam", 0, 0, 'L', true);
	$this->Cell(2.8, .4,"", 0, 0, 'L', true);
	
	
	}	
	

function table4($driver_name,$registrationno){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->MultiCell(6.7, .2, "This is to certify that Mr '".$driver_name."' whose signature is appended below is the person authorized to pick up your vehicle bearing Reg Nos '".$registrationno."' We would request you to also please check his ID card.
", 0, 1, 'C', true);
	
		
	}	
	
function table5(){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->MultiCell(6.7, .2, "Please list down any specific complaints that you would like to highlight in the enclosed sheet.", 0, 1, 'C', true);
}
	
function table10(){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->Cell(2.16, .4, "4. Mr.Sreenu Babu", 0, 0, 'L', true);
	$this->SetFillColor(1000);
	$this->Cell(2.16, .4, "9052058885", 0, 0, 'C', true);
	$this->Cell(2.16, .4, "Works Manager", 0, 0, 'L', true);
	}	
function table11($contact_no,$address){
	
	$this->SetFillColor(1000);
	$this->SetFont('Times','I',12);
	$this->MultiCell(6.7, .3, "Our Office no's: ".$contact_no."", 0, 1, 'C', true);
	$this->MultiCell(6.7, .6, "Signature of Mr............................who has been authorized to pick up the vehicle.....................", 0, 1, 'C', true);
	$this->MultiCell(6.7, .3, "Thanking you", 0, 1, 'C', true);
	$this->MultiCell(6.7, .6, "You're sincerely", 0, 1, 'C', true);
	$this->MultiCell(6.7, .3, "Works Manager", 0, 1, 'C', true);
	$this->MultiCell(6.7, 1, "Note: All vehicles that are driven are solely at customer risk.", 0, 1, 'C', true);
	$this->SetFont('Times','',9);
	$this->MultiCell(6.7, .2, "Mahindra  First Choice services Limited ".$address." Tel :".$contact_no."", 0, 1, 'C', true);
	}	

}

$queryuser = $adb->query("SELECT CONCAT(firstname,' ',lastname) AS 'customername',lane AS 'customer_address',cf_881 AS 'pickup_date',vtiger_service.model AS 'models',registrationno,drivermaster AS 'driver_name',address,contact_no 
FROM vtiger_leaddetails
INNER JOIN vtiger_leadscf ON vtiger_leadscf.leadid = vtiger_leaddetails.leadid
LEFT JOIN vtiger_leadaddress ON vtiger_leadaddress.leadaddressid = vtiger_leaddetails.leadid
LEFT JOIN vtiger_service ON vtiger_service.serviceid = vtiger_leadscf.model
INNER JOIN vtiger_crmentity ON vtiger_crmentity.crmid = vtiger_leaddetails.leadid
INNER JOIN vtiger_users ON vtiger_users.id = vtiger_crmentity.smownerid
INNER JOIN vtiger_outletmastercf ON vtiger_outletmastercf.outletmasterid = vtiger_users.cf_775
INNER JOIN vtiger_crmentityrel ON vtiger_crmentityrel.crmid = vtiger_leaddetails.leadid
INNER JOIN vtiger_drivercf ON vtiger_drivercf.driverid = vtiger_crmentityrel.relcrmid
INNER JOIN vtiger_drivermaster ON vtiger_drivermaster.drivermasterid = vtiger_drivercf.cf_877
INNER JOIN vtiger_crmentity AS vtiger_crmentitydriver ON vtiger_crmentitydriver.crmid = vtiger_drivercf.driverid
WHERE vtiger_crmentity.deleted = 0 AND vtiger_crmentitydriver.deleted = 0 AND vtiger_drivercf.driverid = ".$_REQUEST['driverid_']."");

	if($adb->num_rows($queryuser) > 0) {
			$resultuser = $adb->fetch_array($queryuser);
			$customer_name = $resultuser['customername'];
			$customer_address = $resultuser['customer_address'];
			$pickup_date = $resultuser['pickup_date'];
			$model = $resultuser['models'];
			$registrationno = $resultuser['registrationno'];
			$driver_name = $resultuser['driver_name'];
			$address = $resultuser['address'];
			$contact_no = $resultuser['contact_no'];			
	}
 
//class instantiation
$pdf=new PDF("P","in","Letter");
 
$pdf->SetMargins(1,1,1);
 
$pdf->AddPage();
$pdf->SetFont('Times','',12);
$pdf->Ln(.3);
$pdf->Ln(.5);
$pdf->company_name();
$pdf->Ln(.4);
$pdf->Ln(.3);
$pdf->table1($customer_name,$customer_address,$pickup_date);
$pdf->Ln(.4);
$pdf->table2($registrationno,$model);
$pdf->Ln(.4);
$pdf->table3();
$pdf->Ln(.4);
$pdf->table4($driver_name,$registrationno);
$pdf->Ln(.2);
$pdf->table5();

$pdf->Ln(.2);
$pdf->table11($contact_no,$address);

$pdf->Output("Driver_Letter.pdf",'D');
exit();
?>