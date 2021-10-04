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
 
                                           
     


                        $result = $m_init->selectAllOrderByA('ac_office_expense','date_incurred');
                                    
                                    
                                    
                            
                                    
                                    
                                                                           foreach($result as $row){
       $rows++;	                                
                                       // $otherName = $m_init->getValue('parents','othernames','phonenumber',$classrow['parentId']);					
// $supplier= $m_init->getValue('ac_clients','client_name','client_id',$row['supplier']);
 //$supplier_pin= $m_init->getValue('ac_clients','taxpin','client_id',$row['supplier']);
 //$project_name= $m_init->getValue('ac_projects','project_name','id',$row['project_id']);

 //$client_id= $m_init->getValue('ac_projects','client_code','id',$row['project_id']);
 //$branch= $m_init->getValue('ac_clients','client_name','client_id',$client_id);
 // $client_code= $m_init->getValue('ac_clients','client_code','client_id',$client_id);
 //$main= $m_init->getValueTwo('ac_clients','client_name','client_code',$client_code,'client_type','0');

      // $sub_category= $m_init->getValue('ac_products_category','name','id',$row['sub_category']);
               
             //  if($sub_category==""){
       //   $sub_category="N/A";
        // }
       


                                          
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A'.$rows, $row['supplier']);
$sheet->setCellValue('B'.$rows,$row['krapin']);
$sheet->setCellValue('C'.$rows, '');
$sheet->setCellValue('D'.$rows, '');
$sheet->setCellValue('E'.$rows,  '');
$sheet->setCellValue('F'.$rows, $row['description']);
$sheet->setCellValue('G'.$rows, '0');
$sheet->setCellValue('H'.$rows, $row['amount_to_tax']);
$sheet->setCellValue('I'.$rows, $row['amount']);
$sheet->setCellValue('J'.$rows, $row['date_incurred']);





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


//headinggs
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A2', ''.date('Y').' : PROJEOFFICE  EXPENSES ');
$sheet->setCellValue('A3', 'SUPPLIER  NAME');
$sheet->setCellValue('B3', 'KRA PIN');
$sheet->setCellValue('C3', 'PROJECT');
$sheet->setCellValue('D3', 'CLIENT NAME');
$sheet->setCellValue('E3', 'BRANCH NAME');
$sheet->setCellValue('F3', 'EXPENSE DESCRIPTION');
$sheet->setCellValue('G3', 'AMOUNT EXC TAX');
$sheet->setCellValue('H3', 'VAT');
$sheet->setCellValue('I3', 'TOTAL INCL TAX ');
$sheet->setCellValue('J3', 'DATE ');
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
    header('Content-Disposition: attachment; filename="officeexpenses.xlsx"');
    $writer->save("php://output");
exit;

//$writer->save('hello world.xlsx');
?>