<?php
ob_start();
session_start();
include_once('../core/class.manageData.php');

$m_init = new ManageData;
//$gov_name = $m_init->getInventoryRecord();
/*foreach($gov_name as $row){
	$gov_name = $m_init->getValueDate('item_id', $row['item_id']);
	echo $gov_name;
}*/

?>