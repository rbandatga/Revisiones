<?php
echo $nomArchivo = "dba_planok_gleads_gestiones_pro";
$puente = new mysqli('216.70.88.35','portga','kXn@t775','puertotga',3306); #produccion
$res_gleads = new mysqli('54.176.79.99','tga_crond','T_i466pyl8','res_gleads',3306);
$to 		= "agonzalez@tga.cl;mrincon@tga.cl";
$subject 	= "Notificacion: $nomArchivo";
$headers 	= "From: notificaciones@tga.cl";
if($puente->connect_errno || $res_gleads->connect_errno){
	$txt = "Revisar conexion puertotga o res_gleads"; 
	mail($to,$subject,$txt,$headers);
	exit;
}else{
	$consulta_conexion = $puente->query("
		SELECT id,idInmobiliaria,descripcion,idSV,localhost,usuario,clave,bdd,fechaGesPro 
		FROM conexiones
		WHERE estadoGesPro = 1
		AND traspasoGesPro = 0
		ORDER BY idInmobiliaria ASC
		LIMIT 1;
	");
	$fila_con = mysqli_fetch_array($consulta_conexion);
	$id = $fila_con['id'];
	$idInmobiliaria = $fila_con['idInmobiliaria'];
	$descripcion = $fila_con['descripcion'];
	$idSV = $fila_con['idSV'];
	$host = $fila_con['localhost'];
	$usuario = $fila_con['usuario'];
	$clave = $fila_con['clave'];
	$bdd = $fila_con['bdd'];
	$fechaGesPro = $fila_con['fechaGesPro'];	 
	
	$puente->query("UPDATE conexiones SET traspasoGesPro = 1 WHERE  idInmobiliaria = '$idInmobiliaria' AND id ='$id' LIMIT 1;");
	$consulta_estado = $puente->query("SELECT COUNT(*) AS total FROM conexiones WHERE estadoGesPro = 1 AND traspasoGesPro = 0;");
	$estado = mysqli_fetch_array($consulta_estado);
	if($estado['total']==0){$puente->query("UPDATE conexiones SET traspasoGesPro = 0 WHERE estadoGesPro = 1;");}
		  
	$conexion = new mysqli($host,$usuario,$clave,$bdd,3306);
	if ($conexion->connect_errno){
		$txt = "Error de conexion de bdd inmobiliaria";
		mail($to,$subject,$txt,$headers);
		exit;
	}
	$consulta_ultimoId = $conexion->query("SELECT MAX(idGestion) AS 'ultimo' FROM gleads_gestiones WHERE idInmobiliaria = '$idInmobiliaria';");
	$fila_ultimoId  = mysqli_fetch_array($consulta_ultimoId);
	$ultimoIdGestion = $fila_ultimoId['ultimo'];
	if($ultimoIdGestion == ''){ $ultimoIdGestion = 0;}	
	
	$gestion 	= utf8_decode('Gestión');
	$asignacion = utf8_decode('Asignación');
	$gesCat 	= utf8_decode('Gestión de un Categorizado');
	$gesCot 	= utf8_decode('Gestión de un Cotizante');
	
	if($ultimoIdGestion >=0 && $idInmobiliaria >0){
		$consulta_gestion_pro = $res_gleads->query("
			SELECT 
				  a.idGestionPro AS 'idGestion'
				, a.idInmobiliaria
				, b.nombre AS 'nombreInmobiliaria'
				, a.idProyecto AS 'idProyecto'
				, p.nombreP AS 'nombreProyecto'
				, a.idUser AS 'idVendedor'
				, UPPER(CONCAT(u.Nombre,' ',u.Apellido)) AS 'nombreVendedor'
				, a.fecha AS 'fechaGestion'
				, IF(d.nombre !='',UPPER(d.nombre),UPPER(CONCAT(c.nombre,' ',c.apellido))) AS 'nombre'
				, LOWER(a.email) AS 'email'
				, IF(d.rut !='',d.rut,c.rut) AS 'rut'
				, IF(d.fono !='',d.fono,c.telefono) AS 'fono'
				, a.comentario
				, a.idOpcion                  
				, e.opcion AS 'opcion'
				, a.tipo AS 'idTipo'
				, CASE a.tipo 
						WHEN 1 THEN '$gesCat'
						WHEN 2 THEN '$gesCot'
					ELSE 'Sin identificar' END AS 'tipo'
				, a.typeAction AS 'idTipoAccion'
				,CASE a.typeAction 
						WHEN 0 THEN '$gestion'
						WHEN 1 THEN 'Agenda'
						WHEN 2 THEN '$asignacion'
						WHEN 3 THEN 'Referencia'
						WHEN 4 THEN 'Reseva'
						WHEN 5 THEN 'Categorización'
					ELSE 'Sin identificar' END AS 'tipoAccion'
			FROM zz_glead_gestion_pro AS a
				LEFT JOIN zz_glead_inmobiliaria AS b ON	a.idInmobiliaria = b.idInmobiliaria
				LEFT JOIN zz_glead_proyectos AS p ON a.idProyecto = p.idProyecto
				LEFT JOIN zz_glead_datos_personas AS c ON a.email = c.mail AND a.idInmobiliaria = c.idInmobiliaria
				LEFT JOIN zz_glead_vendedor_categoriza AS d ON a.email = d.email AND a.idInmobiliaria =	d.idInmobiliaria AND a.idProyecto = d.idProyecto
				LEFT JOIN zz_glead_gestion_pro_opcion AS e ON a.idOpcion = e.idOpcion AND (a.tipo = e.catCotCon = 0 or a.tipo = e.catCotCon)
				LEFT JOIN app_user AS u ON a.idUser=u.idUser
			WHERE a.idInmobiliaria = '$idInmobiliaria'
				AND a.idGestionPro > '$ultimoIdGestion'
				AND a.idProyecto >0
				AND a.idUser >0
				AND a.fecha >='$fechaGesPro'
				AND a.tipo IN (1,2) -- 1Cat/2Cot 
				AND a.idSV NOT IN (1001,1002) -- NoPlanok
				AND a.prueba = 0
				AND a.descarte = 0
				AND a.cancelada = 0
				AND b.estado = 1
				AND p.estado = 1
				AND p.estado2 = 1
				AND e.opcion !=''
			GROUP BY a.idGestionPro
			ORDER BY a.idGestionPro asc 
			LIMIT 30;
		");		
		$resultado = $consulta_gestion_pro->num_rows;
		$total_procesados = 0;
		if($resultado >0){ 
			$n = 0;
			while($fila = mysqli_fetch_array($consulta_gestion_pro)){				
				$idGestion = $fila['idGestion'];
				$idInmobiliaria = $fila['idInmobiliaria'];
				$nombreInmobiliaria = $fila['nombreInmobiliaria'];
				$idProyecto = $fila['idProyecto'];
				$nombreProyecto = $fila['nombreProyecto'];
				$idVendedor = $fila['idVendedor'];
				$nombreVendedor = $fila['nombreVendedor'];
				$fechaGestion = $fila['fechaGestion'];
				$nombre = $fila['nombre'];
				$email = $fila['email'];
				$rut = $fila['rut'];
				$fono = $fila['fono'];
				$idOpcion = $fila['idOpcion'];
				$opcion = $fila['opcion'];			    
				$idTipo = $fila['idTipo'];
				$tipo = $fila['tipo'];
				$idTipoAccion = $fila['idTipoAccion'];
				$tipoAccion = $fila['tipoAccion'];				
					
				$n++;
				echo "<br>------------------------------------------------------- ".$n;
				$consulta_existe = $conexion->query("SELECT idGestion FROM gleads_gestiones WHERE idGestion = '$idGestion' LIMIT 1;");
				$fila_existe = mysqli_fetch_array($consulta_existe);
				if($fila_existe['idGestion'] >0){
					echo '<br>1.-La gestion existe';						
				}else{
					$consulta_duplicado = $conexion->query("
						SELECT idGestion FROM gleads_gestiones 
						WHERE idInmobiliaria = '$idInmobiliaria'
							AND nombreInmobiliaria = '$nombreInmobiliaria'
							AND idProyecto = '$idProyecto' 
							AND nombreProyecto = '$nombreProyecto'
							AND idVendedor = '$idVendedor'
							AND nombreVendedor = '$nombreVendedor'
							AND fechaGestion = '$fechaGestion'
							AND nombre = '$nombre'
							AND email = '$email'
							AND rut = '$rut'
							AND fono = '$fono'
							AND comentario = '$comentario'
							AND idOpcion = '$idOpcion'
							AND opcion = '$opcion'
							AND idTipo = '$idTipo'
							AND tipo = '$tipo'							
							AND idTipoAccion = '$idTipoAccion'
							AND tipoAccion = '$tipoAccion'							
						LIMIT 1;
					");
					$fila_duplicado = mysqli_fetch_array($consulta_duplicado);
					if($fila_duplicado['idGestion'] >0){
						echo '<br>1.-La gestion_pro esta duplicada';							
					}else{						
						$inserta_gestion = $conexion->query("
							INSERT INTO gleads_gestiones(
								idGestion,idInmobiliaria,nombreInmobiliaria,idProyecto,nombreProyecto,
								idVendedor,nombreVendedor,fechaGestion,nombre,email,
								rut,fono,comentario,idOpcion,opcion,
								idTipo,tipo,idTipoAccion,tipoAccion
							)VALUES(
								'$idGestion','$idInmobiliaria','$nombreInmobiliaria','$idProyecto','$nombreProyecto',
								'$idVendedor','$nombreVendedor','$fechaGestion','$nombre','$email',
								'$rut','$fono','$comentario','$idOpcion','$opcion',
								'$idTipo','$tipo','$idTipoAccion','$tipoAccion'
							);
						");
						if(!$inserta_gestion){
							echo '<br>1.- Error al insertar la gestion_pro';
							$txt = "Error al insertar la gestion_pro -IDI: $idInmobiliaria -IDG: $idGestion";
							mail($to,$subject,$txt,$headers);
						}else{
							echo '<br>1.-Inserta gestion_pro';
							$total_procesados++;
						}
					}
				}				
			}#w	
		}else{
			echo '<br>1.-No existen nuevas gestiones_pro';
		}#r		
	}else{
		echo '<br>1.-Sin valor en campo ultimoIdGestion o idInmobiliaria';
	}
	mysqli_close($puente);
	mysqli_close($res_gleads);
	echo "<br><br>Total procesados: $total_procesados";
	$idCron = 196;
	$total = $total_procesados; 
	include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
}
?>