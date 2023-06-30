<?php
//require_once (plugin_dir_path( __FILE__ ).'fpdf/fpdf.php');
require_once (plugin_dir_path( __FILE__ ).'fpdf/tfpdf.php');

class PDF extends tFPDF
{


	
function puzzle($data){
	 $this->SetFillColor(255,0,0);
    $this->SetTextColor(0);
    $this->SetDrawColor(128,0,0);
    $this->SetLineWidth(.3);
   // $this->SetFont('','B');
	 
	foreach($data as $k=>$row){
		foreach ($row as $key=> $element)
		{
		//	$element = utf8_decode($element);
		$this->Cell(10,10,$element,1,0,'C');
		
		}
		 $this->Ln();
	}
	
}	
	
	function word($word,$hints){
	 $this->SetTextColor(0);
   // $this->SetFont('','B');
	 
		
		
	foreach($word as $k=>$w){
		//$this->Write(10,($k+1).". ".strtoupper($hints[$k]).' - '.strtoupper($w),'');
		$this->Write(10,($k+1).". ".( empty($hints->$w) ? $w : $hints->$w.' - '.$w ),'');
		$this->Ln();
		}
	}
	
}


?>