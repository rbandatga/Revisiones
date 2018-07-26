<?php
echo $nom = 'dba_portal_cotizaciones_pabellon'; 
#$puente  = new mysqli('localhost','dev_agonzalez','5yk?hD_C2D','db_agonzalez',3306); #desarrollo
$puente = new mysqli('216.70.88.35','portga','kXn@t775','puertotga',3306); #produccion
$to = "agonzalez@tga.cl;mrincon@tga.cl";
$subject = $nom;
$headers = "From: notificacion@tga.cl";
if($puente->connect_errno){	
	echo $txt = "Error de conexion";	
	mail($to,$subject,$txt,$headers);
	exit;
}else{	
	date_default_timezone_set ('America/Santiago');
	$fecha_sv = date("Y-m-d H:i:s");
	echo '<br>Inicio: '.$fecha_sv.'<br>';
	
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
	function validar_email($mail){if(preg_match('/^[A-Za-z0-9-_.+%]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,4}$/',$mail)){return 1;}else{return 0;}}
	
	$consulta_conexion = $puente->query("SELECT * FROM conexiones WHERE idSV = 1010 AND estadoSV = 4 ORDER BY id ASC LIMIT 1;");
	$fila_con = mysqli_fetch_array($consulta_conexion);	
	$resultado_conexion = $consulta_conexion->num_rows;
	if($resultado_conexion ==0){
		echo '<br>No existe resultado de conexion';
		exit;
	}	
	$id 			= $fila_con['id'];
	$idSV 			= $fila_con['idSV'];
	$host 			= $fila_con['localhost'];
	$bdd 			= $fila_con['bdd'];
	$usuario		= $fila_con['usuario'];
	$clave 			= $fila_con['clave'];
	$descripcion	= $fila_con['descripcion'];
	
	$conexion = new mysqli($host,$usuario,$clave,$bdd,3306);
	if($conexion->connect_errno){
		echo $txt = "Error de conexion de bdd portal";
		mail($to,$subject,$txt,$headers);
		exit;
	}
 
	$consulta_cotizaciones = $conexion->query("  
		SELECT 			
			a.nombreInmobiliaria,a.nombreProyecto,a.portal,
			a.id,a.fechaCotizacion,UPPER(a.nombre) AS 'nombre',a.rut,LOWER(a.email) AS 'email',a.fono,a.programa,a.modelo,mt2_construido,mt2_total,precioUf,
			year(a.fechaCotizacion) AS 'ano', MONTH(a.fechaCotizacion) AS 'mes', DAY(a.fechaCotizacion) AS 'dia',
			b.idInmobiliariaTGA,b.idPais,
			c.idProyectoTGA,
			d.idPortalTGA,d.nombrePortalTGA,d.dondevieneTGA,d.idPlantillaTGA			
		FROM portal_cotizaciones AS a 
		LEFT JOIN integridad_inmobiliaria AS b
				ON  a.idInmobiliaria = b.idInmobiliariaCliente
				AND a.nombreInmobiliaria = b.nombreInmobiliariaCliente
		LEFT JOIN integridad_proyectos AS c 
				ON  a.idInmobiliaria = c.idInmobiliariaCliente 
				AND a.idProyecto = c.idProyectoCliente
				AND a.nombreProyecto = c.nombreProyectoCliente
		LEFT JOIN integridad_portales AS d
				ON  a.portal = d.nombrePortalCliente
		WHERE 	a.id >0
			AND a.traspaso =0
			AND a.fechaCotizacion >'2018-06-01'  
			AND b.idInmobiliariaTGA >0
			AND b.estado =1
			AND c.idInmobiliariaTGA >0
			AND c.idProyectoTGA >0
			AND c.estado =1
			AND d.idPortalTGA >0
			AND d.estado =1
		ORDER BY a.fechaCotizacion ASC
		LIMIT 50;
	");	
    $resultado = $consulta_cotizaciones->num_rows;
	$total_procesados =0;
	$n =0;
	if($resultado >0){		
		while($fila = mysqli_fetch_array($consulta_cotizaciones)){ 
			$limpia      	= array("'",'-');
			$campo_base     = 'prueba';
			$campo_final    = trim(str_replace($limpia,'',$campo_base));
			
			$idInmobiliaria = trim($fila['idInmobiliariaTGA']);
			$idPais 		= trim($fila['idPais']);
			$idProyecto 	= trim($fila['idProyectoTGA']);
			$idPortal 		= trim($fila['idPortalTGA']);
			$portal 		= trim($fila['nombrePortalTGA']);			
			$dondeviene 	= trim($fila['dondevieneTGA']);
			$idPlantilla    = trim($fila['idPlantillaTGA']);			
			$idConexion 	= trim($fila['id']);
			$fecha 			= trim($fila['fechaCotizacion']);
			$nombre 		= trim($fila['nombre']);
			$rut 			= valida_rut(trim($fila['rut']));
			$email 			= trim($fila['email']);
			$fono 			= trim($fila['fono']);
			$programa	 	= trim($fila['programa']);
			$modelo 		= trim($fila['modelo']);			                
							if($idInmobiliaria == 115 && $idProyecto == 759 && stristr($modelo == 'Torre G')==TRUE){
								$idProyecto = 760;#separarcion grupo hogares
							}
			$Construida 	= trim($fila['mt2_construido']);
			$Terreno 		= trim($fila['mt2_total']);
			$precioUf 		= trim($fila['precioUf']);	
			$ano 			= trim($fila['ano']);
			$mes 			= trim($fila['mes']);
			$dia 			= trim($fila['dia']);
			$traspaso 		= 0;
			$gleads 		= 0;			
			$n++;
			
			echo '<br>--------------------------------------------------------------------------'.$n;		
			echo '<br>idConexion: '.$idConexion;		
			
			if($idInmobiliaria>0 && $idProyecto>0 && $idPortal>0 && $portal!='' && $dondeviene!='' && $idPlantilla>0 &&
				$nombre!='' && $rut!=0 && validar_email($email)==1 && $idConexion>0 && $idSV>0){
				$consulta_existe = $puente->query("SELECT COUNT(*) AS 'total_existe' FROM glead_vendedor_categoriza
													WHERE idInmobiliaria = '$idInmobiliaria'
														AND idProyecto = '$idProyecto'
														AND idPortal = '$idPortal'
														AND idSV = '$idSV'
														AND idConexion = '$idConexion';");
				$existe = mysqli_fetch_array($consulta_existe);
				if($existe['total_existe'] >0){
					echo '<br>Existe';
					$traspaso =1;
				}else{
					$consulta_duplicado = $puente->query("SELECT COUNT(*) AS 'total_duplicado' FROM glead_vendedor_categoriza
															WHERE idInmobiliaria = '$idInmobiliaria'
																AND idProyecto = '$idProyecto'
																AND idPortal = '$idPortal'
																AND nombre = '$nombre'
																AND rut = '$rut'
																AND email = '$email'
																AND programa = '$programa'
																AND modelo = '$modelo'															
																AND YEAR(fechaCotizacion) = '$ano'
																AND MONTH(fechaCotizacion) = '$mes'
																AND DAY(fechaCotizacion) = '$dia';");
					$duplicado = mysqli_fetch_array($consulta_duplicado);
					if($duplicado['total_duplicado']>0){
						echo '<br>Duplicado';
						$traspaso=2;
					}else{
						$inserta = $puente->query("INSERT INTO glead_vendedor_categoriza(
														idInmobiliaria,idProyecto,idPortal,portal,dondeviene,
														idPlantillaPortales,fechaCotizacion,fechaIngreso,fechaCategorizado,idPais,
														nombre,rut,email,programa,modelo,
														fono,Terreno,Construida,precioUf,gleads,
														idSV,fecha_sv,idConexion
													)VALUES(
														'$idInmobiliaria','$idProyecto','$idPortal','$portal','$dondeviene',
														'$idPlantilla','$fecha','$fecha','$fecha','$idPais',
														'$nombre','$rut','$email','$programa','$modelo',
														'$fono','$Terreno','$Construida','$precioUf','$gleads',
														'$idSV','$fecha_sv','$idConexion'
													);");
                        if(!$inserta){
							$traspaso=9;
							echo $txt = "Error de insercion -IDI: $idInmobiliaria -IDP: $idPortal -IDC: $idConexion";
							mail($to,$subject,$txt,$headers);
						}else{
							echo '<br>Inserta';
							$traspaso=1;
							$total_procesados++;
						}						
					}					
				}												
			}else{
				#traspaso correcto/existe=1, duplicado=2, error_rut=3, programa=4, nombre=5, fecha=6, email=7, ErrorCampo=8, ErrorInsercion=9
				if(validar_email($email)==0){$traspaso=7;}
				elseif($rut==0){$traspaso=3;}
				else{$traspaso=8;}
			}	
			$conexion->query("UPDATE portal_cotizaciones SET traspaso = '$traspaso' WHERE id = '$idConexion' LIMIT 1;");
			echo '<br>Traspaso: '.$traspaso;	
		}#w
	}else{
		echo '<br>Sin resultados';
	}
	mysqli_close($conexion);
	mysqli_close($puente);
	echo '<br><br>Total calculados: '.$n;
	echo '<br>Total procesados: '.$total_procesados;
	$idCron = 180;
	$total 	= $total_procesados;
	include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');	
    echo '<br>Fin';
}
?>