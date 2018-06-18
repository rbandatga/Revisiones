<?php
$procesos_db = new mysqli('172.31.9.135','prorep_crond','u5e7S4&s','procesos_reportes');
if (mysqli_connect_errno()) {echo 'fallo conexión';exit();}
#$procesos_db = new mysqli('localhost','proc_crond','59M&y=tK','procesos');
#if (mysqli_connect_errno()) {echo 'fallo conexión';exit();}

date_default_timezone_set('America/Santiago');

if(!isset($tabla)){		  echo 'Agregue variable $tabla';exit;}
if($tabla == ""){		  echo 'Agregue tabla en la variable $tabla';exit;}
if(!isset($campo_id)){	  echo 'Agregue variable $campo_id';exit;}
if($campo_id == ""){	  echo 'Agregue campo id en la variable $campo_id';exit;}
if(!isset($campo_email)){ echo 'Agregue variable $campo_email';exit;}
if($campo_email == ""){	  echo 'Agregue campo email en la variable $campo_email';exit;}
if(!isset($campo_fecha)){ echo 'Agregue variable $campo_fecha';exit;}
if($campo_fecha == ""){	  echo 'Agregue campo fecha en la variable $campo_fecha';exit;}
if(!isset($tipo)){		  echo 'Agregue variable $tipo';exit;}
if($tipo == ""){	 	  echo 'Agregue campo tipo en la variable $tipo';exit;}
if(!isset($filtro)){	  echo 'Agregue variable $filtro';exit;}

$sql = "SELECT a.$campo_id AS idCatCotCon
             , a.$campo_email AS email
             , a.idInmobiliaria
             , a.idProyecto
             , b.idLead
             , a.idEmail_unico
             , a.$campo_fecha AS fecha
             , date_format(a.$campo_fecha, '%Y%m%d') AS fechaNum
		  FROM $tabla a
	 LEFT JOIN gleads_lead b ON b.idInmobiliaria = a.idInmobiliaria AND b.idEmail_unico = a.idEmail_unico AND b.estado = 1
		 WHERE a.prueba    = 0 
		   AND a.cancelada = 0 
		   AND a.descarte  = 0
		   AND a.mLead2    = 0
		   $filtro
		   AND b.idLead IS NOT NULL
		       LIMIT 50;";
$res    = $procesos_db->query($sql);
$totalG = $res->num_rows;

