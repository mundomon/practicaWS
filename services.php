<?
// Hacemos el include de la clase bdMySQL
require("bdMysql.php");
require("rest.php");

		// Creamos el objeto
	$bdMysql = new bdMysql();

	// Conectamos a la BDD
	$bdMysql->Connecta();
		

// Que servicio? (Aplicada la regla en .htacces) Este es para obtener del $GET
/*$getService = array_keys($_GET);
$service = $getService[0];
*/

//Tratamos entrada de datos (url y json)

$rest=new Rest();

///////////////// SERVICIOS //////////////////////

//Vemos si es usuario o grupo
switch($rest->datosURL[0]){
	case 'usuario':
		procesarUsuario($rest,$bdMysql);
		break;
		
	case 'grupo':
		procesarGrupo($rest,$bdMysql);
		break;
		
	default:
		$rest->mostrarRespuesta('ERROR',400);//echo '<br>ERROR 400, no se puede procesar esta peticion';
		break;
}

//servicios para USUARIO
function procesarUsuario($data,$bdMysql){
	
	//servicio /usuario/
	if(count($data->datosURL)==1){ 
		if($data->metodo=='GET'){
			$consulta = "SELECT * FROM usuarios";
			$result = $bdMysql->jsonQuery($consulta);
			$data->mostrarRespuesta($result,200);//echo '<br>OK Operacion realizada con exito'.$result;
		}
		else if($data->metodo=='POST'){
			//obtenemos los valores a insertar de los parametros JSON si existen, si no los ponemos al valor por defecto
			$self='/rest/'.array_keys($_GET)[0]; 
			$alias=(array_key_exists('alias', $data->datosJSON))? $data->datosJSON["alias"] : null;
			$name=(array_key_exists('name', $data->datosJSON))? $data->datosJSON["name"] : null;
			$surname=(array_key_exists('surname', $data->datosJSON))? $data->datosJSON["surname"] : null;
			$age=(array_key_exists('age', $data->datosJSON))? $data->datosJSON["age"] : '0';
			$phone=(array_key_exists('phone', $data->datosJSON))? $data->datosJSON["phone"] : '0'; 
			$group=(array_key_exists('group', $data->datosJSON))? $data->datosJSON["group"] : ''; 
			$photo=(array_key_exists('photo', $data->datosJSON))? $data->datosJSON["photo"] : ''; 
			
			if($alias==null || $name==null || $surname==null || !ctype_digit($age) || !ctype_digit($phone)){
				$data->mostrarRespuesta('ERROR',400);//echo '<br>ERROR 400 faltan parametros';
			}
			else{
				$consulta="SELECT * FROM usuarios WHERE alias='$alias'";
				if($bdMysql->numRowsQuery($consulta)>0) {
					$data->mostrarRespuesta('ERROR',409); //echo '<br>ERROR 409 el usuario ya existe';
				}
				else{
					if($group!=''){
						//insrerto el grupo nuevo si no existia
						$consultaGrupo="SELECT * FROM grupos where name='$group'";
						if($bdMysql->numRowsQuery($consultaGrupo)<=0) {
							$self='/rest/grupo/'.$group;
							$bdMysql->Query("INSERT INTO grupos (self,name) values ('$self','$group')");
						}
					}	
					$consulta2="INSERT INTO usuarios (self,alias,name,surname,age,phone,`group`,photo) values ('$self','$alias','$name','$surname',$age,$phone,'$group','$photo');";
					$result=$bdMysql->Query($consulta2);
					$consulta3="SELECT id FROM usuarios WHERE id=".$bdMysql->lastIdQuery();
					$mostra=$bdMysql->jsonQuery($consulta3);
					$data->mostrarRespuesta($mostra,201); //echo '<br>OK HTTP 201 UsuarioId: '.mysql_insert_id(); //Muestra el ultimo id generado en consulta
				}	
			}
		}
		else{
			$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR operación no VALIDA, solo GET o POST';
		}	
	}
	//servicio /usuario/{id}
	else if(count($data->datosURL)==2){
		if(ctype_digit($data->datosURL[1])){
			$id=$data->datosURL[1];
			$consultaValidacio="SELECT * FROM usuarios WHERE id=$id";
			if($bdMysql->numRowsQuery($consultaValidacio)<=0){
				$data->mostrarRespuesta('ERROR',404); //$id no existe	
			}
			
			//GET
			if($data->metodo=='GET'){
				$consulta="SELECT * FROM usuarios WHERE id=$id";
				$result = $bdMysql->jsonQuery($consulta);
				$data->mostrarRespuesta($result,200); //echo '<br>OK HTTP 200 get correcto';
			}
			//DELETE
			else if($data->metodo=='DELETE'){
				$consulta="DELETE FROM usuarios WHERE id=$id";
				$result = $bdMysql->Query($consulta);
				$data->mostrarRespuesta('',200); //echo '<br>OK HTTP 201 elemento eliminado';
			}
			else{
				$data->mostrarRespuesta('ERROR',400); //echo '<br> ERROR Operacion no valida solo GET y DELETE';
			}
		}
		else{
			$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR HTTP 400 el id tiene que ser un entero';
		}
	}
	//servicio /usuario/{id}/*
	else if(count($data->datosURL)==3){
		if(ctype_digit($data->datosURL[1])){
			$id=$data->datosURL[1];
			$propiedad=$data->datosURL[2];
			
			$consultaValidacio="SELECT * FROM usuarios WHERE id=$id";
			if($bdMysql->numRowsQuery($consultaValidacio)<=0){
				$data->mostrarRespuesta('ERROR',404); //$id no existe	
			}
			
			
			//GET
			if($data->metodo=='GET'){
				$consulta="SELECT self,`$propiedad` FROM usuarios WHERE id=$id";
				$result = $bdMysql->aQuery($consulta);
				//comprovamos que exita la propiedad y mostramos los resultados
				if($propiedad=='self' || $propiedad=='alias'|| $propiedad=='name' || $propiedad=='surname' || $propiedad=='photo'){
					if($result["$propiedad"]==''){
						$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';
					}
					else{
						$mostra = $bdMysql->jsonQuery($consulta);
						$data->mostrarRespuesta($mostra,200); //echo '<br>OK HTTP 200 get correcto';
					}
				}
				else if($propiedad=='id' || $propiedad=='age' || $propiedad=='phone'){
					if($result["$propiedad"]==0){
						$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';	
					}
					else{
						$mostra = $bdMysql->jsonQuery($consulta);
						$data->mostrarRespuesta($mostra,200); //echo '<br>OK HTTP 200 get correcto';
					}
				}
				else if($propiedad=='group'){		
					if($result["$propiedad"]==''){
						$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';
					}
					else{
						$consulta2="SELECT * FROM grupos WHERE name='".$result["$propiedad"]."'";
						$mostra = $bdMysql->jsonQuery($consulta2);
						$data->mostrarRespuesta($mostra,200);//echo '<br>OK HTTP 200 get correcto';	
					}
				}
				else{
					$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';
				}
			}
			
			//DELETE
			else if($data->metodo=='DELETE'){
				if($propiedad=='self' || $propiedad=='alias'|| $propiedad=='name' || $propiedad=='surname' || $propiedad=='group' || $propiedad=='photo'){
					$consulta="UPDATE usuarios SET `$propiedad`='' WHERE id=$id";
					$result = $bdMysql->Query($consulta);
					$data->mostrarRespuesta('',200); //echo '<br>OK HTTP 200 propiedad eliminada';
				}
				else if($propiedad=='id' || $propiedad=='age' || $propiedad=='phone'){
					$consulta="UPDATE usuarios SET $propiedad=0 WHERE id=$id";
					$result = $bdMysql->Query($consulta);
					$data->mostrarRespuesta('',200); //echo '<br>OK HTTP 201 propiedad eliminada';
				}
				else{
					$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';
				}	
			}
			
			//PUT
			else if($data->metodo=='PUT'){
				if(array_key_exists("$propiedad", $data->datosJSON)){
					$valorValido=true;
					$valor=$data->datosJSON["$propiedad"];
										
					//validamos valores de id, phone, age (numericos) 
					if($propiedad=='phone' || $propiedad=='age' || $propiedad=='id'){
						if(!ctype_digit($valor)){
							$valorValido=false;
						}
						//y que id no sea repetido
						else if($propiedad=='id'){
							$consultaValidacio="SELECT * FROM usuarios WHERE id=$valor";
							if($bdMysql->numRowsQuery($consultaValidacio)>0){
								$valorValidacio=false;
								$data->mostrarRespuesta('ERROR',409); //conflicto ids
							}
						}
					}
					
					//validamos que photo sea Base64
					else if($propiedad=='photo'){
						//compruebo que empiece con 'data'
						if (strncmp($valor,'data',4)!=0){
							$valorValido=false;
						}
					}
					else if($propiedad=='alias'){
						$consultaValidacio="SELECT * FROM usuarios WHERE alias='$valor'";	
						if($bdMysql->numRowsQuery($consultaValidacio)>0){
							$valorValidacio=false;
							$data->mostrarRespuesta('ERROR',409); //conflicto alias
						}
					}

					//Ejecutamos PUT si los datos son validos
					if($valorValido){
						$consulta="UPDATE usuarios SET `$propiedad`='$valor' WHERE id=$id";
						if($propiedad=='id') $id=$valor;
						$bdMysql->Query($consulta);
						
						//casos especiales group, tengo que añadirlo en tabla GRUPOS
						if($propiedad=='group'){
							//miramos si el grupo existe, si no lo creamos
							$consultaGrup="SELECT name FROM grupos WHERE name='$valor'";
							
							if($bdMysql->numRowsQuery($consultaGrup)<=0) {
								$self='/rest/grupo/'.$valor;
								$consulta3="INSERT INTO grupos (self,name) values ('$self','$valor')";
								$bdMysql->Query($consulta3);
							}	
						}
						$consulta2="SELECT * FROM usuarios WHERE id=$id";
						$mostra = $bdMysql->jsonQuery($consulta2);
						$data->mostrarRespuesta($mostra,200); //echo '<br>OK HTTP 200 PUT correcto';
					}
					else{
						$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR HTTP 400 valor no valido';
					}
				}
				else{
					$data->mostrarRespuesta('ERROR',404); //echo '<br>ERROR HTTP 404 Not Found. No tiene esa propiedad definida';
				}
			}
			else{
				$data->mostrarRespuesta('ERROR',400); //echo '<br> ERROR Operacion no valida solo GET, PUT y DELETE';
			}
		}
		else{
			$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR HTTP 400 el id tiene que ser un entero';
		}
	}
	else{
		$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR Numero de parametros incorrecto';
	}
}



