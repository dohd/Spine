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
 
                                           
     


                        $result = $m_init->selectIssueList();
                                    
                                    
                                    
                            
                                    
                                    
                                                                           foreach($result as $row){
       $rows++;	                                
                                       // $otherName = $m_init->getValue('parents','othernames','phonenumber',$classrow['parentId']);					
 //$supplier= $m_init->getValue('ac_clients','client_name','client_id',$row['supplier_id']);
 //$supplier_pin= $m_init->getValue('ac_clients','taxpin','client_id',$row['supplier_id']);


 $project_name= $m_init->getValue('ac_projects','project_name','id',$row['project_id']);

 $client_id= $m_init->getValue('ac_projects','client_code','id',$row['project_id']);
 $branch= $m_init->getValue('ac_clients','client_name','client_id',$client_id);
  $client_code= $m_init->getValue('ac_clients','client_code','client_id',$client_id);
 $main= $m_init->getValueTwo('ac_clients','client_name','client_code',$client_code,'client_type','0');
 $project_id= $m_init->getValue('ac_projects','project_uniqueid','id',$row['project_id']);
// $item_id= $m_init->getValue('ac_product_list','product_code','id',$row['product_id']);
// $item_name= $m_init->getValue('ac_product_list','name','id',$row['product_id']);

      // $sub_category= $m_init->getValue('ac_products_category','name','id',$row['sub_category']);
               
             //  if($sub_category==""){
       //   $sub_category="N/A";
        // }




       


                                          
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A'.$rows, $project_id);
$sheet->setCellValue('B'.$rows, $project_name);
$sheet->setCellValue('C'.$rows, $main);
$sheet->setCellValue('D'.$rows, $branch );
$sheet->setCellValue('E'.$rows, $row['product_code']);
$sheet->setCellValue('F'.$rows, $row['name']);
$sheet->setCellValue('G'.$rows, $row['quantity_issued']);
$sheet->setCellValue('H'.$rows, $row['unit_cost_of_materials']);
$sheet->setCellValue('I'.$rows, $row['total_cost_of_materials']);
$sheet->setCellValue('J'.$rows, $row['issue_date']);

//$sheet->setCellValue('J'.$rows, $row['exp_invoice_amount']);
//$sheet->setCellValue('K'.$rows, $row['date']);





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
//$spreadsheet->getActiveSheet()->getColumnDimension('K')->setWidth(15);


//headinggs
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A2', ''.date('Y').' : PROJECT MATERIALS ');
$sheet->setCellValue('A3', 'PROJECT ID');
$sheet->setCellValue('B3', 'PROJECT');
$sheet->setCellValue('C3', 'CLIENT NAME');
$sheet->setCellValue('D3', 'BRANCH NAME');
$sheet->setCellValue('E3', 'MATERIAL ID');
$sheet->setCellValue('F3', 'MATERIAL NAME');
$sheet->setCellValue('G3', 'QTY ISSUED');
$sheet->setCellValue('H3', 'UNIT COST');
$sheet->setCellValue('I3', 'TOTAL COST');
$sheet->setCellValue('J3', 'DATE');
//$sheet->setCellValue('J3', 'TOTAL COST ');
//$sheet->setCellValue('K3', 'DATE ');
//$sheet->setCellValue('H3', 'CREDIT');


$spreadsheet->getActiveSheet()->mergeCells('A2:J2');
$spreadsheet->getActiveSheet()->getstyle('A2')->applyFromArray(
array(
'font'=>array(
'size'=>24,
)
)
);
$spreadsheet->getActiveSheet()->getstyle('A2:J2')->applyFromArray(
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
    header('Content-Disposition: attachment; filename="projectmaterials.xlsx"');
    $writer->save("php://output");
exit;

//$writer->save('hello world.xlsx');
?>