if ($res->num_rows > 0) {
    while($row = $res->fetch_assoc()) {
		$variable['unicoXAnio']         = 0;
		$variable['unicoXMes']          = 0;
		$variable['unicoXDia']          = 0;
		$variable['unicoProyectoXAnio'] = 0;
		$variable['unicoProyectoXMes']  = 0;
		$variable['unicoProyectoXDia']  = 0;
		$variable['fechaAnio']          = 0;
		$variable['fechaMes']           = 0;
		$variable['fechaDia']           = 0;
		$variable['numDia']             = 0;
		$variable['numSemana']          = 0;
    	if(!llaveExiste($row, $variable)){
    		$variable = getvariablesFechas($variable, $row);
    		if(!unicoAnioExiste($row,$variable)){
				$variable['unicoXAnio'] = 1;
    		}
    		if(!unicoMesExiste($row,$variable)){
				$variable['unicoXMes'] = 1;
    		}
    		if(!unicoDiaExiste($row,$variable)){
				$variable['unicoXDia'] = 1;
    		}
    		if(!unicoProyAnioExiste($row,$variable)){
				$variable['unicoProyectoXAnio'] = 1;
    		}
    		if(!unicoProyMesExiste($row,$variable)){
				$variable['unicoProyectoXMes'] = 1;
    		}
    		if(!unicoProyDiaExiste($row,$variable)){
				$variable['unicoProyectoXDia'] = 1;
    		}
    		insertaLead($row,$variable);
    	}else{
    		marcaRegistro($row);
    	}
    }
}
function totalCargar(){
    $total = 0;
    $sql   = "SELECT FOUND_ROWS() AS total;";
    $res   = $GLOBALS['procesos_db']->query($sql);
    if ($res->num_rows > 0) {
        $row   = $res->fetch_assoc();
        $total = number_format($row["total"], 0, '', '.');
    }
    return $total;
}
function llaveExiste($row, $variable){
	$email          = $row["email"];
	$idInmobiliaria = $row["idInmobiliaria"];
	$idProyecto     = $row["idProyecto"];
	$idLead         = $row["idLead"];
	$idEmail_unico  = $row["idEmail_unico"];
	$fechaNum       = $row["fechaNum"];
	$tipo           = $GLOBALS['tipo'];
	$sql  			= "SELECT 1
			   			 FROM gleads_lead_dia
			  			WHERE idInmobiliaria = $idInmobiliaria
			    		  AND idProyecto     = $idProyecto
						  AND idLead         = $idLead
						  AND idEmail_unico  = $idEmail_unico
						  AND tipo           = $tipo
						  AND fechaNum       = $fechaNum
				    		  LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function insertaLead($row, $variable){
    $email              = $row['email'];
    $idInmobiliaria     = $row['idInmobiliaria'];
    $idProyecto         = $row['idProyecto'];
    $idLead             = $row['idLead'];
    $idEmail_unico      = $row['idEmail_unico'];
    $fechaNum           = $row['fechaNum'];
    $fecha              = $row['fecha'];
    $tipo               = $GLOBALS['tipo'];
    $fechaAnio          = $variable['fechaAnio'];
    $fechaMes           = $variable['fechaMes'];
    $fechaDia           = $variable['fechaDia'];
    $numDia             = $variable['numDia'];
    $numSemana          = $variable['numSemana'];
    $unicoXAnio         = $variable['unicoXAnio'];
    $unicoXMes          = $variable['unicoXMes'];
    $unicoXDia          = $variable['unicoXDia'];
    $unicoProyectoXAnio = $variable['unicoProyectoXAnio'];
    $unicoProyectoXMes  = $variable['unicoProyectoXMes'];
    $unicoProyectoXDia  = $variable['unicoProyectoXDia'];
    $estado             = 1;
    $sql 				= "INSERT INTO gleads_lead_dia(email   , idInmobiliaria , idProyecto , idLead , idEmail_unico , fechaNum , fecha   , tipo , fechaAnio , fechaMes , fechaDia , numDia , numSemana , unicoXAnio , unicoXMes , unicoXDia , unicoProyectoXAnio , unicoProyectoXMes , unicoProyectoXDia , estado)
                                                VALUES('$email', $idInmobiliaria, $idProyecto, $idLead, $idEmail_unico, $fechaNum, '$fecha', $tipo, $fechaAnio, $fechaMes, $fechaDia, $numDia, $numSemana, $unicoXAnio, $unicoXMes, $unicoXDia, $unicoProyectoXAnio, $unicoProyectoXMes, $unicoProyectoXDia, $estado)";
	if ($GLOBALS['procesos_db']->query($sql)===true) {
		marcaRegistro($row);
	}
}
function unicoAnioExiste($row, $variable){
	$idLead    = $row['idLead'];
	$fechaAnio = $variable['fechaAnio'];
	$sql       = "SELECT 1
				    FROM gleads_lead_dia
				   WHERE idLead     = $idLead
				     AND fechaAnio  = $fechaAnio
				     AND unicoXAnio = 1
					     LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function unicoMesExiste($row, $variable){
	$idLead    = $row['idLead'];
	$fechaAnio = $variable['fechaAnio'];
	$fechaMes  = $variable['fechaMes'];
	$sql  	   = "SELECT 1
			   		FROM gleads_lead_dia
			  	   WHERE idLead    = $idLead
			    	 AND fechaAnio = $fechaAnio
			    	 AND fechaMes  = $fechaMes
			    	 AND unicoXMes = 1
				    	 LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function unicoDiaExiste($row, $variable){
	$idLead    = $row['idLead'];
	$fechaAnio = $variable['fechaAnio'];
	$fechaMes  = $variable['fechaMes'];
	$fechaDia  = $variable['fechaDia'];
	$sql  	   = "SELECT 1
			   		FROM gleads_lead_dia
			  	   WHERE idLead    = $idLead
			    	 AND fechaAnio = $fechaAnio
			    	 AND fechaMes  = $fechaMes
			    	 AND fechaDia  = $fechaDia
			    	 AND unicoXDia = 1
				    	 LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function unicoProyAnioExiste($row, $variable){
	$idLead     = $row['idLead'];
	$idProyecto = $row['idProyecto'];
	$fechaAnio  = $variable['fechaAnio'];
	$sql  		= "SELECT 1
			   		FROM gleads_lead_dia
			  	   WHERE idLead     = $idLead
			  	     AND idProyecto = $idProyecto
			    	 AND fechaAnio  = $fechaAnio
			    	 AND unicoProyectoXAnio = 1
				    	 LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function unicoProyMesExiste($row, $variable){
	$idLead     = $row['idLead'];
	$idProyecto = $row['idProyecto'];
	$fechaAnio  = $variable['fechaAnio'];
	$fechaMes   = $variable['fechaMes'];
	$sql  		= "SELECT 1
			   		 FROM gleads_lead_dia
			 	    WHERE idLead     = $idLead
			 	      AND idProyecto = $idProyecto
			    	  AND fechaAnio  = $fechaAnio
			    	  AND fechaMes   = $fechaMes
			    	  AND unicoProyectoXMes  = 1
				    	  LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function unicoProyDiaExiste($row, $variable){
	$idLead     = $row['idLead'];
	$idProyecto = $row['idProyecto'];
	$fechaAnio  = $variable['fechaAnio'];
	$fechaMes   = $variable['fechaMes'];
	$fechaDia   = $variable['fechaDia'];
	$sql  		= "SELECT 1
			   		 FROM gleads_lead_dia
			 	 	WHERE idLead     = $idLead
			 	 	  AND idProyecto = $idProyecto
			    	  AND fechaAnio  = $fechaAnio
			    	  AND fechaMes   = $fechaMes
			    	  AND fechaDia   = $fechaDia
			    	  AND unicoProyectoXDia  = 1
				    	  LIMIT 1;";
	$res = $GLOBALS['procesos_db']->query($sql);
	return ($res->num_rows > 0)?true:false;
}
function getVariablesFechas($variable, $row){
	$fecha = $row['fechaNum'];
	if(strlen($fecha) == 8){
		$variable['fechaAnio'] = substr($fecha, 0, -4);
		$variable['fechaMes']  = substr($fecha, 4, -2);
		$variable['fechaDia']  = substr($fecha, 6);
		$variable['numDia']    = (int)date("z", mktime(0,0,0,$variable['fechaMes'],$variable['fechaDia'],$variable['fechaAnio']))+1;
		$variable['numSemana'] = (int)date("W", mktime(0,0,0,$variable['fechaMes'],$variable['fechaDia'],$variable['fechaAnio']));
	}
	return $variable;
}
function marcaRegistro($row){
	$idCotCon       = $row['idCatCotCon'];
	$idInmobiliaria = $row['idInmobiliaria'];
	$idEmail_unico  = $row['idEmail_unico'];
	$tabla          = $GLOBALS['tabla'];
	$campo_id       = $GLOBALS['campo_id'];
	$sql  			= "UPDATE $tabla
						  SET mLead2  	     = 1
			  			WHERE $campo_id      = $idCotCon
			    		  AND idInmobiliaria = $idInmobiliaria
						  AND idEmail_unico  = $idEmail_unico;";
	if ($GLOBALS['procesos_db']->query($sql)==true) {
		$GLOBALS['total'] ++;
	}
}
mysqli_close($procesos_db);
?>
