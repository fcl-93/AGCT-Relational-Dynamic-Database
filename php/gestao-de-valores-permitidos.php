<?php

require_once("custom/php/common.php");
$addValues = new ValoresPermitidos();
/**
 *
 * @author fabio
 *
 */
class ValoresPermitidos
{
	private $bd;
        private $histVal;
	/**
	 * Contructor
	 */
	public function __construct(){
		$this->bd = new Db_Op();
                $this->histVal = new ValPerHist();
		$this->checkUser();
	}
	/**
	 *  This method will check if the user as the permission to acess this page
	 * and will handle all the Requests states.
	 */
	public function checkUser(){
		$capability = 'manage_custom_forms';
		if(is_user_logged_in())
		{
			if(current_user_can($capability))
			{
				if(empty($_REQUEST))
				{
					$this->tablePrintEntities();
                                        $this->tablePrintRelation();
				}
				else if($_REQUEST['estado'] == 'introducao') 
				{
					$this->insertionForm();
				}
				else if($_REQUEST['estado'] == 'inserir')
				{
					$this->insertState();
				}
				else if($_REQUEST['estado'] == 'ativar')
	 			{
					$this->activate();
	 			}
	 			else if($_REQUEST['estado'] == 'desativar')
	 			{
	 				$this->desactivate();
	 			}
	 			else if($_REQUEST['estado']=='editar')
	 			{
	 				$this->editForm();	 				
	 			}
	 			else if($_REQUEST['estado'] == 'alteracao')
	 			{
	 				$this->changeEnum();
	 			}
                                else if($_REQUEST['estado'] == 'historico')
	 			{
	 				$this->histVal->showHist($this->bd);
	 			}
                                else if($_REQUEST['estado'] == 'voltar')
	 			{
	 				$this->histVal->estadoVoltar($this->bd);
	 			}
                                
			}
			else 
			{
?>
				<html>
					<p>Não tem autorização para a aceder a esta página.</p>
				</html>
<?php 
			}
		}
		else 
		{
?>
			<html>
                            <p> O utilizador não se encontra logado.</p>
                            <p>Clique <a href="/login">aqui</a> para iniciar sessão.</p>
			</html>
<?php
		}
	}
	/**
	 * This method will be responsable for the table print that will show properties with enum value 
	 * and the diferent values assigned to that field
	 */
	public function tablePrintEntities()
	{
		// gets all properties with enum in value_type.
?>
            <h3>Gestão de valores permitidos - Entidades</h3>
            <form method="GET">
                Verificar valores permitidos existentes no dia : 
                <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data"> 
                <input type="hidden" name="estado" value="historico">
                <input type="hidden" name="histAll" value="true">
                <input type="hidden" name="tipo" value="ent">
                <input type="submit" value="Apresentar valores permitidos">
            </form>
<?php
		$res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum' AND rel_type_id IS NULL ORDER BY `property`.`ent_type_id` ASC"); 
		$num_Prop = $res_NProp->num_rows;
		if($num_Prop > 0)
		{
?>
			<html>
				<table class="table">
					<thead>
						<tr>
							<th>Entidade</th>
							<th>Id</th>
							<th>Propriedade</th>
							<th>Id</th>
							<th>Valores permitidos</th>
							<th>Estado</th>
							<th>Ação</th>
						<tr>
					</thead>
					<tbody>
<?php
						$printedNames = array();
						while($read_PropWEnum = $res_NProp->fetch_assoc())
						{
?>
                                                    <tr>
<?php 				
                                                        //Get all enum values for the property that in will start printing now
                                                        $res_Enum = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_PropWEnum['id']);

                                                        //Get the entity name and id that is related to the property we are printing
                                                        $res_Ent = $this->bd->runQuery("SELECT id, name FROM ent_type WHERE id = ".$read_PropWEnum['ent_type_id']);
                                                        $read_EntName = $res_Ent->fetch_assoc();

                                                        //Get the number of properties with that belonh to the etity I'm printing and have enum tipe
                                                        $res_NumProps= $this->bd->runQuery("SELECT * FROM property WHERE ent_type_id = ".$read_PropWEnum['ent_type_id']." AND value_type = 'enum'");

                                                        //Get all the enum values that we wil print this is only the number.
                                                        $acerta = $this->bd->runQuery("SELECT * FROM prop_allowed_value as pav ,property as prop, ent_type as ent WHERE ent.id = ".$read_EntName['id']." AND  prop.ent_type_id = ".$read_EntName['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id");
                                                        $acerta2 = $this->bd->runQuery("SELECT * FROM property WHERE property.id NOT IN (SELECT property_id FROM prop_allowed_value) AND property.value_type='enum' AND ent_type_id =".$read_EntName['id']);
                                                        //verifies if the name i'm printing has ever been written
							$conta = 0;
							for($i = 0; $i < count($printedNames); $i++)
							{
                                                            if($printedNames[$i] == $read_EntName['name'])
                                                            {
                                                                    $conta++;
                                                            }
							}

							if($conta == 0)
							{
?>
                                                            <td rowspan='<?php echo $acerta->num_rows + $acerta2->num_rows; ?>'><?php echo $read_EntName['name'];?></td>
<?php 	
                                                            $printedNames[] = $read_EntName['name'];
							}
							else
							{
                                                            //echo '<td rowspan='.mysqli_num_rows($acerta).'>';	
							}
?>
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><?php echo $read_PropWEnum['id'];?></td>
							<!-- Nome da propriedade -->
							<td rowspan="<?php echo $res_Enum->num_rows;?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade=<?php echo $read_PropWEnum['id'];?>">[<?php echo $read_PropWEnum['name'];?>]</a><a href="gestao-de-valores-permitidos?estado=historico&prop_id=<?php echo $read_PropWEnum['id'];?>">[Histórico]</a</td>

<?php 							
							//$propAllowedArray = mysqli_fetch_assoc($propAllowed);
							if($res_Enum->num_rows == 0)
							{
?>
                                                            <td colspan=4> Não há valores permitidos definidos </td>
<?php 
							}
							else
							{
                                                            while($read_EnumValues = $res_Enum->fetch_assoc())
                                                            {			
?>									
                                                                <td><?php  echo $read_EnumValues['id'];?></td>
                                                                <td><?php echo $read_EnumValues['value'];?></td>
                                                                <td>
<?php 			
                                                                if($read_EnumValues['state'] == 'active')
                                                                {
?>
                                                                    Ativo
<?php 
                                                                }
                                                                else 
                                                                {
?>	
                                                                    Inativo
<?php 											
                                                                }										
?>										
                                                                </td>
                                                                <td>
                                                                <a href="gestao-de-valores-permitidos?estado=editar&enum_id=<?php echo $read_EnumValues['id'];?>&prop_id=<?php echo $read_PropWEnum['id'];?>">[Editar]</a>  
<?php 
                                                                if($read_EnumValues['state'] === 'active')
                                                                {
?>
                                                                    <a href="gestao-de-valores-permitidos?estado=desativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Desativar]</a>
<?php 
                                                                }
                                                                else 
                                                                {
?>
                                                                    <a href="gestao-de-valores-permitidos?estado=ativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Ativar]</a>
<?php 
                                                                }
?>										
                                                                </td>
                                                            </tr>		
<?php 								
                                                            }
                                                        }
?>
                                                    </tr>
<?php 
						}
?>
					<tbody>
				</table>
			<html>	
<?php 										
		}
		else
		{
?>
			<html>
				<p>Não existem propriedades especificadas para entidades, cujo tipo de valor seja enum. <br>
				Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção</p>
			</html>
<?php 						
		}
	}
        
        
        public function tablePrintRelation(){
?>
            <h3>Gestão de valores permitidos - Relações</h3>
            <form method="GET">
                Verificar propriedades existentes no dia : 
                <input type="text" class="datepicker" id="datepickerRel" name="data" placeholder="Introduza uma data"> 
                <input type="hidden" name="estado" value="historico">
                <input type="hidden" name="histAll" value="true">
                <input type="hidden" name="tipo" value="rel">
                <input type="submit" value="Apresentar propriedades">
            </form>
<?php
            $res_NProp = $this->bd->runQuery("SELECT * FROM property WHERE value_type = 'enum' AND ent_type_id IS NULL ORDER BY `property`.`rel_type_id` ASC");
            $numberRltn = $res_NProp->num_rows;
            if($numberRltn > 0)
            {
?>
            <html>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Relação</th>
                            <th>Id</th>
                            <th>Propriedade</th>
                            <th>Id</th>
                            <th>Valores permitidos</th>
                            <th>Estado</th>
                            <th>Ação</th>
                        <tr>
                    </thead>
                    <tbody>
<?php
                    $printedId = array();
                    while($read_PropWEnum = $res_NProp->fetch_assoc())
                    {
?>
                        <tr>
<?php
                            //Get all enum values for the property that in will start printing now
                            $res_Enum = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$read_PropWEnum['id']);
                                                                    
                            //Get the relation name and id that is related to the property we are printing
                            $res_Rel = $this->bd->runQuery("SELECT * FROM rel_type WHERE id = ".$read_PropWEnum['rel_type_id']);
                            $read_RelName = $res_Rel->fetch_assoc();
                                                                    
                            //Get the number of properties with that belong to the entity I'm printing and have enum type
                            $res_NumProps= $this->bd->runQuery("SELECT * FROM property WHERE rel_type_id = ".$read_PropWEnum['rel_type_id']." AND value_type = 'enum'");
                                                                    
                            //Get all the enum values that we wil print this is only the number.
                            $acerta = $this->bd->runQuery("SELECT * FROM prop_allowed_value as pav ,property as prop, rel_type as rl_tp WHERE rl_tp.id = ".$read_RelName['id']." AND  prop.rel_type_id = ".$read_RelName['id']." AND prop.value_type = 'enum' AND prop.id = pav.property_id");
                            $acerta2 = $this->bd->runQuery("SELECT * FROM property WHERE property.id NOT IN (SELECT property_id FROM prop_allowed_value) AND property.value_type='enum' AND rel_type_id =".$read_RelName['id']);
                            //verifies if the id i'm printing has ever been printed before
                            $conta = 0;
                            for($i = 0; $i < count($printedId); $i++)
                            {
				if($printedId[$i] == $read_PropWEnum['rel_type_id'])
				{
                                    $conta++;
				}
                                                             
                            }
                                                        
                            if($conta == 0)
                            {
?>
                                <td rowspan='<?php echo $acerta->num_rows + $acerta2->num_rows; ?>'><?php echo $read_RelName['name'];?></td>
<?php                           
                                $printedId[] = $read_PropWEnum['rel_type_id'];
                            }
?>
                            <td rowspan="<?php echo $res_Enum->num_rows;?>"><?php echo $read_PropWEnum['id'];?></td>
                            <!-- Nome da propriedade -->
                            <td rowspan="<?php echo $res_Enum->num_rows;?>"><a href="gestao-de-valores-permitidos?estado=introducao&propriedade=<?php echo $read_PropWEnum['id'];?>">[<?php echo $read_PropWEnum['name'];?>]</a><a href="gestao-de-valores-permitidos?estado=historico&prop_id=<?php echo $read_PropWEnum['id'];?>">[Histórico]</a</td>
                                
<?php 							
							
                            if($res_Enum->num_rows == 0)
                            {
?>
                            <td colspan=4> Não há valores permitidos definidos </td>
<?php

                            }
                            else
                            {
                            while($read_EnumValues = $res_Enum->fetch_assoc()){			
?>			
                            <td><?php  echo $read_EnumValues['id'];?></td>
                            <td><?php echo $read_EnumValues['value'];?></td>
                            <td>
<?php 			
                            if($read_EnumValues['state'] == 'active')
                            {
?>
                                Ativo
<?php 
                            }
                            else 
                            {
?>	
                                Inativo
<?php 											
                            }
                                                                                    
?>										
                            </td>
                            <td>
                                <a href="gestao-de-valores-permitidos?estado=editar&enum_id=<?php echo $read_EnumValues['id'];?>&prop_id=<?php echo $read_PropWEnum['id'];?>">[Editar]</a>  
<?php 
                                if($read_EnumValues['state'] === 'active')
                                {
?>
                                    <a href="gestao-de-valores-permitidos?estado=desativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Desativar]</a>
<?php 
				}
				else 
				{
?>
                                    <a href="gestao-de-valores-permitidos?estado=ativar&enum_id=<?php echo $read_EnumValues['id'];?>">[Ativar]</a>
<?php 
				}
?>										
                            </td>
                        </tr>		
<?php           

                                }
                                     
                            }
?>
                        </tr>
<?php               }
?>
                    </tbody>
                </table>
            </html>    
<?php
            }
            else
            {
?>
                        <html>
				<p>Não há propriedades especificadas cujo tipo de valor seja enum. <br>
				Especificar primeiro nova(s) propriedade(s) e depois voltar a esta opção</p>
			</html>
<?php                
            }
        }
        
        
        
        
	/**
	 * This method will print the for to insert new enum values.
	 */
	public function insertionForm()
	{
		$_SESSION['property_id'] = $_REQUEST['propriedade'];//
		//print_r($_SESSION);
?>
		<h3>Gestão de valores permitidos - introdução</h3><br>
			<form id="insertForm">
				<label>Valor: </label>
				<input type="text" name="valor">
				<input type="hidden" name="estado" value="inserir">
				<input type="submit" value="Inserir valor permitido">
				<br>
				<label id="valor" class="error" for="valor"></label>
			</form>
<?php 
	}
	/**
	 * This method will print the form and fill it with the properties from the selected enum.
	 */
	public function editForm(){
		$res_EnumName=$this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$_REQUEST['enum_id']);
		$read_EnumName = $res_EnumName->fetch_assoc();
		?>
			<h3>Gestão de valores permitidos - introdução</h3><br>
				<form id="editForm">
					<label>Valor: </label>
					<input type="text" name="valor" value="<?php echo $read_EnumName['value']; ?>">
					
