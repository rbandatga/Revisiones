<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');
class Boletinmodel extends CI_Model{
	function __construct(){
		parent::__construct();
	}
	function getResumen0($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){
		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.totalCotWeb
								 , a.totalCotWebUnico
								 , a.totalCotPresencial
								 , a.totalCotPresencialUnico
								 , a.totalCon
								 , a.totalConUnico
								 , a.totalCat
								 , a.totalCatUnico
								 , a.catOrigenWeb
								 , a.catOrigenEmailing
								 , a.catOrigenEjecutivo
								 , a.catOrigenVenta
							  FROM boletin_resumen_0 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio;
								");
		return $res0;
	}
	function getResumen1($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.portal
								 , a.cotWebPortal
								 , a.cotWebPortalUnico
							  FROM boletin_resumen_1 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio
						  ORDER BY a.cotWebPortal DESC, a.cotWebPortalUnico DESC;
								");
		return $res0;
	}
	function getResumen2($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.portal
								 , a.conPortal
								 , a.conPortalUnico
							  FROM boletin_resumen_2 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio
						  ORDER BY a.conPortal DESC, a.conPortalUnico DESC;
								");
		return $res0;
	}
	function getResumen3($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.idProyecto
								 , a.proyecto
								 , a.idPortal
								 , a.portal
								 , a.cotResumen
								 , a.cotResumenUnico
							  FROM boletin_resumen_3 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio
						  ORDER BY a.proyecto ASC, a.portal ASC;
								");
		return $res0;
	}
	function getResumen4($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.ejecutivo
								 , a.catEjecutivoPanel
								 , a.catEjecutivoFicha
							  FROM boletin_resumen_4 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio;
								");
		return $res0;
	}
	function getResumen5($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.idProyecto
								 , a.proyecto
								 , a.cotUnicos
								 , a.cotUnicosGest
								 , a.cotUnicosNoGest
							  FROM boletin_resumen_5 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio;
								");
		return $res0;
	}
	function getResumen6($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio){

		$boletin_db = $this->load->database('boletin_mensual',TRUE);
		$res0 = $boletin_db->query("
							SELECT a.ejecutivo
								 , a.cotGesUnico
								 , a.cotGes
								 , a.cotRatio
								 , a.cotDias
								 , a.catGesUnico
								 , a.catGes
								 , a.catRatio
								 , a.catDias
							  FROM boletin_resumen_6 a
							 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
							   AND a.idZona 		= $idZona
							   AND a.fechaMes 		= $fechaMes
							   AND a.fechaAnio 		= $fechaAnio
						  ORDER BY a.ejecutivo;
								");
		return $res0;
	}
}
