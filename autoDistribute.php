
<?php

/**
* USE FOR TESTING PURPOSE
* @author: Arya Linggoputro
* @date : 12 May 2015
*/
	//error_reporting(E_ALL);
//ini_set('display_errors', 1);

	class AutoDistribute  {

		public $data = "";
		
		const DB_SERVER = "127.0.0.1";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "conduct";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){

			
			$this->dbConnect();					// Initiate Database connection
		
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}

		private function _formattedPrint($data) {
			print "<pre>";
			print_r($data);
			print "</pre>";
		}
		/*
		 * Automatic distribute leads to active agents
		*/
		public function autoDistributeAgents() {
			
			try{
				// 1. init data
				$input = $this->_getLeadID(); // test input data
				$output = array();        // the output container	
				$activeAgentIDs = $this->_getActiveAgentID();
				$numBuckets = count($activeAgentIDs);          // number of agents to fill
			
				// 2. Split the number of lead into buckets equally
				for ( $num = count($input), $i=0; $numBuckets > 0; $numBuckets -= 1, $num -= $bucketSize, $i++) {
				  $agentID = $activeAgentIDs[$i]; // get agentid
				  $bucketSize = ceil($num / $numBuckets); // rounding up
				  $output[$agentID] = array_splice($input, 0, $bucketSize); // assigned to the agent id
				}

				
				//3. Prepare update query 
				$query = "";
				foreach ($output as $key =>$value) {				
					foreach($value as $row) {
						$query .= "UPDATE leads SET agent_id=$key WHERE id=$row;";
					}
					
				}

				//4. execute the query
				if ($this->mysqli->multi_query($query)) {
				    do {
				        /* store first result set */
				        if ($result = $this->mysqli->store_result()) {
				            while ($row = $result->fetch_row()) {
				                //printf("%s\n", $row[0]);
				            }
				            $result->free();
				        }
				        /* print divider */
				        if ($this->mysqli->more_results()) {
				           // printf("-----------------\n");
				        }
				    } while ($this->mysqli->next_result());
				}

			}  catch (Exception $e) {
  				  echo 'Caught exception: ',  $e->getMessage(), "\n";
			} // end catch
			return true;
		} // end function

		private function _getActiveAgentID(){
			$query = "SELECT a.id FROM agents a WHERE a.active = 1";
			
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row['id'];  // only insert the id to the result array
				}
				return $result;
			}
			return false;	// If no records return false status
		}


		private function _getLeadID(){
			$query = "SELECT l.id FROM leads l ";
			
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row['id'];  // only insert the id to the result array
				}
				return $result;
			}
			return false;	// If no records return false status
		}

		public function _getEmailByAgentID($agentID){
			$query = "SELECT a.id, a.email FROM agents a WHERE id= $agentID ";
			
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result = $row['email'];  // only insert the id to the result array
				}
				return $result;
			}
			return false;	// If no records return false status
		}


	}

	$a = new AutoDistribute;
	//var_dump($a->autoDistributeAgents());
	//var_dump($a->_getEmailByAgentID(2));
?>

