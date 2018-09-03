<?php
error_reporting(E_ALL);
ini_set('display_errors', '1');
#########################Conexión a gleads.#####################################
include('/var/www/php/crones/conecta/web/conexion_gleads.php');
#########################Conexión a respaldo.###################################
$tipo = 0;
$query = "SELECT idEmail_unico,email,idUser,idInmobiliaria,idProyecto,LOWER(donde) AS donde,fechaPromesa,idPais,idPromesaIn
		    FROM zz_glead_promesa
		   WHERE verificaGestion = 0
			 AND idConexion      = 0
			 AND cancelada       = 0
			 AND prueba          = 0
			 AND descarte        = 0
			 AND idSV            = 0
			 AND llegada         = 'Panel G-Leads'
		ORDER BY fechaPromesa DESC
				 LIMIT 30;";
$result = $gleads_db->query($query);
if ($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
	    $idEmail_unico 	 = $row["idEmail_unico"];
	    $email 			 = $row["email"];
	    $ID_INMOBILIARIA = $row["idInmobiliaria"];
	    $idProyecto 	 = $row["idProyecto"];
	    $donde 			 = $row["donde"];
	    $fechaPromesa 	 = $row["fechaPromesa"];
	    $idPromesaIn 	 = $row["idPromesaIn"];
	    $idUser 		 = $row["idUser"];
	    $idPais 		 = $row["idPais"];
	    if($donde == 'categorizados') {
	    	$tipo = 1;
	    }elseif($donde == 'cotizaciones' || $donde == 'sin categorizar'){
	    	$tipo = 2;
	    }
	    $queryValida = "SELECT 1
						  FROM zz_glead_gestion_pro_master
				         WHERE idInmobiliaria = $ID_INMOBILIARIA
						   AND proyectos      = $idProyecto
						   AND email          = '$email'
						   AND creado         = 2
						   AND idOpcion       = 17;";
		$resultValida = $gleads_db->query($queryValida);
		if ($resultValida->num_rows > 0) {	
		    $queryValidaUp = "UPDATE zz_glead_promesa
								 SET verificaGestion = 1
							   WHERE idInmobiliaria  = $ID_INMOBILIARIA
								 AND idProyecto      = $idProyecto
								 AND email           = '$email'
								 AND idPromesaIn     = $idPromesaIn;";
			if($gleads_db->query($queryValidaUp)){
                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- actualizada <br>";
			}
		}else{
			date_default_timezone_set('UTC');
			$fecha 				= new DateTime($fechaPromesa);
			$fechaAnio 			= date_format($fecha, 'Y');
			$fechaMes 			= date_format($fecha, 'm');
			$fechaDia 			= date_format($fecha, 'd');
			$fechautc           = date('Y-m-d H:i:s', time());
			$queryValidaInsrt = "INSERT INTO zz_glead_gestion_pro_master (tipo,creado,idInmobiliaria,fecha,fechaAnio,fechaMes,fechaDia
											,fechaUtc,idEmail_unico,idUser,idOpcion,opcion,email,proyectos,idPais,typeAction,origen,procesoD)
								VALUES ($tipo, 2, $ID_INMOBILIARIA,'$fecha','$fechaAnio','$fechaMes','$fechaDia','$fechautc',$idEmail_unico,$idUser,17,'Cerro negocio desde G-Leads','$email',$idProyecto,$idPais,0,0,1);";
			if($gleads_db->query($queryValidaInsrt)){
                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- insertada <br>";
			    $queryValidaUp = "UPDATE zz_glead_promesa
									 SET verificaGestion = 1
								   WHERE idInmobiliaria  = $ID_INMOBILIARIA
									 AND idProyecto      = $idProyecto
									 AND email           = '$email'
									 AND idPromesaIn     = $idPromesaIn;";
				if($gleads_db->query($queryValidaUp)){
	                echo "PROMESA - INMOBILIARIA: $ID_INMOBILIARIA -- actualizada <br>";
				}
			}
		}
	}
}


mysqli_close($gleads_db);
// ------------------------------------------------------
//  monitor
// ------------------------------------------------------
$idCron = 213;
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
