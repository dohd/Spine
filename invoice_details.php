<?php
ob_start();
session_start();
include_once('../core/class.manageData.php');

$m_init = new ManageData;

require '../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;






$spreadsheet = new Spreadsheet();
$rows=3;
 
                                           
     


                        $result = $m_init->selectAllOrderByA('ac_project_invoice_details','id');
                                    
                                    
                                    
                            
                                    
                                    
                                                                           foreach($result as $row){
       $rows++;	                                
                                       // $otherName = $m_init->getValue('parents','othernames','phonenumber',$classrow['parentId']);					
// $supplier= $m_init->getValue('ac_clients','client_name','client_id',$row['supplier']);
 //$supplier_pin= $m_init->getValue('ac_clients','taxpin','client_id',$row['supplier']);
 //$project_name= $m_init->getValue('ac_projects','project_name','id',$row['project_id']);
  $invoice_id= $m_init->getValue('ac_project_invoice','invoice_no','uniqueid',$row['invoice_id']);

 //$client_id= $m_init->getValue('ac_projects','client_code','id',$row['project_id']);
 //$branch= $m_init->getValue('ac_clients','client_name','client_id',$client_id);
 // $client_code= $m_init->getValue('ac_clients','client_code','client_id',$client_id);
 //$main= $m_init->getValueTwo('ac_clients','client_name','client_code',$client_code,'client_type','0');

      // $sub_category= $m_init->getValue('ac_products_category','name','id',$row['sub_category']);
               
             //  if($sub_category==""){
       //   $sub_category="N/A";
        // }
       


                                          
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A'.$rows, $invoice_id);
$sheet->setCellValue('B'.$rows,$row['details']);
$sheet->setCellValue('C'.$rows, $row['invoice_description']);
$sheet->setCellValue('D'.$rows, $row['invoice_qnty']);
$sheet->setCellValue('E'.$rows,  $row['invoive_priceperunit']);
$sheet->setCellValue('F'.$rows, $row['invoice_line_total']);
//$sheet->setCellValue('G'.$rows, $row['invoice_line_total']);
//$sheet->setCellValue('H'.$rows, $row['invoice_enddate']);
//$sheet->setCellValue('I'.$rows, $row['amount']);
//$sheet->setCellValue('J'.$rows, $row['date_incurred']);





	}
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(20);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(10);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
//$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
//$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
//$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
//$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);


//headinggs
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A2', ''.date('Y').' : PROJECT INVOICE DETAILS ');
$sheet->setCellValue('A3', 'INVOICE  NUMBER');
$sheet->setCellValue('B3', 'LINE  REFERENCE  ');
$sheet->setCellValue('C3', 'LINE DESCRIPTION');
$sheet->setCellValue('D3', 'QTY');
$sheet->setCellValue('E3', 'LINE TOTAL');
$sheet->setCellValue('F3', 'TOTAL AMOUNT');
//$sheet->setCellValue('G3', 'INVOICE DATE');
//$sheet->setCellValue('H3', 'DUE DATE');
//$sheet->setCellValue('I3', 'INVOICE DATE ');
//$sheet->setCellValue('J3', 'DUE DATE ');
//$sheet->setCellValue('H3', 'CREDIT');


$spreadsheet->getActiveSheet()->mergeCells('A2:K2');
$spreadsheet->getActiveSheet()->getstyle('A2')->applyFromArray(
array(
'font'=>array(
'size'=>24,
)
)
);
$spreadsheet->getActiveSheet()->getstyle('A2:K2')->applyFromArray(
array(
'font'=>array(
'bold'=>TRUE,
),
'borders'=>array(
'outline'=>array(
'style'=> \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
)
)
)
);

$writer = \PhpOffice\PhpSpreadsheet\IOFactory::createWriter($spreadsheet, "Xlsx");
$writer = new Xlsx($spreadsheet);
ob_clean();
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="project_inoice_details.xlsx"');
    $writer->save("php://output");
exit;

//$writer->save('hello world.xlsx');
?>