<?php

class complaintIdOldAndNew extends page 
{
	private $maxResults = 6;
	
	function __construct()
	{
		parent::__construct();
		
		$this->search = isset($_REQUEST['loadId']) ? (int)$_REQUEST['loadId'] : die();
		
		$this->getData_NEW();
		$this->getData_OLD();
		
		
		echo "<ul>";
		
		$displayed_OLD = $this->output_OLD();
		
		$displayed_NEW = $this->output_NEW();
		
		$this->output_summary( $displayed_OLD + $displayed_NEW );
		
		echo "</ul>";
	}
	
	private function output_summary( $totalDisplayed )
	{
		$max = $this->results_NEW + $this->results_OLD;
		
		$results = translate::getInstance()->translate("results");
		$old_system = translate::getInstance()->translate("old_system");
		$new_system = translate::getInstance()->translate("new_system");
		
		echo "
			<li class='load_results_summary'>
				<span class='informal'>
					$totalDisplayed / $max $results
				</span>
				
				<div class='informal load_results_legend'>
					<div class='load_results_legend_color' style='background: #B3FFB3;'></div>
					<div class='load_results_legend_text'>$old_system</div>
					
					<div class='load_results_legend_color' style='background: #ffffff;'></div>
					<div class='load_results_legend_text'>$new_system</div>
				</div>
			</li>";
	}
	
	private function getData_OLD()
	{
		$sql = "SELECT * 
				FROM complaint 
				WHERE (id LIKE '" . $this->search . "%' 
				OR sapCustomerNumber LIKE '" . $this->search . "%' 
				OR sapName LIKE '%" . $this->search . "%' ) 
				ORDER BY id";
		
		$this->dataset_OLD = mysql::getInstance()->selectDatabase("complaints")->Execute($sql);
		$this->results_OLD = mysql_num_rows($this->dataset_OLD);
	}
	
	private function output_OLD()
	{
		$count = 0;
		
		$max = ($this->results_OLD > $this->maxResults) ? $this->maxResults : $this->results_OLD;
		
		while($count < $max)	
		{
			$fields = mysql_fetch_assoc($this->dataset_OLD);
			
			$id = $fields['id'];
			
			if( $fields["typeOfComplaint"] == "customer_complaint" )
			{
				$name = utf8_encode(htmlspecialchars($fields['sapName'], ENT_NOQUOTES));
				$number = $fields['sapCustomerNumber'];
				
				if( strlen($name) > 30 )
				{
					$name = substr( $name, 0 , 27) . "...";
				}
				
				echo "
					<li class='complaint_old'>
						<strong>$id</strong><span class='informal' style='padding-left: 5px;'>($number)</span><br />
						<span class='informal'>
							<span style='padding-left: 5px;'>$name</span>
						</span>
					</li>";
			}
			else
			{
				echo "
					<li class='complaint_old'>
						<strong>$id</strong>
					</li>";
			}

			$count++;
		}
		
		return $count;
	}
	
	private function getData_NEW()
	{
		$sql = "SELECT 
					cc.id AS id, 
					IFNULL( CONCAT( sap.name1, ' ', IFNULL(sap.name2,'')), 'N/A') AS customerName,
					IFNULL(cc.sapCustomerNo, 'N/A') AS customerNumber,
					IFNULL(ret.sapReturnNo, 'N/A') AS returnNumber
				FROM complaintsCustomer.complaint cc
				LEFT JOIN complaintsCustomer.conclusionReturnNo ret
				ON cc.id = ret.complaintId 
				LEFT JOIN SAP.customers sap
				ON cc.sapCustomerNo = sap.id 
							
				WHERE (cc.id LIKE '%" . $this->search . "%'
				OR CONCAT( sap.name1, ' ', IFNULL(sap.name2,'') ) LIKE '" . $this->search . "%' 
				OR sap.id LIKE '" . $this->search . "%'
				OR ret.sapReturnNo LIKE '%" . $this->search . "%')
				
				ORDER BY cc.id";
		
		$this->dataset_NEW = mysql::getInstance()->selectDatabase("complaintsCustomer")->Execute($sql);
		$this->results_NEW = mysql_num_rows($this->dataset_NEW);
	}
	
	private function output_NEW()
	{
		$count = 0;
		
		$max = ($this->results_NEW > $this->maxResults) ? $this->maxResults : $this->results_NEW;
		
		while($count < $max)	
		{
			$fields = mysql_fetch_assoc($this->dataset_NEW);
			
			$name = utf8_encode(htmlspecialchars($fields['customerName'], ENT_NOQUOTES));
			$number = $fields['customerNumber'];
			$id = $fields['id'];
			$return = $fields['returnNumber'];
			
			if( strlen($name) > 30 )
			{
				$name = substr( $name, 0 , 27) . "...";
			}
			
			echo "
				<li class='complaint_new'>
					<strong>$id</strong><span class='informal' style='padding-left: 5px;'>($number)</span><br />
					<span class='informal'>
						<span style='padding-left: 5px;'>$name</span>
					</span><br/>
					<span class='informal'>
						<span style='padding-left: 5px;'>Return: $return</span>
					</span>
				</li>";

			$count++;
		}
		
		return $count;
	}
}

?>