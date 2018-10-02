<?php
echo $nombreArchivo = "dba_planok_peru"; 
  
$puente = new mysqli('216.70.88.35','portga','kXn@t775','puertotga',3306); 
$planok = new mysqli('54.241.204.124','tga_crond','T_i466pyl8','sv_1002',3306); 
$res_gleads = new mysqli('54.176.79.99','tga_crond','T_i466pyl8','res_gleads',3306); 

	$to = "agonzalez@tga.cl;mrincon@tga.cl";
	$subject = $nombreArchivo;
	$headers = "From: notificaciones_dba@tga.cl";

if($puente->connect_errno || $planok->connect_errno || $res_gleads->connect_errno){
	echo $txt = "Error de conexion";
	mail($to,$subject,$txt,$headers);
	exit;
}else{
	date_default_timezone_set ('America/Santiago');
	$fecha_sv = date("Y-m-d H:i:s");
	echo '<br>Inicio lectura: '.$fecha_sv;
	
	function validar_email($mail){if (preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',$mail)){return 1;}else{return 0;}}
		
	$consulta = ("
		SELECT 
			a.idPlanOk,a.fecha,a.hora,UPPER(a.tipoCliente)AS 'tipoCliente',a.rut,
			UPPER(CONCAT(TRIM(a.nombre),' ',TRIM(a.apellidoPaterno),' ',TRIM(a.apellidoMaterno)))AS 'nombre',
			UPPER(a.sexo)AS 'sexo',UPPER(a.comunaCliente)AS 'comuna',
			CONCAT(TRIM(a.fonoCliente),' ',TRIM(a.celularCliente))AS 'fono',
			LOWER(a.emailCliente)AS 'email',
			LOWER(TRIM(a.tipoCotizacion))AS 'tipoCotizacion',LOWER(TRIM(a.medioLlegada))AS 'medioLlegada',
			LOWER(TRIM(a.tipoContacto))AS 'tipoContacto',a.producto,a.modelo,a.programa,
			CONCAT(UPPER(LEFT(a.orientacion,1)),SUBSTR(LOWER(a.orientacion),2))AS 'orientacion',
			LOWER(a.comentario) AS 'comentario',
			b.Tga_idProyecto,b.Tga_idInmobiliaria,c.Tga_idvendedor,
			a.idInmobiliaria,a.total
		FROM planok AS a
			LEFT JOIN proyectos AS b 
				ON 	a.idInmobiliaria = b.Pok_idInmobiliaria 
				AND a.idProyecto = b.Pok_idProyecto
				AND a.etapa = b.Pok_etapa 
				AND a.subagrupacion = b.Pok_subagrupacion
				AND b.estado = 1 
			LEFT JOIN vendedores AS c 
				ON  c.Pok_rut = a.rutVendedor 
				AND c.Pok_idInmobiliaria =a.idInmobiliaria
		WHERE a.traspaso = 0
			AND	a.prueba = 0	
			AND b.Tga_idProyecto >0
			AND c.Tga_idvendedor >0
			AND b.Tga_idInmobiliaria >0
			AND a.fecha > DATE_SUB(NOW(),INTERVAL 6 MONTH)
		ORDER BY a.idPlanOk ASC
		LIMIT 50;
	");
	$resultado = $planok->query($consulta);
	
	$n =0;
	$total_procesados =0;
	while($fila = mysqli_fetch_array($resultado)){
		
		$idConexion   	= trim($fila['idPlanOk']);
		$fecha          = trim($fila['fecha']);
		$hora			= trim($fila['hora']);
							if($hora > '23:59'){
								$hora = '00:00';
							}
		$fecha_hora     = trim($fecha." ".$hora);		
		$tipoCliente   	= trim($fila['tipoCliente']);
							if($tipoCliente == 'NATURAL'){
								$tipoCliente = 1;
							}elseif($tipoCliente == 'JURIDICA'){
								$tipoCliente = 2;
							}else{
								$tipoCliente = 0;
							}
		$rut            = trim($fila['rut']);
							if($rut =='' || $rut ==0){
								$rut = utf8_decode('Sin Información');
							}
		$limpia      	= array("'","-","_",".");
		$nombre_base    = trim($fila['nombre']);		
		$nombre    		= trim(str_replace($limpia,'',$nombre_base));
		$sexo			= trim($fila['sexo']);
							if($sexo =='MASCULINO'){
								$sexo =1;
							}elseif($sexo =='FEMENINO'){
								$sexo =2;
							}else{
								$sexo=0;
							}
        $comuna 		= trim($fila['comuna']);
							if($comuna ==''){
								$comuna = utf8_decode('Sin Información');
							}
		$fono	    	= trim($fila['fono']);
							if($fono =='Ingrese su celu' || $fono ==''){
								$fono == utf8_decode('Sin Información');
							}
		$email          = trim($fila['email']);
		$tipoCotizacion	= trim($fila['tipoCotizacion']);
		$medioLlegada	= trim($fila['medioLlegada']);
		$tipoContacto 	= trim($fila['tipoContacto']);
		$unidad       	= trim($fila['producto']);
		$modelo 		= trim($fila['modelo']);
		$programa      	= trim($fila['programa']);
							if($programa ==''){
								$programa = utf8_decode('Sin Información');
							}
		$orientacion 	= trim($fila['orientacion']);		
		$comentario_base= utf8_decode('Generado desde Planok Perú');
		$comentario_pok	= trim($fila['comentario']);
		
		$comentario_cot	= $comentario_base;
		$comentario_gest= "";
							if($comentario_pok!=''){
								$comentario_gest = $comentario_pok.' - '.$comentario_base;
							}else{
								$comentario_gest = $comentario_base;
							}		
		$idProyecto 	= trim($fila['Tga_idProyecto']);
		$idInmobiliaria	= trim($fila['Tga_idInmobiliaria']);
		$idUser       	= trim($fila['Tga_idvendedor']);
		$idInmobiliaria_planok = trim($fila['idInmobiliaria']);
				
		$precio_base   	= trim($fila['total']);
		$precioPeso   	= trim($fila['total']);
		$precioUf     	= 0;
							if($idInmobiliaria == 136 && $idInmobiliaria_planok == 222){ #senda soles
								$precio_total = str_replace(',','.',$precio_base);
								$precioPeso = number_format($precio_total, 2, ',', '.'); #formato sol 000.000,00
							}									
		$idSV			= 1002;
		$dondeviene		= utf8_decode('Planok Perú');
		$idPais			= 116; 
		$leads			= '0';
		$gestion        = 0; 
		$presencial		= 2;
		$traspaso  		= 0;
		$leadsResto 	= 0;
		$n++;
		
		if($tipoCotizacion == 'en sala'){			
			
			$gestion = 1;
			$idPlantillaPortales = 96;
			$idPortal = 105;
			$portal = 'Presencial';
			$idOpcion = 26;
			$opcion = utf8_decode('Vino a sala de venta y cotizó');
		
		}elseif($tipoCotizacion == 'a distancia' && $medioLlegada == 'cotizador web pok'||
				$tipoCotizacion == 'a distancia' && stristr($tipoContacto,"web") == TRUE){			
			
			$fecha_web = date("Y-m-d",strtotime($fecha_sv."- 7 days")); 
			if($fecha >= $fecha_web){
				if($idInmobiliaria == 136){
					$portal ='Web Senda';
					$idPortal = 378;
					$idPlantillaPortales = 716;
					$presencial = 0;
				}
			}else{
				$traspaso = 6;
			}
			
		}elseif($tipoCotizacion == 'A Distancia' && $medioLlegada == 'viva met' && $idInmobiliaria == 136){
			
			$portal ='Landing';
			$idPortal = 330;
			$idPlantillaPortales = 786;
			$presencial = 0;
			
		}elseif($tipoCotizacion == 'a distancia' && stristr($tipoContacto,"telef") == TRUE ||
				$tipoCotizacion == 'a distancia' && stristr($medioLlegada,"telef") == TRUE){				
			
			$gestion = 1;
			$idPlantillaPortales = 429;
			$idPortal = 274;
			$portal = utf8_decode('A Distancia');
			$idOpcion = 1;
			$opcion = utf8_decode('Cotizó vía telefónica');
			
		}elseif($tipoCotizacion == 'a distancia' && stristr($tipoContacto,"mail") == TRUE){            
			
			$gestion = 1;
			$idPlantillaPortales = 429;
			$idPortal = 274;
			$portal = utf8_decode('A Distancia');
			$idOpcion = 25;
			$opcion = utf8_decode('Se contactó vía correo electrónico');
			
		}else{
			
			$leadsResto = 1;
			$gestion = 1;
			$idPlantillaPortales = 429;
			$idPortal = 274;
			$portal = utf8_decode('A Distancia');
			$idOpcion = 25;
			$opcion = utf8_decode('Se contactó vía correo electrónico');			
		}
		
		echo "<br>------------------------------------------------------- ".$n;	
		
		if( $idConexion >0 && $nombre !='' && !is_numeric($nombre) && $email!='' && validar_email($email)==1 &&
			$idProyecto >0 && $idInmobiliaria >0 && $idPortal >0 && $portal !='' && $idPlantillaPortales >0 && $traspaso ==0){
            			
			$consulta_existe = ("
				SELECT idCategorizar FROM glead_vendedor_categoriza
				WHERE idInmobiliaria = '$idInmobiliaria'
				AND idProyecto = '$idProyecto'
				AND idPortal = '$idPortal'
				AND email = '$email'
				AND idSV = '$idSV'
				AND idConexion = '$idConexion'
				LIMIT 1;
			");
			$resultado_existe = $puente->query($consulta_existe);
			$existe = mysqli_fetch_array($resultado_existe);

			if($existe['idCategorizar']>0){
				echo'<br>La cotizacion existe';
				$traspaso = 1;
		    }else{				
				$consulta_duplicado = ("
					SELECT idCategorizar FROM glead_vendedor_categoriza
					WHERE idInmobiliaria 		= '$idInmobiliaria'
						AND idProyecto 			= '$idProyecto'
						AND idPlantillaPortales = '$idPlantillaPortales'
						AND idPortal 			= '$idPortal'
						AND idUser 				= '$idUser'
						AND idSV                = '$idSV'
						AND fechaCotizacion 	= '$fecha'
						AND nombre 				= '$nombre'
						AND rut 				= '$rut'
						AND email 				= '$email'
						AND programa 			= '$programa'
						AND unidad 				= '$unidad'
					LIMIT 1;
				");	
				$resultado_duplicado = $puente->query($consulta_duplicado);
				$duplicado = mysqli_fetch_array($resultado_duplicado);

				if($duplicado['idCategorizar']>0){
					echo'<br>La cotizacion esta duplicada';
					$traspaso = 2;
				}else{					
					$existe_resto = 0;
					if($leadsResto == 1){ #valida si leadResto existe el ultimo mes
						$consulta_resto = $puente->query("
							SELECT idCategorizar FROM glead_vendedor_categoriza
							WHERE idInmobiliaria = '$idInmobiliaria'
								AND idProyecto = '$idProyecto'
								AND email = '$email'
								AND fechaCotizacion > DATE_SUB(NOW(),INTERVAL 1 MONTH)								
							LIMIT 1;
						");
						$resultado_resto = mysqli_fetch_array($consulta_resto);
						$existe_resto = $resultado_resto['idCategorizar'];						
					}
					if($existe_resto >0){
						$traspaso = 4;
					}else{				
						$inserta_cotizacion = $puente->query("
							INSERT INTO glead_vendedor_categoriza(
								idInmobiliaria,idProyecto,idPlantillaPortales,idPortal,portal,
								fechaCotizacion,fechaIngreso,fechaCategorizado,nombre,rut,
								comuna,email,fono,programa,idConexion,
								dondeviene,presencial,precioPeso,idUser,comentario,
								sexo,orientacion,unidad,tipoCliente,idSV,
								idPais,gleads,precioUf,fecha_sv,modelo
							)VALUES(
								'$idInmobiliaria','$idProyecto','$idPlantillaPortales','$idPortal','$portal',
								'$fecha','$fecha_hora','$fecha_hora','$nombre','$rut',
								'$comuna','$email','$fono','$programa','$idConexion',
								'$dondeviene','$presencial','$precioPeso','$idUser','$comentario_cot',
								'$sexo','$orientacion','$unidad','$tipoCliente','$idSV',
								'$idPais','$leads','$precioUf','$fecha_sv','$modelo'
							);
						");

						if(!$inserta_cotizacion){
							echo '<br>Error al insertar la cotizacion';
							$traspaso = 9;
							$txt = "Error al insertar la cotización -IDP: $idProyecto -IDI: $idInmobiliaria -IDC: $idConexion";
							mail($to,$subject,$txt,$headers);
						}else{
							echo '<br>Inserta cotizacion';
							$traspaso = 1;
							$total_procesados++;						

							if($gestion == 1){
								$consulta_cat = $res_gleads->query("
									SELECT idRepuesta FROM zz_glead_respuestas
									WHERE idInmobiliaria= '$idInmobiliaria'
									AND idProyecto 		= '$idProyecto'
									AND Correo 			= '$email'
									AND fechaPuntos > DATE_SUB(NOW(), INTERVAL 8 MONTH)
									ORDER BY fechaPuntos DESC
									LIMIT 1;
								");
								$idRes = mysqli_fetch_array($consulta_cat);
								$idRespuesta = $idRes['idRespuesta'];

								if($idRespuesta >0){
									$inserta_cat_gestion = $puente->query("
										INSERT INTO glead_gestion_cat(
											idRespuesta,idInmobiliaria,IdProyecto,fecha,idUser,
											email,Comentario,idOpcion,idConexion,idSV,
											idPais,gleads,fecha_sv
										)VALUES(
											'$idRespuesta','$idInmobiliaria','$idProyecto','$fecha_hora','$idUser',
											'$email','$comentario_gest','$idOpcion','$idConexion','$idSV',
											'$idPais','$leads','$fecha_sv'
										);
									");

									if(!$inserta_cat_gestion){
										echo '<br>Error al insertar gestion categorizada';
										$txt = "Error al insertar gestion categorizada - IdP: $idProyecto -IdI: $idInmobiliaria -IdC: $idConexion - ";
										mail($to,$subject,$txt,$headers);
									}else{
										echo '<br>Inserta gestion categorizada';
									}
								}else{
									$consulta_idCategoriza = ("
										SELECT idCategorizar FROM glead_vendedor_categoriza
										WHERE idInmobiliaria= '$idInmobiliaria'
										AND idProyecto 		= '$idProyecto'
										AND email 			= '$email'
										AND idSV 			= '$idSV'
										AND idConexion 		= '$idConexion'
										LIMIT 1;
									");
									$resultado_idCategoriza = $puente->query($consulta_idCategoriza);
									$idCat = mysqli_fetch_array($resultado_idCategoriza);
									$idCategoriza = $idCat['idCategorizar'];

									if($idCategoriza >0){
										$inserta_cot_gestion = $puente->query("
											INSERT INTO glead_gestion_cot(
												idCategoriza,idInmobiliaria,idProyecto,fecha,idUser,
												email,idOpcion,opcion,idConexion,comentario,
												idSV,idPais,gleads,fecha_sv
											)VALUES(
												'$idCategoriza','$idInmobiliaria','$idProyecto','$fecha_hora','$idUser',
												'$email','$idOpcion','$opcion','$idConexion','$comentario_gest',
												'$idSV','$idPais','$leads','$fecha_sv'
											);
										");
										if(!$inserta_cot_gestion){
											echo '<br>Error al insertar cotizacion gestionada';
											$txt = "Error al insertar cotizacion gestionada - IdP:$idProyecto -IdI:$idInmobiliaria -IdC:$idConexion ";
											mail($to,$subject,$txt,$headers);
										}else{
											echo '<br>Inserta cotizacion gestionada';
										}
									}else{
										echo '<br>Error al insertar cotizacion gestionada, no encuentra idCategoriza';
										$txt = "Error al insertar cotización gestionada, no encuentra idCategoriza -IdP:$idProyecto -IdI:$idInmobiliaria -IdC:$idConexion ";
										mail($to,$subject,$txt,$headers);
									}
								}
							}
						}
					}
				}
			}
		}else{
			if($traspaso == 6 || $traspaso == 4){
			}elseif($nombre =='' || is_numeric($nombre)){
				$traspaso = 5;
			}elseif($email=='' || validar_email($email)==0){
				$traspaso = 7;
			}else{
				$traspaso = 8;
			}			
		}
		echo '<br>Traspaso: '.$traspaso;
		$planok->query("UPDATE planok SET traspaso = '$traspaso' WHERE idPlanOk = '$idConexion' LIMIT 1;");
	}#w
    
	echo "<br>------------------------------------------------------- ";	
	#notificaciones para nuevas cargas de vendedores y proyectos
	$to = 'agonzalez@tga.cl;wbravo@tga.cl;vbecerra@tga.cl;ivaldivieso@tga.cl;mrincon@tga.cl';

	$consulta_proyecto = $planok->query("
		SELECT 
			a.idProyecto,UPPER(a.proyecto)AS 'proyecto',UPPER(a.etapa)AS 'etapa',
			UPPER(a.subagrupacion)AS 'subagrupacion',a.idInmobiliaria,UPPER(a.inmobiliaria)AS 'inmobiliaria'
		FROM planok AS a
		WHERE NOT EXISTS(
			SELECT * FROM proyectos AS b
			WHERE a.idProyecto = b.Pok_idProyecto 
				AND a.idInmobiliaria = b.Pok_idInmobiliaria
				AND a.etapa = b.Pok_etapa
				AND a.subagrupacion = b.Pok_subagrupacion
		)
		AND a.fecha >= DATE_SUB(NOW(),INTERVAL 6 MONTH)
		GROUP BY a.idProyecto,a.proyecto,a.etapa,a.subagrupacion,a.idInmobiliaria,a.inmobiliaria 
		ORDER BY a.idInmobiliaria,a.idProyecto ASC
		LIMIT 10;
	");
	$resultadop = $consulta_proyecto->num_rows;
	if($resultadop >0){
		echo '<br>Se registra un nuevo proyecto'; 
		$contenido = '';
		while($fila = mysqli_fetch_array($consulta_proyecto)){
			if( $fila['idProyecto']!=''){
				$inserta_proyecto = $planok->query("
					INSERT INTO proyectos (
						Pok_idInmobiliaria,Pok_nombreInmobiliaria,Pok_idProyecto,Pok_nomProyecto,
						Pok_etapa,Pok_subagrupacion,llave						
					)VALUES(
						'".$fila['idInmobiliaria']."','".$fila['inmobiliaria']."','".$fila['idProyecto']."','".$fila['proyecto']."',
						'".$fila['etapa']."','".$fila['subagrupacion']."',
						MD5(CONCAT(
							'".$fila['idInmobiliaria']."','".$fila['inmobiliaria']."','".$fila['idProyecto']."','".$fila['proyecto']."',
							'".$fila['etapa']."','".$fila['subagrupacion']."'						
						))						
					);
				");
				if(!$inserta_proyecto){
					$to = "agonzalez@tga.cl;mrincon@tga.cl";
					$contenido = "$contenido 
					<br>ERROR PROYECTO ".$fila['proyecto'].", ".$fila['inmobiliaria']." "; 
				}else{
					$contenido = "$contenido 
					<br>INGRESO PROYECTO ".$fila['proyecto'].", ".$fila['inmobiliaria']." ";
				}			
			}
		}		
		$txt = "$contenido";
		mail($to,$subject,$txt,$headers);
	}else{
		echo '<br>Sin notificaciones de proyectos'; 
	}
	
	$consulta_vendedor = $planok->query("
		SELECT a.rutVendedor,UPPER(a.nombreVendedor)AS 'nombreVendedor',a.idInmobiliaria,UPPER(a.inmobiliaria)AS 'inmobiliaria' 
		FROM planok AS a
		WHERE NOT EXISTS(
			SELECT * FROM vendedores AS b
			WHERE a.rutVendedor = b.Pok_rut 
			AND a.idInmobiliaria= b.Pok_idInmobiliaria
		)
		AND a.fecha >= DATE_SUB(NOW(),INTERVAL 6 MONTH)
		GROUP BY a.rutVendedor,a.nombreVendedor,a.idInmobiliaria,a.inmobiliaria 
		ORDER BY a.idInmobiliaria,a.rutVendedor ASC
		LIMIT 10;
	");
	$resultadov = $consulta_vendedor->num_rows;
	if($resultadov >0){
		echo '<br>Se registra un nuevo vendedor'; 
		$contenido = '';
		while($fila = mysqli_fetch_array($consulta_vendedor)){
			$inserta_vendedor = $planok->query("
				INSERT INTO vendedores(
					Pok_rut,Pok_nombre,Pok_idInmobiliaria,Pok_inmobiliaria
				)VALUES(
					'".$fila['rutVendedor']."','".$fila['nombreVendedor']."','".$fila['idInmobiliaria']."','".$fila['inmobiliaria']."'
				);
			");
			if(!$inserta_vendedor){
				$to = "agonzalez@tga.cl;mrincon@tga.cl";
					$contenido = "$contenido 
					<br>ERROR VENDEDOR ".$fila['nombreVendedor'].", ".$fila['idInmobiliaria']." ";
			}else{
				$contenido = "$contenido 
					<br>INGRESO VENDEDOR ".$fila['nombreVendedor'].", ".$fila['idInmobiliaria']." ";
			}		
		}		
		$txt = "$contenido";
		mail($to,$subject,$txt,$headers);
	}else{
		echo '<br>Sin notificaciones de vendedores'; 
	}
	
	mysqli_close($planok);
	mysqli_close($puente);
	mysqli_close($res_gleads);
	echo "<br>------------------------------------------------------- ";
	echo '<br>Total calculados: '.$n;
	echo '<br>Total procesados: '.$total_procesados;
	$idCron = 74;
	$total = $total_procesados;
	include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
    echo '<br>Fin lectura';
}
?>