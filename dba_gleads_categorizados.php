<?php
echo $nomArchivo = "dba_gleads_categorizados";

$puente 	= new mysqli('216.70.88.35','portga','kXn@t775','puertotga',3306);
$res_gleads = new mysqli('54.176.79.99','tga_crond','T_i466pyl8','res_gleads',3306);

$to = "agonzalez@tga.cl;mrincon@tga.cl";
$subject = "Notificacion: $nomArchivo";
$headers = "From: notificacion_dba@tga.cl";

if($puente->connect_errno || $res_gleads->connect_errno){
	echo $txt = "<br>Revisar conexion a puertotga";
	mail($to,$subject,$txt,$headers);
	exit;
}else{
	date_default_timezone_set ('America/Santiago');
	$fecha_lectura = date("Y-m-d H:i:s");
	echo "<br><br>Inicio de lectura: $fecha_lectura<br>";
	
	$consulta_conexion = $puente->query("
		SELECT id,idInmobiliaria,idSV,localhost,usuario,clave,bdd,fechaCat,ultimoCat
		FROM conexiones AS a
		WHERE estadoSV > 0
			AND estadoEnvio = 1
			AND estadoCat = 1
			AND traspasoCat = 0
			AND id >0
			AND idInmobiliaria >0
			AND localhost !=''
			AND usuario !=''
			AND clave !=''
			AND bdd !=''
			AND fechaCat !=''
		ORDER BY idInmobiliaria ASC
		LIMIT 1; 
	");
	$resultado_con = $consulta_conexion->num_rows;
	$n =0;
	$total_procesados =0;
	if($resultado_con >0){		
		$fila = mysqli_fetch_array($consulta_conexion);
		$id 			= $fila['id'];
		$idInmobiliaria = $fila['idInmobiliaria'];
		$idSV 			= $fila['idSV'];
		$host 			= $fila['localhost'];
		$usuario		= $fila['usuario'];
		$clave 			= $fila['clave'];
		$bdd 			= $fila['bdd'];
		$fechaCat 		= $fila['fechaCat'];
		$ultimoCat  	= $fila['ultimoCat'];
		
		$puente->query("UPDATE conexiones SET traspasoCat = 1 WHERE id ='$id' LIMIT 1;");
		$consulta_estado = $puente->query("SELECT COUNT(*) AS total FROM conexiones WHERE estadoSV > 0 AND estadoEnvio = 1 AND estadoCat = 1 AND traspasoCat = 0;");
		$estado = mysqli_fetch_array($consulta_estado);
		if($estado['total']==0){$puente->query("UPDATE conexiones SET traspasoCat = 0 WHERE estadoSV > 0 AND estadoEnvio = 1 AND estadoCat = 1;");}
		
		$conexion = new mysqli($host,$usuario,$clave,$bdd,3306);
		if($conexion->connect_errno){
			echo $txt = "<br>Revisar conexion sv";
			mail($to,$subject,$txt,$headers);
		}else{		
			if($ultimoCat == ''){$ultimoCat = 0;}
			if($idInmobiliaria >0 && $fechaCat >'0000-00-00' && $ultimoCat >=0){
				#nota: se traspasan todos los estados, la regla antigua era: a.estado in ('A','B','CE')
				$consulta_categorizados = $res_gleads->query("
					SELECT
						a.idRepuesta,a.idProyecto,a.idInmobiliaria,a.fechaPuntos,a.Correo,a.puntos,
						YEAR(a.fechaPuntos)AS 'ano',MONTH(a.fechaPuntos)AS 'mes',DAY(a.fechaPuntos)AS 'dia',
						c.nombre,c.apellido,c.rut,c.telefono,c.mail
					FROM zz_glead_respuestas AS a
					LEFT JOIN zz_glead_datos_personas c ON a.Correo=c.mail AND a.idInmobiliaria=c.idInmobiliaria
					LEFT JOIN zz_glead_proyectos d ON d.idInmobiliaria = a.idInmobiliaria AND d.idProyecto = a.idProyecto
					LEFT JOIN zz_glead_inmobiliaria AS e ON a.idInmobiliaria = e.idInmobiliaria
					WHERE a.idInmobiliaria = '$idInmobiliaria'
						AND a.idRepuesta > '$ultimoCat'
						AND a.prueba = 0
						AND a.cancelada = 0
						AND a.descarte = 0
						AND a.fechaPuntos >= '$fechaCat'
					GROUP BY a.idRepuesta
					ORDER BY a.idRepuesta ASC
					LIMIT 50;
				");
				$resultado = $consulta_categorizados->num_rows;
			}else{
				$resultado =0;
			}			
			if($resultado>0){				
				while ($fila = mysqli_fetch_array($consulta_categorizados)){				
					
					$idConexion 	= trim($fila['idRepuesta']);
					$idCategorizados= trim($fila['idRepuesta']);
					$idInmobiliaria = trim($fila['idInmobiliaria']);
					$idProyecto 	= trim($fila['idProyecto']);
					$fecha 			= trim($fila['fechaPuntos']);
					$ano 			= trim($fila['ano']);
					$mes 			= trim($fila['mes']);
					$dia 			= trim($fila['dia']);
					$nombre 		= trim($fila['nombre']);
					$apellido 		= trim($fila['apellido']);
					$email 			= trim($fila['Correo']);
					$rut 			= trim($fila['rut']);
					$fono 			= trim($fila['telefono']);
					$puntos 		= trim($fila['puntos']);
					$n++;
					
						if($puntos<=24.4){$puntos = '0-25';}
					elseif($puntos<=34.4){$puntos = '25-35';}
					elseif($puntos<=44.4){$puntos = '35-45';}
					elseif($puntos<=54.4){$puntos = '45-55';}
					elseif($puntos<=64.4){$puntos = '55-65';}
					elseif($puntos<=74.4){$puntos = '65-75';}
					elseif($puntos >74.4){$puntos = '75-100';}
					
					$consulta_existe = "";
					if($idSV == 1001 || $idSV == 1002){				
						$consulta_existe = $conexion->query("SELECT COUNT(*) AS 'total_existe' FROM tga_categorizados WHERE idConexion = '$idConexion';");
					}else{
						$consulta_existe = $conexion->query("SELECT COUNT(*) AS 'total_existe' FROM gleads_categorizados WHERE idCategorizados = '$idCategorizados';");
					}
					$fila_existe = mysqli_fetch_array($consulta_existe);
					if($fila_existe['total_existe']>0){
						echo "<br>El categorizado ya existe";
					}else{
						$nombreTabla = "";
						if($idSV == 1001 || $idSV == 1002){	
							$nombreTabla = "tga_categorizados";
						}else{
							$nombreTabla = "gleads_categorizados";
						}
						$consulta_duplicado = $conexion->query("
							SELECT COUNT(*) AS 'total_duplicado' FROM $nombreTabla 
							WHERE idInmobiliaria = '$idInmobiliaria'
								AND idProyecto = '$idProyecto'
								AND YEAR(fecha) = '$ano'
								AND MONTH(fecha) = '$mes'
								AND DAY(fecha) = '$dia'
								AND nombre = '$nombre'
								AND apellido = '$apellido'
								AND email = '$email'
								AND rut = '$rut'
								AND telefono = '$fono'
								AND puntos = '$puntos';
						");					
						$fila_duplicado = mysqli_fetch_array($consulta_duplicado);
						if($fila_duplicado['total_duplicado']>0){
							echo "<br>El categorizado esta duplicado";
						}else{	
						    $inserta_categorizado = "";
							if($idSV == 1001 || $idSV == 1002){
								$inserta_categorizado = $conexion->query("
									INSERT INTO tga_categorizados(
										idProyecto,idInmobiliaria,fecha,nombre,apellido,
										email,rut,telefono,puntos,idConexion
									)VALUES(
										'$idProyecto','$idInmobiliaria','$fecha','$nombre','$apellido',
										'$email','$rut','$fono','$puntos','$idConexion'
									);
								");					
							}else{
								$inserta_categorizado = $conexion->query("
									INSERT INTO gleads_categorizados(
										idCategorizados,idProyecto,idInmobiliaria,fecha,nombre,
										apellido,email,rut,telefono,puntos
									)VALUES(
										'$idCategorizados','$idProyecto','$idInmobiliaria','$fecha','$nombre',
										'$apellido','$email','$rut','$fono','$puntos'
									);
								");
							}					
							if(!$inserta_categorizado){
								echo '<br>Error al insertar el categorizadoen sv';
								$txt = "Error al insertar categorizado -IDI: $idInmobiliaria -IDP: $idProyecto -IDC: $idConexion ";
								mail($to,$subject,$txt,$headers);
							}else{
								echo '<br>Inserta categorizado';
								$total_procesados++;
							}	
						}
					} 
					$ingresa_ultimoCat = $puente->query("UPDATE conexiones SET ultimoCat = '$idConexion' WHERE id = '$id' LIMIT 1;");
					if(!$ingresa_ultimoCat){
						echo '<br>Error al insertar ultimoCat';
						$txt = "Error al insertar ultimoCat -IDI: $idInmobiliaria -IDP: $idProyecto -IDC: $idConexion ";
						mail($to,$subject,$txt,$headers);
					}else{
						echo '<br>Inserta ultimoCat';
					}
				}#w		
			}else{
				echo "<br>Sin resultado de categorizaciones";
			}
		}
	}else{
		echo "<br>Revisar la configuracion de la conexion";
	}
	
	mysqli_close($conexion);
	mysqli_close($res_gleads);
	echo "<br><br>Total leidos: $total_procesados ";
	echo "<br>Total traspasados: $total_procesados ";
	$idCron = 104; 
	$total = $total_procesados; 
	#include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');	
}
?>


