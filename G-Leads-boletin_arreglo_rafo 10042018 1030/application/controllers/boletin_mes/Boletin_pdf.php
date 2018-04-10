<?php
class Boletin_pdf extends CI_Controller {
    public function __construct(){

        parent::__construct();
        $this->load->helper('url');
        $this->load->helper('html');
        $this->load->library('user_agent');
        $this->load->database();
        $this->load->helper('cookie');
        session_start();
        $this->load->library('Gleads');
        $this->load->library('Gleadsventas');
        $this->load->library('Valentina');
        $this->load->library('Valentinalista');
        $this->load->library('Valentinausuarios');
        $this->load->library('pagination');
        $this->load->helper(array('form'));
        $this->load->library('form_validation');
        #############################
		####Includes del proyecto####
		#############################
        $this->load->library('tcpdf/Mitcpdf');
        if(isset($_SESSION['user'])){$session=$_SESSION['user'];}else{$session=null;}
        $this->valentinausuarios->user($session);
    }
    public function index(){
    	$boletin_db  =  new mysqli('tga-reportes.cusc3ayieifn.us-west-1.rds.amazonaws.com','glead_view_dev','uBx2v&Uw','reportes');
		if (mysqli_connect_errno()) {echo 'fallo boletin';exit();}

		#############################
		####Variable de librerías####
		#############################
		$ID_INMOBILIARIA 		 = $this->valentinausuarios->empresaid($_SESSION['user']);
		$nombreInmobiliaria      = $this->valentinausuarios->empresa($_SESSION['user']);
		$nombreUsuario			 = $this->valentinausuarios->nombre($_SESSION['user']);

		##############################
		####Obtención de variables####
		##############################
		$idZona                 = $this->input->post("idZona");
		$fechaMes 		 		= $this->input->post("fechaMes");
		$fechaAnio 		 		= $this->input->post("fechaAnio");
		$fecha 					= $this->input->post("fechaCompleta");
		$svg_origen				= $this->input->post("svg_origen");
		$svg_volumen			= $this->input->post("svg_volumen");

		
		$nombreInmobiliaria		= $nombreInmobiliaria;
		$nombreUsuario			= $nombreUsuario;


		############################ 
		##########Imágenes##########
		############################
		$img_logon			= site_url() . "images/sitio/reporte_pro/boletin/pdf/logon_trans.png";
		$img_bullet_1		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (1).png";
		$img_bullet_2		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (2).png";
		$img_bullet_3		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (3).png";
		$img_bullet_4		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (4).png";
		$img_bullet_5		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (5).png";
		$img_bullet_6		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (6).png";
		$img_bullet_7		= site_url() . "images/sitio/reporte_pro/boletin/pdf/bullets/bullet_ (7).png";
		$img_cot_barra		= site_url() . "images/sitio/reporte_pro/boletin/pdf/cot_barra.gif";
		$img_cat_barra		= site_url() . "images/sitio/reporte_pro/boletin/pdf/cat_barra.gif";

		###############
		####queries####
		###############
		//PAG 1   : Resumen Mensual
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
									   AND a.idZona = $idZona
									   AND a.fechaMes = $fechaMes
									   AND a.fechaAnio = $fechaAnio;
										");
		//PAG 2
		//Fuente de tráfico por PORTAL
		$res1 = $boletin_db->query("
									SELECT a.portal
										 , a.cotWebPortal
										 , a.cotWebPortalUnico
									  FROM boletin_resumen_1 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio;
										");
		//Fuente de tráfico por CONSULTA
		$res1_con = $boletin_db->query("
										SELECT a.portal
											 , a.conPortal
											 , a.conPortalUnico
										  FROM boletin_resumen_2 a
										 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
										   AND a.idZona 		= $idZona
										   AND a.fechaMes 		= $fechaMes
										   AND a.fechaAnio 		= $fechaAnio;
											");
		//PAG 3
		//Resumen de cotizaciones
		$res2 = $boletin_db->query("
									SELECT a.idProyecto, a.proyecto, a.idPortal, a.portal, a.cotResumen, a.cotResumenUnico
									  FROM boletin_resumen_3 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio
								  ORDER BY a.proyecto;
										");

		//PAG 4
		//Resumen cantidades Categorizados Ejecutivos
		$res3 = $boletin_db->query("
									SELECT a.ejecutivo
										 , a.catEjecutivoPanel
										 , a.catEjecutivoFicha
									  FROM boletin_resumen_4 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio;
										");
		//PAG 5
		//Resumen gestiones
		$res4 = $boletin_db->query("
									SELECT ejecutivo
										 , cotGesUnico
										 , cotGes
										 , cotRatio
										 , cotDias
										 , catGesUnico
										 , catGes
										 , catRatio
										 , catDias
									  FROM boletin_resumen_6 a
									 WHERE a.idInmobiliaria = $ID_INMOBILIARIA
									   AND a.idZona 		= $idZona
									   AND a.fechaMes 		= $fechaMes
									   AND a.fechaAnio 		= $fechaAnio
								  ORDER BY a.ejecutivo;
									");

		if($res0->num_rows > 0){
			$row0 = mysqli_fetch_array($res0);
			$totalCotWeb 			 = ($row0['totalCotWeb']=="")?0:$row0['totalCotWeb'];
			$totalCotWebUnico 		 = ($row0['totalCotWebUnico']=="")?0:$row0['totalCotWebUnico'];
			$totalCotPresencial 	 = ($row0['totalCotPresencial']=="")?0:$row0['totalCotPresencial'];
			$totalCotPresencialUnico = ($row0['totalCotPresencialUnico']=="")?0:$row0['totalCotPresencialUnico'];
			$totalCon 				 = ($row0['totalCon']=="")?0:$row0['totalCon'];
			$totalConUnico 			 = ($row0['totalConUnico']=="")?0:$row0['totalConUnico'];
			$totalCat 				 = ($row0['totalCat']=="")?0:$row0['totalCat'];
			$totalCatUnico 			 = ($row0['totalCatUnico']=="")?0:$row0['totalCatUnico'];
			$catOrigenWeb 			 = ($row0['catOrigenWeb']=="")?0:$row0['catOrigenWeb'];
			$catOrigenEmailing 		 = ($row0['catOrigenEmailing']=="")?0:$row0['catOrigenEmailing'];
			$catOrigenEjecutivo 	 = ($row0['catOrigenEjecutivo']=="")?0:$row0['catOrigenEjecutivo'];
			$catOrigenVenta 		 = ($row0['catOrigenVenta']=="")?0:$row0['catOrigenVenta'];
		}



		##############################
		####Generación del gráfico####
		##############################
		$Grandomico 		= rand(100000,999999999);
		$Grandomicob		= rand(1000,9999);
		$Gname				= "g_" . $Grandomico . "_" . $Grandomicob .".svg";
		$Gname2				= "g_" . $Grandomico . "_" . $Grandomicob ."_2.svg";
	
		
		if(base_url() == "https://gleads.tgapps.net/"){
			$GubicacionLocal 	   = "/var/www/vhosts/TGApps.net/gleads.tgapps.net/images/deposito/";
		}else if(base_url() == "http://gleads.desarrollo.cool/"){
			$GubicacionLocal 	   = "/usr/share/nginx/html/gleads.desarrollo.cool/web/images/deposito/";
		}else{
			$GubicacionLocal = "insertar ubicación de prueba";
		}
		
		$GubicacionFinalLocal  = $GubicacionLocal . $Gname;
		$GubicacionFinal2Local = $GubicacionLocal . $Gname2;

		$Gubicacion      	= base_url().'images/deposito/';
		$GubicacionFinal	= $Gubicacion . $Gname;
		$GubicacionFinal2	= $Gubicacion . $Gname2;


		file_put_contents($GubicacionFinalLocal, $svg_volumen);
		file_put_contents($GubicacionFinal2Local, $svg_origen);
		$e = chmod($GubicacionFinalLocal , 0775);
		$i = chmod($GubicacionFinal2Local, 0775);

		if (!$e || !$i) {
			$this->doError(3747);
			exit();
		}

		###########################
		####Creación del objeto####
		###########################
		$pageLayout = array(216, 279); //Tamaño carta
		$pdf = new Mitcpdf(PDF_PAGE_ORIENTATION, PDF_UNIT, $pageLayout, true, 'UTF-8', false);
		#############
		####Metas####
		#############
		$pdf->SetCreator("Trend Group America");
		$pdf->SetAuthor("Trend Group America");
		$pdf->SetTitle('Boletín_mensual_' . $fechaMes . ' ' .$fechaAnio);
		$pdf->SetSubject('Trend Group America');
		$pdf->SetKeywords('TGA, Gleads, boletin, pdf');




		######################################################################################################
		#################################### Cabecera y Footer ###############################################
		######################################################################################################
		$htmlHeader= '
		<table width="100%" style="border-bottom: 1px solid #ededed; color: #636262;">
			<tr style="vertical-align: middle;">
				<td width="80%" style="background-color: #BE191E;  vertical-align: middle;"><img width="117px" src="' . $img_logon. '" /></td>
				<td width="20%" style="background-color: #BE191E;  vertical-align: middle; text-align: center; color: white; font-size: 9pt;">' . $fecha . '</td>
			</tr>
			<tr>
				<td width="60%">
					&nbsp;
					<br />
					<span style="font-size: 14pt; font-weight: bold;">Boletín mensual G-Leads</span><br />
					<span>Cliente: <b>' . $nombreInmobiliaria  .'</b></span>
				</td>
				<td width="40%" style="text-align: right;">
					&nbsp;
					<br />
					<span style="text-align: right;">Págs: ' . $pdf->getAliasNumPage() . '/' . $pdf->getAliasNbPages() .'</span><br/><span>Usuario: <b>' . $nombreUsuario . '</b></span>
					<br />
					&nbsp;
				</td>
			</tr>
		</table>'
		;
		$pdf->setHeaderData($ln='', $lw=0, $ht='', $hs=$htmlHeader, $tc=array(0,0,0), $lc=array(0,0,0));
		$pdf->SetHeaderMargin(10);
		$pdf->SetMargins(10, 40, 10, true);
		$pdf->setFooterMargin(35);
		$pdf->SetAutoPageBreak(TRUE, 35); 









		######################################################################################################
		#################################### Página 1/5 ######################################################
		######################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina
		
		//Estilos en general
		$cssPdf = '
		<style>
		.subTitulo{
			font-size: 11pt;
			border-bottom: 2px solid #8c8c8c;
		}
		.grayTable{
			border: 1px solid #cecece;
		}
		.grayTable>tr{
			border: 1px solid #cecece;
		}
		.grayTable>tr>td{
			border: 1px solid #cecece;
		}
		.st1{ text-align: right; font-size: 10pt;}
		.st2{ border-bottom: 1px solid #ededed;}
		.st3{ color: #767a7b;}
		.st4{ border-bottom: 2px solid #7a7a7a; margin: 0; padding: 0;}
		.st5{ color: #5787b3; background-color: #e6f2fa;}
		.st6{ color: #5787b3; font-weight: bold;}
		.st7{ color: #353535;}
		.bulletes{
			width: 10px;
			height: 10px;
			margin-right: 8px;
		}
		.tab_2 td{
			border-bottom: 1px solid #d2d4d3;
		}
		.tab_4{
			font-size: 11pt;
		}
		.tab_4head{
			background-color: #edeeed;
			border-top: 1px solid #dedfde;
		}
		.tab_4 td{
			border-bottom: 1px solid #dedfde;
		}
		</style>
		';
		//$pdf->writeHTML($cssPdf, false, false, true, false, '');

		$htmlpdf .= '
		<table width="100%">
			<tr>
				<td width="45%"><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_7 . '"> Resumen mensual</div></td>
				<td width="10%">&nbsp;</td>
				<td width="45%"><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_4 . '"> Origen de la categorización</div></td>
			</tr>
			<tr>
				<td width="45%">
					<table width="100%" cellpadding="3"> 
						<thead>
							<tr>
								<td width="50%" >&nbsp;</td>
								<td width="25%" class=" st5" style=" font-size: 9pt;" align="center">Totales</td>
								<td width="25%" style="font-size: 9pt;" align="center">Únicos</td>
							</tr>
						</thead>
						<tbody>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;">Cotizaciones web</td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotWeb .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotWebUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;">Cotizaciones presenciales</td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotPresencial .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCotPresencialUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;">Consultas</td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCon .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalConUnico .'</td>
							</tr>
							<tr>
								<td class="st2" align="right" style="font-size: 12pt;">Personas categorizadas</td>
								<td class="st2  st5" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCat .'</td>
								<td class="st2  st3" align="center" style="font-size: 20pt; font-weight:bold;">' . $totalCatUnico .'</td>
							</tr>
						</tbody>
						
					</table>
				</td>
				<td width="10%">
					&nbsp;
				</td>
				<td width="45%">
					<img src="'  .  $GubicacionFinal2  .'" width="300px"/>
				</td>
			</tr>
		</table>
		<br /> <br />
		<table>
			<tr>
				<td style="font-size: 7pt;">
					<b>Categorización web:</b> Respuestas obtenidas de banner situado en la web de la inmobiliaria.<br />
					<b>Categorización E-mailing:</b> Respuestas obtenidas a través de la campaña E-mailing post cotización o consulta.<br />
					<b>Categorización Ejecutivo:</b> Cuestionario realizado por el ejecutivo a través de la ficha cotizante (Vía Telefónica).<br />
					<b>Categorización Ventas:</b> Cuestionario realizado por el ejecutivo a través del botón cuestionario del panel de G-Leads (Envío por email o ingreso de cliente)
				</td>
			</tr>
		</table>
		<br /> <br />
		<table width="100%">
			<tr>
				<td><div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_6 . '"> Volumen de personas obtenidas al mes y que no han sido gestionadas por proyecto mensual</div></td>
			</tr>
			<tr>
				<td>
					<img src="'  .  $GubicacionFinal  .'" width="550px"/>
				</td>
			</tr>
		</table>
		';

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');


		#####################################################################################################
		#################################### Página 2/5######################################################
		#####################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina

		$htmlpdf .= "
		<style>
			.titleTable{
				background-color: #f2f2f2;
				color: #666666;
				font-size: 8pt;
				text-align: center;
			}
		</style>
		";

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_2 . '"> Fuente de tráfico</div><br />';





		$htmlpdf.= '<table width="100%" align="center" vertical-align="center">';
		$htmlpdf.= "<tr>";
		$htmlpdf.= '<td width="47%">';
		#######Portales#######
		$htmlpdf .= '<table width="100%" class="tab_2" cellpadding="5">';
		$htmlpdf .= '<thead>';
		$htmlpdf .= '<tr class="titleTable">';
		$htmlpdf .= '<td width="50%" style="border-top: 1px solid #d2d4d3;"><b>Por portal</b></td>';
		$htmlpdf .= '<td width="25%" style="border-top: 1px solid #d2d4d3;"><b>Totales</b></td>';
		$htmlpdf .= '<td width="25%" style="border-top: 1px solid #d2d4d3;"><b>Únicos</b></td>';
		$htmlpdf .= '</tr>';
		$htmlpdf .= '</thead><tbody>';

		$porTotUn = array();
		if($res1->num_rows > 0){
			
			while($row1 = $res1->fetch_assoc()) {
				$portal		  	   = utf8_decode(($row1['portal']=="")?"Sin portal":$row1['portal']);
				$cotWebPortal	   = ($row1['cotWebPortal']=="")?0:$row1['cotWebPortal'];
				$cotWebPortalUnico = ($row1['cotWebPortalUnico']=="")?0:$row1['cotWebPortalUnico'];
				array_push($porTotUn, array($portal,$cotWebPortal,$cotWebPortalUnico));
		    }
		}
		$cantidadPortales 	= count($porTotUn);

		for ($i=0; $i < $cantidadPortales; $i++) {
			$portal		  	   = ($row1['portal']=="")?"Sin portal":$row1['portal'];
			$cotWebPortal	   = ($row1['cotWebPortal']=="")?0:$row1['cotWebPortal'];
			$cotWebPortalUnico = ($row1['cotWebPortalUnico']=="")?0:$row1['cotWebPortalUnico'];

			$htmlpdf .= '<tr><td style="font-size: 10pt;">' . $porTotUn[$i][0] . '</td><td class="st6">' . $porTotUn[$i][1] . '</td><td class="st7">' . $porTotUn[$i][2] . '</td></tr>';
		}
		$htmlpdf .= '</tbody></table>';


		$htmlpdf.= "</td>";
		$htmlpdf.= '<td width="6%">';
			$htmlpdf.= "&nbsp;";
		$htmlpdf.= "</td>";
		$htmlpdf.= '<td width="47%">';


		#######Consultas#######
		$htmlpdf .= '<table width="100%" class="tab_2" align="center" cellpadding="5">';
		$htmlpdf .= '<thead>';
		$htmlpdf .= '<tr class="titleTable">';
		$htmlpdf .= '<td width="50%"><b>Por consulta</b></td>';
		$htmlpdf .= '<td width="25%"><b>Totales</b></td>';
		$htmlpdf .= '<td width="25%"><b>Únicos</b></td>';
		$htmlpdf .= '</tr>';
		$htmlpdf .= '</thead><tbody>';

		$PorTotCon = array();
		while($row1 = $res1_con->fetch_assoc()) {
			$portal		  	= utf8_decode(($row1['portal']=="")?"Sin portal":$row1['portal']);
			$conPortal	    = ($row1['conPortal']=="")?0:$row1['conPortal'];
			$conPortalUnico = ($row1['conPortalUnico']=="")?0:$row1['conPortalUnico'];
			array_push($PorTotCon, array($portal,$conPortal,$conPortalUnico));
		}

		$cantidadConsultas	= count($PorTotCon);

		for ($i=0; $i < $cantidadConsultas; $i++) { 
			$htmlpdf .= '<tr><td style="font-size: 10pt;">' . $PorTotCon[$i][0] . '</td><td class="st6">' . $PorTotCon[$i][1] . '</td><td class="st7">' . $PorTotCon[$i][2] . '</td></tr>';
		}
		$htmlpdf .= '</tbody></table>';



		$htmlpdf.= "</td>";
		$htmlpdf.= "</tr>";
		$htmlpdf.= "</table>";


		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');



		######################################################################################################
		#################################### Página 3/5 ######################################################
		######################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		$cantidadPortales = 0;
		//< info pagina

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_1 . '"> Resumen de cotizaciones</div>';




		$arrIdProyectos     = array();
		$arrNomProyectos 	= array();
		$arrIdPortales 		= array();
		$arrNomPortales 	= array();

		$arrCotResumen 		= array();
		$arrCotResumenUnico = array();


		if($res2->num_rows > 0){
			$proy = 0;
			$port = 0;
			while($row2 = $res2->fetch_assoc()) {
				$idProyecto		 = $row2['idProyecto'];
				$proyecto	     = $row2['proyecto'];
				$idPortal 		 = $row2['idPortal'];
				$portal		  	 = $row2['portal'];
				$cotResumen	     = $row2['cotResumen'];
				$cotResumenUnico = $row2['cotResumenUnico'];
				if (!in_array("$idProyecto", $arrIdProyectos)) {
				    $arrIdProyectos[$proy]  = "$idProyecto";
				    $arrNomProyectos[$proy] = "$proyecto";
				    $proy++;
				}
				if (!in_array("$idPortal", $arrIdPortales)) {
				    $arrIdPortales[$port]  = "$idPortal";
				    $arrNomPortales[$port] = "$portal";
				    $port++;
				}
				$arrCotResumen[$idProyecto][$idPortal] 		= "$cotResumen";
				$arrCotResumenUnico[$idProyecto][$idPortal] = "$cotResumenUnico";
		    }

		}

		$anchMax 			= 10; //Cantidad de portales por tabla a mostrar
		$paginacion			= count($arrIdPortales) / $anchMax;
		$pagactual			= 1; //No mover. No puede quedarse en 0

		for ($a=$paginacion; $a > 0; $a--) {
			$htmlpdf .= '&nbsp;<br/><table style="font-size: 8pt;" cellpadding="1" width="900px" align="center">';
				$htmlpdf .= '<tr>';
				$htmlpdf .= '<td width="70px" ></td>';
				for ($i=0; $i < count($arrIdPortales); $i++) { 
					$htmlpdf .= '<td colspan="2" width="48px" style="border-bottom:0.5px solid #d2d4d3; font-size: 8pt;">' . utf8_decode($arrNomPortales[$i]) . '</td>';
				}
				$htmlpdf .= '</tr>';

				$htmlpdf .= '<tr>';
				$htmlpdf .= '<td width="70px" ></td>';
				for ($i=0; $i < count($arrIdPortales); $i++) { 
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:1.7px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt;">Total</td>';
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:0.5px solid #d2d4d3; border-bottom:1px solid #d2d4d3; font-size:7pt; color:#636363;">Únicos</td>';
				}
				$htmlpdf .= '</tr>';

				
				for ($i=0; $i < count($arrIdProyectos); $i++) { 
					$idProyectoFind = $arrIdProyectos[$i];
					if($i % 2){$colorR ='#edeeed;';}else{$colorR = 'white;';}
					$htmlpdf .= '<tr style="background-color:'.$colorR.'">';
					$htmlpdf .= '<td width="70px" >' . utf8_decode($arrNomProyectos[$i]) . '</td>';
					for ($u=0; $u < count($arrIdPortales); $u++) { 
						$idPortalFind = $arrIdPortales[$u];
						$valorCotR  = ( empty($arrCotResumen[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumen[$idProyectoFind][$idPortalFind];
						$valorCotRU = ( empty($arrCotResumenUnico[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumenUnico[$idProyectoFind][$idPortalFind];
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:1.5px solid #d2d4d3; color: black;">' . $valorCotR . '</td>';
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:0.5px solid #d2d4d3; color: black;">' . $valorCotRU . '</td>';
					}
					$htmlpdf .= '</tr>';
				}
				
			$htmlpdf .= '</table>';
			$pagactual++;
		}


		############## #INI R
/*		$arrPortales	 = array();
		$arrProyTotUn	 = array();



		if($res2->num_rows > 0){
			while($row2 = $res2->fetch_assoc()) {
				$idProyecto		 = $row2['idProyecto'];
				$proyecto	     = $row2['proyecto'];
				$idPortal 		 = $row2['idPortal'];
				$portal		  	 = $row2['portal'];
				$cotResumen	     = ($row2['cotResumen']=="")?0:$row2['cotResumen'];
				$cotResumenUnico = ($row2['cotResumenUnico']=="")?0:$row2['cotResumenUnico'];

				array_push($arrPortales, $portal); // array("portal_1","portal_2","portal_3",...);
				$totrUn = array();
				$totlUn = array();
				while ($row22 = $res2->fetch_assoc()) {
					if ($row22["proyecto"] == $row2["proyecto"]) {
						array_push($totrUn, $row22["cotResumen"]);
						array_push($totlUn, $row22["cotResumenUnico"]);
					}
				}
				array_push($arrProyTotUn, array($proyecto,$totrUn,$totlUn));
		    }
		}

		$cantidadProyectos	= count($totrUn);
		$cantidadPortales 	= count($arrPortales); //Cantidad
		$anchMax 			= 10; //Cantidad de portales por tabla a mostrar
		$paginacion			= $cantidadPortales / $anchMax;
		$pagactual			= 1; //No mover. No puede quedarse en 0


		for ($a=$paginacion; $a > 0; $a--) {
			$htmlpdf .= '&nbsp;<br/><table style="font-size: 8pt;" cellpadding="1" width="900px" align="center">';
			for ($i=-1; $i < $cantidadProyectos; $i++) {
					//Color gris por cada TR
					if($i > 0){if($i % 2){$htmlpdf .= '<tr style="background-color: #edeeed;">';}else{$htmlpdf .= '<tr style="background-color: white;">';}
					}else{$htmlpdf .= '<tr>';}

				if ($i == -1) {
					$htmlpdf .= '<td width="70px" ></td>';
					for ($e=0; $e < $anchMax; $e++) { 
						$htmlpdf .= '<td colspan="2" width="48px" style="border-bottom:0.5px solid #d2d4d3;">' . $arrPortales[$e] . '</td>';
					}
				} elseif ($i == 0) {
					$htmlpdf .= '<td width="70px" ></td>';
					for ($e=0; $e < $anchMax; $e++) { 
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:1.7px solid #d2d4d3; border-bottom:1.7px solid #d2d4d3; font-size:7pt;">Total</td>';
						$htmlpdf .= '<td width="24px" height="12px" style="line-height:12px; border-left:0.5px solid #d2d4d3; border-bottom:1.7px solid #d2d4d3; font-size:7pt; color:#636363;">Únicos</td>';
					}
				}else{
					$htmlpdf .= '<td width="70px" >' . $arrProyTotUn[$i] . '</td>';
					for ($e=0; $e < $anchMax; $e++) {
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:1.5px solid #d2d4d3; color: black;">' . $arrProyTotUn[$i - 1][0][$e] . '</td>';
						$htmlpdf .= '<td width="24px" height="24px" style="border-left:0.5px solid #d2d4d3; color: black;">' . $arrProyTotUn[$i - 1][1][$e]. '</td>';
					}
				}
				$htmlpdf .= '</tr>';
			}
			$htmlpdf .= '</table>';
			$pagactual++;
		}*/
		#################### FIN R

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');


		######################################################################################################
		#################################### Página 4/5 ######################################################
		######################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";
		//< info pagina

		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_5 . '"> Resumen cantidades Categorizados Ejecutivos</div><br/>';

		##############################
		###Declaración de variables###
		##############################
		$tagTableBig 				= '<table width="100%"  class="tab_4" cellpadding="4" align="center"><tr><td class="tab_4head">Nombre ejecutivo</td><td class="tab_4head">Cant. Cat. Ejec</td><td class="tab_4head">Cant. Cat. Ventas</td></tr><tbody><tr><td width="45%">';
		$tagTablaSub				= '<table>';
		$vueltasMaximas 		= 24;			//Cantidad de vueltas por subTabla


		
		$cierreTabla = 1;
		$htmlpdf .= $tagTableBig . $tagTablaSub; // Tabla grande + sub_table 1
		$vueltasTab1 = $vueltasMaximas;			// |  datos  |  |         |
		$vueltasTab2 = $vueltasMaximas * 2;		// |         |  |  datos  |
		$vuelta 	 = $vueltasTab2;

		if($res3->num_rows > 0){
			while($row3 = $res3->fetch_assoc()) {
				if ($vuelta == $vueltasTab1) {
					//Cierro tab_1, agrego espacio en blanco bigTable y abro tab_2
							//Cierre_tab1|  tabmargin         | tab2 de 45%
					$htmlpdf .= '</table><td width="10%"></td><td width="45%">' . $tagTablaSub;
				}else if($vuelta == 0){
					//Cierro tab_2, bigTable y genero otro bigTable
					$htmlpdf .= '</table></table>';
					$htmlpdf .= $tagTableBig . $tagTablaSub; //Genero una nueva bigTable

					$cierreTabla++;
					$vuelta = $vueltasTab2;
				}
				$ejecutivo		   = utf8_decode(trim(($row3['ejecutivo']=="")?"<i>Sin ejecutivo</i>":$row3['ejecutivo']));
				$catEjecutivoPanel = trim(($row3['catEjecutivoPanel']=="")?"0":$row3['catEjecutivoPanel']);
				$catEjecutivoFicha = trim(($row3['catEjecutivoFicha']=="")?"0":$row3['catEjecutivoFicha']); 
				$htmlpdf.='<tr>';
				$htmlpdf.='<td>'.$ejecutivo.'</td><td>'.$catEjecutivoPanel.'</td><td>'.$catEjecutivoFicha.'</td>';
				$htmlpdf.='</tr>';

				$vuelta --;
		    }
		}
		$htmlpdf .= "</tbody></table></table>";
		

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');
		
		$pdf->Output("algo.pdf", 'I');exit(); #asd

		######################################################################################################
		#################################### Página 5/5 ######################################################
		######################################################################################################
		//info pagina >
		$pdf->AddPage();
		$htmlpdf = "";




		$htmlpdf .= '<div class="subTitulo st4"><img class="bulletes" src="' . $img_bullet_7 . '" /> Resumen de gestiones</div><br />';

		$htmlpdf .= '<table width="100%" cellpadding="3" align="center"  style="font-size: 10pt;">';

		$htmlpdf .= '<thead><tr>';
		$htmlpdf .= '<td width="28%"></td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt;">Cantidad de gestiones</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt;">Cantidad de personas</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt;">Ratio seguimiento</td>';
		$htmlpdf .= '<td width="18%" colspan="2" style="font-size:7pt;">Días de primer contacto</td>';
		$htmlpdf .= '</tr></thead>';

		$htmlpdf .= '<tbody>';
		$htmlpdf .= '<tr>';
		$htmlpdf .= '<td style="font-size: 7pt;">Nombre ejecutivo</td>';
		$htmlpdf .= '<td style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Cotizantes<br/>   <img src="' . $img_cot_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= '<td style="font-size:6pt;">Categorizados<br/><img src="' . $img_cat_barra . '" width="50px" height="3px" /></td>';
		$htmlpdf .= "</tr>";

		//< info pagina
		$resGes = array();
		$cotGesUnicoTotal = 0;
		$cotGesTotal 	  = 0;
		$cotRatioTotal 	  = 0;
		$cotDiasTotal 	  = 0;
		$catGesUnicoTotal = 0;
		$catGesTotal 	  = 0;
		$catRatioTotal 	  = 0;
		$catDiasTotal 	  = 0;

		$colorPar = 1;
		while($row4 = $res4->fetch_assoc()) {
			$ejecutivo 	 = utf8_decode(($row4['ejecutivo']=="")?"<i>No encontrado</i>":$row4['ejecutivo']);
			$cotGesUnico = ($row4['cotGesUnico']=="")?0:$row4['cotGesUnico'];
			$cotGes 	 = ($row4['cotGes']=="")?0:$row4['cotGes'];
			$cotRatio 	 = ($row4['cotRatio']=="")?0:$row4['cotRatio'];
			$cotDias 	 = ($row4['cotDias']=="")?0:$row4['cotDias'];
			$catGesUnico = ($row4['catGesUnico']=="")?0:$row4['catGesUnico'];
			$catGes 	 = ($row4['catGes']=="")?0:$row4['catGes'];
			$catRatio 	 = ($row4['catRatio']=="")?0:$row4['catRatio'];
			$catDias 	 = ($row4['catDias']=="")?0:$row4['catDias'];

			$cotGesUnicoTotal += $cotGesUnico;
			$cotGesTotal 	  += $cotGes;
			$cotRatioTotal 	  += $cotRatio;
			$cotDiasTotal 	  += $cotDias;
			$catGesUnicoTotal += $catGesUnico;
			$catGesTotal 	  += $catGes;
			$catRatioTotal 	  += $catRatio;
			$catDiasTotal 	  += $catDias;

			if($colorPar % 2){	$clasex = '#edeeed'; }else{	$clasex = 'white';	}
			$htmlpdf .= '<tr style="background-color: '. $clasex .';">';
			$htmlpdf .= '<td height="13px" style="font-size: 8pt;">'. $ejecutivo .'</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotGesUnico . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $cotGes . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $cotRatio . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $cotDias . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $catGesUnico . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catGes . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1.8px solid #d2d4d3; font-size: 7pt;">' . $catRatio . '</td>';
			$htmlpdf .= '<td height="13px" style="border-left:1px   solid #d2d4d3; font-size: 7pt;">' . $catDias . '</td>';
			$htmlpdf .= "</tr>";
			$colorPar++;
		}

		$htmlpdf .= '<tr style="background-color: #d2eae1; color: #444444;" cellpadding="4">';
		$htmlpdf .= '<td>Total</td>';
		$htmlpdf .= '<td>' . $cotGesUnicoTotal . '</td>';
		$htmlpdf .= '<td>' . $cotGesTotal . '</td>';
		$htmlpdf .= '<td>' . $cotRatioTotal . '</td>';
		$htmlpdf .= '<td>' . $cotDiasTotal . '</td>';
		$htmlpdf .= '<td>' . $catGesUnicoTotal . '</td>';
		$htmlpdf .= '<td>' . $catGesTotal . '</td>';
		$htmlpdf .= '<td>' . $catRatioTotal . '</td>';
		$htmlpdf .= '<td>' . $catDiasTotal . '</td>';
		$htmlpdf .= '</tr>';

		$htmlpdf .= '</tbody></table>';

		/*
		$pdf->SetY(-40);
		$pdf->setFooterMargin(40);
		$pdf->SetAutoPageBreak(TRUE, 40); 
		*/

		//Se imprime
		$htmlpdfFinal = $cssPdf . $htmlpdf;
		$pdf->writeHTML($htmlpdfFinal, true, false, true, false, '');

		################################################################################
		#################################### CIERRE ####################################
		################################################################################
		#mysqli_close($boletin_db);

		$guardarComo		= "Boletin_mensual_" . $fechaAnio . "-" . $fechaMes . ".pdf";
		$pdf->Output($guardarComo, 'I');


		unlink($GubicacionFinal2Local);
		unlink($GubicacionFinalLocal);
    }

    public function doError($nro){
    	$nro = ""; $head = ""; $body = ""; $foot = "";
    	switch ($nro) {
    		case '3747':
    			$head = "Se produjo un error."; $body = "Lo sentimos, pero se produjo un error al intentar <b>exportar lo gráficos.</b>"; $foot = "";
    			break;
    		
    		default:
    			$head = "Se produjo un error en la generación del documento."; $body = "Lo sentimos, pero se produjo un error desconocido."; $foot = print_r(error_get_last()); $nro= "0";
    			break;
    	}
    	echo "<div style='background-color: #c42727; color: white; padding: 15px; border: 1px solid black; font-family: roboto, arial;'><h3>Error#" .  $nro . " - " . $head . "</h3>" . "<div>" . $body . "<br /></div>" . "<div style='background-color: white !IMPORTANT; border: 1px solid gray; color: black;'>Descripción:<br />"  . $foot . "</div></div>";
    }
}
?>
