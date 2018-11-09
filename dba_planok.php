<?php
echo 'dba_planok_chile.'; #recordar envíos de cotizaciones y categorizados por cada integración

$puente = new mysqli('216.70.88.35','portga','kXn@t775','puertotga',3306); 
$planok = new mysqli('54.241.204.124','tga_crond','T_i466pyl8','sv_1001',3306);
$res_gleads = new mysqli('54.176.79.99','tga_crond','T_i466pyl8','res_gleads',3306);

$to = 'agonzalez@tga.cl;mrincon@tga.cl;';
$headers = 'From: dba@alertastga.cl;';
$subject = 'Notificacion: dba_planok_chile';

if ($puente->connect_errno || $planok->connect_errno || $res_gleads->connect_errno){
	echo $txt = '<br>revisar conexion puente, planok y res_gleads';
	mail($to,$subject,$txt,$headers);
	exit;
}else{
		
	date_default_timezone_set ('America/Santiago');
	$fecha_sv = date('Y-m-d H:i:s');
	echo utf8_decode("<br><br>Conexión exitosa: $fecha_sv");
	
	$consulta = $planok->query("
		SELECT
			a.idPlanOk,a.fecha,a.hora,UPPER(a.tipoCliente)AS 'tipoCliente',a.rut,a.dvRut,
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
		WHERE(a.traspaso = 0	
			AND	a.prueba = 0
			AND b.Tga_idProyecto >0
			AND c.Tga_idvendedor >0
			AND b.Tga_idInmobiliaria >0
			AND a.fecha > DATE_SUB(NOW(),INTERVAL 6 MONTH)
		)OR(a.traspaso = 0	
			AND	a.prueba = 0
			AND b.Tga_idProyecto >0
			AND c.Tga_idvendedor =0
			AND b.Tga_idInmobiliaria >0
			AND a.fecha > DATE_SUB(NOW(),INTERVAL 7 DAY)
			AND a.medioLlegada ='COTIZADOR WEB POK'
		)
		ORDER BY a.idPlanOk ASC
		LIMIT 50;
	");
	$resultado = $consulta->num_rows;
	$n = 0;
	$total_procesados = 0;
	
	if($resultado >0){
		while($fila = mysqli_fetch_array($consulta)){
		
			$idConexion 	= trim($fila['idPlanOk']);
			$fecha          = trim($fila['fecha']);
			$hora			= trim($fila['hora']);
							if($hora > '23:59:59'){
								$hora = '00:00:00';
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
			$rut_base 		= $fila['rut'] .'-'. $fila['dvRut'];
			$rut 			= valida_rut(trim($rut_base));
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
			$comentario_base= 'Generado desde Planok';
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
			$precioUf		= trim($fila['total']);
			
			$idSV			= 1001;
			$dondeviene		= 'Planok';
			$idPais			= 30;
			$leads			= '0';
			$gestion        = 0;
			$presencial		= 0;
			$traspaso  		= 0;
			$leadsResto 	= 0;
			$n++;
			
			if($idInmobiliaria == 15 && $idProyecto == 457){
				if(stristr($modelo,'Duplex') == TRUE){
					$idProyecto = 458;
				}
			}
			
			if($tipoCotizacion == 'en sala'){
				$gestion = 1;
				$idPlantillaPortales = 96;
				$idPortal = 105;
				$portal = 'Presencial';
				$presencial = 2;
				$idOpcion = 26;
				$opcion = utf8_decode('Vino a sala de venta y cotizó');
			
			}elseif($tipoCotizacion == 'a distancia' && $medioLlegada == 'cotizador web pok' 
					&& stristr($tipoContacto,'fono') == FALSE
					&& stristr($tipoContacto,'telef') == FALSE
					&& stristr($tipoContacto,'mail') == FALSE ){			
				
				$fecha_web = date('Y-m-d',strtotime($fecha_sv."- 7 days"));
				if($fecha >= $fecha_web){
					if($idInmobiliaria == 2){
						$idPlantillaPortales = 533;
						$idPortal = 181;
						$portal ='Web Desco';
						
					}elseif($idInmobiliaria == 9){
						$idPlantillaPortales = 433;
						$idPortal = 203;
						$portal ='Web Senexco';
						
					}elseif($idInmobiliaria == 10){
						$idPlantillaPortales = 248;
						$idPortal = 194;
						$portal ='Web Lo Campino';
						
					}elseif($idInmobiliaria == 12){
						$idPlantillaPortales = 418;
						$idPortal = 158;
						$portal ='Web Actual';
						
					}elseif($idInmobiliaria == 15){
						$idPlantillaPortales = 560;
						$idPortal = 16;
						$portal ='Web PCM';
					
					}elseif($idInmobiliaria == 37){
						$idPlantillaPortales = 245;
						$idPortal = 192;
						$portal ='Web Sinergia';
						
					}elseif($idInmobiliaria == 41){
						$idPlantillaPortales = 374;
						$idPortal = 146;
						$portal ='Web Beltec';
						
					}elseif($idInmobiliaria == 43){
						$idPlantillaPortales = 246;
						$idPortal = 191;
						$portal ='Web Idea';
						
					}elseif($idInmobiliaria == 78){
						$idPlantillaPortales = 473;
						$idPortal = 302;
						$portal ='Web Ecasa';
						
					}elseif($idInmobiliaria == 80){
						$idPlantillaPortales = 490;
						$idPortal = 306;
						$portal ='Web Nollagam';
						
					}elseif($idInmobiliaria == 88){
						$idPlantillaPortales = 522;
						$idPortal = 325;
						$portal ='Web Leben';
						
					}elseif($idInmobiliaria == 102){
						$idPlantillaPortales = 673;
						$idPortal = 370;
						$portal ='Web Devisa';
						
					}elseif($idInmobiliaria == 107){
						$idPlantillaPortales = 674;
						$idPortal = 363;
						$portal = utf8_decode('Web Raúl del Río');
						
					}elseif($idInmobiliaria == 109){
						$idPlantillaPortales = 664;
						$idPortal = 367;
						$portal = utf8_decode('Web Larraín Prieto');
						
					}elseif($idInmobiliaria == 142){
						$idPlantillaPortales = 732;
						$idPortal = 382;
						$portal = 'Web Madesal';
						
					}elseif($idInmobiliaria == 143){
						$idPlantillaPortales = 746;
						$idPortal = 384;
						$portal = 'Web Brotec';
					}
				}else{
					$traspaso = 6;
				}
			
			}elseif($tipoCotizacion == 'a distancia' && stristr($tipoContacto,'fono') == TRUE ||
					$tipoCotizacion == 'a distancia' && stristr($tipoContacto,'telef') == TRUE ||
					$tipoCotizacion == 'a distancia' && stristr($medioLlegada,'telef') == TRUE){			
				
				$gestion = 1;
				$idPlantillaPortales = 429;
				$idPortal = 274;
				$portal = 'A Distancia';
				$idOpcion = 1;
				$opcion = utf8_decode('Cotizó vía telefónica');
				
			}elseif($tipoCotizacion == 'a distancia' && stristr($tipoContacto,'mail') == TRUE){           
				
				$gestion = 1;
				$idPlantillaPortales = 429;
				$idPortal = 274;
				$portal = 'A Distancia';
				$idOpcion = 25;
				$opcion = utf8_decode('Se contactó vía correo electrónico');
			
			}else{
			
				$leadsResto = 1;
				$gestion = 1;
				$idPlantillaPortales = 429;
				$idPortal = 274;
				$portal = 'A Distancia';
				$idOpcion = 25;
				$opcion = utf8_decode('Se contactó vía correo electrónico');		
			}
			
			echo '<br>------------------------------------------------------- '.$n;	
			echo "<br>idConexion: $idConexion";				

			if( $gestion == 1 && $idUser >0 && $idConexion >0 && $idInmobiliaria >0 && $idProyecto >0 && $idPortal >0 && $portal !='' && $idPlantillaPortales >0 && $traspaso ==0 && 
				$nombre !='' && !is_numeric($nombre) && validar_email($email)==1 && $rut!=0 && $rut!='11111111-1' 
				||
				$gestion == 0 && $idConexion >0 && $idInmobiliaria >0 && $idProyecto >0 && $idPortal >0 && $portal !='' && $idPlantillaPortales >0 && $traspaso ==0 && 
				$nombre !='' && !is_numeric($nombre) && validar_email($email)==1 && $rut!=0 && $rut!='11111111-1'				
				){
				
				$consulta_existe = $puente->query("
					SELECT idCategorizar FROM glead_vendedor_categoriza
					WHERE idInmobiliaria = '".$idInmobiliaria."'
						AND idSV 		= '".$idSV."'
						AND idProyecto 	= '".$idProyecto."'
						AND idPortal 	= '".$idPortal."'
						AND email 		= '".$email."'
						AND idConexion 	= '".$idConexion."'
					LIMIT 1;
				");
				$existe = mysqli_fetch_array($consulta_existe);
				
				if($existe['idCategorizar']>0){
					echo'<br>La cotizacion existe';
					$traspaso = 1;
				}else{
					$consulta_duplicada = $puente->query("
						SELECT idCategorizar FROM glead_vendedor_categoriza
						WHERE idInmobiliaria = '".$idInmobiliaria."'
							AND idSV		= '".$idSV."'
							AND idProyecto 	= '".$idProyecto."'
							AND idUser 		= '".$idUser."'
							AND idPortal 	= '".$idPortal."'
							AND idPlantillaPortales = '".$idPlantillaPortales."'
							AND fechaCotizacion = '".$fecha."'
							AND rut 		= '".$rut."'
							AND unidad 		= '".$unidad."'
							AND programa 	= '".$programa."'
							AND modelo      = '".$modelo."'
							AND email 		= '".$email."'
						LIMIT 1;
					");
					$duplicada = mysqli_fetch_array($consulta_duplicada);
					
					if($duplicada['idCategorizar'] >0){
						echo '<br>La cotizacion esta duplicada';
						$traspaso = 2;
					}else{
						$existe_resto = 0;
						if($leadsResto == 1){#valida si leadResto existe en puertotga
							$consulta_resto = $puente->query("
								SELECT idCategorizar FROM glead_vendedor_categoriza
								WHERE idInmobiliaria = '$idInmobiliaria'
									AND idProyecto = '$idProyecto'
									AND email = '$email'
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
									dondeviene,presencial,precioUf,idUser,comentario,
									sexo,orientacion,unidad,tipoCliente,idSV,
									idPais,gleads,fecha_sv,modelo
								)VALUES(
									'".$idInmobiliaria."','".$idProyecto."','".$idPlantillaPortales."','".$idPortal."','".$portal."',
									'".$fecha."','".$fecha_hora."','".$fecha_hora."','".$nombre."','".$rut."',
									'".$comuna."','".$email."','".$fono."','".$programa."','".$idConexion."',
									'".$dondeviene."','".$presencial."','".$precioUf."','".$idUser."','".$comentario."',
									'".$sexo."','".$orientacion."','".$unidad."','".$tipoCliente."','".$idSV."',
									'".$idPais."','".$leads."','".$fecha_sv."','".$modelo."'
								);
							");

							if(!$inserta_cotizacion){
								echo '<br>Error al insertar la cotizacion';
								$traspaso = 9;
								$txt = "Error al insertar la cotización Proyecto: ".$idProyecto." - Inmobiliaria: ".$idInmobiliaria." - idConexion: ".$idConexion." - ";
								mail($to,$subject,$txt,$headers);
							}else{
								echo '<br>Inserta cotizacion';
								$traspaso = 1;
								$total_procesados++;
								
								if($gestion == 1){
									$consulta_cat = $res_gleads->query("
										SELECT idRepuesta FROM zz_glead_respuestas
										WHERE idInmobiliaria = '".$idInmobiliaria."'
										AND idProyecto = '".$idProyecto."'
										AND Correo = '".$email."'
										AND fechaPuntos > DATE_SUB(NOW(), INTERVAL 8 MONTH)
										ORDER BY fechaPuntos DESC
										LIMIT 1;
									");
									$res_cat = mysqli_fetch_array($consulta_cat);
									$idRespuesta = $res_cat['idRepuesta'];
									
									if($idRespuesta >0){
										$inserta_cat_gestion = $puente->query("
											INSERT INTO glead_gestion_cat(
												idRespuesta,idInmobiliaria,IdProyecto,fecha,idUser,
												email,Comentario,idOpcion,idConexion,idSV,
												idPais,gleads,fecha_sv
											)VALUES(
												'".$idRespuesta."','".$idInmobiliaria."','".$idProyecto."','".$fecha_hora."','".$idUser."',
												'".$email."','".$comentario_gest."','".$idOpcion."','".$idConexion."','".$idSV."',
												'".$idPais."','".$leads."','".$fecha_sv."'
											);
										");

										if(!$inserta_cat_gestion){
											echo '<br>Error al insertar gestion categorizada';
											$txt = "Planok: error al insertar gestion categorizada -Proyecto: ".$idProyecto." -Inmobiliaria: ".$idInmobiliaria." -idConexion: ".$idConexion." - ";
											mail($to,$subject,$txt,$headers);
										}else{
											echo '<br>Inserta gestion cat';
										}
									}else{
										$consulta_idCategoriza = $puente->query("
											SELECT idCategorizar FROM glead_vendedor_categoriza
											WHERE idInmobiliaria = '".$idInmobiliaria."'
											AND idProyecto = '".$idProyecto."'
											AND email = '".$email."'
											AND idConexion = '".$idConexion."'
										");
										$idCat = mysqli_fetch_array($consulta_idCategoriza);
										$idCategoriza = $idCat['idCategorizar'];

										if($idCategoriza >0){
											$inserta_cot_gestion = $puente->query("
												INSERT INTO glead_gestion_cot(
													idCategoriza,idInmobiliaria,idProyecto,fecha,idUser,
													email,idOpcion,opcion,idConexion,comentario,
													idSV,idPais,gleads,fecha_sv
												)VALUES(
													'".$idCategoriza."','".$idInmobiliaria."','".$idProyecto."','".$fecha_hora."','".$idUser."',
													'".$email."','".$idOpcion."','".$opcion."','".$idConexion."','".$comentario_gestion."',
													'".$idSV."','".$idPais."','".$leads."','".$fecha_sv."'
												);
											");
											if(!$inserta_cot_gestion){
												echo '<br>Error al insertar cotizacion gestionada';
												$txt = "Planok: error al insertar cotizacion gestionada -Proyecto: ".$idProyecto." -Inmobiliaria: ".$idInmobiliaria." -idConexion: ".$idConexion." - ";
												mail($to,$subject,$txt,$headers);
											}else{
												echo '<br>Insertar gestion cot';
											}

										}else{
											echo '<br>Error al isertar cot gest, idCategoriza';
											$txt = "Planok: Error al isertar cot gest, falta idCategoriza -Proyecto: ".$idProyecto." -Inmobiliaria: ".$idInmobiliaria." -idConexion: ".$idConexion." - ";
											mail($to,$subject,$txt,$headers);
										}
									}
								}
							}
						}
					}
				}
			}else{
				if($email=='' || validar_email($email)==0){
					$traspaso = 7;
				}elseif($rut =='' || $rut == 0 || $rut =='0-0' || $rut =='1-9' || $rut =='11111111-1'){
					$traspaso = 3;
				}else{
					$traspaso = 8;
				}
			}
			echo '<br>Traspaso: '.$traspaso;
			$planok->query("UPDATE planok SET traspaso = '".$traspaso."' WHERE idPlanOk = '".$idConexion."' LIMIT 1;");
		}#w
    }else{
		echo "<br>Sin resultados";
	}
	
	#ingresos/notificaciones para nuevas cargas de vendedores y proyectos
	$to = 'agonzalez@tga.cl;wbravo@tga.cl;vbecerra@tga.cl;vparedes@tga.cl;ivaldivieso@tga.cl;mrincon@tga.cl;';
	$consulta_proyecto = ("
		SELECT 
			a.idInmobiliaria,UPPER(a.inmobiliaria) AS 'inmobiliaria',a.idProyecto,UPPER(a.proyecto) AS 'proyecto',UPPER(a.etapa) AS 'etapa',
			UPPER(a.subagrupacion) AS 'subagrupacion',MD5(CONCAT(a.idInmobiliaria,a.idProyecto,a.etapa,a.subagrupacion)) as 'llave'
		FROM planok AS a
		WHERE NOT EXISTS(
				SELECT * FROM proyectos as b
				WHERE a.idInmobiliaria = b.Pok_idInmobiliaria
					AND a.idProyecto = b.Pok_idProyecto
					AND a.etapa = b.Pok_etapa
					AND a.subagrupacion = b.Pok_subagrupacion
				)
			AND a.inmobiliaria NOT LIKE '%prueba%'
			AND a.fecha >='2018-04-01'
			AND a.idInmobiliaria NOT IN (55,68,77,111,138,155,196)
		GROUP BY a.idInmobiliaria,a.inmobiliaria,a.idProyecto,a.proyecto,a.etapa,a.subagrupacion
		ORDER BY a.idInmobiliaria,a.idProyecto ASC
		LIMIT 1;
	");
	$resultado_proyecto = $planok->query($consulta_proyecto);

	while($fila_pro = mysqli_fetch_array($resultado_proyecto)){
		if( $fila_pro['idProyecto']!=""){
			$inserta_proyecto = $planok->query("
				INSERT INTO proyectos(
					Pok_idInmobiliaria,Pok_nombreInmobiliaria,
					Pok_idProyecto,Pok_nomProyecto,
					Pok_etapa,Pok_subagrupacion,
					llave
				)VALUES(
				    '".$fila_pro['idInmobiliaria']."','".$fila_pro['inmobiliaria']."',
					'".$fila_pro['idProyecto']."','".$fila_pro['proyecto']."',
					'".$fila_pro['etapa']."','".$fila_pro['subagrupacion']."',
					'".$fila_pro['llave']."'
				);
			");
			if(!$inserta_proyecto){
				echo '<br>Error al insertar proyecto';
				$to = 'agonzalez@tga.cl;mrincon@tga.cl;';
				$subject = "Planok: error al insertar un proyecto";
				$txt = "Proyecto: ".$fila_pro['proyecto']." -Inmobiliaria: ".$fila_pro['inmobiliaria']." - ";
				mail($to,$subject,$txt,$headers);

			}else{
				echo '<br>Se ingresa un nuevo proyecto';
				echo $subject = 'Planok: Se ingresa un nuevo proyecto';
				echo $txt = "Proyecto: ".$fila_pro['proyecto']." - Inmobiliaria: ".$fila_pro['inmobiliaria']." - ";
				mail($to,$subject,$txt,$headers);
			}
		}
	}
	
	$consulta_vendedor = ("
		SELECT rutVendedor,nombreVendedor,idInmobiliaria,inmobiliaria
		FROM planok
		WHERE NOT EXISTS(
			SELECT * FROM vendedores
			WHERE planok.rutVendedor = vendedores.Pok_rut
			AND planok.idInmobiliaria=vendedores.Pok_idInmobiliaria
		)
		AND inmobiliaria NOT LIKE '%prueba%'
		LIMIT 1;
	");
	$resultado_vendedor = $planok->query($consulta_vendedor);
	while($fila_ven = mysqli_fetch_array($resultado_vendedor)){
		if( $fila_ven['rutVendedor']!=""){
			$inserta_vendedor = $planok->query("
				INSERT INTO vendedores (Pok_rut,Pok_nombre,Pok_idInmobiliaria,Pok_nombre_inmobiliaria)
				VALUES ('".$fila_ven['rutVendedor']."','".$fila_ven['nombreVendedor']."','".$fila_ven['idInmobiliaria']."','".$fila_ven['inmobiliaria']."');
			");
			if(!$inserta_vendedor){
				echo '<br>Error al insertar un vendedor';
				$to = 'agonzalez@tga.cl;mrincon@tga.cl;';
				$subject = 'Planok: error al insertar un vendedor';
				$txt = "Vendedor: ".$fila_ven['nombreVendedor']." - Inmobiliaria: ".$fila_ven['inmobiliaria']." - ";
				mail($to,$subject,$txt,$headers);
			}else{
				echo '<br>Se ingresa un nuevo vendedor';
				$subject = 'Planok: Se ingresa un nuevo vendedor';
				$txt = "Vendedor: ".$fila_ven['nombreVendedor']." - Inmobiliaria: ".$fila_ven['inmobiliaria']." - ";
				mail($to,$subject,$txt,$headers);
			}
		}
	}

	mysqli_close($puente);
	mysqli_close($planok);
	mysqli_close($res_gleads);
	echo utf8_decode('<br><br>Cierre de conexión');
	
	echo '<br><br>Total lectura: '.$n;
	echo '<br>Total procesados: '.$total_procesados;
	$idCron = 71; 
	$total = $total_procesados; 
	include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
}
function valida_rut($r){
	$r  = str_replace(" ", "", $r);
	$r  = str_replace("k", "K", $r);
	$r  = str_replace(".", "", $r);
	$r  = str_replace("-", "", $r);
	if (strlen($r)<8 || strlen($r)>9) {return 0;}
	$dv = substr($r,(strlen($r)-1),1);
	$r  = substr($r,0,(strlen($r)-1));
	$rut= $r;
	if(!is_numeric($r)){return 0;}
	$s=1;
	for($m=0;$r!=0;$r/=10)$s=($s+$r%10*(9-$m++%6))%11;
	$v = chr($s?$s+47:75);
	if ($dv!=$v) {return 0;}
	return $rut.'-'.$v;
}
function validar_email($mail){if (preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',$mail)) {return 1;}else{return 0;}}	
?>