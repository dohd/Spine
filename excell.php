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
 
                                           
     


                        $result = $m_init->selectAllOrderByA('project_expenses','date');
                                    
                                    
                                    
                            
                                    
                                    
                                                                           foreach($result as $row){
       $rows++;	                                
                                       // $otherName = $m_init->getValue('parents','othernames','phonenumber',$classrow['parentId']);					
 $supplier= $m_init->getValue('ac_clients','client_name','client_id',$row['supplier_id']);
 $supplier_pin= $m_init->getValue('ac_clients','taxpin','client_id',$row['supplier_id']);
 $project_name= $m_init->getValue('ac_projects','project_name','id',$row['project_id']);

 $client_id= $m_init->getValue('ac_projects','client_code','id',$row['project_id']);
 $branch= $m_init->getValue('ac_clients','client_name','client_id',$client_id);
  $client_code= $m_init->getValue('ac_clients','client_code','client_id',$client_id);
 $main= $m_init->getValueTwo('ac_clients','client_name','client_code',$client_code,'client_type','0');
 $project_id= $m_init->getValue('ac_projects','project_uniqueid','id',$row['project_id']);

      // $sub_category= $m_init->getValue('ac_products_category','name','id',$row['sub_category']);
               
             //  if($sub_category==""){
       //   $sub_category="N/A";
        // }
       


                                          
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A'.$rows, $supplier);
$sheet->setCellValue('B'.$rows,$supplier_pin);
$sheet->setCellValue('C'.$rows, $project_id);
$sheet->setCellValue('D'.$rows,  $project_name);
$sheet->setCellValue('E'.$rows,  $main);
$sheet->setCellValue('F'.$rows, $branch);
$sheet->setCellValue('G'.$rows, $row['expense_description']);
$sheet->setCellValue('H'.$rows, '0');
$sheet->setCellValue('I'.$rows, $row['expense_tax']);
$sheet->setCellValue('J'.$rows, $row['exp_invoice_amount']);
$sheet->setCellValue('K'.$rows, $row['date']);





	}
$spreadsheet->getActiveSheet()->getColumnDimension('A')->setWidth(10);
$spreadsheet->getActiveSheet()->getColumnDimension('B')->setWidth(30);
$spreadsheet->getActiveSheet()->getColumnDimension('C')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('D')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('E')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('F')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('G')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('H')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('I')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('J')->setWidth(15);
$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);


//headinggs
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A2', ''.date('Y').' : PROJECT EXPENSES ');
$sheet->setCellValue('A3', 'SUPPLIER  NAME');
$sheet->setCellValue('B3', 'KRA PIN');
$sheet->setCellValue('C3', 'PROJECT ID');
$sheet->setCellValue('D3', 'PROJECT');
$sheet->setCellValue('E3', 'CLIENT NAME');
$sheet->setCellValue('F3', 'BRANCH NAME');
$sheet->setCellValue('G3', 'EXPENSE DESCRIPTION');
$sheet->setCellValue('H3', 'AMOUNT EXC TAX');
$sheet->setCellValue('I3', 'VAT');
$sheet->setCellValue('J3', 'TOTAL INCL TAX ');
$sheet->setCellValue('K3', 'DATE ');
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
    header('Content-Disposition: attachment; filename="projectexpenses.xlsx"');
    $writer->save("php://output");
exit;

//$writer->save('hello world.xlsx');
?>