					<input type="hidden" name="enum_id" value="<?php echo $_REQUEST['enum_id']; ?>">
                                        <input type="hidden" name="propriedade" value="<?php echo $_REQUEST['prop_id']; ?>">
					<input type="hidden" name="estado" value="alteracao">
					<input type="submit" value="Inserir valor permitido">
					<br>
					<label id="valor" class="error" for="valor"></label>
				</form>
	<?php 
		}
	/**
	 * Check if the value of the form is empty or not
	 */
	public function ssvalidation()
	{
		if(empty($_REQUEST['valor']))
		{
?>
			<html>
				<p>O campo valor é de preenchimento obrigatório.</p>
			</html>
<?php 
			return false;
		}
		else 
		{
			$sanitizedName = $this->bd->userInputVal($_REQUEST['valor']);//for both if's the value input
                        if (isset($_REQUEST['propriedade'])) {
                            $_SESSION['property_id'] = $_REQUEST['propriedade'];
                        }
			$res_CheckPropEnums = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE property_id=".$_SESSION['property_id']." AND value='".$sanitizedName."'");
			
			//for the edit submission
			
			if($_REQUEST['estado'] == 'alteracao')
			{
				if($res_CheckPropEnums->num_rows != 0)
				{
?>
					<p>	O valor que está a tentar introduzir já se encontra registado.</p>
<?php 
					return false;
				}
				else
				{
					return true;
				}
			}
			else
			{
				//for the insert submission
				if($_REQUEST['estado'] == 'inserir' && $res_CheckPropEnums->num_rows)
				{
?>
					<p>	O valor que está a tentar introduzir já se encontra registado.</p>
<?php 
					return false;	
				}
				else
				{
					return true;
				}
			}	
		}
	}
	/**
	 * This method will handle the insertion state if the user input is ok
	 */
	public function insertState()
	{
?>
            <h3>Gestão de valores permitidos - inserção</h3>
<?php 
            if($this->ssvalidation())
            {
                $data = date("Y-m-d H:i:s",time());
                //echo "INSERT INTO `prop_allowed_value`(`id`, `property_id`, `value`, `state`) VALUES (NULL,".$_SESSION['property_id'].",'".$_REQUEST['valor']."','active')";
                $_sanitizedInput = $this->bd->userInputVal($_REQUEST['valor']);                        
                if ($this->bd->runQuery("INSERT INTO `prop_allowed_value`(`id`, `property_id`, `value`, `state`, `updated_on`) VALUES (NULL,".$_SESSION['property_id'].",'".$_sanitizedInput."','active', '".$data."')")) {
?>
                    <p>Inseriu os dados de novo valor permitido com sucesso.</p>
                    <p>Clique em <a href="gestao-de-valores-permitidos"> Continuar </a> para avançar</p>
<?php 
                }
                else {
?>
                    <p>Não foi possível inserir o novo valor permitido</p>
<?php
                    goBack();
                }
            }
            else {
?>
                <p>Não foi possível inserir o novo valor permitido</p>
<?php
                goBack();
            }
	}
	

	
	/**
	 * This method will check if the edition that we are trying to make in the enum is of and if it 
	 * is it will submit.
	 */
	public function changeEnum(){
            if($this->ssvalidation())
            {               //new name
                $sanitizedName = $this->bd->userInputVal($_REQUEST['valor']);
                //History generation 
                $getEnumId = $this->bd->userInputVal($_REQUEST['enum_id']);

                $selProp = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE id = ".$getEnumId);
                $idProp = $selProp->fetch_assoc()["property_id"];
                
                $data = date("Y-m-d H:i:s",time());
                
                if($this->histVal->addHist($getEnumId, $this->bd, $data)){
                    //insert the new value for the enum.
                    $this->bd->runQuery("UPDATE `prop_allowed_value` SET updated_on = '".$data."', value='".$sanitizedName."' WHERE id=".$getEnumId);
                //echo "UPDATE `prop_allowed_value` SET value='".$sanitizedName."' WHERE id=".$_REQUEST['enum_id'];
                    $this->bd->getMysqli()->commit();
?>
                    <p>	Alterou o nome do valor enum selecionado para <?php echo $_REQUEST['valor'] ?>.</p>
                    <p>	Clique em <a href="gestao-de-valores-permitidos"> Continuar </a> para avançar</p>
<?php 
                }
                else
                {
                    $this->bd->getMysqli()->rollback();
?>

                    <p>O nome do valor enum selecionado não pode ser alterado para <?php echo $_REQUEST['valor'] ?>.</p>
                    <p>	Clique em <?php goBack(); ?></p>
<?php
                }
            }
            else
            {
                goBack();
            }
	}
	/**
	 * This method will activate the enum.
	 */
	public function activate(){
            
            $getEnum = $this->bd->userInputVal($_REQUEST['enum_id']);
            $selProp = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE id = ".$getEnum);
            $idProp = $selProp->fetch_assoc()["property_id"];
            $data = date("Y-m-d H:i:s",time());
            if($this->histVal->addHist($getEnum, $this->bd, $data))
            {
		$this->bd->runQuery("UPDATE `prop_allowed_value` SET updated_on = '".$data."', state='active' WHERE id=".$getEnum);
                //gets the name of the enum that has been enabled 
		$res_enumName = $this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$getEnum);
		$read_enumName = $res_enumName->fetch_assoc();
                $this->bd->getMysqli()->commit();
?>
	<html>
	 	<p>O valor <?php echo $read_enumName['value'] ?> foi ativado</p>
	 	<p>Clique em <a href="/gestao-de-valores-permitidos"/>Continuar</a> para avançar</p>
	</html>
<?php
            }
            else
            {
                $this->bd->getMysqli()->rollback();
                
?>
                    <p>O valor enum selecionado não pode ser ativado.</p>
                    <p>	Clique em <?php goBack(); ?></p>
<?php
            }
	}
	/**
	 * This method will desactivate the enum values
	 */
	public function desactivate(){
            
            $getEnum = $this->bd->userInputVal($_REQUEST['enum_id']);
            $selProp = $this->bd->runQuery("SELECT * FROM prop_allowed_value WHERE id = ".$getEnum);
            $prop = $selProp->fetch_assoc();
            $idProp = $prop["property_id"];
            $value = $prop['value'];
            $data = date("Y-m-d H:i:s",time());
            if (!$this->checkValues($idProp,$value)) {
                if($this->histVal->addHist($getEnum, $this->bd, $data))
                {
                    $this->bd->runQuery("UPDATE `prop_allowed_value` SET updated_on = '".$data."', state='inactive' WHERE id=".$getEnum);
                    //get the name to show to the users after the item is disabled
                    $res_enumName = $this->bd->runQuery("SELECT value FROM prop_allowed_value WHERE id=".$getEnum);
                    $read_enumName = $res_enumName->fetch_assoc();
                    $this->bd->getMysqli()->commit();
?>
                <html>
                        <p>O valor <?php echo $read_enumName['value'] ?> foi desativado</p>
                        <p>Clique em <a href="/gestao-de-valores-permitidos"/>Continuar</a> para avançar</p>
                </html>
<?php 
                }
                else
                {
                    $this->bd->getMysqli()->rollback();
?>
                    <p>O valor enum selecionado não pode ser desativado.</p>
                    <p>	Clique em <?php goBack(); ?></p>
<?php
                }
            }
            else {
?>
                <p>O valor enum selecionado não pode ser desativado.</p>
                <p>Clique em <?php goBack(); ?></p>
<?php
       
            }
	}
        
        private function checkValues ($idProp,$enum) {
            $selVal = "SELECT * FROM value WHERE property_id = ".$idProp;
            $selVal = $this->bd->runQuery($selVal);
            while ($val = $selVal->fetch_assoc()) {
                if ($val['value'] === $enum) {
                    return true;
                }
            }
            return false;
        }
	
}
/**
 * History table gestion class 
 * will have all the methods to change the history
 */