//servicios para GRUPO
function procesarGrupo($data,$bdMysql){
	//servicio /grupo/
	if(count($data->datosURL)==1){ 
		if($data->metodo=='GET'){
			$consulta = "SELECT * FROM grupos";
			$result = $bdMysql->jsonQuery($consulta);
			$data->mostrarRespuesta($result, 200);//'<br>OK Operacion realizada con exito'.$result;
		}
		else if($data->metodo=='POST'){
			//obtenemos los valores a insertar de los parametros JSON si existen, si no los ponemos al valor por defecto 
			$name=(array_key_exists('name', $data->datosJSON))? $data->datosJSON["name"] : null;
				
			if($name==null){
				$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR 400 faltan parametros';
			}
			else{
				$self='/rest/grupo/'.$name;
				$consulta="SELECT * FROM grupos WHERE name='$name'";
				if($bdMysql->numRowsQuery($consulta)>0){
					$data->mostrarRespuesta('ERROR',409); //echo '<br>ERROR 409 el grupo ya existe';
				}
				else{
					$consulta2="INSERT INTO grupos (self,name) values ('$self','$name');";
					$resultat2=$bdMysql->Query($consulta2);
					
					$consulta3="SELECT * FROM grupos";
					$resultat3=$bdMysql->jsonQuery($consulta);
					$data->mostrarRespuesta($resultat3,201); //echo '<br>OK HTTP 201 Grupos: ';
					
				}	
			}	
		}
		else{
			$data->mostrarRespuesta('ERROR',400); //echo '<br>ERROR operación no VALIDA, solo GET o POST';
		}
	}
}

//////////////////////////////////////////////////

// Desconectamos de la BDD
$bdMysql->Desconnecta();
?>
