<?php
 class ManageDatabase{
		 protected $db_conn;
		 protected $db_host = 'localhost';
		 protected $db_name = 'spine';
		 protected $db_user = 'root';
		 protected $db_pass = '';
		 
		 function connect(){
		  try{
			  $this->db_conn = new PDO("mysql:host=$this->db_host;dbname=$this->db_name",$this->db_user,$this->db_pass);
			  return $this->db_conn;
			 }
			 catch(PDOException $e){
				 return $e->getMessage();
				 }
			 }
		}
	
$timezone = "Africa/Nairobi";
if(function_exists('date_default_timezone_set')) date_default_timezone_set($timezone);
?>