class ValPerHist{
    
    //Constructor
    public function __construct(){}
    
    /**
     * This method controls the excution flow when the state is Voltar
     * Basicly he does all the necessary queries to reverse a property to an old version
     * saved in the history
     * @param type $db (object form the class Db_Op)
     */
    public function estadoVoltar ($db) {
        $data = date("Y-m-d H:i:s",time());
        if ($this->addHist($_REQUEST["prop_id"],$db,$data)) {
            //get all the prop_allowed_values in the selected version
            $selInactive = $db->runQuery("SELECT * FROM hist_prop_allowed_value WHERE id = ".$_REQUEST["hist"]);
            $selInactive = $selInactive->fetch_assoc();
            $dataHist = $selInactive['inactive_on'];
            $selOld = "SELECT * FROM hist_prop_allowed_value WHERE inactive_on >= '".$dataHist."' AND active_on < '".$dataHist."'";
            $selOld = $db->runQuery($selOld);
            $erro = false;
            while ($old = $selOld->fetch_assoc()) {
                $selOldVal = "SELECT * FROM hist_prop_allowed_value WHERE id = ".$old["id"];
                $selOldVal = $db->runQuery($selOldVal);
                $atributos = $selOldVal->fetch_assoc();
                $updateHist = "UPDATE prop_allowed_value SET ";
                foreach ($atributos as $atributo => $valor) {
                    if ($atributo == 'state_backup') {
                        $atributo = 'state';
                    }
                    if ($atributo != "id" && $atributo != "inactive_on" && $atributo != "active_on" && $atributo != "prop_allowed_value_id" && !is_null($valor)) {
                        $updateHist .= $atributo." = '".$valor."',"; 
                    }
                }
                $updateHist .= " updated_on = '".$data."' WHERE id = ".$old['prop_allowed_value_id'];
                $updateHist =$db->runQuery($updateHist);
                if ($updateHist) {}
                else {
?>
                    <p>Não foi possível reverter os valores permitidos para a versão selecionada</p>
<?php
                    $db->getMysqli()->rollback();
                    goBack();
                    $erro = true;
                    break;
                }
            }
            // desactive values that didn't exist in the versionwe'll go back
            $selPropOut = $db->runQuery("SELECT * FROM prop_allowed_value WHERE property_id = ".$_REQUEST["prop_id"]." AND updated_on > '".$selInactive['inactive_on']."' AND id NOT IN (SELECT prop_allowed_value_id FROM hist_prop_allowed_value)");
            while ($propOut = $selPropOut->fetch_assoc()) {
                $updateOut = $db->runQuery("UPDATE prop_allowed_value SET updated_on = '".$data."', state = 'inactive' WHERE id = ".$propOut["id"]);
                if (!$updateOut) {
?>
                    <p>Não foi possível reverter os valores permitidos para a versão selecionada</p>
<?php
                    $db->getMysqli()->rollback();
                    goBack();
                    $erro = true;
                    break;
                }
            }
            if (!$erro) {
                $db->getMysqli()->commit();
?>
                <p>Atualizou os valores permitidos com sucesso para uma versão anterior.</p>
                <p>Clique em <a href="/gestao-de-valores-permitidos/">Continuar</a> para avançar.</p>
<?php
            }
        }
        else {
?>
            <p>Não foi possível reverter os valores permitidos para a versão selecionada</p>
<?php
            $db->getMysqli()->rollback();
            goBack();
        }
    }
    
