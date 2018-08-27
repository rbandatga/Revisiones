<?php
#########################Conexión a gquest.#####################################
$quest_db = new mysqli('216.70.88.35','donquest','As$3d4%Re','quest');
if (mysqli_connect_errno()) {echo 'fallo quest_db';exit();}
################################################################################

$total = 0;
################################################################################
// Se obtienen nuevos proyectos
$query1 = $quest_db->query("SELECT idInmobiliaria, idProyecto
						 FROM 	proyectos_quest
						 WHERE 	estado	  = 1
						 AND 	nuevo 	  = 1
						 AND 	enProceso = 0
						 LIMIT 	1;");
if(mysqli_num_rows($query1)>0){
	$filaProy = mysqli_fetch_array($query1);
	$idInmobiliaria = $filaProy['idInmobiliaria'];
	$idProyecto 	= $filaProy['idProyecto'];
	en_proceso($idInmobiliaria,$idProyecto,1);//Se bloquea registro

	// Lista encuestas agrupadas por inmobiliaria y persona
	$query2 = $quest_db->query("
								SELECT e.idEncuesta as idEncuesta, e.email as email, e.donde as idDonde, idEmail_unico, idPais, idUser
								FROM zz_glead_encuesta e,
														 (SELECT MAX(fechaTermino) as fechaTermino, email, idInmobiliaria
															FROM zz_glead_encuesta
															WHERE idInmobiliaria = $idInmobiliaria
															AND avance 	         = 999
															AND estado 	         = 2
															AND X1				 = 1
														  	AND X10 			 > 0
														  	AND proceso          = 3
															AND fechaTermino 	 >  DATE_ADD(NOW(), INTERVAL - 8 MONTH)
															GROUP BY idInmobiliaria, email) eMax
								WHERE e.idInmobiliaria = eMax.idInmobiliaria
								AND e.email 		   = eMax.email
								AND e.fechaTermino 	   = eMax.fechaTermino
								AND e.avance 	       = 999
								AND e.estado 	       = 2
								AND e.X1			   = 1
								AND e.X10 			   > 0
								AND e.proceso          = 3
								GROUP BY e.idInmobiliaria, e.email
								ORDER BY e.idEncuesta;
								");
	if(mysqli_num_rows($query2)>0){
		foreach($query2 as $respuesta2){
			// Inserta nuevos proyectos para cada inmobiliaria/persona (solo si ya no fueron insertados)
			$respuesta2 = _getDonde($respuesta2);
			$fechaPuntos = fecha_puntos($respuesta2['idEncuesta']);
			if($fechaPuntos){
				//$fechaPuntos = fecha_puntos($respuesta2['idEncuesta']);
				$query3 = $quest_db->query("INSERT INTO zz_glead_respuestas (idRespuestaEncuesta, idInmobiliaria, idProyecto, idEmail_unico, Correo, idPais, idDonde, donde, idUser, fechaPuntos, X1, activo, estado)
										SELECT * FROM  (
											SELECT ".$respuesta2['idEncuesta']." AS idRespuestaEncuesta, $idInmobiliaria AS idInmobiliaria, $idProyecto AS idProyecto, idEmail_unico AS ".$respuesta2['idEmail_unico'].",'".$respuesta2['email']."' AS Correo,".$respuesta2['idPais']." AS idPais, ".$respuesta2['idDonde']." AS idDonde,'".$respuesta2['donde']."' AS donde, ".$respuesta2['idUser']." AS idUser, '$fechaPuntos' AS fechaPuntos, 1 AS X1, 1 AS activo,'X' AS estado
										) AS tmp
										WHERE NOT EXISTS (
											SELECT idInmobiliaria, idProyecto, Correo, idRespuestaEncuesta FROM zz_glead_respuestas WHERE idInmobiliaria = $idInmobiliaria AND idProyecto = $idProyecto AND Correo = '".$respuesta2['email']."' AND idRespuestaEncuesta = ".$respuesta2['idEncuesta']."
										) LIMIT 1;");
				if(!$query3){
					echo "Problemas para multiplicar el registro de la inmobiliaria: $idInmobiliaria, proyecto: $idProyecto";
					$total++;
				}
			}else{
				echo "No se inserto registro ya que no existe fecha de puntos por procesar para idEncuesta: ".$respuesta2['idEncuesta']."<br";
			}
		}
	}
	ex_nuevo($idInmobiliaria,$idProyecto);//Se notifica que ya se multiplico
	en_proceso($idInmobiliaria,$idProyecto,0);//Se desbloquea registro
}else{
	echo "Sin registros que procesar";
}
function _getDonde($row0){
	$row0['idDonde'] = $row0['idDonde'] == "Survey" ? 4 : $row0['idDonde'] ;
	$row0['donde']   = "Quest no encontro donde";
	$res1 = $GLOBALS['gquest_db']->query("SELECT a.nombre AS donde
                                            FROM tt_tema_donde a
                                           WHERE a.idDonde = ".$row0['idDonde']."
                                             AND a.idPais  = ".$row0['idPais'].";");
	if(!$res1){echo "> SENTENCIA _getDonde INVALIDA";exit;}
	
	if($res1->num_rows > 0){
		$row1 = mysqli_fetch_array($res1);
		$row0['donde'] = $row1['donde'];
	}
	return $row0;
}
function en_proceso($idInmobiliaria,$idProyecto,$valor){
	$query = $GLOBALS['quest_db']->query("UPDATE proyectos_quest
										SET enProceso = $valor
										WHERE idInmobiliaria = $idInmobiliaria
										AND idProyecto = $idProyecto;
									");
	if(!$query){
		echo "No se pudo actualizar el estado enProceso para proyectos_quest inmobiliaria: $idInmobiliaria,proyecto: $idProyecto";
		exit();
	}
}
function ex_nuevo($idInmobiliaria,$idProyecto){
	$query = $GLOBALS['quest_db']->query("UPDATE proyectos_quest
										SET nuevo = 0
										WHERE idInmobiliaria = $idInmobiliaria
										AND idProyecto = $idProyecto;
									");
	if(!$query){
		echo "No se pudo actualizar el estado nuevo para proyectos_quest inmobiliaria: $idInmobiliaria,proyecto: $idProyecto";
	}
}
function fecha_puntos($idRespuestaEncuesta){
    $resultado = $GLOBALS['quest_db']->query(" SELECT fechaPuntos
    										FROM zz_glead_respuestas
    										WHERE idRespuestaEncuesta = $idRespuestaEncuesta
    										GROUP BY fechaPuntos
    										LIMIT 1");
    if(!$resultado){echo "SENTENCIA fecha_puntos($idRespuestaEncuesta) INVALIDA";exit;}
	if($fila = mysqli_fetch_array($resultado)){
		return $fila['fechaPuntos'];
	}else{
		return false;
	}
}
mysqli_close($quest_db);
# ------------------------------------------------------
#  monitor
# ------------------------------------------------------
$idCron = 167;
include('/var/www/php/crones/monitor/pro/web/monitor_cron.php');
?>
