<?php
require_once("custom/php/common.php");
	
	$getData = new FetchData();

class FetchData{
	
	private $bd;
	
	public function __construct(){
		$this->bd = new Db_Op();
		$this->getData();
	}
	
	public function getData(){
                $sanitizeId = $this->bd->userInputVal($_REQUEST['ent']);
		$res_Props = $this->bd->runQuery("SELECT * FROM value WHERE entity_id=".$sanitizeId);
?>
            <div id="results">
<?php
		while($read_Props = $res_Props->fetch_assoc())
		{
			$nome = $this->bd->runQuery("SELECT * FROM property WHERE id=".$read_Props['property_id'])->fetch_assoc()['name'];
                        
                        echo $nome . " : " .$read_Props['value']."\n";
		}
?>
            </div>
<?php
                
	}
}
?>