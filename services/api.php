<?php
/**
*
* @author: Arya Linggoputro
* @date : 12 May 2015
*/

 	require_once("Rest.inc.php");

	class API extends REST {
	
		public $data = "";
		
		const DB_SERVER = "127.0.0.1";
		const DB_USER = "root";
		const DB_PASSWORD = "";
		const DB = "conduct";

		private $db = NULL;
		private $mysqli = NULL;
		public function __construct(){
			parent::__construct();				// Init parent contructor
			$this->dbConnect();					// Initiate Database connection
		}
		
		/*
		 *  Connect to Database
		*/
		private function dbConnect(){
			$this->mysqli = new mysqli(self::DB_SERVER, self::DB_USER, self::DB_PASSWORD, self::DB);
		}
		
		/*
		 * Dynmically call the method based on the query string
		 */
		public function processApi(){
			$func = strtolower(trim(str_replace("/","",$_REQUEST['x'])));
			if((int)method_exists($this,$func) > 0)
				$this->$func();
			else
				$this->response('',404); // If the method not exist with in this class "Page not found".
		}
				
				
		private function leads(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$query="SELECT distinct l.id, l.first_name, l.last_name, l.email, l.mobile, l.message, l.agent_id FROM leads l order by l.id desc";
			$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);

			if($r->num_rows > 0){
				$result = array();
				while($row = $r->fetch_assoc()){
					$result[] = $row;
				}
				$this->response($this->json($result), 200); // send user details
			}
			$this->response('',204);	// If no records "No Content" status
		}

		private function lead(){	
			if($this->get_request_method() != "GET"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){	
				$query="SELECT distinct l.id, l.first_name, l.last_name, l.email, l.mobile, l.message, l.agent_id FROM leads l  where l.id=$id";
			
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				if($r->num_rows > 0) {
					$result = $r->fetch_assoc();	
					$this->response($this->json($result), 200); // send user details

				}
			}
			$this->response('',204);	// If no records "No Content" status

		}
		
		private function insertLead(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$lead = json_decode(file_get_contents("php://input"),true);
			$column_names = array('first_name', 'last_name','email', 'mobile', 'message');
			$keys = array_keys($lead);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the lead received. If blank insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $lead[$desired_key];
				}
				$columns = $columns.$desired_key.',';
				$values = $values."'".$$desired_key."',";
			}
			$query = "INSERT INTO leads(".trim($columns,',').") VALUES(".trim($values,',').")";
			if(!empty($lead)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$this->_autoDistributeAgents(); // reassigned the lead to active agent
				$success = array('status' => "Success", "msg" => "Lead Created Successfully.", "data" => $lead);
				$this->response($this->json($success),200);
			} else
				$this->response('',204);	//"No Content" status
		}

		private function updateLead(){
			if($this->get_request_method() != "POST"){
				$this->response('',406);
			}

			$lead = json_decode(file_get_contents("php://input"),true);
			$id = (int)$lead['id'];
			$column_names = array('first_name', 'last_name','email', 'mobile', 'message');
			$keys = array_keys($lead['lead']);
			$columns = '';
			$values = '';
			foreach($column_names as $desired_key){ // Check the lead received. If key does not exist, insert blank into the array.
			   if(!in_array($desired_key, $keys)) {
			   		$$desired_key = '';
				}else{
					$$desired_key = $lead['lead'][$desired_key];
				}
				$columns = $columns.$desired_key."='".$$desired_key."',";
			}
			$query = "UPDATE leads SET ".trim($columns,',')." WHERE id=$id";
			if(!empty($lead)){
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$success = array('status' => "Success", "msg" => "Lead ".$id." Updated Successfully.", "data" => $lead);
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// "No Content" status
		}
		
		private function deleteLead(){
			if($this->get_request_method() != "DELETE"){
				$this->response('',406);
			}
			$id = (int)$this->_request['id'];
			if($id > 0){				
				$query="DELETE FROM leads WHERE id = $id";
				$r = $this->mysqli->query($query) or die($this->mysqli->error.__LINE__);
				$this->_autoDistributeAgents(); // reassigned the lead to active agent
				$success = array('status' => "Success", "msg" => "Successfully deleted one record.");
				$this->response($this->json($success),200);
			}else
				$this->response('',204);	// If no records "No Content" status
		}
		
		/*
		 *	Encode array into JSON
		*/
		private function json($data){
			if(is_array($data)){
				return json_encode($data);
			}
		}



		// Extra function for equally distribute lead to active agent
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

		
		public function _getEmailByLeadID($leadID){
			$query = "SELECT l.id, l.email FROM lead l WHERE id= $leadID ";
			
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

		private function _sendSimpleEmailToAgent($agentID) {
			$to = $this->_getEmailByAgentID($agentID);
			$subject = "You have been assigned to Lead";
			$message = "Bla Bla Bla";
			mail($to, $subject, $message);
		}

		private function _sendSimpleEmailToLead($leadID) {
			$to = $this->_getEmailByLeadID($leadID);
			$subject = "You have been assigned to Agent";
			$message = "Bla Bla Bla";
			mail($to, $subject, $message);
		}

		/*
		 * Automatic distribute leads to active agents
		*/
		private function _autoDistributeAgents() {
			
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

				
				//3. Prepare update query & sending out email
				$query = ""; //init query var
				foreach ($output as $key =>$value) {				
					foreach($value as $row) {
						$query .= "UPDATE leads SET agent_id=$key WHERE id=$row;";
						// sending email to agents
						$this->_sendSimpleEmailToAgent($key);
						$this->_sendSimpleEmailToLead($row);
					}
					
				}

				//4. execute the query
				if ($this->mysqli->multi_query($query)) {
				    do {
				        /* store first result set */
				        if ($result = $this->mysqli->store_result()) {
				            while ($row = $result->fetch_row()) {
				               // printf("%s\n", $row[0]);
				            }
				            $result->free();
				        }
				        /* print divider */
				        if ($this->mysqli->more_results()) {
				            //printf("-----------------\n");
				        }
				    } while ($this->mysqli->next_result());
				}

			}  catch (Exception $e) {
  				  echo 'Caught exception: ',  $e->getMessage(), "\n";
			} // end catch
			return true;
		} // end function

	}
	
	// Initiiate Library
	
	$api = new API;
	$api->processApi();

?>