<?php
require_once("custom/php/common.php");	
	
$entity = new Entidade();
    
/**
 * This method present in this class will handle all the operations that we can do in 
 * Entity page.
 * @author fabio
 *
 */
class Entidade
{
    private $bd;
    private $gereHist;
    /**
     * Constructor
     */
    public function __construct(){
        $this->bd = new Db_Op();
        $this->gereHist = new EntHist();
        $this->checkUser();
    }
    /**
     * Checks if the user has permission to use the page.
     */
    public function checkUser(){
        if ( is_user_logged_in() )
	{
            if(current_user_can('manage_entities'))
		{
			if(empty($_REQUEST['estado']))
			{
                                $this->tableToprint();
				$this->form(); // object lead the method to print the form 
 			}
 			else if($_REQUEST['estado'] =='editar')
 			{
 				$this->editEntity($_REQUEST['ent_id']);
 			}
 			else if($_REQUEST['estado'] == 'ativar')
 			{
				$this->enableEnt();
 			}
 			else if($_REQUEST['estado'] == 'desativar')
 			{
 				$this->disableEnt();
 			}
 			else if($_REQUEST['estado']=='alteracao')
 			{
 				$this->changeEnt();
 				
 			}
			else if($_REQUEST['estado'] == 'inserir')
			{
				$this->insertEnt();
				
			}
			
		}
		else
		{
?>
			<html>
				<p> Não tem autorização para aceder a esta página.</p>
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
     * This method will print the table that will show all the ent_types
     */
    public function tableToprint(){
        //Apresentar tabela
	$res_EntType = $this->bd->runQuery("SELECT * FROM ent_type");
	//verifica se há ou não entidades
	if($res_EntType->num_rows > 0)
	{
					
?>
            <html>
                <table id="sortedTable" class="table">
                    <thead>
                        <tr>
                            <th> <span>ID</span></th>
                            <th> <span>Nome</span></th>
                            <th> <span>Estado</span></th>
                            <th> <span>Ação</span></th>
                        </tr>
                    </thead>
		<tbody>
<?php				
		while($read_EntType = $res_EntType->fetch_assoc())
		{	//print_r($read_EntType);
                        //printa a restante tabela
?>						
                    <tr>
                        <td><?php echo $read_EntType['id']; ?></td>
			<td><?php echo $read_EntType['name']?></td>
							
							
<?php 			if($read_EntType['state'] === 'active')
			{
								
?>								
                            <td> Ativo </td>
                                <td>
                                    <a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id'];?>">[Editar]</a>  
                                    <a href="gestao-de-entidades?estado=desativar&ent_id=<?php echo $read_EntType['id'];?>">[Desativar]</a>
				</td>
<?php			}
			else
			{
?>
                            <td> Inativo </td>
				<td>
                                    <a href="gestao-de-entidades?estado=editar&ent_id=<?php echo $read_EntType['id'];?>">[Editar]</a>  
                                    <a href="gestao-de-entidades?estado=ativar&ent_id=<?php echo $read_EntType['id'];?>">[Ativar]</a>
				</td>	
<?php                   }
?>
			</td>
                    </tr>
<?php 
                }	
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
                <p> Não há componentes.</p>
            </html>
<?php 			

        }
    }
        
    
	/**
	 * This method will be responsable for the print of the form
	*/
	public function form()
	{
?>
		<html>
			<h3>Gestão de Componentes - Introdução</h3>
			<form id="insertForm">
				<label>Nome:</label>
				<br>
				<input type="text" id="nome" name="nome">
				<br>
				<label class="error" for="nome"></label>
				<br>
				<label>Estado:</label><br>
<?php 
			$stateEnumValues = $this->bd->getEnumValues('ent_type','state'); //this function is in common.php
			//print_r($stateEnumValues);
			
			foreach($stateEnumValues as $value)
			{
				if($value == 'active')
				{
?>				
					<html>
						<input type="radio" id="atv_int" name="atv_int" value="active" >Ativo
						<br>
					</html>
<?php 	
				}
				else 
				{
?>
					<html>
						<input type="radio" id="atv_int" name="atv_int" value="inactive" >Inativo
						<br>
					</html>
<?php 				
				}
			}
?>
				<label class="error" for="atv_int"></label>
				<br>
					<input type="hidden" name="estado" value="inserir">
					<input type="submit" value="Inserir Componente">
				</form>
				</html>
<?php 	
	}
	/**
	 * This method will do the server side validation
	 */
	public function ssvalidation()
	{
		echo '<h3>Gestão de componentes - inserção</h3>';
		if(empty($_REQUEST['nome']))
		{
?>
			<html><p>O campo nome é de preenchimento obrigatório.</p></html>
<?php 
			return false;
		}
		elseif(empty($_REQUEST['atv_int']))
		{
?>
			<html><p>Deve escolhe uma das opções do campo estado.</p></html>
<?php 	
			return false;
		}
		else
		{
			$sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);
			$res_checkRep = $this->bd->runQuery("SELECT * FROM ent_type WHERE name like '".$sanitizeName."'");
			if($res_checkRep->num_rows)
			{
?>
				<html><p>Já existe uma entidade do tipo que está a introduzir.</p></html>
<?php 
				return false;
			}
			else
			{
				return true;
			}
		}
	}
	/**
	 * This method will be responsable for populated the form for the user to be able to  edit a selected entity
	*/
	public function editEntity($ent_id)
	{
		$res_EntEdit = $this->bd->runQuery("SELECT * FROM ent_type WHERE id='".$ent_id."'");
		$read_EntToEdit = $res_EntEdit->fetch_assoc();
		
?>		
		<html>
			<h3>Gestão de Componentes - Edição</h3>
				<form id="editForm">
					<label>Nome:</label>
					<br>
					<input type="text" id="nome" name="nome" value="<?php echo $read_EntToEdit['name'] ?>">
					<br>
					<label class="error" for="nome"></label>
					<br>
<?php 
		$stateEnumValues = $this->bd->getEnumValues('ent_type','state');
		foreach($stateEnumValues as $value)
		{
			if($value == 'active')
			{
				if(	$read_EntToEdit['state'] == 'active' )
				{
?>
					<input type="radio" id="atv_int" name="atv_int" value="active" checked="checked" >Ativo
					<br>
<?php 	
				}
				else
				{
?>
					<input type="radio" id="atv_int" name="atv_int" value="active" >Ativo
					<br>
<?php 
				}
			  }
			  else 
			  {
			  	if($read_EntToEdit['state'] == 'inactive')
			  	{
?>
					<input type="radio" id="atv_int" name="atv_int" value="inactive" checked="checked" >Inativo
					<br>
<?php 			
			  	}
			  	else 
			  	{
?>
					<input type="radio" id="atv_int" name="atv_int" value="inactive" >Inativo
					<br>	
<?php 
			  	}
			  }
		}//fim for each
?>
			
				<label class="error" for="atv_int"></label>
				<br>
				<input type="hidden" name="ent_id" value="<?php echo $read_EntToEdit['id'] ?>">
				<input type="hidden" name="estado" value="alteracao">
				<input type="submit" value="Alterar Componente">
			</form>
		</html>
<?php 	}
		
	/**
	 *  This method will check if is everything ok with the submited data and if really is
	 *  it will update the existing entity
	 */
	public function changeEnt() 
	{
		if ($this->ssvalidation ()) // / verifies if all the field are filled and if the name i'm trying to submit exists in ent_type
		{
			$sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);

		//	print_r($_REQUEST);
		//	echo "UPDATE `ent_type` SET `name`=".$sanitizeName.",`state`=".$_REQUEST['atv_int']." WHERE id = ".$_REQUEST['ent_id']."";
                        
                        
                        $id = $this->bd->userInputVal($_REQUEST['ent_id']);
                        if($this->gereHist->addHist($id,$this->bd))
                        {
                            $res_EntTypeAS =  $this->bd->runQuery("UPDATE `ent_type` SET `name`='".$sanitizeName."',`state`='".$_REQUEST['atv_int']."' WHERE id = ".$id."");
                        
                                                    
?>
                		<p>Alterou os dados da entidade com sucesso.</p>
        			<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php 
                        }
                        else
                        {
?>
                            <h3>Gestão de Componentes - Edição</h3>
                            <p>O tipo de entidade não foi alterado.</p>
                            
<?php
                            goBack ();
                        }
                        
                        
		}
		else
		{
			goBack ();
		}
	}

	/**
	 * This method will disable an enttity when we click in desactivar button 
	 */
	public function disableEnt()
	{
                $id = $this->bd->userInputVal($_REQUEST['ent_id']);
                
		$res_EntTypeD = $this->bd->runQuery("SELECT name FROM ent_type WHERE id = ".$id);
		$read_EntTypeD = $res_EntTypeD->fetch_assoc();
                
                if($this->gereHist->addHist($id,$this->bd))
                {
                    $this->bd->runQuery("UPDATE ent_type SET state='inactive', updated_on='".date("Y-m-d H:i:s",time())."' WHERE id =".$id);
?>
                    <p>A entidade <?php echo $read_EntTypeD['name'] ?>  foi desativada</p>
                    <p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php
                }
                else
                {
?>
                        <p>A entidade <?php echo $read_EntTypeD['name'] ?>  não pode ser desativada</p>
<?php
                        goBack();
                }
                
                
?>
			
<?php 		
	}
	
	/**
	 * This method will enable the entity when we click in then activate button 
	 */
	public function enableEnt()
	{
            
                $id = $this->bd->userInputVal($_REQUEST['ent_id']);
                
		$res_EntTypeA = $this->bd->runQuery("SELECT name FROM ent_type WHERE id = ".$id);
		$read_EntTypeA = $res_EntTypeA->fetch_assoc();
		
                
                 if($this->gereHist->addHist($id,$this->bd))
                {
                    $this->bd->runQuery("UPDATE ent_type SET state='active', updated_on='".date("Y-m-d H:i:s",time())."' WHERE id =".$id);
?>                        
		<html>
		 	<p>A entidade <?php echo $read_EntTypeA['name'] ?> foi ativada</p>
		 	<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
		</html>
<?php 		
                }
                else
                {
?>
                        <p>A entidade <?php echo $read_EntTypeA['name'] ?>  não pode ser ativada</p>
<?php
                        goBack();
                }
	}
	
	/**
	 * This method will insert a new entity in the database
	 */
	public function insertEnt()
	{
		if($this->ssvalidation()) 
		{
			//print_R($_REQUEST);
			$sanitizeName = $this->bd->userInputVal($_REQUEST['nome']);
                        
                        //get time stamp 
                        //$time = $_SERVER['REQUEST_TIME'];
			$queryInsert = "INSERT INTO `ent_type`(`id`, `name`, `state`) VALUES (NULL,'".$sanitizeName."','".$_REQUEST['atv_int']."','".date("Y-m-d H:i:s",time())."')";
			$res_querState = $this->bd->runQuery($queryInsert);
                        
                      
?>
				<p>Inseriu os dados de uma nova entidade com sucesso</p>
				<p>Clique em <a href="/gestao-de-entidades"/>Continuar</a> para avançar</p>
<?php 	
		}
		else
		{
			goBack();
		}

	}
}


class EntHist{
    
    public function __construct(){
        
    }
    
    /**
     * This method will add a backup to the mirror table of ent_type all the tuples in that table are
     * @param type $id
     * @return boolean
     */
    public function addHist($id,$bd)
    {
       
        
        //gets info from the ent_type that id about to get changed
        $res_getEntTp = $bd->runQuery("SELECT * FROM ent_type WHERE id=".$id."");
        $read_getEntTp = $res_getEntTp->fetch_assoc();
        //create a copy in the history table  
        if($bd->runQuery("INSERT INTO `hist_ent_type`(`id`, `name`, `state`, `active_on`, `inactive_on`, `ent_type_id`) VALUES (NULL,'".$read_getEntTp['name']."','".$read_getEntTp['state']."','".$read_getEntTp['updated_on']."','".date("Y-m-d H:i:s",time())."',".$id.")"))
        {
            return true;
        }
        else
        {
            return false;
        }
                                     
                        
                        
    }
}
?>