    /**
     * This method is responsible for the execution flow when the state is Histórico.
     * He starts by presenting a datepicker with options to do a kind of filter of 
     * all the history of the selected unit type.
     * After that he presents a table with all the versions presented in the history
     * @param type $db (object form the class Db_Op)
     */
    public function showHist ($db) {
        if (isset($_REQUEST["histAll"])) {
            $this->apresentaHistTodas($_REQUEST["tipo"], $db);
        }
        else if (empty($_REQUEST["selData"]) || (!empty($_REQUEST["selData"]) && $db->validaDatas($_REQUEST['data']))){
        //meto um datepicker        
?>
        <form method="GET">
            Verificar histórico:<br>
            <input type="radio" name="controlDia" value="ate">até ao dia<br>
            <input type="radio" name="controlDia" value="aPartir">a partir do dia<br>
            <input type="radio" name="controlDia" value="dia">no dia<br>
            <input type="text" class="datepicker" id="datepicker" name="data" placeholder="Introduza uma data">
            <input type="hidden" name="selData" value="true">
            <input type="hidden" name="estado" value="historico">
            <input type="hidden" name="prop_id" value="<?php echo $_REQUEST["prop_id"]; ?>">
            <input type="submit" value="Apresentar histórico">
        </form>

        <table class="table">
            <thead>
                <tr>
                    <th>Data de Início</th>
                    <th>Data de Fim</th>
                    <th>Valores Permitidos</th>
                    <th>Estado</th>
                    <th>Ação</th>
                </tr>
            </thead>
            <tbody>
<?php
        $idProp = $db->userInputVal($_REQUEST["prop_id"]);
        if (empty($_REQUEST['data'])) {
            $queryHistorico = "SELECT * FROM hist_prop_allowed_value WHERE property_id = ".$idProp." ORDER BY inactive_on DESC";
        }
        else {
            $data = $db->userInputVal($_REQUEST['data']);
            if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "ate") {
                $queryHistorico = "SELECT * FROM hist_prop_allowed_value WHERE property_id = ".$idProp." AND inactive_on <= '".$data."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "aPartir") {
                $queryHistorico = "SELECT * FROM hist_prop_allowed_value WHERE property_id = ".$idProp." AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
            }
            else if (isset($_REQUEST["controlDia"]) && $_REQUEST["controlDia"] == "dia"){
                $queryHistorico = "SELECT * FROM hist_prop_allowed_value WHERE property_id = ".$idProp." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
            }
            else {
                $queryHistorico = "SELECT * FROM hist_prop_allowed_value WHERE property_id = ".$idProp." AND inactive_on < '".date("Y-m-d",(strtotime($data) + 86400))."' AND inactive_on >= '".$data."' ORDER BY inactive_on DESC";
            }
        }
        $queryHistorico = $db->runQuery($queryHistorico);
        if ($queryHistorico->num_rows == 0) {
?>
            <tr>
                <td colspan="4">Não existe registo referente à propriedade selecionada no histórico</td>
                <td><?php goBack(); ?></td>
            </tr>
<?php
        }
        else {
            while ($hist = $queryHistorico->fetch_assoc()) {
                $contaLinhas = 1;
                $selProp = $db->runQuery("SELECT * FROM prop_allowed_value WHERE updated_on < '".$hist["inactive_on"]."' AND property_id = ".$idProp);
                $selPropHist = $db->runQuery("SELECT * FROM hist_prop_allowed_value WHERE inactive_on >= '".$hist["inactive_on"]."' AND active_on < '".$hist["inactive_on"]."' AND property_id = ".$idProp);
                $rowspan = $selProp->num_rows + $selPropHist->num_rows;
                while ($prop = $selProp->fetch_assoc()) {
                    if ($contaLinhas > $rowspan) {
                        $contaLinhas = 1;
                    }
?>
                    <tr>
<?php
                    if ($contaLinhas === 1) {
?>
                        <td rowspan="<?php echo $rowspan;?>"><?php echo $hist["active_on"];?></td>
                        <td rowspan="<?php echo $rowspan;?>"><?php echo $hist["inactive_on"];?></td>
<?php
                    }
?>
                    <td><?php echo $prop["value"];?></td>
                    <td>
<?php
                    if ($prop["state"] === "active")
                    {
                        echo 'Ativo';
                    }
                    else
                    {
                        echo 'Inativo';
                    }
?>
                    </td>
<?php
                    if ($contaLinhas === 1) {
?>
                        <td rowspan="<?php echo $rowspan;?>"><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&prop_id=<?php echo $_REQUEST["prop_id"];?>">Voltar para esta versão
                            </a>
                        </td>
<?php
                    }
?>
                </tr>
<?php
                $contaLinhas++;
                }
                while ($prop = $selPropHist->fetch_assoc()) {
                    if ($contaLinhas > $rowspan) {
                        $contaLinhas = 1;
                    }
?>
                    <tr>
<?php
                    if ($contaLinhas === 1) {
?>
                        <td rowspan="<?php echo $rowspan;?>"><?php echo $hist["active_on"];?></td>
                        <td rowspan="<?php echo $rowspan;?>"><?php echo $hist["inactive_on"];?></td>
<?php
                    }
?>
                    <td><?php echo $prop["value"];?></td>
                    <td>
<?php
                    if ($prop["state_backup"] === "active")
                    {
                        echo 'Ativo';
                    }
                    else
                    {
                        echo 'Inativo';
                    }
?>
                    </td>
<?php
                    if ($contaLinhas === 1) {
?>
                        <td rowspan="<?php echo $rowspan;?>"><a href ="?estado=voltar&hist=<?php echo $hist["id"];?>&prop_id=<?php echo $_REQUEST["prop_id"];?>">Voltar para esta versão
                            </a>
                        </td>
<?php
                    }
?>
                </tr>
<?php
                $contaLinhas++;
                }
            }
        }
?>
            <tbody>
        </table>
<?php
        
    }
    }
    
    
    /**
     * Will insert an item to the table hist_prop_allowed_value
     * to generate the history with all modifications.
     *
     * @param type $id -> enum from the id that will be changed, this id comes sanitized.
     * @param type $bd -> database object to allow me to use the database run querys.
     * @return boolean 
     */
    public function addHist($id,$bd,$updateTime){
        $bd->getMySqli()->autocommit(false);
        $bd->getMySqli()->begin_transaction();
        $selOld = $bd->runQuery("SELECT * FROM prop_allowed_value WHERE id = ".$id);
        $read_oldEnum = $selOld->fetch_assoc();
        if(!$bd->runQuery("INSERT INTO `hist_prop_allowed_value`(`id`, `property_id`, `value`, `state`, `state_backup`, `prop_allowed_value_id`, `active_on`, `inactive_on`) VALUES (NULL,".$read_oldEnum['property_id'].",'".$read_oldEnum['value']."','inactive','".$read_oldEnum['state']."',".$read_oldEnum['id'].",'".$read_oldEnum['updated_on']."','".$updateTime."')"))
        {
            return false;
        } 
        return true;
    }
    
    /**
     * This method creates a table with a view of all the properties in the selected day
     * @param type $tipo (indicates if we are working with relations or entities)
     * @param type $db (object form the class Db_Op)
     */
    private function apresentaHistTodas ($tipo, $db) {
        if ($db->validaDatas($_REQUEST['data'])) {
?>
        <table class="table">
            <thead>
                <tr>
<?php
                    if ($tipo == "ent") {
?>
                       <th>Entidade</th> 
<?php                        
                    }
                    else {
?>
                       <th>Relação</th> 
<?php
                    }
?>
                    <th>Id</th>
                    <th>Propriedade</th>
                    <th>Id</th>
                    <th>Valores permitidos</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
<?php
                if ($tipo === "ent")
                {
                    $data = $db->userInputVal($_REQUEST['data']);
                    $selecionaEntOrRel = "SELECT name, id FROM ent_type";
                    $resultSelEntOrRel = $db->runQuery($selecionaEntOrRel);
                }
                else
                {
                    $data = $db->userInputVal($_REQUEST['data']);
                    $selecionaEntOrRel = "SELECT name, id FROM rel_type";
                    $resultSelEntOrRel = $db->runQuery($selecionaEntOrRel);
                }  
                
                if ($resultSelEntOrRel->num_rows < 1) {
?>
                    <tr>
                        <td colspan="6">Não existe registos para esta tabela no dia selecionado</td>
                    </tr>
<?php
                } else {
                    while ($resEntRel = $resultSelEntOrRel->fetch_assoc())
                    {
                        $idEntRel = $resEntRel["id"];
                        $nome = $resEntRel["name"];
                        if ($tipo === "ent")
                        {
                            $selProp = "SELECT * FROM property WHERE value_type = 'enum' AND ent_type_id = $idEntRel";
                            $selProp = $db->runQuery($selProp);

                        }
                        else
                        {
                            $selProp = "SELECT * FROM property WHERE value_type = 'enum' AND rel_type_id = $idEntRel";
                            $selProp = $db->runQuery($selProp);

                        }
                        if ($selProp->num_rows < 1) {
?>
                            <tr>
                                <td colspan="6">Não existe registos para esta tabela no dia selecionado</td>
                            </tr>
<?php
                        } else {              

                            while ($prop = $selProp->fetch_assoc()) {
                                $selecionaHist = "SELECT * FROM hist_prop_allowed_value WHERE (('".$data."' > active_on AND '".$data."' < inactive_on) OR ((active_on LIKE '".$data."%' AND inactive_on < '".$data."') OR inactive_on LIKE '".$data."%')) AND property_id = ".$prop["id"]." GROUP BY property_id ORDER BY inactive_on DESC";
                                $selecionaProp = "SELECT * FROM prop_allowed_value WHERE (updated_on < '".$data."'OR updated_on LIKE '".$data."%') AND property_id = ".$prop["id"];
                                $resultSelecionaProp = $db->runQuery($selecionaProp);
                                $resultSelecionaHist = $db->runQuery($selecionaHist);

                                $creatTempTable = "CREATE TEMPORARY TABLE temp_table (`id` INT UNSIGNED NOT NULL,
                                    `property_id` INT NOT NULL,
                                    `value` VARCHAR(128) NOT NULL,
                                    `state` ENUM('active','inactive') NOT NULL)";
                                $creatTempTable = $db->runQuery($creatTempTable);
                                while ($val = $resultSelecionaProp->fetch_assoc()) {
                                    $db->runQuery("INSERT INTO temp_table VALUES (".$val['id'].",'".$val['property_id']."','".$val['value']."','".$val['state']."')");
                                }
                                while ($hist = $resultSelecionaHist->fetch_assoc()) {

                                    $db->runQuery("INSERT INTO temp_table VALUES (".$hist['prop_allowed_value_id'].",'".$hist['property_id']."','".$hist['value']."','".$hist['state']."')");
                                }

                                $resultSeleciona = $db->runQuery("SELECT * FROM temp_table GROUP BY id ORDER BY id ASC");
?>
                                <tr>
                                    <td rowspan="<?php echo $resultSeleciona->num_rows; ?>"><?php echo $nome; ?></td>
                                    <td rowspan="<?php echo $resultSeleciona->num_rows; ?>"><?php echo $prop["id"]; ?></td>
                                    <td rowspan="<?php echo $resultSeleciona->num_rows; ?>"><?php echo $prop["name"]; ?></td>
<?php
                                if ($resultSeleciona->num_rows > 0) {
                                    while($arraySelec = $resultSeleciona->fetch_assoc())
                                    {
?>
                                        <td><?php echo $arraySelec["id"]; ?></td>
                                        <td><?php echo $arraySelec["value"]; ?></td>
                                        <td><?php echo $arraySelec["state"]; ?></td>
                                        </td>
                                    </tr>
<?php
                                    }

                                }
                                else {
?>
                                    <td rowspan="3">Não existem valores atributídos</td>
<?php
                                }
                                $db->runQuery("DROP TEMPORARY TABLE temp_table");
                            }
                        }
                    }
                }
?>
            </tbody>
        </table>
<?php
        }
    }
}

?>