<?php
include_once('class.dabase.php');
class ManageData{
function __construct(){
   $db_conn = new ManageDatabase;
   $this->link = $db_conn->connect();
   return $this->link;
   } 
   	function selectAll($table){
		$query = $this->link->prepare("SELECT * FROM `$table` ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		   	function selectProducts($table,$column,$where,$column1,$where1,$column2){
		$query = $this->link->prepare("SELECT * FROM `$table` WHERE  (`$column`='$where') OR  ( `$column`!='$where' AND  `$column1` >'$where1') ORDER BY `$column2` DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		   	function selectOpeningStock($table,$where,$where1){
		$query = $this->link->prepare("SELECT * FROM  lean_governor_opening_stock a INNER JOIN lean_item_inventory_details b ON  a.transaction_ref=b.id WHERE  (  b.item_serial_no='$where') OR  (  b.item_serial_no!='$where' AND  b.item_in_stock >'$where1') ORDER BY b.id DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		    function selectMax($table,$column){
				foreach($this->link->query("SELECT MAX(`$column`) AS `sum` FROM `$table`   ") as $row) {
				return $row['sum'];
				}
				}
				function selectMaxWhereOne($table,$column,$where,$column1){
				foreach($this->link->query("SELECT MAX(`$column`) AS `sum` FROM `$table` WHERE `$column1`='$where'   ") as $row) {
				return $row['sum'];
				}
				}
				
		    function selectCol($col,$table){
		$query = $this->link->prepare("SELECT $col FROM `$table` ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
			 }
			    function overDue($table,$column,$where,$column1){
	foreach($this->link->query("SELECT `invoice_id`  FROM `$table` WHERE `$column1`='$where'  AND current_date >  date(`$column`)   ") as $row) {
		 return $row['invoice_id'];
		 }
	 }
			   function withinThirtyDays($table,$column,$where,$column1){
	foreach($this->link->query("SELECT `$column1`  FROM `$table` WHERE `$column1`='$where'  AND datediff(current_date,date(`$column`)) BETWEEN  0 AND 30   ") as $row) {
		 return $row[$column1];
		 }
	 }
	 
	 
	 function withinSixtyDays($table,$column,$where,$column1){
	foreach($this->link->query("SELECT `$column1`  FROM `$table` WHERE `$column1`='$where'  AND datediff(current_date,date(`$column`)) BETWEEN  31 AND 60  ") as $row) {
		 return $row[$column1];
		 }
	 }
	 function getSum($table,$column){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table`") as $row) {
		 return $row['Total'];
		 }
	 }
	   function selectJobCard($table,$where,$column){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ORDER BY `id` DESC");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   	function selectTwoNot($table,$where,$column,$column1,$where1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  AND `$column1` !='$where1' ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	     function sumWithheld($where){
	foreach($this->link->query("SELECT SUM(tax_amount) AS Total  FROM `ac_taxaccount`  WHERE   (`transaction_ref` = '$where' AND `tax_id` ='2' ) OR  (`transaction_ref` = '$where' AND `tax_id` ='3' ) OR (`transaction_ref` = '$where' AND `tax_id` ='4' )    ") as $row) {
		 return $row['Total'];
		 }
	 }
	 
	  function withinNinetyDays($table,$column,$where,$column1){
	foreach($this->link->query("SELECT `$column1`  FROM `$table` WHERE `$column1`='$where'  AND datediff(current_date,date(`$column`)) BETWEEN  61 AND 90  ") as $row) {
		 return $row[$column1];
		 }
	 }
	 function withinOneTwentyDays($table,$column,$where,$column1){
	foreach($this->link->query("SELECT $column1  FROM `$table` WHERE `$column1`='$where'  AND datediff(current_date,date(`$column`)) BETWEEN  91 AND 120  ") as $row) {
		 return $row[$column1];
		 }
	 }
	  function greaterThanOneTwenty($table,$column,$where,$column1){
	foreach($this->link->query("SELECT $column1  FROM `$table` WHERE `$column1`='$where'  AND `$column` + INTERVAL 121 DAY <= NOW()   ") as $row) {
		 return $row[$column1];
		 }
	 }
	   function selectDateBetween($table,$where1,$where2,$column,$column2,$where3){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2'  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			
			
			return $result;
			  }
			  function selectDateBetweenCenter($table,$where1,$where2,$column,$column2,$where3,$column3,$where4){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column3`='$where4' AND `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2'  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			return $result;
			  }
			
			
			  function selectDateBetweenOne($table,$where1,$where2,$column,$column2,$where3,$column3,$where4){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column3`='$where4' AND  `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2'  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			
			return $result;
			  }
			   function getSumTwoMBetween($table,$column,$column2,$where,$column3,$where1,$column4,$where2,$where3){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE `$column2` ='$where' AND  `$column3` >'$where1' AND `$column4`  BETWEEN '$where2' AND '$where3'   ") as $row) {
		 return $row['Total'];
		 }
	 }
			  function selectDateBetweenOneCenter($table,$where1,$where2,$column,$column2,$where3,$column3,$where4,$column4,$where5){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column4`='$where5'  AND  `$column3`='$where4' AND  `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2'  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			
			return $result;
			  }
			   function selectDateBetweenCert($table,$where1,$where2,$column,$column2,$where3,$column3,$where4,$where5){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE (`$column3`='$where4' AND   `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2') OR (`$column3`='$where5' AND   `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2')  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			
			return $result;
			  }
			  function selectDateBetweenCertCenter($table,$where1,$where2,$column,$column2,$where3,$column3,$where4,$where5,$column4,$where6){
			$query = $this->link->prepare("SELECT  *  FROM `$table` WHERE (`$column4`='$where6' AND `$column3`='$where4' AND   `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2') OR (`$column4`='$where6' AND  `$column3`='$where5' AND   `$column2`='$where3' AND  `$column`  BETWEEN '$where1' AND '$where2')  ORDER BY `$column` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			
			return $result;
			  }
			 function incomeReport($where){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,a.payment_mode,b.invoice_no,a.transaction_ref,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname FROM lean_payments a, lean_master_table b, lean_governor_clients c WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND a.income_expense_type = '2'  ".$where."  GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
	 
			   function selectOneM($table,$where,$column){
			$query = $this->link->prepare("SELECT  `project_id`,maint_no,SUM(`exp_invoice_amount`) AS exp_invoice_amount  FROM `$table` WHERE `$column` ='$where' GROUP BY `project_id`,`maint_no` ORDER BY `date` DESC ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			return $result;
			  }
			   function selectMaxID($table){
				$query = $this->link->prepare("SELECT MAX(ID) FROM `$table` ");
					 $query->execute();
					 $rowCount = $query->rowCount();
						if($rowCount >= 1)
					 {
						$result = $query->fetchAll(); 
					 }
					 else
					 {
						 $result = 0;	 
					 }
					 return $result;
					 }
					  function getSumOneM($table,$column,$column2,$where){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` >'$where'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	   function getSumTwoM($table,$column,$column2,$where,$column3,$where1){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE `$column2` ='$where' AND  `$column3` >'$where1'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	 
		    function dynamicFour($table,$dt1,$dt2,$dt3,$dt4,$cl1,$cl2,$cl3,$cl4){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`) VALUES (?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   	function selectOne($table,$where,$column){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	     	function selectOneInArray($table,$array,$column){
	 $query = $this->link->prepare("SELECT  *  FROM `$table`  WHERE `$column` IN ('".$array."')  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	   function withinDaysInArray($table,$array,$column,$column1,$min,$max){
	 $query = $this->link->prepare("SELECT *  FROM `$table` WHERE   `$column` IN ('".$array."')  AND  datediff(current_date,date(`$column1`)) BETWEEN '$min' AND '$max'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	     function withinDaysAboveInArray($table,$array,$column,$column1,$days){
	 $query = $this->link->prepare("SELECT *  FROM `$table` WHERE   `$column` IN ('".$array."')  AND `$column1`  + INTERVAL '$days' DAY <= NOW()  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	   

	   
	   function selectOneOrderBy($table,$where,$column,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ORDER BY `$column1` DESC ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function selectProductLimit(){
	 $query = $this->link->prepare("SELECT  *  FROM `ac_product_list`    ORDER BY `entry_date` DESC LIMIT 10 ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	     function selectProductLimitId($id){
	 $query = $this->link->prepare("SELECT  *  FROM `ac_product_list` WHERE `category` ='$id'   ORDER BY `entry_date` DESC LIMIT 10 ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	     
			 function RecievePayment($where){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,a.payment_mode,b.invoice_no,a.transaction_ref,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname FROM lean_payments a, lean_master_table b, lean_governor_clients c WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.income_expense_type = '2' AND b.fitting_centre='$where' GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function RecievePaymentAll(){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,a.payment_mode,b.invoice_no,a.transaction_ref,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2' GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function selectTransactionInfo($where){
		$query = $this->link->prepare("SELECT a.item_id,c.Item_name AS name,a.quantity,a.amount,a.master_id,b.master_item_id,b.transaction_ref,c.id,c.item_code,c.item_description,c.item_serial_no,d.item_code,d.Item_name,d.product_category FROM lean_purchase_sale_per_item a,lean_master_table b,lean_item_inventory_details c, lean_item_inventory d WHERE c.id=a.item_id AND b.master_item_id=a.master_id AND c.item_code=d.item_code AND b.transaction_ref='$where' ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			  function selectMaintHistory($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.machine_id,a.project_id,a.mach_condition,a.remarks,a.service_id,a.maintenance_no,b.id,b.project_id,b.jobcard_no,b.entry_date,b.technician,b.action_on_machine FROM ac_machine_maintenance a, ac_maintenance_tbl b WHERE a.service_id=b.id AND  a.machine_id = '$where' AND b.action_on_machine = 2");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectRepairHistory($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.machine_id,a.project_id,a.mach_condition,a.remarks,a.service_id,b.id,b.project_id,b.jobcard_no,b.entry_date,b.technician,b.action_on_machine FROM ac_machine_maintenance a, ac_maintenance_tbl b WHERE a.service_id=b.id AND  a.machine_id = '$where' AND b.action_on_machine = 3");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectProspects(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.fitting_centre,a.client_id,a.vehicle_reg_no,a.gov_type,a.expiry_date,a.entry_date,b.governor_client_code,b.governor_client_fname,b.governor_client_phone,c.id,c.name FROM lean_prospects a, lean_governor_clients b, fittingcenter c WHERE a.fitting_centre=c.id AND  a.client_id = b.governor_client_code ORDER BY a.id DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
	   function selectOneGreater($table,$where,$column){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` > '$where'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	     function selectGreater($table,$where,$column,$where2,$column2){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` > '$where2'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectTwo($table,$where,$column,$where2,$column2){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2'   ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectTwoGreater($table,$where,$column,$where2,$column2,$where3,$column3){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` > '$where3'   ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectThreeStock($table,$where,$column,$where2,$column2,$where3,$column3,$where4,$column4,$column5){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` ='$where3' AND `$column4` ='$where4' AND `$column5` > 0");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function selectThree($table,$where,$column,$where2,$column2,$where3,$column3){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` ='$where3'");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectThreeGreaterThan($table,$where,$column,$where2,$column2,$where3,$column3,$where4,$column4){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` ='$where3' AND `$column4` > '$where4'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function selectActiveVehicles($table,$column,$column2){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column2` >= curdate()  ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	
	function selectActiveVehiclesType($table,$column,$column2,$column3,$where,$column4,$where1){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where' AND `$column4`='$where1' AND`$column2` >= curdate()  ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 function selectExpiredVehiclesType($table,$column,$column2,$column3,$where,$column4,$where1){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where' AND `$column4`='$where1' AND `$column2` < curdate()  ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 function selectActiveVehiclesNew($table,$column,$column2){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column2` > curdate() ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function selectActiveVehiclesNewTown($table,$column,$where,$colum2,$column3){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$colum2`='$where' AND `$column3` > curdate() ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   
	  function selectExpiresVehicles($table,$column,$column2){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column2` < curdate() AND  `$column2` >  DATE_SUB(NOW(),INTERVAL 3 MONTH)   ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 function selectExpiresVehiclesNew($table,$column,$column2,$where){
		$query = $this->link->prepare("SELECT * FROM `$table` WHERE `$column2` < curdate() AND  `$column2` >  DATE_SUB(NOW(),INTERVAL 3 MONTH) ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		    function selectExpiresVehiclesNewTown($table,$where,$column,$column2){
		$query = $this->link->prepare("SELECT * FROM `$table` WHERE `$column`='$where' AND  `$column2` < curdate() AND  `$column2` >  DATE_SUB(NOW(),INTERVAL 3 MONTH) ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   
	  function selectFollowup($table,$column,$column2){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column2` < curdate() AND  `$column2` <  DATE_SUB(NOW(),INTERVAL 3 MONTH)   ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	  function selectFollowupType($table,$column,$column2,$column3,$where){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE  `$column3`='$where' AND `$column2` < curdate() AND  `$column2` <  DATE_SUB(NOW(),INTERVAL 3 MONTH)   ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 function selectFollowupNew($table,$column,$column2,$where){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column2` < curdate() AND  `$column2` <  DATE_SUB(NOW(),INTERVAL 3 MONTH) ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function selectFollowupNewTown($table,$where,$column,$column2){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column`='$where' AND  `$column2` < curdate() AND  `$column2` <  DATE_SUB(NOW(),INTERVAL 3 MONTH)  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		    function upcomingMaintanance($table,$column2,$where,$column3){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column3`='$where' AND `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 1 MONTH)  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   
		   
		   
	   function selectNextThirtyDays($table,$column,$column2){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 1 MONTH)   ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	  function upcomingMaintananceNextThirtyDays(){
	foreach($this->link->query("SELECT COUNT(b.machine_id) AS countrecord  FROM ac_projects  a INNER JOIN ac_project_vs_machine b ON a.id = b.project_id INNER JOIN ac_project_machines c ON  b.machine_id=c.id WHERE a.project_type='2' AND  ( c.next_maint_date > curdate() AND  c.next_maint_date <  DATE_ADD(NOW(),INTERVAL 1 MONTH))   ") as $row) {
		 return $row['countrecord'];
		 }
	 }
	  function upcomingMaintananceNextThirtyDaysAll(){
		$query = $this->link->prepare("SELECT *  FROM ac_projects  a INNER JOIN ac_project_vs_machine b ON a.id = b.project_id INNER JOIN ac_project_machines c ON  b.machine_id=c.id WHERE a.project_type='2' AND  ( c.next_maint_date > curdate() AND  c.next_maint_date <  DATE_ADD(NOW(),INTERVAL 1 MONTH))  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   
	   function expiredMaintanance($table,$column,$column2,$where,$column3){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where' AND `$column2` < curdate() ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 
	 function expiredMaintanances($table,$column2,$where,$column3){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column3`='$where' AND `$column2` < curdate() ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   	  function getSumWht($where){
	foreach($this->link->query("SELECT SUM(a.wht_paid_amount) as Total  FROM ac_invoice_payment a,ac_project_invoice b WHERE    a.uniqueid=b.uniqueid AND b.project_id='$where' GROUP BY b.project_id     ") as $row) {
		 return $row['Total'];
		 }
	 }
	    	function selectAllOrderByD($table,$column){
		$query = $this->link->prepare("SELECT * FROM `$table` ORDER BY `$column` DESC ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function selectTwoOrderByD($table,$where,$column,$where2,$column2,$column3){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' ORDER BY `$column3` DESC ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
		   	 function selectOneMoreMHISTORY($where){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT a.id,b.service_id,b.machine_id,b.mach_condition,b.remarks,c.id,c.jobcard_no,c.technician,c.action_on_machine,c.entry_date FROM ac_project_machines a,ac_machine_maintenance b,ac_maintenance_tbl c WHERE a.id=b.machine_id AND b.service_id=c.id AND a.id = '$where'  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 }
			 function selectExpense(){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT a.expense_code,a.expense_name,b.expense_details_id,b.expense_code,b.fitting_centre,b.expense_description,b.expense_amount,b.expense_date,c.id,c.name FROM lean_expenses a,lean_expense_details b, fittingcenter c WHERE a.expense_code=b.expense_code AND b.fitting_centre=c.id ORDER BY b.expense_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectExpenseReport($where1,$where2){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.fitting_centre,b.expense_description,b.ref_no,b.expense_code,c.expense_code,c.expense_name FROM lean_master_table a, lean_expense_details b, lean_expenses c WHERE a.ref_no=b.ref_no AND b.expense_code=c.expense_code AND a.item_fitting_centre='$where1' AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectExpenseReportAllCentres($where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.fitting_centre,b.expense_description,b.ref_no,b.expense_code,c.expense_code,c.expense_name FROM lean_master_table a, lean_expense_details b, lean_expenses c WHERE a.ref_no=b.ref_no AND b.expense_code=c.expense_code AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function CancelledCerts(){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,a.payment_mode,a.payment_type,b.invoice_no,a.transaction_ref,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,d.id,d.name,e.item_id,e.master_id,f.id,f.Item_name,f.item_description,f.item_serial_no FROM lean_payments a, lean_master_table b, fittingcenter d, lean_purchase_sale_per_item e, lean_item_inventory_details f WHERE f.id=e.item_id AND e.master_id=b.master_item_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND a.income_expense_type = '2' AND a.payment_type='3' GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{ 
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function selectExpenseReport1($where1,$where2){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.Item_name,b.item_description,b.ref_no,b.item_code,c.item_code,c.Item_name as name FROM lean_master_table a, lean_item_inventory_details b, lean_item_inventory c WHERE a.ref_no=b.ref_no AND b.item_code=c.item_code AND a.item_fitting_centre='$where1' AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectExpenseReportAllCentres1($where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.ref_no,b.Item_name,b.item_code,c.item_code,c.Item_name as name FROM lean_master_table a, lean_item_inventory_details b, lean_item_inventory c WHERE a.ref_no=b.ref_no AND b.item_code=c.item_code AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
		function getMountTodeposite($where,$where2,$where3){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT SUM(a.amount) as total  FROM lean_payments a,lean_master_table b WHERE   a.transaction_ref=b.transaction_ref AND a.income_expense_type='2' AND b.income_expense_type='2' AND b.fitting_centre='$where3' AND a.payment_mode='1' AND a.amount >0 AND a.entry_date BETWEEN '$where' AND  '$where2'");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 }
function getMountTodepositeAll($where,$where2,$where3){
		$query = $this->link->prepare("SELECT a.amount,a.payment_mode,a.transaction_ref,a.payment_reference,a.entry_date  FROM lean_payments a,lean_master_table b WHERE    a.transaction_ref=b.transaction_ref AND a.income_expense_type='2' AND b.income_expense_type='2' AND b.fitting_centre='$where3'    AND a.payment_mode='1' AND a.amount >0 AND a.entry_date BETWEEN '$where' AND '$where2'  ORDER BY a.entry_date ASC    ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
		   }
			 function selectIncomeReportAllCentres($where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,a.reference FROM lean_master_table a WHERE a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectIncomeReportAllCentres1($where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.bills_ref,b.bill_type FROM lean_master_table a, governor_bills b WHERE b.bills_ref=a.ref_no AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectIncomeReportAllCentres2($where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.uniqueid,b.governortype,c.item_code,c.Item_name FROM lean_master_table a, governers_renewals b, lean_item_inventory c WHERE b.uniqueid=a.ref_no AND c.item_code=b.governortype AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectIncomeReport($where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,a.reference FROM lean_master_table a WHERE a.item_fitting_centre='$where1' AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectIncomeReport1($where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.bills_ref,b.bill_type FROM lean_master_table a, governor_bills b WHERE b.bills_ref=a.ref_no AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 	function selectIncomeReport2($where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_fitting_centre,a.item_description,a.income_amount,a.expense_amount,a.entry_date,a.tax,a.invoice_no,a.ref_no,b.uniqueid,b.governortype,c.item_code,c.Item_name FROM lean_master_table a, governers_renewals b, lean_item_inventory c WHERE b.uniqueid=a.ref_no AND c.item_code=b.governortype AND a.item_description='$where2' ORDER BY a.entry_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectStockTransferReport(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.item_id,a.quantity,a.center_from,a.center_to,a.transfer_date,a.entry_date,c.item_code,c.item_description,c.item_serial_no,c.id FROM lean_stock_transfer a, lean_item_inventory_details c WHERE a.item_id=c.id ORDER BY a.transfer_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			   function getAllStock(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.item_code,a.Item_name,a.item_description,SUM(a.item_in_stock) AS item_in_stock,a.lean_fitting_centre,b.item_code,b.Item_name as cat_name FROM lean_item_inventory_details a, lean_item_inventory b WHERE a.item_in_stock > 0 AND b.item_code=a.item_code GROUP BY a.Item_name,a.item_description,a.item_code");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 
			 
			
			function selectStock($where,$where1,$where3,$where4){
	foreach($this->link->query("SELECT a.id,a.item_code,a.Item_name,a.item_description,SUM(a.item_in_stock) AS item_in_stock,a.lean_fitting_centre FROM lean_item_inventory_details a WHERE a.item_in_stock > 0 AND a.lean_fitting_centre='$where' AND a.Item_name='$where1' AND a.item_code='$where3' AND a.item_description='$where4'  GROUP BY a.Item_name,a.lean_fitting_centre,a.item_description  ") as $row) {
		 return $row['item_in_stock'];
		 }
	 }
	 
	  
	 
	   function getSumOneInvoicebf($where,$where1,$where2){
	foreach($this->link->query("SELECT SUM(action_amount) as Total  FROM ac_client_transaction  WHERE client_id ='$where' AND  action_type ='$where1'  AND transaction_date <  '$where2'    ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function searchMachine($where){
		$query = $this->link->prepare("SELECT a.id,a.machine_name,a.serial_no FROM ac_project_machines a WHERE a.machine_name LIKE '$where%' OR a.serial_no LIKE '$where%' LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
			 }
	
	 
	 function selectNextThirtyDaysType($table,$column,$column2,$column3,$where){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where' AND  `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 1 MONTH)   ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 
	function selectNextThirtyDaysNew($table,$column,$column2,$where){
		$query = $this->link->prepare("SELECT *   FROM `$table` WHERE `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 1 MONTH) ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 

		   function selectNextSixMonths($table,$column,$column2,$where){
		$query = $this->link->prepare("SELECT *   FROM `$table` WHERE `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 6 MONTH) ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		    function selectNextThirtyDaysNewTown($table,$where,$column,$column2){
		$query = $this->link->prepare("SELECT *   FROM `$table` WHERE `$column`='$where' AND  `$column2` > curdate() AND  `$column2` <  DATE_ADD(NOW(),INTERVAL 1 MONTH) ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
	     function selectFour($table,$where,$column,$where2,$column2,$where3,$column3,$where4,$column4){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` ='$where3' AND `$column4` ='$where4' ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectFourNot($table,$where,$column,$where2,$column2,$where3,$column3,$where4,$column4){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where2' AND `$column3` ='$where3' AND `$column4` !='$where4' ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function updateThreeTwo($id,$id1,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$where,$where1){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3' WHERE `$where` = '$id' AND `$where1` = '$id1'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		   	function selectJoinOne($table,$table2,$column1id,$column2id){
		$query = $this->link->prepare("SELECT * FROM $table a INNER JOIN $table2 b ON $column1id = $column2id ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		   function selectRenewals($table,$table2,$column1id,$column2id,$where){
		$query = $this->link->prepare("SELECT * FROM $table a INNER JOIN $table2 b ON $column1id = $column2id  WHERE b.fittingcenter='$where' ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		    function selectRenewalsAll($table,$table2,$column1id,$column2id,$where){
		$query = $this->link->prepare("SELECT * FROM $table a INNER JOIN $table2 b ON $column1id = $column2id ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		      function selectOneMore($table,$where,$column){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT * FROM `$table` WHERE `$column` = :name  ");
		   $query->execute(['name' => $where]);
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
		   }
		    function updateOne($id,$table,$edit,$colmn,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit' WHERE `$where` = '$id' ");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function updateOneTwo($id,$id1,$table,$edit,$colmn,$where,$where1){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit' WHERE `$where` = '$id' AND `$where1` = '$id1' ");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function updateOneThree($id,$id1,$id2,$table,$edit,$colmn,$where,$where1,$where2){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit' WHERE `$where` = '$id' AND `$where1` = '$id1' AND `$where2` = '$id2' ");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateTwo($id,$table,$edit,$colmn,$edit2,$colmn2,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
			}
		  function updateThree($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function updateFour($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateFive($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateSix($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateSeven($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function updateEight($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateNine($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		  function updateFourFour($id,$id2,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$where,$where2){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4'  WHERE `$where` = '$id' AND `$where2` = '$id2'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		    function getValue($table,$column,$column2,$where){
	foreach($this->link->query("SELECT `$column`  FROM `$table` WHERE `$column2` ='$where' ") as $row) {
		 return $row[''.$column.''];
		 }
	 }
	   function getValueThree($table,$column,$column2,$where,$column3,$where1,$column4,$where2){
	foreach($this->link->query("SELECT `$column`  FROM `$table` WHERE `$column2` ='$where' AND `$column3` ='$where1' AND `$column4` ='$where2'") as $row) {
		 return $row[''.$column.''];
		 }
	 }
	  function getValueCount($table,$column,$column2,$where){
	foreach($this->link->query("SELECT COUNT(`$column`) as coutRow  FROM `$table` WHERE `$column2` ='$where'   ") as $row) {
		 return $row['coutRow'];
		 }
	 }
	  function getValueCountTwo($table,$column,$column2,$where,$column3,$where2){
	foreach($this->link->query("SELECT COUNT(DISTINCT(`$column`)) as coutRow  FROM `$table` WHERE `$column2` ='$where' AND  `$column3` ='$where2'  ") as $row) {
		 return $row['coutRow'];
		 }
	 }
	  
	   function getValueCountThree($table,$column,$column2,$where,$column3,$where2,$column4,$where3){
	foreach($this->link->query("SELECT COUNT(DISTINCT(`$column`)) as coutRow  FROM `$table` WHERE `$column2` ='$where' AND  `$column3` ='$where2' AND  `$column4` ='$where3' ") as $row) {
		 return $row['coutRow'];
		 }
	 }
	  function getValueFour($table,$column,$column1,$where1,$column2,$where2,$column3,$where3,$column4,$where4){
	foreach($this->link->query("SELECT `$column`  FROM `$table` WHERE `$column1` ='$where1' AND `$column2` ='$where2' AND `$column3` ='$where3' AND `$column4` ='$where4' ") as $row) {
		 return $row[''.$column.''];
		 }
	 }
	 	  function searchRecord($where){
		$query = $this->link->prepare("SELECT a.id,a.Item_name,a.item_description,a.item_in_stock,a.item_serial_no,b.Item_name as name FROM lean_item_inventory_details a,lean_item_inventory b WHERE ((a.item_code=b.item_code AND a.item_in_stock>0 AND a.item_name='General_item') AND (b.Item_name LIKE '$where%' OR a.item_description LIKE '$where%' OR a.item_serial_no LIKE '$where%')) LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function searchClientRecord($where){
			$query = $this->link->prepare("SELECT * FROM lean_governor_clients  WHERE (governor_client_fname LIKE '$where%' OR governor_client_lname LIKE '$where%' OR governor_client_phone LIKE '$where%') AND client_type=0 ");
			  $query->execute();
			  $rowCount = $query->rowCount();
			 if($rowCount >= 1)
			{
			$result = $query->fetchAll(); 
			}
			else
			{
			  $result = 0;	 
			}
			return $result;
			}
		function searchCompDetails($where,$type){
		$query = $this->link->prepare("SELECT * FROM lean_item_inventory_details WHERE 	Item_name='$type' AND item_description LIKE '$where%' LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 
			 function searchVehicleReg($where){
		$query = $this->link->prepare("SELECT a.renewal_vehicle_regno,a.renewal_client_id,a.renewal_id,a.renewal_vehicle_chassisno,a.renewal_vehicle_type,a.governortype,b.governor_client_fname,b.governor_client_id FROM governers_renewals a, lean_governor_clients b WHERE b.governor_client_id=a.renewal_client_id AND a.renewal_vehicle_regno LIKE '$where%' LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			  function selectOneMoreINV($where){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT b.client_code as code, c.client_level,b.project_lpo_number,a.bank_account,a.project_id,a.invoice_no,a.invoice_creditterms,a.invoice_enddate,a.invoice_amount,a.invoice_tax,a.invoice_date,a.subTotal,a.invoicetype,c.taxpin,c.address,c.town,b.client_code,b.project_name,c.client_name,c.client_phone1,c.client_email,a.taxid FROM ac_project_invoice a,ac_projects b,ac_clients c WHERE a.project_id=b.id AND b.client_code=c.client_id  AND a.uniqueid = '$where'  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 }
			  function selectOneMoreGovSupplier(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.transaction_ref,a.currentdate,a.client_supplier_id,a.entry_date,a.credit_term,a.amount,a.invoice_no,c.governor_client_id,c.governor_client_fname,c.governor_client_lname FROM lean_master_table a   LEFT JOIN lean_governor_clients c ON a.client_supplier_id=c.governor_client_id    WHERE    c.client_type =1 AND a.amount >0 AND a.purchasetype=1   AND a.income_expense_type=1  ORDER BY a.entry_date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			
		function selectOneMoreExpense(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.transaction_ref,a.client_supplier_id,a.entry_date,a.credit_term,a.amount,a.invoice_no,c.id,c.expense_name,a.sale_purchase_description,a.fitting_centre,a.currentdate FROM lean_master_table a   LEFT JOIN lean_expenses c ON a.client_supplier_id=c.id    WHERE   a.amount >0 AND a.purchasetype=2   AND a.income_expense_type=1  ORDER BY a.entry_date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			 	 function selectOneMorePAY(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id  WHERE a.burden_material='0' AND   c.client_type =1  GROUP BY a.expense_uniqueid ORDER BY a.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function selectOneMorePAID(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.expense_id,a.project_id,a.expense_category_id,a.maint_no,a.supplier_id,a.expense_description,a.paper_clip,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.invoice_no,d.invoice_amount,d.paid_amount,d.due_date FROM project_expenses a,project_exp_category b,ac_clients c,ac_make_payment d WHERE a.expense_category_id=b.id AND a.supplier_id=c.client_id AND a.expense_id=d.expense_id AND c.client_type =1 AND a.balance = 0 GROUP BY d.invoice_no" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			 function selectAllQ($table){
		$query = $this->link->prepare("SELECT * FROM `$table` ORDER BY quot_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function selectEXP($where){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.maint_no,a.supplier_id,a.invoice_no,a.expense_description,a.paper_clip,a.date,a.due_date,a.exp_invoice_amount,a.expense_tax,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id  WHERE    c.client_type =1   AND a.project_id = '$where' GROUP BY a.expense_uniqueid ORDER BY a.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			} 
			 
			
			
			  function deleteFunc($table, $id, $where){
				$query = $this->link->query("DELETE FROM `$table` WHERE `$where` = '$id' ");
	 $rowCount = $query->rowCount();
	 return $rowCount;
		}
	  function additionAll($table,$column){
	foreach($this->link->query("SELECT SUM(`$column`) AS `sum` FROM `$table`  ") as $row) {
		 return $row['sum'];
		 }
	 }
	     function getSumOne($table,$column,$column2,$where){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	
	 function selectCallRemark($table,$where,$column,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ORDER BY `$column1` DESC LIMIT 5");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectCallRemarkVehicle($table,$where,$column,$column2,$where1,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' AND `$column2` ='$where1' ORDER BY `$column1` DESC LIMIT 1");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	  function selectOneOrderByD($table,$where,$column,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ORDER BY `$column1` DESC");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	 
	
	 
	 	function selectOneAllStatements($where,$where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT *  FROM ac_client_transaction  WHERE  client_id ='$where' AND transaction_date BETWEEN   '$where1' AND  '$where2'   " );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
	  function getSumTwo($table,$column,$column2,$where,$column3,$where1){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where' AND  `$column3` ='$where1'  ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getSumOneTwo($table,$column,$column2,$where,$column3,$where1){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where'  AND `$column3` ='$where1'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getSumThree($table,$column,$column2,$where,$column3,$where1,$column4,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where' AND  `$column3` ='$where1' AND `$column4` ='$where2'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	 function getSumFour($table,$column,$column2,$where,$column3,$where1,$column4,$where2,$column5,$where3){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where' AND  `$column3` ='$where1' AND `$column4` ='$where2' AND `$column5` ='$where3'   ") as $row) {
		 return $row['Total'];
		 }
	 }
	   function getSumOneThree($table,$column,$column2,$where,$column3,$where1,$column4,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) as Total  FROM `$table` WHERE  `$column2` ='$where'  AND `$column3` ='$where1' AND `$column4` ='$where2'   ") as $row) {
		 return $row['Total'];
		 }
	 }
		     function getValueTwo($table,$column,$column2,$where,$column3,$where1){
	foreach($this->link->query("SELECT `$column`  FROM `$table` WHERE `$column2` ='$where' AND `$column3` ='$where1' ") as $row) {
		 return $row[''.$column.''];
		 }
	 }
	 function getSumBetween($table,$column,$column3,$where1,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) AS Total  FROM `$table` WHERE    `$column3` BETWEEN  '$where1' AND  '$where2' ") as $row) {
		 return $row['Total'];
		 }
	 }
	 function getSumBetweenOne($table,$column,$column2,$where,$column3,$where1,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) AS Total  FROM `$table` WHERE `$column2`='$where' AND  `$column3` BETWEEN  '$where1' AND  '$where2' ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getSumOpeningStock($table,$column,$column2,$where,$column3,$where1,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) AS Total  FROM `$table` WHERE `$column2`>'$where' AND  `$column3` BETWEEN  '$where1' AND  '$where2' ") as $row) {
		 return $row['Total'];
		 }
	 }
	 function getSumOpeningStockPercenter($table,$column,$column2,$where,$column3,$where1,$column4,$where2,$where3){
	foreach($this->link->query("SELECT SUM(`$column`) AS Total  FROM `$table` WHERE `$column2` >'$where' AND `$column3`='$where1' AND  `$column4` BETWEEN  '$where2' AND  '$where3' ") as $row) {
		 return $row['Total'];
		 }
	 }
	 
	  function getSumBetweenTwo($table,$column,$column2,$where,$column3,$where1,$column4,$where2,$where3){
	foreach($this->link->query("SELECT SUM(`$column`) AS Total  FROM `$table` WHERE `$column2`='$where' AND `$column3`='$where1' AND  `$column4` BETWEEN  '$where2' AND  '$where3' ") as $row) {
		 return $row['Total'];
		 }
	 }
	 function addition($table,$column,$column2,$where){
	foreach($this->link->query("SELECT SUM(`$column`) AS `sum` FROM `$table` WHERE `$column2` ='$where' ") as $row) {
		 return $row['sum'];
		 }
	 }
	  function additionLike($table,$column,$column2,$where){
	foreach($this->link->query("SELECT SUM(`$column`) AS `sum` FROM `$table` WHERE `$column2` like '$where' ") as $row) {
		 return $row['sum'];
		 }
	 }
	  function dynamicTwo($table,$dt1,$dt2,$cl1,$cl2){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`) VALUES (?,?)");
   $values = array($dt1,$dt2);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
	  function dynamicThree($table,$dt1,$dt2,$dt3,$cl1,$cl2,$cl3){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`) VALUES (?,?,?)");
   $values = array($dt1,$dt2,$dt3);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
     function dynamicFive($table,$dt1,$dt2,$dt3,$dt4,$dt5,$cl1,$cl2,$cl3,$cl4,$cl5){
   	try{
   		  $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`) VALUES (?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   		
	   	
	   } catch (PDOException $e) {
	   	
	   	echo $e->getMessage();
	   	
	   }
 
   }
   function dynamicSix($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`) VALUES (?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
      function dynamicSeven($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`) VALUES (?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   
     function dynamicEight($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`) VALUES (?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   function dynamicNine($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`) VALUES (?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
     function dynamicTen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`) VALUES (?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
    function dynamicEleven($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`) VALUES (?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
      function dynamicTwelve($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
      function dynamicThirteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13)
   {
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
     function dynamicFourteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
    function dynamicFifteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   function dynamicSixteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
      function dynamicSeventeen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   	 function generateRandomString($length = 10) {
    $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $charactersLength = strlen($characters);
    $randomString = '';
    for ($i = 0; $i < $length; $i++) {
        $randomString .= $characters[rand(0, $charactersLength - 1)];
    }
    return $randomString;
}
 function generatePIN($digits = 4){
    $i = 0; //counter
    $pin = ""; //our default pin is blank.
    while($i < $digits){
        //generate a random number between 0 and 9.
        $pin .= mt_rand(0, 9);
        $i++;
    }
    return $pin;
}
 function addOrdinalNumberSuffix($num) {
    if (!in_array(($num % 100),array(11,12,13))){
      switch ($num % 10) {
        // Handle 1st, 2nd, 3rd
        case 1:  return $num.'st';
        case 2:  return $num.'nd';
        case 3:  return $num.'rd';
      }
    }
    return $num.'th';
  }
		  function doLogin($uname,$umail,$upass,$acivestatus,$role)
	{
		try
		{
			$query = $this->link->prepare("SELECT userid,user_systemid,user_username,user_email,user_password FROM lean_users WHERE  (user_username=:uname AND user_activestatus=:activestatus AND user_activestatus=:activestatus AND user_role!=:userrole ) OR (user_email=:umail ) ");
			$query->execute(array(':uname'=>$uname, ':umail'=>$umail, ':activestatus'=>$acivestatus,':userrole'=>$role));
			$userRow=$query->fetch(PDO::FETCH_ASSOC);
			if($query->rowCount() == 1)
			{
				
				if(password_verify($upass, $userRow['user_password']))
				{
				$_SESSION['user_session'] = $userRow['user_systemid'];
						
					return true;
				}
				else
				{
						
					return false;
				}
			}
		}
		catch(PDOException $e)
		{
			echo $e->getMessage();
		}
	}
		  
		      
		function is_loggedin()
	{
		if(isset($_SESSION['user_session']))
		{
			return true;
		}
	}
		 function doLogout()
	{
		session_destroy();
		unset($_SESSION['user_session']);
		return true;
	}
	 function redirect($url)
	{
		header("Location: $url");
	}
	  function ConfirmPayment(){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,b.confirmed,a.payment_mode,b.invoice_no,a.reciept_no,a.transaction_ref,b.	expected_SP,b.master_item_id,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.tax,b.amount,b.transaction_ref,b.client_supplier_id,b.invoice_number,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2'  GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
	
	function totalDiscvalue($where){
	foreach($this->link->query("SELECT SUM(`comb_value`) AS total FROM `lean_governor_component` WHERE `governor_id`='$where'") as $row) {
		 return $row['total'];
		 }
	 }
	 function selectTwoPayInfo($where){
	 $query = $this->link->prepare("SELECT a.amount,a.payment_mode,a.payment_reference,a.reciept_no,a.entry_date,a.transaction_ref FROM lean_payments a WHERE a.transaction_ref ='$where' AND a.amount !='0' ORDER BY a.entry_date DESC");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectSalesPerItem($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.item_id,a.quantity,a.amount,a.master_id,a.renewal_history_id,c.entry_date,b.id,b.item_description,b.item_serial_no,b.Item_name,c.currentdate,c.master_item_id,c.income_expense_type,c.fitting_centre,c.client_supplier_id FROM lean_purchase_sale_per_item a, lean_item_inventory_details b, lean_master_table c WHERE a.item_id=b.id AND c.master_item_id=a.master_id AND c.income_expense_type=2 ".$where." ORDER BY c.currentdate DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function selectAllMaintenance(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.project_type,a.project_name,a.client_code,b.client_id,b.client_name,c.id AS machid,c.maint_no,c.machine_manf,c.serial_no,c.maint_period,c.machine_type,c.machine_capacity,c.machine_gas,c.machine_location,c.next_maint_date,d.project_id,d.machine_id FROM ac_projects a, ac_clients b, ac_project_machines c,ac_project_vs_machine d WHERE a.project_type=2 AND a.client_code=b.client_id AND a.id=d.project_id AND d.machine_id=c.id ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			  function selectAllRepairs(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.project_type,a.project_name,a.client_code,b.client_id,b.client_name,c.id as machid,c.maint_no,c.machine_manf,c.serial_no,c.maint_period,c.machine_type,c.machine_capacity,c.machine_gas,c.machine_location,c.next_maint_date,c.repair_checked,c.machine_status,d.project_id,d.machine_id,c.entry_date FROM ac_projects a, ac_clients b, ac_project_machines c,ac_project_vs_machine d WHERE a.project_type=3 AND a.client_code=b.client_id AND a.id=d.project_id AND d.machine_id=c.id ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function maintMachineHistory($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.machine_id,a.mach_condition,a.remarks,a.service_id,a.maintenance_no,b.id,b.jobcard_no,b.technician,b.entry_date,b.action_on_machine FROM ac_machine_maintenance a, ac_maintenance_tbl b WHERE b.id=a.service_id AND b.action_on_machine=2 AND a.machine_id='$where' ORDER BY a.maintenance_no DESC ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function repairMachineHistory($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.machine_id,a.mach_condition,a.remarks,a.service_id,a.maintenance_no,b.id,b.jobcard_no,b.technician,b.entry_date,b.action_on_machine FROM ac_machine_maintenance a, ac_maintenance_tbl b WHERE b.id=a.service_id AND b.action_on_machine=3 AND a.machine_id='$where' ORDER BY a.maintenance_no DESC ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function getValueTwoServ($table,$column,$column2,$where){
			foreach($this->link->query("SELECT MAX(`$column`) AS max  FROM `$table` WHERE `$column2` ='$where' ") as $row) {
				 return $row['max'];
				 }
			 }
			  function countMachinesUnderMaintanance(){
			foreach($this->link->query("SELECT COUNT(c.serial_no) AS machinecount FROM ac_projects a, ac_clients b, ac_project_machines c,ac_project_vs_machine d WHERE a.project_type=2 AND a.client_code=b.client_id AND a.id=d.project_id AND d.machine_id=c.id  ") as $row) {
				 return $row['machinecount'];
				 }
			 }
			 function countMachinesUnderRepair(){
			foreach($this->link->query("SELECT COUNT(c.serial_no) AS machinecount FROM ac_projects a, ac_clients b, ac_project_machines c,ac_project_vs_machine d WHERE a.project_type=3 AND a.client_code=b.client_id AND a.id=d.project_id AND d.machine_id=c.id ") as $row) {
				 return $row['machinecount'];
				 }
			 }
			 function selectOneMoreRepMach($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.project_id,a.machine_id,b.id,b.machine_type,b.machine_capacity,b.machine_gas,b.serial_no,b.machine_status FROM ac_project_vs_machine a, ac_project_machines b WHERE a.machine_id=b.id AND a.project_id = '$where' AND b.machine_status = 2 ORDER BY b.id DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 }
			  function selectOneMoreInstallMach($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.machine_manf,a.machine_type,a.machine_capacity,a.machine_gas,a.serial_no,a.machine_status,a.machine_location,b.project_id,b.machine_id FROM ac_project_machines a, ac_project_vs_machine b WHERE a.id=b.machine_id AND b.project_id = '$where' ORDER BY a.id DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 function getWarranty($id,$id2,$salestype){
	foreach($this->link->query("SELECT COUNT(b.id) AS itemcount FROM lean_master_table a, lean_purchase_sale_per_item b,lean_item_inventory_details c WHERE  a.master_item_id=b.master_id AND b.master_id=c.id   AND a.transaction_type=3 AND a.income_expense_type='$salestype' AND c.item_code='$id'  AND c.item_description='$id2' ") as $row) {
		 return $row['itemcount'];
		 }
	 }
	  function getAllWarranty($id,$id2,$salestype){
		   	try{
		   		$query = $this->link->prepare("SELECT * FROM lean_master_table a, lean_purchase_sale_per_item b,lean_item_inventory_details c WHERE  a.master_item_id=b.master_id AND b.master_id=c.id   AND a.transaction_type=3 AND a.income_expense_type='$salestype' AND c.item_code='$id'  AND c.item_description='$id2'");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			  function totalpaymentmode($mode,$center,$from,$to){
	foreach($this->link->query("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,b.confirmed,a.payment_mode,b.invoice_no,a.transaction_ref,b.expected_SP,b.master_item_id,b.entry_date,SUM(a.amount) AS totalamount,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2' AND a.payment_mode='$mode' AND b.fitting_centre='$center' AND  b.entry_date BETWEEN '$from' AND '$to'   ") as $row) {
		 return $row['totalamount'];
		 }
	 }
			 
			 
	function ConfirmPaymentPercenter($center,$from,$to){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,b.confirmed,a.payment_mode,b.invoice_no,a.reciept_no,a.transaction_ref,b.	expected_SP,b.master_item_id,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.fitting_centre='$center' AND  b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2' AND  b.entry_date BETWEEN '$from' AND '$to'  GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }	
			  function totalPaymetPerCenter($mode,$from,$to){
	foreach($this->link->query("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,b.confirmed,a.payment_mode,b.invoice_no,a.transaction_ref,b.expected_SP,b.master_item_id,b.entry_date,SUM(a.amount) AS totalamount,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2' AND a.payment_mode='$mode'  AND  b.entry_date BETWEEN '$from' AND '$to'   ") as $row) {
		 return $row['totalamount'];
		 }
	 }
	 function selectItemRecovery($centre_id){
		$query = $this->link->prepare("SELECT a.id,a.Item_name,a.item_description,a.item_in_stock,a.item_serial_no,a.lean_fitting_centre,b.Item_name as name FROM lean_item_inventory_details a,lean_item_inventory b WHERE a.item_code=b.item_code AND a.lean_fitting_centre='$centre_id' AND a.item_in_stock>0 ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			   function ConfirmPaymentDateBetween($from,$to){
		$query = $this->link->prepare("SELECT a.client_supplier_id,a.income_expense_type,SUM(a.amount) as paidamount,b.confirmed,a.payment_mode,b.invoice_no,a.reciept_no,a.transaction_ref,b.	expected_SP,b.master_item_id,b.entry_date,(b.amount - SUM(a.amount)) AS balance,b.income_expense_type,b.fitting_centre,b.amount,b.transaction_ref,b.client_supplier_id,c.governor_client_id,c.governor_client_fname,d.id,d.name FROM lean_payments a, lean_master_table b, lean_governor_clients c, fittingcenter d WHERE b.client_supplier_id=c.governor_client_id AND b.transaction_ref = a.transaction_ref AND b.fitting_centre=d.id AND b.income_expense_type = '2' AND  b.entry_date BETWEEN '$from' AND '$to'   GROUP BY a.transaction_ref ORDER BY balance DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			  function selectActiveVehiclesNewCustom($table,$column,$column2,$where){
		$query = $this->link->prepare("SELECT *  FROM `$table` WHERE `$column2` > curdate() ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function selectActiveVehiclesTypeNew($table,$column,$column2,$column3,$where){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where'  AND`$column2` >= curdate()  ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	  function selectExpiredVehiclesTypeNew($table,$column,$column2,$column3,$where){
	foreach($this->link->query("SELECT COUNT(`$column`) AS activevehicles  FROM `$table` WHERE `$column3`='$where'  AND `$column2` < curdate()  ") as $row) {
		 return $row['activevehicles'];
		 }
	 }
	 
	 function searchRecordUser($where,$where1){
		$query = $this->link->prepare("SELECT a.id,a.Item_name,a.item_description,a.item_in_stock,a.item_serial_no,a.lean_fitting_centre,b.Item_name as name FROM lean_item_inventory_details a,lean_item_inventory b WHERE ((a.item_code=b.item_code AND a.item_in_stock>0) AND (b.Item_name LIKE '$where%' OR a.item_description LIKE '$where%' OR a.item_serial_no LIKE '$where%') AND a.lean_fitting_centre='$where1') LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			  function dynamicNineteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   
   function img_resize($ini_path, $dest_path, $params = array()) {
    $width = !empty($params['width']) ? $params['width'] : null;
    $height = !empty($params['height']) ? $params['height'] : null;
    $constraint = !empty($params['constraint']) ? $params['constraint'] : false;
    $rgb = !empty($params['rgb']) ?  $params['rgb'] : 0xFFFFFF;
    $quality = !empty($params['quality']) ?  $params['quality'] : 100;
    $aspect_ratio = isset($params['aspect_ratio']) ?  $params['aspect_ratio'] : true;
    $crop = isset($params['crop']) ?  $params['crop'] : true;
 
    if (!file_exists($ini_path)) return false;
 
 
    if (!is_dir($dir=dirname($dest_path))) mkdir($dir);
 
    $img_info = getimagesize($ini_path);
    if ($img_info === false) return false;
 
    $ini_p = $img_info[0]/$img_info[1];
    if ( $constraint ) {
        $con_p = $constraint['width']/$constraint['height'];
        $calc_p = $constraint['width']/$img_info[0];
 
        if ( $ini_p < $con_p ) {
            $height = $constraint['height'];
            $width = $height*$ini_p;
        } else {
            $width = $constraint['width'];
            $height = $img_info[1]*$calc_p;
        }
    } else {
        if ( !$width && $height ) {
            $width = ($height*$img_info[0])/$img_info[1];
        } else if ( !$height && $width ) {
            $height = ($width*$img_info[1])/$img_info[0];
        } else if ( !$height && !$width ) {
            $width = $img_info[0];
            $height = $img_info[1];
        }
    }
 
    preg_match('/\.([^\.]+)$/i',basename($dest_path), $match);
    $ext = $match[1];
    $output_format = ($ext == 'jpg') ? 'jpeg' : $ext;
 
    $format = strtolower(substr($img_info['mime'], strpos($img_info['mime'], '/')+1));
    $icfunc = "imagecreatefrom" . $format;
 
    $iresfunc = "image" . $output_format;
 
    if (!function_exists($icfunc)) return false;
 
    $dst_x = $dst_y = 0;
    $src_x = $src_y = 0;
    $res_p = $width/$height;
    if ( $crop && !$constraint ) {
        $dst_w  = $width;
        $dst_h = $height;
        if ( $ini_p > $res_p ) {
            $src_h = $img_info[1];
            $src_w = $img_info[1]*$res_p;
            $src_x = ($img_info[0] >= $src_w) ? floor(($img_info[0] - $src_w) / 2) : $src_w;
        } else {
            $src_w = $img_info[0];
            $src_h = $img_info[0]/$res_p;
            $src_y    = ($img_info[1] >= $src_h) ? floor(($img_info[1] - $src_h) / 2) : $src_h;
        }
    } else {
        if ( $ini_p > $res_p ) {
            $dst_w = $width;
            $dst_h = $aspect_ratio ? floor($dst_w/$img_info[0]*$img_info[1]) : $height;
            $dst_y = $aspect_ratio ? floor(($height-$dst_h)/2) : 0;
        } else {
            $dst_h = $height;
            $dst_w = $aspect_ratio ? floor($dst_h/$img_info[1]*$img_info[0]) : $width;
            $dst_x = $aspect_ratio ? floor(($width-$dst_w)/2) : 0;
        }
        $src_w = $img_info[0];
        $src_h = $img_info[1];
    }
 
    $isrc = $icfunc($ini_path);
    $idest = imagecreatetruecolor($width, $height);
    if ( ($format == 'png' || $format == 'gif') && $output_format == $format ) {
        imagealphablending($idest, false);
        imagesavealpha($idest,true);
        imagefill($idest, 0, 0, IMG_COLOR_TRANSPARENT);
        imagealphablending($isrc, true);
        $quality = 0;
    } else {
        imagefill($idest, 0, 0, $rgb);
    }
    imagecopyresampled($idest, $isrc, $dst_x, $dst_y, $src_x, $src_y, $dst_w, $dst_h, $src_w, $src_h);
    $res = $iresfunc($idest, $dest_path, $quality);
 
    imagedestroy($isrc);
    imagedestroy($idest);
 
    return $res;
}	 function deleteAll($table){
				$query = $this->link->query("DELETE FROM `$table`  ");
	 $rowCount = $query->rowCount();
	 return $rowCount;
		}
		
		function getBurden($where,$where1){
	foreach($this->link->query("SELECT SUM(amount) AS totalamount  FROM ac_office_expense  WHERE   due_date BETWEEN   '$where' AND  '$where1'  ") as $row) {
		 return $row['totalamount'];
		 }
	 }

	 function expenseFilter($center,$from,$to){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.transaction_ref,a.client_supplier_id,a.entry_date,a.credit_term,a.amount,a.invoice_no,c.id,c.expense_name,a.sale_purchase_description,a.fitting_centre,a.currentdate FROM lean_master_table a   LEFT JOIN lean_expenses c ON a.client_supplier_id=c.id    WHERE   a.amount >0 AND a.purchasetype=2   AND a.income_expense_type=1 AND a.fitting_centre='$center' AND  a.entry_date BETWEEN '$from' AND '$to'  ORDER BY a.entry_date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function expenseFilterAll($from,$to){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.transaction_ref,a.client_supplier_id,a.entry_date,a.credit_term,a.amount,a.invoice_no,c.id,c.expense_name,a.sale_purchase_description,a.fitting_centre,a.currentdate FROM lean_master_table a   LEFT JOIN lean_expenses c ON a.client_supplier_id=c.id    WHERE   a.amount >0 AND a.purchasetype=2   AND a.income_expense_type=1 AND a.entry_date BETWEEN '$from' AND '$to' ORDER BY a.entry_date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			
			function getBurdenOne($column,$where,$where1,$where2){
	foreach($this->link->query("SELECT SUM(`$column`) AS totalamount  FROM ac_joballocation_sheet  WHERE  employee_id='$where2' AND  job_date BETWEEN   '$where' AND  '$where1'  ") as $row) {
		 return $row['totalamount'];
		 }
	 }
	  function selectIssuedMaterials($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.issue_date,b.id,b.issue_id,b.project_id,b.product_id,b.quantity_issued,c.id AS id1,c.name,c.serial_no,c.product_code,c.purchase_price,c.unit_of_measure FROM ac_issue_details a, ac_project_materials b, ac_product_list c WHERE a.id=b.issue_id AND b.product_id =c.id AND b.project_id='$where' AND b.quantity_issued >0  ORDER BY a.issue_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			function selectIssueList(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.issue_ref,a.issued_by,a.issue_description,a.issue_date,a.entry_date,a.collected_by,d.unit_cost_of_materials,d.total_cost_of_materials,c.id,c.client_code,c.project_name,d.issue_id,d.quantity_issued,d.project_id,e.product_code,e.id,e.name,e.purchase_price,f.client_id,f.client_name,f.client_level,f.town FROM ac_issue_details a, ac_projects c,ac_project_materials d, ac_product_list e, ac_clients f WHERE d.project_id =c.id AND d.issue_id=a.id AND e.id=d.product_id AND f.client_id=c.client_code ORDER BY a.issue_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			
			 function selectAllOrderBy($table,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` ORDER BY `$column1` DESC ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectOneOrderByA($table,$where,$column,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where'  ORDER BY `$column1` ASC ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   	function selectOneMoreEXP($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.expense_id,a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id WHERE c.client_type =1 AND a.project_id = '$where' GROUP BY a.expense_uniqueid ORDER BY a.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
	 function updateFourteen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function selectOneGroupBy($table,$where,$column,$column1){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' GROUP BY   `$column1`");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	 function selectAgingRecievable($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.project_id,a.invoice_id,a.withheld,a.invoice_no, a.invoice_enddate,a.invoice_date,a.invoice_creditterms,a.invoice_amount,a.invoice_no,a.uniqueid, b.id,b.project_name,b.client_code,c.client_id,c.client_code,c.client_name,c.client_type,c.town FROM ac_project_invoice a, ac_projects b, ac_clients c, ac_invoice_payment d WHERE  a.project_id=b.id AND b.client_code=c.client_id AND c.client_code='$where' GROUP BY a.uniqueid ORDER BY a.invoice_enddate ASC " );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			
			function selectProjectAgingRecievable($where,$where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.project_id,a.invoice_id,a.withheld,a.invoice_no, a.invoice_enddate,a.invoice_date,a.invoice_creditterms,a.invoice_amount,a.invoice_no,a.uniqueid,d.uniqueid,(a.invoice_amount - SUM(d.invoice_paid_amount)) AS balance, b.id,b.project_name,b.client_code,c.client_id,c.client_code,c.client_name,c.client_type,c.town FROM ac_project_invoice a, ac_projects b, ac_clients c, ac_invoice_payment d WHERE a.uniqueid=d.uniqueid AND a.project_id=b.id AND b.client_code=c.client_id AND c.client_code='$where' AND c.client_id='$where1' AND b.id='$where2' GROUP BY a.uniqueid ORDER BY a.invoice_enddate ASC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			
			function selectBranchAgingRecievable($where,$where1){
		   	try{
		   		$query = $this->link->prepare("SELECT a.project_id,a.invoice_id,a.withheld,a.invoice_no, a.invoice_enddate,a.invoice_date,a.invoice_creditterms,a.invoice_amount,a.invoice_no,a.uniqueid,d.uniqueid,(a.invoice_amount - SUM(d.invoice_paid_amount)) AS balance, b.id,b.project_name,b.client_code,c.client_id,c.client_code,c.client_name,c.client_type,c.town FROM ac_project_invoice a, ac_projects b, ac_clients c, ac_invoice_payment d WHERE a.uniqueid=d.uniqueid AND a.project_id=b.id AND b.client_code=c.client_id AND c.client_code='$where' AND c.client_id='$where1' GROUP BY a.uniqueid ORDER BY a.invoice_enddate ASC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function searchProductList($where){
		$query = $this->link->prepare("SELECT * FROM ac_product_list  WHERE serial_no LIKE '$where%' OR name LIKE '%$where%' OR product_code LIKE '%$where%'  GROUP BY name,unit_of_measure,unit_value	 LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			
			
			
			function selectMainClientStatements($where,$where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.pay_mode,a.receipt, a.payment_ref,a.action_type, a.transaction_id,a.client_id,a.client_master_id,a.action_amount,a.withheld,a.transaction_date,a.invoice_ref,b.client_id,b.client_code,b.client_name,c.id,c.client_code,c.project_name  FROM  ac_client_transaction  a LEFT JOIN ac_clients b  ON a.client_id = b.client_id  LEFT JOIN ac_projects c ON b.client_id = c.client_code   WHERE  a.client_id=b.client_id AND c.client_code=b.client_id AND a.action_amount >0 AND  a.client_master_id ='$where' AND transaction_date BETWEEN '$where1' AND '$where2' GROUP BY a.transaction_id" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
	 		
	 		
	 	function selectBranchStatements($where,$where3,$where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.pay_mode,a.receipt, a.payment_ref,a.action_type,a.transaction_id,a.client_id,a.client_master_id,a.action_amount,a.withheld,a.transaction_date,a.invoice_ref,b.client_id,b.client_code,b.client_name,c.id,c.client_code,c.project_name  FROM ac_client_transaction a,ac_clients b,ac_projects c  WHERE a.client_id=b.client_id AND c.client_code=b.client_id AND a.action_amount >0 AND  a.client_master_id ='$where' AND a.client_id ='$where3' AND transaction_date BETWEEN   '$where1' AND  '$where2' GROUP BY a.transaction_id");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function selectProjectStatements($where,$where3,$where4,$where1,$where2){
		   	try{
		   		$query = $this->link->prepare("SELECT a.pay_mode,a.receipt, a.payment_ref,a.action_type,a.transaction_id,a.client_id,a.client_master_id,a.action_amount,a.withheld,a.transaction_date,a.invoice_ref,b.client_id,b.client_code,b.client_name,c.id,c.client_code,c.project_name  FROM ac_client_transaction a,ac_clients b,ac_projects c  WHERE a.client_id=b.client_id AND c.client_code=b.client_id AND a.action_amount >0 AND a.client_master_id ='$where' AND a.client_id ='$where3' AND c.id ='$where4' AND transaction_date BETWEEN   '$where1' AND  '$where2' GROUP BY a.transaction_id");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function getLoanBalance($where){
	foreach($this->link->query("SELECT SUM(b.amount_paid) AS Total  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE  a.loan_type='$where' AND a.cleared_status=0 ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getLoanBalanceUser($where,$where1){
	foreach($this->link->query("SELECT a.id, SUM(b.amount_paid) AS Total  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND a.cleared_status=0  GROUP BY a.id  ") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getLoanTotalToDate($where,$where1){
	foreach($this->link->query("SELECT a.id, SUM(b.amount_paid) AS Total  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND a.cleared_status=0 GROUP BY a.id  ") as $row) {
		 return $row['Total'];
		 }
	 }
	 
	  function getLoanId($where,$where1,$where2,$where3){
	foreach($this->link->query("SELECT b.id  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND b.month_id='$where2' AND b.year_id='$where3' AND a.cleared_status=0 GROUP BY a.id  ") as $row) {
		 return $row['id'];
		 }
	 }
	 
	 function getTotalLoanToDate($where,$where1,$where2,$where3,$where4){
	foreach($this->link->query("SELECT a.id, SUM(b.amount_to_pay) AS Total  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND b.month_id='$where2' AND b.year_id='$where3' AND b.id >='$where4' AND a.cleared_status=0 GROUP BY a.id  ") as $row) {
		 return $row['Total'];
		 }
	 }
	 function getTotalLoanPaidToDate($where,$where1,$where2,$where3,$where4){
	foreach($this->link->query("SELECT a.id, SUM(b.amount_paid) AS Total  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND b.month_id='$where2' AND b.year_id='$where3' AND b.id >='$where4' AND a.cleared_status=0 GROUP BY a.id  ") as $row) {
		 return $row['Total'];
		 }
	 }
	   
		function dynamicOne($table,$dt1,$cl1){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`) VALUES (?)");
   $values = array($dt1);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }	 
    
		 function updateEleven($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 
		 
   function getLoanPaid($where,$where1,$where2,$where3){
	foreach($this->link->query("SELECT b.amount_paid  FROM ac_loans_applied a  INNER JOIN ac_loan_distribution b ON a.id=b.loan_id WHERE a.loan_type='$where' AND a.employeeid='$where1' AND b.month_id='$where2' AND b.year_id='$where3' AND a.cleared_status=0 GROUP BY a.id  ") as $row) {
		 return $row['amount_paid'];
		 }
	 }
	 function dynamicTwentyOne($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20,$cl21){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`,`$cl21`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   function dynamicTwentyTwo($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20,$cl21,$cl22){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`,`$cl21`,`$cl22`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   
   function updateTwentyTwo($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$edit20,$colmn20,$edit21,$colmn21,$edit22,$colmn22,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19',`$colmn20` ='$edit20',`$colmn21` ='$edit21',`$colmn22` ='$edit22' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function updateTwentyOne($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$edit20,$colmn20,$edit21,$colmn21,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19',`$colmn20` ='$edit20',`$colmn21` ='$edit21' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 } 
		function deleteFunction($table, $id, $where){
				$query = $this->link->query("DELETE FROM `$table` WHERE `$where` = '$id' ");
		 $rowCount = $query->rowCount();
		 return $rowCount;
			}
			function selectACSalesPerItem(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.transaction_id,a.item_id,a.qnty,a.amount,b.id,b.name,b.product_code,b.serial_no,c.id,c.client_id,c.sale_date,d.client_id,d.client_name FROM ac_sale_per_item a, ac_product_list b, ac_sales c,ac_clients d WHERE a.transaction_id=c.id AND a.item_id=b.id AND c.client_id=d.client_id GROUP BY a.id ORDER BY c.sale_date DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function selectAssetAssignment(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id AS id1,a.employee_id,a.asset_id,a.qnty_assigned,a.issue_date,a.entry_date,a.return_replace_date,b.id,b.employeeno,a.qnty_lost,b.surname,b.othernames,c.id,c.asset_name,c.asset_category_id,c.asset_sno,c.asset_status,d.id,d.category_name FROM ac_asset_assignment a, employeelist b, ac_assets c,ac_assets_category d WHERE a.employee_id=b.id AND c.id=a.asset_id AND c.asset_category_id=d.id   ORDER BY a.issue_date ASC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}		
			 function updateSeventeen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 function selectAllWhere($table,$where){
		$query = $this->link->prepare("SELECT * FROM `$table` WHERE expense_incurred='0'  ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function updateThirteen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		   function dynamicEighteen($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   function searchJobCard($projectid,$where){
		$query = $this->link->prepare("SELECT * FROM ac_maintenance_tbl WHERE 	project_id='$projectid'  AND jobcard_no LIKE '$where%' LIMIT 10  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function selectAllOrderByA($table,$column){
		$query = $this->link->prepare("SELECT * FROM `$table` ORDER BY `$column` ASC ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
		   }
		   function selectAllJobAllocation($table,$where){
		$query = $this->link->prepare("SELECT * FROM `$table` WHERE complete='1'  ".$where." ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		    function selectOneMorePAYWhere($where){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id  WHERE a.burden_material='0' AND   c.client_type =1 ".$where." GROUP BY a.expense_uniqueid   ORDER BY a.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function updateTwenty($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$edit20,$colmn20,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19',`$colmn20` ='$edit20' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
	 function getValueGreaterThan($table,$column,$column2,$where,$column3,$where1){
	foreach($this->link->query("SELECT `$column`  FROM `$table` WHERE `$column2` ='$where' AND $column3 > $where1 ") as $row) {
		 return $row[''.$column.''];
		 }
	 }
	  function updateSixteen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 
		 function selectOneMoreJC($where){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.machine_id,a.mach_condition,a.remarks,b.id,b.serial_no,b.machine_capacity,b.machine_gas,b.machine_type,b.maint_period,b.maint_no,b.machine_uniqueid,b.machine_manf,b.machine_location,c.project_id,c.jobcard_no,c.technician,c.supervisor,c.sup_remarks,c.entry_date,d.problem,d.recommendation,d.cause  FROM ac_machine_maintenance a, ac_project_machines b,ac_maintenance_tbl c,ac_machine_fault_info d WHERE a.machine_id=b.id AND a.service_id=c.id  AND c.id=d.service_id  AND b.id=d.machine_id AND  c.project_id = '$where' ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 
			 function selectOneMoreJCReport(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id,a.machine_id,a.mach_condition,a.remarks,b.id,b.serial_no,b.machine_capacity,b.machine_gas,b.machine_type,b.maint_period,b.maint_no,b.machine_uniqueid,b.machine_manf,b.machine_location,c.project_id,c.jobcard_no,c.technician,c.supervisor,c.sup_remarks,c.entry_date,d.problem,d.recommendation,d.cause  FROM ac_machine_maintenance a, ac_project_machines b,ac_maintenance_tbl c,ac_machine_fault_info d WHERE a.machine_id=b.id AND a.service_id=c.id  AND c.id=d.service_id  AND b.id=d.machine_id  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			 }
			 
			  function selectExpNew($where){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,a.expense_tax,b.id,a.burden_material,b.category_name,c.client_id,c.client_name,c.client_type FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id  WHERE    c.client_type =1 AND a.burden_material =0   AND a.project_id = '$where' GROUP BY a.expense_uniqueid ORDER BY a.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function deleteTrensaction($table, $column,$id, $column2,$id2){
				$query = $this->link->query("DELETE FROM `$table` WHERE `$column` = '$id' AND `$column2` > '$id2' ");
	 $rowCount = $query->rowCount();
	 return $rowCount;
		}
		
		function sumJobCards($projectid){
		$query = $this->link->prepare("SELECT jobcard_no,SUM(`charged_amount`) AS totalamount FROM ac_maintenance_tbl  WHERE 	project_id='$projectid' AND `invoiced`='0'  GROUP BY  jobcard_no   ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }	
	function updateTen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$where){
$query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 	function selectOneNot($table,$where,$column){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE  `$column` !='$where' ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectOneMoreNot($table,$where,$column,$where1,$column1){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT * FROM `$table` WHERE `$column` = :name  AND `$column1` != :nametwo  ");
		   $query->execute(['name' => $where,'nametwo' => $where1]);
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
		   }
		   
		    function checkOverlaps($where,$where1,$where2){
	foreach($this->link->query("SELECT `id`  FROM `ac_schedule` WHERE `fromdate` < '$where'
      AND `todate`   > '$where1' AND `project_id`='$where2'  ") as $row) {
		 return $row['id'];
		 }
	 }
	 
	 
	 function countShedule($where){
	foreach($this->link->query("SELECT SUM(a.total_maintanance) AS total   FROM ac_project_machines a  INNER JOIN ac_project_vs_machine b ON a.id=b.machine_id WHERE b.project_id='$where'  ") as $row) {
		 return $row['total'];
		 }
	 }
	 function removerWhereTwo($table,$column,$id,$column2,$id2){
	 $query = $this->link->query("DELETE FROM `$table` WHERE `$column` = '$id' AND `$column2` = '$id2'    ");
	 $rowCount = $query->rowCount();
	 return $rowCount;
	 }
	  function dynamicTwentyFive($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23,$dt24,$dt25,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20,$cl21,$cl22,$cl23,$cl24,$cl25){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`,`$cl21`,`$cl22`,`$cl23`,`$cl24`,`$cl25`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23,$dt24,$dt25);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
	 
	 function upcomingMaintananceNextSevenDays(){
	foreach($this->link->query("SELECT COUNT(b.machine_id) AS countrecord  FROM ac_projects  a INNER JOIN ac_project_vs_machine b ON a.id = b.project_id INNER JOIN ac_project_machines c ON  b.machine_id=c.id WHERE a.project_type='2' AND  ( c.next_maint_date < curdate() AND  c.next_maint_date <  DATE_ADD(NOW(),INTERVAL 7 DAY))   ") as $row) {
		 return $row['countrecord'];
		 }
	 }
	 
	 
	  function countInvoiceDue(){
	  	$m=0;
	foreach($this->link->query("SELECT COUNT(a.invoice_id) AS total, a.invoice_enddate,a.invoice_amount,SUM(b.invoice_paid_amount),a.invoice_amount-SUM(ifnull(b.invoice_paid_amount, 0)) AS balance   FROM ac_project_invoice a  LEFT JOIN ac_invoice_payment b ON a.uniqueid=b.uniqueid  WHERE ( a.invoice_enddate < curdate() AND  a.invoice_enddate <  DATE_ADD(NOW(),INTERVAL 7 DAY))  GROUP BY a.uniqueid HAVING balance >0 ") as $row) {
		$m++;
		
		 
		 }
		 return $m;
	 }
	 
	 function countPaymentDue(){
	  	$m=0;
	foreach($this->link->query("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount,a.exp_invoice_amount-SUM(ifnull(d.paid_amount, 0)) AS balance   FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id  WHERE a.due_date > curdate() AND a.due_date <  DATE_ADD(NOW(),INTERVAL 7 DAY) AND    c.client_type =1  GROUP BY a.expense_uniqueid   HAVING balance >0 ORDER BY a.date DESC ") as $row) {
		$m++;
		
		 
		 }
		 return $m;
	 }
	 function selectInvoiceDue(){
	 $query = $this->link->prepare("SELECT a.docno ,a.invoice_date, a.invoice_no,a.withheld,a.uniqueid,a.invoice_enddate,a.project_id,a.invoice_enddate,a.invoice_amount,SUM(b.invoice_paid_amount),a.invoice_amount-SUM(ifnull(b.invoice_paid_amount, 0)) AS balance   FROM ac_project_invoice a  LEFT JOIN ac_invoice_payment b ON a.uniqueid=b.uniqueid  WHERE  ( a.invoice_enddate < curdate() AND  a.invoice_enddate <  DATE_ADD(NOW(),INTERVAL 7 DAY))   GROUP BY a.uniqueid HAVING balance >0 ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectPaymentDue(){
	 $query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,SUM(d.paid_amount) AS paid_amount,a.exp_invoice_amount-SUM(ifnull(d.paid_amount, 0)) AS balance   FROM project_expenses a LEFT JOIN project_exp_category b ON a.expense_category_id=b.id  LEFT JOIN ac_clients c ON a.supplier_id=c.client_id LEFT JOIN ac_make_payment d ON a.expense_uniqueid=d.expense_id  WHERE a.due_date > curdate() AND a.due_date <  DATE_ADD(NOW(),INTERVAL 7 DAY) AND    c.client_type =1  GROUP BY a.expense_uniqueid   HAVING balance >0 ORDER BY a.date DESC ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	  function selectOneMorePO($where){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT a.shippedto, a.lpo_term,a.lpo_no,a.lpo_enddate,a.lpo_amount,a.lpo_tax,a.lpo_date,a.subTotal,c.taxpin,c.address,c.town,c.client_name,c.client_phone1,c.client_email,a.doneby FROM ac_lpo a,ac_clients c WHERE a.supplierid=c.client_id   AND a.uniqueid = '$where'  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 } 
			   function selectOneMoreCN($where){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT  a.invoice_id,a.c_no,a.amount,a.tax,a.cr_date,a.sub_total,c.taxpin,c.address,c.town,c.client_name,c.client_phone1,c.client_email FROM ac_creditnote a,ac_clients c WHERE a.client_id=c.client_id   AND a.refno= '$where'  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
			 } 
			 function getSumOneInvoicebfall($where,$where1,$where2){
	foreach($this->link->query("select SUM(amount) as Total from ( select distinct a.client_master_id,a.action_amount as amount from ac_client_transaction a,ac_clients b,ac_projects c WHERE a.client_id=b.client_id AND c.client_code=b.client_id AND a.client_master_id ='$where' AND transaction_date < '$where1' AND action_type ='$where2' ) t1") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getSumOneAllProjecsbf($where,$where1,$where2,$where3){
	foreach($this->link->query("select SUM(amount) as Total from ( select distinct a.client_master_id,a.action_amount as amount from ac_client_transaction a,ac_clients b,ac_projects c WHERE a.client_id=b.client_id AND c.client_code=b.client_id AND a.client_master_id ='$where' AND a.client_id ='$where3'  AND transaction_date < '$where1' AND action_type ='$where2' ) t1") as $row) {
		 return $row['Total'];
		 }
	 }
	  function getSumOneAllPerProjecsbf($where,$where1,$where2,$where3,$where4){
	foreach($this->link->query("select SUM(amount) as Total from ( select distinct a.client_master_id,a.action_amount as amount from ac_client_transaction a,ac_clients b,ac_projects c WHERE a.client_id=b.client_id AND c.client_code=b.client_id AND a.client_master_id ='$where' AND a.client_id ='$where3'  AND c.id ='$where4'  AND transaction_date < '$where1' AND action_type ='$where2' ) t1") as $row) {
		 return $row['Total'];
		 }
	 }
	 function searchQt($where){
		$query = $this->link->prepare("SELECT a.purchase_price,a.pvalue,a.sub_category,a.qnty AS qty_available,a.selling_price,a.id,a.name,a.unit_of_measure,a.product_code,b.name as cname FROM ac_product_list a,ac_products_category b WHERE ((a.category=b.id OR a.sub_category=b.id ) AND (b.name LIKE '%$where%' OR a.name LIKE '%$where%' OR a.product_code LIKE '%$where%')) LIMIT 15  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 
			 function dynamicTwenty($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   } 
	 
	function updateNineteen($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 } 
		 
		 
		  function upcomingMaintananceNextSevenDaysAll(){
		$query = $this->link->prepare("SELECT *  FROM ac_projects  a INNER JOIN ac_project_vs_machine b ON a.id = b.project_id INNER JOIN ac_project_machines c ON  b.machine_id=c.id WHERE a.project_type='2' AND  ( c.next_maint_date < curdate() AND  c.next_maint_date <  DATE_ADD(NOW(),INTERVAL 7 DAY))  ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }
		   
		   function selectAssetAssignmentLean(){
		   	try{
		   		$query = $this->link->prepare("SELECT a.id AS id1,a.employee_id,a.asset_id,a.qnty_assigned,a.issue_date,a.entry_date,a.return_replace_date,b.id,b.employeeno,a.qnty_lost,b.surname,b.othernames,c.id,c.asset_name,c.asset_category_id,c.asset_sno,c.asset_status,d.id,d.category_name FROM lean_asset_assignment a, lean_employeelist b, lean_assets c,ac_assets_category d WHERE a.employee_id=b.id AND c.id=a.asset_id AND c.asset_category_id=d.id   ORDER BY a.issue_date ASC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetchAll();	
			 }else{
			   $result = 0;	 
			 }
			 return $result;	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			function dynamicTwentyThree($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20,$cl21,$cl22,$cl23){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`,`$cl21`,`$cl22`,`$cl23`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
    function searchReqProduct($where){
		$query = $this->link->prepare("SELECT a.purchase_price, a.id,a.name,a.product_code,a.qnty,a.serial_no,a.unit_of_measure FROM ac_product_list a WHERE (a.name LIKE '%$where%' OR a.product_code LIKE '%$where%' OR a.serial_no LIKE '%$where%') AND a.qnty>0 LIMIT 10 ");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }else{
			   $result = 0;	 
			 }
			 return $result;
			 }
			 function dynamicTwentyFour($table,$dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23,$dt24,$cl1,$cl2,$cl3,$cl4,$cl5,$cl6,$cl7,$cl8,$cl9,$cl10,$cl11,$cl12,$cl13,$cl14,$cl15,$cl16,$cl17,$cl18,$cl19,$cl20,$cl21,$cl22,$cl23,$cl24){
   $query = $this->link->prepare("INSERT INTO `$table` (`$cl1`,`$cl2`,`$cl3`,`$cl4`,`$cl5`,`$cl6`,`$cl7`,`$cl8`,`$cl9`,`$cl10`,`$cl11`,`$cl12`,`$cl13`,`$cl14`,`$cl15`,`$cl16`,`$cl17`,`$cl18`,`$cl19`,`$cl20`,`$cl21`,`$cl22`,`$cl23`,`$cl24`) VALUES (?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?,?)");
   $values = array($dt1,$dt2,$dt3,$dt4,$dt5,$dt6,$dt7,$dt8,$dt9,$dt10,$dt11,$dt12,$dt13,$dt14,$dt15,$dt16,$dt17,$dt18,$dt19,$dt20,$dt21,$dt22,$dt23,$dt24);
   $query->execute($values); 
   $insertedId = $this->link->lastInsertId();
   return $insertedId;
   }
   function updateTwentyThree($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$edit20,$colmn20,$edit21,$colmn21,$edit22,$colmn22,$edit23,$colmn23,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19',`$colmn20` ='$edit20',`$colmn21` ='$edit21',`$colmn22` ='$edit22',`$colmn23` ='$edit23' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }
		 
		   function selectWithHeldD(){
	 $query = $this->link->prepare("SELECT  *  FROM `ac_taxaccount` WHERE (tax_amount>0 AND `tax_id` ='2') OR (tax_amount>0 AND `tax_id` ='3') OR (tax_amount>0 AND `tax_id` ='4')  ORDER BY `date` DESC");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	      function selectWithHeldDWhere($from,$to){
	 $query = $this->link->prepare("SELECT  *  FROM `ac_taxaccount` WHERE (tax_amount>0 AND `tax_id` ='2' AND `date` BETWEEN '$from' AND '$to') OR (tax_amount>0 AND `tax_id` ='3' AND `date` BETWEEN '$from' AND '$to') OR (tax_amount>0 AND `tax_id` ='4' AND `date` BETWEEN '$from' AND '$to') OR (tax_amount>0 AND `tax_id` ='1' AND `date` BETWEEN '$from' AND '$to')  ORDER BY `date` DESC");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	 
	   function getInvoices($where,$dateFrom,$dateTo){
	 $query = $this->link->prepare("select ac_project_invoice.invoice_id,ac_project_invoice.invoice_no,ac_project_invoice.invoice_amount,ac_project_invoice.invoice_date from ac_project_invoice,ac_projects WHERE ac_projects.id=ac_project_invoice.project_id AND ac_projects.client_code='$where' AND ac_project_invoice.invoice_date BETWEEN '$dateFrom' AND '$dateTo'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	     function getInvoicesPaid($where,$dateFrom,$dateTo){
	 $query = $this->link->prepare("select ac_invoice_payment.invoice_paid_amount,ac_invoice_payment.invoice_pay_date,ac_invoice_payment.invoice_pay_mtd,ac_invoice_payment.invoice_pay_refno from ac_project_invoice,ac_projects,ac_invoice_payment WHERE ac_projects.id=ac_project_invoice.project_id AND ac_invoice_payment.uniqueid=ac_project_invoice.uniqueid AND  ac_projects.client_code='$where' AND ac_invoice_payment.invoice_pay_date BETWEEN '$dateFrom' AND '$dateTo'  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function getInvoicesWht($where,$dateFrom,$dateTo){
	 $query = $this->link->prepare("select ac_taxaccount.date,ac_taxaccount.inv_no,ac_taxaccount.tax_amount from ac_project_invoice,ac_projects,ac_taxaccount WHERE  ( ac_projects.id=ac_project_invoice.project_id AND ac_taxaccount.transaction_ref=ac_project_invoice.uniqueid AND ac_projects.client_code='$where' AND ac_taxaccount.tax_amount>0 AND ac_taxaccount.tax_id ='2' AND ac_taxaccount.date BETWEEN '$dateFrom' AND '$dateTo' ) OR ( ac_projects.id=ac_project_invoice.project_id AND ac_taxaccount.transaction_ref=ac_project_invoice.uniqueid AND ac_projects.client_code='$where' AND ac_taxaccount.tax_amount>0 AND ac_taxaccount.tax_id ='3' AND ac_taxaccount.date BETWEEN '$dateFrom' AND '$dateTo') OR (ac_projects.id=ac_project_invoice.project_id AND ac_taxaccount.transaction_ref=ac_project_invoice.uniqueid AND  ac_projects.client_code='$where' AND ac_taxaccount.tax_amount>0 AND ac_taxaccount.tax_id ='4' AND ac_taxaccount.date BETWEEN '$dateFrom' AND '$dateTo') ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	   function getInvoicesPaidKRA($where,$dateFrom,$dateTo){
	 $query = $this->link->prepare("select ac_invoice_payment.invoice_paid_amount,ac_invoice_payment.remark,ac_invoice_payment.invoice_pay_date from ac_project_invoice,ac_projects,ac_invoice_payment WHERE ac_projects.id=ac_project_invoice.project_id AND ac_invoice_payment.uniqueid=ac_project_invoice.uniqueid AND  ac_projects.client_code='$where' AND ac_invoice_payment.invoice_pay_date BETWEEN '$dateFrom' AND '$dateTo' AND ac_invoice_payment.tax_paymant_ref >0  ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	    function selectCustom($table,$where){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE   1  ".$where."   ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }
		 else
		 {
		   $result = 0;	 
		 }
		 return $result;
	   }
	   function selectOneCustome($table,$where,$column,$where2){
	 $query = $this->link->prepare("SELECT  *  FROM `$table` WHERE `$column` ='$where' ".$where2." ");
	   $query->execute();
	   $rowCount = $query->rowCount();
		  if($rowCount >= 1)
		 {
			$result = $query->fetchAll(); 
		 }else{
		   $result = 0;	 
		 }
		 return $result;
	   }
	   
	   function sendClintSms($client,$message){
	   	
	   	if($client=="All"){
	   		
	   		 try{
   	
   	     $this->link->beginTransaction();

		$query = $this->link->prepare("SELECT * FROM `smstest` ");
		$query->execute();
		$rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$results = $query->fetchAll(); 
				
				foreach($results as $result){
					
					 
				return $this-> sendSms($message,$result['phone']);
				}
			 }
			 else
			 {
			  return 0;
			 }
			


   	       $this->link->commit();
			 }
			 catch(PDOException $e){
				 return  $e->getMessage();
				 $this->link->rollBack();
				 }
			
		}else if($client=="clients"){
			
		}else if($client=="prospects"){
			
			
		}
	   	


}


function sendSms($message,$recipient)
{


$phone =$recipient;

//Get an access token
  $token = json_decode( $this->generateAccessToken(), true)['access_token'];

  //set the senderID
  $senderID = "TOPGEARS";

  //set the api key
  $api_key = 'trCtNINcQxcIyj21Jk0rmRfvcqUuPtvky5GAIBdN2LiQdwkhEI6DCvc7E1Fe';

  //Send the message and dump the response
  $resultVals = json_decode( $this->sendPostSms($token, $senderID, $phone, $message, $api_key),true);
  
  return $resultVals;
  
  

  
  
 

}

//generating an access token - this is used to authenticate SMS requests
function generateAccessToken() 
{
  $postData = array(
      'client_id' => '7c5700e0-a3d2-11ea-b040-3d171069dcb6',
      'client_secret' => 'GGFC5BNKplC7qX8jvdKJFztkm9zX6ilPhXJHhSlS',
      'grant_type' => 'client_credentials'
  );

  $requestBody = json_encode($postData);

   // Setup cURL
  $ch = curl_init('https://account.mobilesasa.com/oauth/token');
  curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
          'Content-Type: application/json'
      ),
      CURLOPT_POSTFIELDS => $requestBody
  ));

  // Send the request
  $response = curl_exec($ch);

  return $response;
}

  function updateTwentyFive($id,$table,$edit,$colmn,$edit2,$colmn2,$edit3,$colmn3,$edit4,$colmn4,$edit5,$colmn5,$edit6,$colmn6,$edit7,$colmn7,$edit8,$colmn8,$edit9,$colmn9,$edit10,$colmn10,$edit11,$colmn11,$edit12,$colmn12,$edit13,$colmn13,$edit14,$colmn14,$edit15,$colmn15,$edit16,$colmn16,$edit17,$colmn17,$edit18,$colmn18,$edit19,$colmn19,$edit20,$colmn20,$edit21,$colmn21,$edit22,$colmn22,$edit23,$colmn23,$edit24,$colmn24,$edit25,$colmn25,$where){
         $query = $this->link->query("UPDATE `$table` SET `$colmn` ='$edit',`$colmn2` ='$edit2',`$colmn3` ='$edit3',`$colmn4` ='$edit4',`$colmn5` ='$edit5',`$colmn6` ='$edit6',`$colmn7` ='$edit7',`$colmn8` ='$edit8',`$colmn9` ='$edit9',`$colmn10` ='$edit10',`$colmn11` ='$edit11',`$colmn12` ='$edit12',`$colmn13` ='$edit13',`$colmn14` ='$edit14',`$colmn15` ='$edit15',`$colmn16` ='$edit16',`$colmn17` ='$edit17',`$colmn18` ='$edit18',`$colmn19` ='$edit19',`$colmn20` ='$edit20',`$colmn21` ='$edit21',`$colmn22` ='$edit22',`$colmn23` ='$edit23',`$colmn24` ='$edit24',`$colmn25` ='$edit25' WHERE `$where` = '$id'");
		$rowCount = $query->rowCount();
		return $rowCount;
		 }


//Send SMS using Post
function sendPostSms($token, $senderID, $phone, $message, $api_key)
{

$accessToken = "Bearer ".$token;//this is the token generated by generateAccessToken()  

  $postData = array(
      'senderID' => $senderID,
      'phone' => $phone,
      'message' => $message,
      'api_key' => $api_key
  );

  $requestBody = json_encode($postData);

  // Setup cURL
  $ch = curl_init('https://account.mobilesasa.com/api/post-sms');
  curl_setopt_array($ch, array(
      CURLOPT_POST => TRUE,
      CURLOPT_RETURNTRANSFER => TRUE,
      CURLOPT_HTTPHEADER => array(
          'Accept: application/json',
          'Authorization: '.$accessToken
      ),
      CURLOPT_POSTFIELDS => $requestBody
  ));

  // Send the request
  $response = curl_exec($ch);
  $someArray = json_decode($response);
  
  if(($someArray->code)>0){
  	
  	  $phonenumber = $phone;
  	  $arrayone=$someArray->messageID;
        $messageId = $arrayone[0];
        $actualcost=($someArray->smsCost);
        $date=date('Y-m-d H:i:s');
        $status=$someArray->status;
        
        $this->dynamicSix('creditused',$phonenumber,$messageId,$actualcost,$date,$message,$status,'phone','description','amount','datet','message','status');

       
       return 1;
  	
  	
  	
  }else{
  	
  	  return 0; 
  	
  }
  
  
  

}

 function selectTwoMore($table,$where,$column,$where2,$column2){
		   	try{
		   		
		   		$query = $this->link->prepare("SELECT * FROM `$table` WHERE `$column` = :name AND `$column2` = :name2  ");
		   $query->execute(['name' => $where,'name2' => $where2]);
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
			 	$result=$query->fetch(PDO::FETCH_ASSOC);
			 	
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   		
			   	
			   } catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
		
		   }

		   function getSoldItems($where){
	foreach($this->link->query("SELECT GROUP_CONCAT(lean_item_inventory_details.item_description) AS concat FROM lean_master_table LEFT JOIN lean_purchase_sale_per_item ON lean_master_table.master_item_id=lean_purchase_sale_per_item.master_id LEFT JOIN lean_item_inventory_details ON lean_item_inventory_details.id=lean_purchase_sale_per_item.item_id  WHERE lean_master_table.transaction_ref='$where' ") as $row) {
		 return $row['concat'];
		 }
	 }

	function paymentjoin(){
		$query = $this->link->prepare("SELECT i.*,p.invoice_paid_amount,p.invoice_pay_refno,p.invoice_pay_date,p.remark,p.invoice_pay_mtd,j.project_name,c.client_name FROM ac_invoice_payment p LEFT JOIN ac_project_invoice i ON p.uniqueid=i. uniqueid LEFT JOIN ac_projects j  ON i.project_id=j.id LEFT JOIN ac_clients c ON j.client_code=c.client_id     ORDER BY p.invoice_pay_date  DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   }




		    function paymentoutjoin(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.date,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type,d.paid_amount,d.payment_method,d.date AS pdate FROM ac_make_payment d  LEFT JOIN project_expenses a  ON a.expense_uniqueid=d.expense_id   LEFT JOIN project_exp_category b ON a.expense_category_id=b.id LEFT JOIN ac_clients c ON a.supplier_id=c.client_id  WHERE a.burden_material='0' AND   c.client_type =1  ORDER BY d.date DESC" );
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}

			  function costofgoodsjoin(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.expense_uniqueid,a.project_id,a.expense_category_id,a.supplier_id,a.invoice_no,a.expense_description,a.due_date,a.exp_invoice_amount,b.id,b.category_name,c.client_id,c.client_name,c.client_type FROM  project_expenses a   LEFT JOIN project_exp_category b ON a.expense_category_id=b.id LEFT JOIN ac_clients c ON a.supplier_id=c.client_id  WHERE a.burden_material='0' AND   c.client_type =1  ORDER BY a.due_date DESC" );
		       $query->execute();
		        $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}

			 function officeexpensejoin(){
		   	try{
		   		$query = $this->link->prepare("SELECT  a.description,a.date_incurred,a.amount,b.category_name,a.supplier FROM  ac_office_expense a   LEFT JOIN project_exp_category b ON a.exp_category=b.id    ORDER BY a.date_incurred DESC" );
		       $query->execute();
		        $rowCount = $query->rowCount();
			  if($rowCount >= 1){
			 	$result=$query->fetchAll();
			 	
			 }
			 else{
			   $result = 0;	 
			 }
			 return $result;
		   		} catch (PDOException $e) {
			   	echo $e->getMessage();
			   }
			}
			
				function getInventoryRecord(){
		$query = $this->link->prepare("SELECT * FROM   `lean_purchase_sale_per_item` WHERE  DATE(enty_date) <= '2020-12-31' AND `item_id`>0 AND `renewal_history_id`='0' ORDER BY `enty_date` DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		   
		   	function getInventoryRecordSales(){
		$query = $this->link->prepare("SELECT * FROM   `lean_purchase_sale_per_item` WHERE  DATE(enty_date) <= '2020-12-31' AND `item_id`>0 AND `renewal_history_id`>0 ORDER BY `enty_date` DESC");
		   $query->execute();
		   $rowCount = $query->rowCount();
			  if($rowCount >= 1)
			 {
				$result = $query->fetchAll(); 
			 }
			 else
			 {
			   $result = 0;	 
			 }
			 return $result;
		   } 
		   
		   
		    function getValueDate($column,$where){
	foreach($this->link->query("SELECT `$column`  FROM `lean_purchase_sale_per_item` WHERE `item_id`='$where' AND  DATE(enty_date) <= '2020-12-31' AND `item_id`>0 AND `renewal_history_id`!='0'  ") as $row) {
		 return $row[''.$column.''];
		 }
	 }






					   
} 
?>

  

