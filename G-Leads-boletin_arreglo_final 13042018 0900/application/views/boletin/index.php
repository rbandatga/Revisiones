<?php

$boletin_db = $this->load->database('boletin_mensual',TRUE);

$arrFecha = array();

$arrFecha[1]  = "ENERO";
$arrFecha[2]  = "FEBRERO";
$arrFecha[3]  = "MARZO";
$arrFecha[4]  = "ABRIL";
$arrFecha[5]  = "MAYO";
$arrFecha[6]  = "JUNIO";
$arrFecha[7]  = "JULIO";
$arrFecha[8]  = "AGOSTO";
$arrFecha[9]  = "SEPTIEMBRE";
$arrFecha[10] = "OCTUBRE";
$arrFecha[11] = "NOVIEMBRE";
$arrFecha[12] = "DICIEMBRE";

$ID_INMOBILIARIA 		 = $this->valentinausuarios->empresaid($_SESSION['user']);
$nombreUsuario			 = $this->valentinausuarios->nombre($_SESSION['user']);
$nombreInmobiliaria      = $this->valentinausuarios->empresa($_SESSION['user']);

$idZona                  = ($this->input->get("zona")=="")?1:$this->input->get("zona");
$fechaMes 		 		 = ($this->input->get("mes")=="")?3:$this->input->get("mes");
$fechaAnio 		 		 = ($this->input->get("anio")=="")?2018:$this->input->get("anio");
if($ID_INMOBILIARIA==96 && $idZona==1){$idZona=2;}
$fechaCompleta 			 = $arrFecha[$fechaMes]." ".$fechaAnio;

$totalCotWeb 			 = 0;
$totalCotWebUnico 		 = 0;
$totalCotPresencial 	 = 0;
$totalCotPresencialUnico = 0;
$totalCon 				 = 0;
$totalConUnico 			 = 0;
$totalCat 				 = 0;
$totalCatUnico 			 = 0;
$catOrigenWeb 			 = 0;
$catOrigenEmailing 		 = 0;
$catOrigenEjecutivo 	 = 0;
$catOrigenVenta 		 = 0;

$res0 	= $this->Boletinmodel->getResumen0($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
$total0 = $res0->num_rows();
if($total0 > 0){
	$row0 					 = $res0->row();
	$totalCotWeb 			 = $row0->totalCotWeb;
	$totalCotWebUnico 		 = $row0->totalCotWebUnico;
	$totalCotPresencial 	 = $row0->totalCotPresencial;
	$totalCotPresencialUnico = $row0->totalCotPresencialUnico;
	$totalCon 				 = $row0->totalCon;
	$totalConUnico 			 = $row0->totalConUnico;
	$totalCat 				 = $row0->totalCat;
	$totalCatUnico 			 = $row0->totalCatUnico;
	$catOrigenWeb			 = round((($row0->catOrigenWeb*100)/$totalCatUnico),0);
	$catOrigenEmailing		 = round((($row0->catOrigenEmailing*100)/$totalCatUnico),0);
	$catOrigenEjecutivo		 = round((($row0->catOrigenEjecutivo*100)/$totalCatUnico),0);
	$catOrigenVenta			 = round((($row0->catOrigenVenta*100)/$totalCatUnico),0);
}


$proyCateg		  = "";
$cotGestCateg	  = "";
$cotNoGestCateg	  = "";
$procentCotGest   = 0;
$procentCotNoGest = 0;
$cantProy5        = 0;

$res5   = $this->Boletinmodel->getResumen5($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
$total5 = $res5->num_rows();
if($total5 > 0){
	$comma  = "";
	$porU   = 0;
	$porUG  = 0;
	$porUNG = 0;
	$row5 	= $res5->row();
	for ($i=0; $i < $total5; $i++) {
		$idProyecto       = $row5->idProyecto;
		$proyecto         = $row5->proyecto;

		$cotUnicos        = ($row5->cotUnicos=="")?0:$row5->cotUnicos;
		$cotUnicosGest    = ($row5->cotUnicosGest=="")?0:$row5->cotUnicosGest;
		$cotUnicosNoGest  = ($row5->cotUnicosNoGest=="")?0:$row5->cotUnicosNoGest;

		$proyCateg 		  = $proyCateg.$comma."'$proyecto'";
		$cotGestCateg     = $cotGestCateg.$comma."$cotUnicosGest";
		$cotNoGestCateg   = $cotNoGestCateg.$comma."$cotUnicosNoGest";
		$comma 			  = ",";

		$porU   = $porU   + $cotUnicos;
		$porUG  = $porUG  + $cotUnicosGest;
		$porUNG = $porUNG + $cotUnicosNoGest;

		$row5 	= $res5->next_row();
    }
    $cantProy5        = count((strpos($proyCateg, ",")===FALSE)?0:explode(",", $proyCateg));
    $procentCotGest   = round(($porUG/$porU)*100);
    $procentCotNoGest = round(($porUNG/$porU)*100);
}

?>
<!--HEADER-->
<header>
	<div class="contenedor">
		<img class="logotipo_gleads" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/logotipo_gleads.png">
		<div class="panel_reporte">
			<div class="btn_reporte" onclick="volver();">
				<img class="icono_volver" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/ico_back.svg">
				<p class="panel_boletin">Panel de reporte</p>
			</div>
			<img class="barra" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/barra.png">
			<img class="icono_off" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/ico_off.svg" onclick="salir();">
		</div>
	</div>
</header>
<!--FIN HEADER-->

<!--ENCABEZADO-->
<section class="encabezado">
	<div class="contenedor">
		<div class="contenedor_titulo">
			<p class="titulo_boletin">Boletín mensual G- Leads</p>
		</div>
		<div class="contenedor_cliente">
			<p class="cliente">Cliente: <b><?= $nombreInmobiliaria; ?></b>
			<img class="icono_cliente" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/ico_inmob.svg"></p>
			<p class="cliente">Usuario: <b><?= $nombreUsuario; ?></b>
			<img class="icono_usuario" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/ico_user.svg"></p>
		</div>
	</div>
</section>
<!--FIN ENCABEZADO-->

<!--FILTRAR POR-->
<section class="select_filtros">
	<div class="contenedor">
	   <div class="contenedor_filtrar">
		    <div class="texto_filtrar">FILTRAR POR</div>
            <div class="selectClass">
                <select id="filtro_anio" data-width="100%">
                    <option value="2018">2018</option>
                </select>
            </div>
			<div class="selectClass">
                <select id="filtro_mes" data-width="100%">
                    <option value="3" selected >Marzo</option>
                </select>
            </div>
            <?php if($ID_INMOBILIARIA == 96){?>
        	<div class="selectClass selectClass3">
				<select id="filtro_zona" data-width="100%">
					<option value="2">Zona Norte</option>
					<option value="3">Zona Centro Norte</option>
					<option value="4">Zona Centro</option>
					<option value="5">Zona Sur</option>
					<option value="6">Zona Sur Austral</option>
				</select>
            </div>
            <?php } ?>
			<div class="btn_filtrar" onclick="filtrar();">
			   	<p class="descargar">FILTRAR</p>
			</div>
			<input type="hidden" name="idZonaCaptcha" 		 value="<?= $idZona; ?>">
			<input type="hidden" name="fechaMesCaptcha" 	 value="<?= $fechaMes; ?>">
			<input type="hidden" name="fechaAnioCaptcha" 	 value="<?= $fechaAnio; ?>">
			<input type="hidden" name="fechaCompletaCaptcha" value="<?= $fechaCompleta; ?>">
			<input type="hidden" name="svg_origenCaptcha" 	 value="" id="svg_origen">
			<input type="hidden" name="svg_volumenCaptcha"   value="" id="svg_volumen">
		    <div class="btn_descargar" onclick="validaReCaptchaPDF();">
		        <img class="icono_descargar" src="<?= base_url(); ?>/images/sitio/reporte_pro/boletin/icono_descargar.png">
		        <p class="descargar">DESCARGAR</p>
		    </div>
		 </div>
	 </div>
</section>
<!--FIN FILTRAR POR-->

<!--RESUMEN MENSUAL-->
<section class="resumen_mensual">
	<div class="contenedor">
	    <!--COTIZACIONES WEB-->
		<div class="resumen">
			<p class="nombre_resumen">Cotizaciones web</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?= $totalCotWeb; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?= $totalCotWebUnico; ?></p>
			</div>
		</div>
		<!--FIN COTIZACIONES WEB-->

		<!--COTIZACIONES PRESENCIALES-->
		<div class="resumen">
			<p class="nombre_resumen">Cotizaciones presenciales</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?= $totalCotPresencial; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?= $totalCotPresencialUnico; ?></p>
			</div>
		</div>
		<!--FIN COTIZACIONES PRESENCIALES-->

		<!--CONSULTAS-->
		<div class="resumen">
			<p class="nombre_resumen">Consultas</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?= $totalCon; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?= $totalConUnico; ?></p>
			</div>
		</div>
		<!--FIN CONSULTAS-->

		<!--PERSONAS CATEGORIZADAS-->
		<div class="resumen">
			<p class="nombre_resumen">Personas categorizadas</p>
			<div class="caja_totales">
				<p class="nombre_totales">Totales</p>
				<p class="totales"><?= $totalCatUnico; ?></p>
			</div>
			<div class="caja_unicos">
				<p class="nombre_unicos">Únicos</p>
				<p class="unicos"><?= $totalCatUnico; ?></p>
			</div>
		</div>
		<!--FIN PERSONAS CATEGORIZADAS-->
	</div>
</section>
<!--FIN RESUMEN MENSUAL-->

<!--CONTENIDO CENTRAL-->
<section class="contenido_central">
	<!--FUENTE DE TRAFICO-->
	<section class="fuente_trafico">
		<div class="subtitulo">
		    <img class="icono_fuente" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_fuente.png">
			<p class="nombre_section">Fuente de tráfico</p>
		</div>
		<!--POR PORTAL-->
		<div class="caja_1">
			<div class="caja_1A">
				<div class="table_responsive_1AB">
					<table class="table_1A">
						<thead>
							<tr>
								<td class="fondo_titulo_lista"><p class="titulo_lista">Cotizaciones por portal</p></td>
								<th class="fondo_titulo_lista">Totales</th>
								<th class="fondo_titulo_lista">Únicos</th>
							</tr>
						</thead>
							<?php
								$res1   = $this->Boletinmodel->getResumen1($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
								$total1 = $res1->num_rows();
								if($total1 > 0){
									$row1 = $res1->row();
									$tcot = 0;
									for ($i=0; $i < $total1; $i++) {
										$portal		  	   = $row1->portal;
										$cotWebPortal	   = $row1->cotWebPortal;
										$cotWebPortalUnico = $row1->cotWebPortalUnico;
										?>
										<tr>
											<td class="fondo_portal"><p class="nombre_lista"><?= $portal; ?></p></td>
											<td class="fondo_portal"><p class="resul_totales"><?= $cotWebPortal; ?></p></td>
											<td class="fondo_portal"><p class="resul_unicos"><?= $cotWebPortalUnico; ?></p></td>
										</tr>
										<?php
										$tcot++;
										$row1 = $res1->next_row();
								    }
								}
							?>
					</table>
					<?php 
						if($total1 == 0){
							#poner imagen, si no tiene datos que mostrar.
						}
					?>
				</div>
			</div> <!-- END caja_1A-->
			<div class="caja_1B">
				<div class="table_responsive_1AB">
					<table class="table_1B">
						<thead>
							<tr>
								<td class="fondo_titulo_lista"><p class="titulo_lista">Consultas por portal</p></td>
								<th class="fondo_titulo_lista">Totales</th>
								<th class="fondo_titulo_lista">Únicos</th>
							</tr>
						</thead>
						<?php
						$res2 	= $this->Boletinmodel->getResumen2($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
						$total2 = $res2->num_rows();
						if($total2 > 0){
							$row2 = $res2->row();
							for ($i=0; $i < $total2; $i++) { 
								$portal		  	= $row2->portal;
								$conPortal	    = $row2->conPortal;
								$conPortalUnico = $row2->conPortalUnico;
								?>
								<tr>
									<td class="fondo_consulta"><p class="nombre_lista"><?= $portal; ?></p></td>
									<td class="fondo_consulta"><p class="resul_totales"><?= $conPortal; ?></p></td>
									<td class="fondo_consulta"><p class="resul_unicos"><?= $conPortalUnico; ?></p></td>
								</tr>
								<?php
								$row2 = $res2->next_row();
						    }
						}
						?>
					</table>
					<?php 
						if($total2 == 0){
							#poner imagen, si no tiene datos que mostrar.
						}
					?>
				</div>
			</div> <!-- END table_1B-->
		</div><!--FIN POR CONSULTA-->
	</section>
	<!--FUENTE DE TRAFICO-->

	<!--RESUMEN DE COTIZACIONES-->
	<section class="cotizaciones">
		<div class="subtitulo">
		    <img class="icono_cotizaciones" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_cotizaciones.png">
			<p class="nombre_section">Resumen de cotizaciones </p>
		</div>
		<div class="caja_2">
			<div class="caja_cotizacion">
				<div class="table_responsive">
					<table class="tabla_cotizacion ">
						<?php

							$arrIdProyectos     = array();
							$arrNomProyectos 	= array();
							$arrIdPortales 		= array();
							$arrNomPortales 	= array();

							$arrCotResumen 		= array();
							$arrCotResumenUnico = array();

							$res3 	= $this->Boletinmodel->getResumen3($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
							$total3 = $res3->num_rows();
							if($total3 > 0){
								$proy = 0;
								$port = 0;
								$row3 = $res3->row();
								for ($i=0; $i < $total3; $i++) {
									$idProyecto		 = $row3->idProyecto;
									$proyecto	     = $row3->proyecto;
									$idPortal 		 = $row3->idPortal;
									$portal		  	 = $row3->portal;
									$cotResumen	     = $row3->cotResumen;
									$cotResumenUnico = $row3->cotResumenUnico;
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
									$row3 = $res3->next_row();
							    }

							}
						?>
						<tr>
							<td class="items" colspan="1"></td>
							<?php
								for ($i=0; $i < count($arrIdPortales); $i++) {
									?>
										<td class="nombre_items" colspan="2"><?= $arrNomPortales[$i]; ?></td>
									<?php
								}
							?>
						</tr>
						<tr>
							<td class="categoria" colspan="1"></td>
							<?php
								for ($i=0; $i < count($arrIdPortales); $i++) {
									?>
										<td class="categoria_t" colspan="1">Total</td>
										<td class="categoria_u" colspan="1">Únicos</td>
									<?php
								}
							?>
						</tr>
						<tr class="fila_gris">
							<?php
								$c0 = '0';
								for ($i=0; $i < count($arrIdProyectos); $i++) {
									$idProyectoFind = $arrIdProyectos[$i];
									?>
									<tr class="<?= ($c0%2==0)?'fila_gris':''; ?>">
										<td class="proyecto" colspan="1"><p class="left"><?= $arrNomProyectos[$i]; ?></p></td>
									<?php
										for ($u=0; $u < count($arrIdPortales); $u++) {
											$idPortalFind = $arrIdPortales[$u];
											?>
												<td class="resultado_t" colspan="1"><?= ( empty($arrCotResumen[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumen[$idProyectoFind][$idPortalFind]; ?></td>
												<td class="resultado_u" colspan="1"><?= ( empty($arrCotResumenUnico[$idProyectoFind][$idPortalFind]) )?0:$arrCotResumenUnico[$idProyectoFind][$idPortalFind]; ?></td>
											<?php
										}
									?>
									</tr>
									<?php
									$c0++;
								}
							?>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</section>
	<!--FIN RESUMEN DE COTIZACIONES-->

	<!--CATEGORIZACION-->
	<section class="categorizacion">
		<div class="caja_3">
			<div class="caja_3_A">
				<div class="subtitulo_resumen_3A">
					<img class="icono_origen" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_origen_categorizacion.png">
					<p class="nombre_section">Origen de la categorización</p>
				</div>
				<div class="caja_origen" id="caja_origen">
				</div>
			</div>
			<div class="caja_3_B">
				<div class="subtitulo_resumen_3B">
		    		<img class="icono_resumen" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_resumen_categorizacion.png">
					<p class="nombre_section">Resumen de categorizados por asesor</p>
		 		</div>
				<div class="caja_resumen">
					<div class="categorizados_ejecutivoscxzcategorizados_ejecutivoscxz">
						<div class="table_responsive_3B">
							<table class="table_3B">
								<thead>
									<tr>
										<td class="fondo_titulo_lista">
											<p class="titulo_lista">Asesor comercial</p>
										</td>
										<th class="fondo_titulo_lista">Cantidad cat. ventas</th>
										<th class="fondo_titulo_lista">Cantidad cat. asesores</th>
									</tr>
								</thead>
								<?php
									$res4 	= $this->Boletinmodel->getResumen4($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
									$total4 = $res4->num_rows();
									if($total4 > 0){
										$row4 = $res4->row();
										for ($i=0; $i < $total4; $i++) {
											$ejecutivo		   = $row4->ejecutivo;
											$catEjecutivoPanel = $row4->catEjecutivoPanel;
											$catEjecutivoFicha = $row4->catEjecutivoFicha;
											?>
												<tr>
													<td class="fondo_ejecutivo"><p class="nombre_lista"><?= $ejecutivo; ?></p></td>
													<td class="fondo_lista"><p class="resul_eject"><?= $catEjecutivoPanel; ?></p></td>
													<td class="fondo_lista"><p class="resul_ventas"><?= $catEjecutivoFicha; ?></p></td>
												</tr>
											<?php
											$row4 = $res4->next_row();
									    }
									}
								?>
							</table>
							<?php 
								if($total4 == 0){
									?>
									<img class="img_no_asesor" src="https://s3-us-west-1.amazonaws.com/tgaspa/G-Leads/boletin/img_mensaje.svg" alt="No hay asesores">
									<?php
								}
							?>
						</div>
				    </div>
			    </div>
			</div>
		</div>
		<div class="texto_info">
			<p class="texto"><b>Categorización web:</b> Respuestas obtenidas de banner situado en la web de la inmobiliaria.</p>
	        <p class="texto"><b>Categorización E-mailing:</b> Respuestas obtenidas a través de la campaña E-mailing post cotización o consulta.</p>
	        <p class="texto"><b>Categorización Ejecutivo:</b> Cuestionario realizado por el ejecutivo a través de la ficha cotizante. (Vía Telefónica)</p>
	        <p class="texto"><b>Categorización Ventas:</b> Cuestionario realizado por el ejecutivo a través del botón cuestionario del panel de G-Leads.</p>
	        <p class="texto">(Envío por email o ingreso de cliente)</p>
		</div>
	</section>
	<!--FIN CATEGORIZACION-->

	<!--VOLUMEN DE PERSONAS-->
	<section class="volumen_personas">
			<div class="subtitulo">
			    <img class="icono_volumen" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_volumen.png">
				<p class="nombre_section">Volumen de personas obtenidas al mes y que no han sido gestionadas por proyecto mensual</p>
			</div>
			<div class="datosgrafico_1">
				<div class="dato">
					<div style="float: left;">
						<b class="porcentaje_1"><?= $procentCotNoGest; ?>%</b>
					</div>
					<div class="cotizantes_unicos">Cotizantes únicos<br>
					    <b class="no_gestionadas">NO gestionados</b>
					</div>
				</div>
				<div class="dato">
					<div style="float: left;">
						<b class="porcentaje_2"><?= $procentCotGest; ?>%</b>
					</div>
					<div class="cotizantes_unicos">Cotizantes únicos<br>
						<b class="si_gestionados">gestionados</b>
					</div>
			    </div>
			</div>
			<div class="caja_4" id="caja_4">
			</div>

	</section>
	<!--FIN VOLUMEN DE PERSONAS-->

	<!--RESUMEN DE GESTIONES-->
	<section class="gestiones">
			<div class="subtitulo">
			    <img class="icono_gestion" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/icono_gestiones.png">
				<p class="nombre_section">Resumen de gestiones</p>
			</div>
			<div class="caja_5">
				<div class="caja_gestiones">
					<div class="table_responsive">
						<table class="">
							<tr>
								<td class="itemss" colspan="1"></td>
								<td class="nombre_items" colspan="4">Cantidad de gestiones</td>
								<td class="nombre_items" colspan="4">Cantidad de personas</td>
								<td class="nombre_items" colspan="4">Ratio seguimiento</td>
								<td class="nombre_items" colspan="4">Días de primer contacto</td>
							</tr>
							<tr>
								<td class="categoria_ejecutvio" colspan="1"></td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
								<td class="cat_cotizantes" colspan="1">Cotizantes</td>
								<td class="cat_categorizados" colspan="3">Categorizados</td>
							</tr>
							<tr>
								<td class="colores" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
								<td class="gris" colspan="1"></td>
								<td class="naranjo" colspan="1"></td>
								<td class="rojo" colspan="1"></td>
								<td class="amarillo" colspan="1"></td>
							</tr>
							<?php
								$cotGesUnicoTotal = 0;
								$cotGesTotal 	  = 0;
								$cotRatioTotal 	  = 0;
								$cotDiasTotal 	  = 0;
								$catGesUnicoTotal = 0;
								$catGesTotal 	  = 0;
								$catRatioTotal 	  = 0;
								$catDiasTotal 	  = 0;
								$res6 			  = $this->Boletinmodel->getResumen6($ID_INMOBILIARIA,$idZona,$fechaMes,$fechaAnio);
								$total6           = $res6->num_rows();
								if($total6 > 0){
									$c 			 = '0';
									$f 			 = $total6-1;
									$cantCatDias = 0;
									$cantCotDias = 0;
									$row6 		 = $res6->row();
									for ($i=0; $i < $total6; $i++) {
										$ejecutivo 	 = $row6->ejecutivo;
										$cotGesUnico = $row6->cotGesUnico;
										$cotGes 	 = $row6->cotGes;
										$cotRatio 	 = $row6->cotRatio;
										$cotDias 	 = $row6->cotDias;
										$catGesUnico = $row6->catGesUnico;
										$catGes 	 = $row6->catGes;
										$catRatio 	 = $row6->catRatio;
										$catDias 	 = $row6->catDias;

										if($catDias!=0){$cantCatDias++;}
										if($cotDias!=0){$cantCotDias++;}

										$cotGesUnicoTotal = $cotGesUnicoTotal+$cotGesUnico;
										$cotGesTotal 	  = $cotGesTotal+$cotGes;
										$cotDiasTotal 	  = $cotDiasTotal+$cotDias;
										$catGesUnicoTotal = $catGesUnicoTotal+$catGesUnico;
										$catGesTotal 	  = $catGesTotal+$catGes;
										$catDiasTotal 	  = $catDiasTotal+$catDias;
										?>
										    <tr class="<?= ($c%2==0)?'fila_gris':''; ?>">
											    <td class="ejecutivos" colspan="1"><p class="left"><?= $ejecutivo; ?></p></td>
												<td class="resultado_cot" colspan="1" id="td_<?= $i ?>_0"><?= $cotGes; ?></td>
												<td class="resultado_cat" colspan="3" id="td_<?= $i ?>_1"><?= $catGes; ?></td>
												<td class="resultado_cot" colspan="1" id="td_<?= $i ?>_2"><?= $cotGesUnico; ?></td>
											    <td class="resultado_cat" colspan="3" id="td_<?= $i ?>_3"><?= $catGesUnico; ?></td>
												<td class="resultado_cot" colspan="1" id="td_<?= $i ?>_4"><?= $cotRatio; ?></td>
												<td class="resultado_cat" colspan="3" id="td_<?= $i ?>_5"><?= $catRatio; ?></td>
												<td class="resultado_cot" colspan="1" id="td_<?= $i ?>_6"><?= round($cotDias,1); ?></td>
												<td class="resultado_cat" colspan="3" id="td_<?= $i ?>_7"><?= round($catDias,1); ?></td>
											</tr>
										<?php
										$c++;
										$row6 = $res6->next_row();
								    }
									$cotRatioTotal = ($cotGesUnicoTotal>0)?round(($cotGesTotal/$cotGesUnicoTotal),1):0;
									$catRatioTotal = ($catGesUnicoTotal>0)?round(($catGesTotal/$catGesUnicoTotal),1):0;
									$cotDiasTotal  = ($cantCotDias>0)?round(($cotDiasTotal/$cantCotDias),1):0;
									$catDiasTotal  = ($cantCatDias>0)?round(($catDiasTotal/$cantCatDias),1):0;
								}
							?>
							<tr class="fila_total">
							    <td class="ejecutivos" colspan="1"><p class="left">Total</p></td>
								<td class="resultado_cot" colspan="1" id="td_t_0"><?= $cotGesTotal; ?></td>
								<td class="resultado_cat" colspan="3" id="td_t_1"><?= $catGesTotal; ?></td>
								<td class="resultado_cot" colspan="1" id="td_t_2"><?= $cotGesUnicoTotal; ?></td>
							    <td class="resultado_cat" colspan="3" id="td_t_3"><?= $catGesUnicoTotal; ?></td>
								<td class="resultado_cot" colspan="1" id="td_t_4"><?= $cotRatioTotal; ?></td>
								<td class="resultado_cat" colspan="3" id="td_t_5"><?= $catRatioTotal; ?></td>
								<td class="resultado_cot" colspan="1" id="td_t_6"><?= $cotDiasTotal; ?></td>
								<td class="resultado_cat" colspan="3" id="td_t_7"><?= $catDiasTotal; ?></td>
							</tr>
						</table>
					</div>
				</div>
			</div>
			<div class="texto_info">
				<p class="texto"><b>Cantidad de gestiones:</b> Es el número total de gestiones realizadas por los ejecutivos a sus cotizantes web durante el mes del informe.</p>
				<p class="texto"><b>Cantidad personas:</b> Es el total de personas únicas web gestionadas por el ejecutivo.</p>
				<p class="texto"><b>Ratio de seguimiento:</b> Representa a la cantidad promedio de gestiones/seguimiento que un ejecutivo hizo a un cotizante que haya realizado una cotización durante el mes del informe.</p>
				<p class="texto"><b>Días de primer contacto:</b> Es cantidad promedio de días que se tardó un ejecutivo en darle un primer contacto a un cotizante que cotizó durante el mes del informe.</p>
			</div>
	</section>
	<!--FIN RESUMEN DE GESTIONES-->

	<!--FOOTER-->
	<footer>
	<div class="contenedor">
		<img class="logotipo_tga" src="<?= base_url(); ?>images/sitio/reporte_pro/boletin/logotipo_tga.png" />
		<p class="texto_footer"></p>
	</div>
	</footer>
	<!--FIN FOOTER-->
</section>
<div id="cargaR"></div>
<!--FIN CONTENIDO CENTRAL-->

<script type="text/javascript">
	$('#filtro_anio').selectpicker();
	$('#filtro_mes').selectpicker();
	<?php if($ID_INMOBILIARIA == 96){ ?>
		$('#filtro_zona').selectpicker();		
	<?php } ?>
	var grafico_origen = new Highcharts.chart('caja_origen',{
    chart: {
        type: 'bar'
    },
	title: {
        text: ''
    },
    subtitle: {
        text: 'Canal de origen de las categorizaciones llegadas durante el mes'
    },
    credits: {
      enabled: false
  	},
    exporting: {
    	enabled: true
    },
    lang: {
	    printChart: 'Imprimir gráfica',
	    downloadPNG: 'Descargar como PNG',
	    downloadJPEG: 'Descargar como JPEG',
	    downloadPDF: 'Descargar como PDF',
	    downloadSVG: 'Descargar como SVG',
	    contextButtonTitle: 'Menú de descargas'
	},
    xAxis: {
        categories: ['% Cat web', '% Cat emailing', '% Cat ejecutivo', '% Cat ventas']
    },
    yAxis: {
        min: 0,
        max: 100,
        labels: {
        	formatter: function() {
           return this.value+"%";
        	}
      	},
        title: {
            text: 'Porcentaje'
        }
    },
    legend: {
    		enabled: false
    },
    plotOptions: {
        series: {
            stacking: '',
            dataLabels: {
            	format: '{y} %'
            }
        },
        bar: {
            dataLabels: {
                enabled: true
            }
        },
    },
    series: [{
        name: '',
        data: [<?= $catOrigenWeb; ?>,<?= $catOrigenEmailing; ?>,<?= $catOrigenEjecutivo; ?>,<?= $catOrigenVenta; ?>],
        color: "#73879c"
    }],
});

function cargado(){}

var grafico_volumen = new Highcharts.chart('caja_4',{
	credits:{
		enabled: false
    },
    chart: {
        type: 'area',
        height: 400
    },
    exporting: {
    	enabled: true
    },
    lang: {
	    printChart: 'Imprimir gráfica',
	    downloadPNG: 'Descargar como PNG',
	    downloadJPEG: 'Descargar como JPEG',
	    downloadPDF: 'Descargar como PDF',
	    downloadSVG: 'Descargar como SVG',
	    contextButtonTitle: 'Menú de descargas'
	},
    title: {
    	useHTML: true,
    	align: 'left',
        text: '<p></p>'
    },
    xAxis: {
    	title: {
      		text: 'Cotizantes únicos'
      	},
      	categories: [<?= $proyCateg; ?>]
  	},
    yAxis: {
        title: {
            text: 'Cantidad de personas'
        }
    },
    tooltip: {
        pointFormat: 'Cantidad de {series.name} : <b>{point.y:,.0f}</b>'
    },
    series: [{
        name: 'Cotizaciones únicos no gestionados',
        data: [<?= $cotNoGestCateg; ?>],
        color: '#73879c',
        label: false
    }, {
        name: 'Cotizantes únicos gestionados',
        data: [<?= $cotGestCateg; ?>],
        color: "#64C79F",
        label: false
    }]
});
function salir(){
	window.location = '<?= base_url(); ?>valentina/entrada/close_user';
}
function volver(){
	window.location = '<?= base_url(); ?>reportes';
}
function filtrar(){
	var filtro_anio = $('#filtro_anio').val();
	var filtro_mes  = $('#filtro_mes').val();
	var filtro_zona = $('#filtro_zona').val();
	if(typeof filtro_zona == 'undefined'){
		filtro_zona = 1;
	}
	window.location = "<?= base_url(); ?>boletin?anio="+filtro_anio+"&mes="+filtro_mes+"&zona="+filtro_zona;
}
function validaReCaptchaPDF(){
    popMuestraEnProceso(2,350,'boletin_mes/Boletin/Descarga_boletin','FFF');
}

function guardaSVG(){
	valWidth = 2200;
	if (<?= $cantProy5; ?> <= 20) {
	    valWidth = 1100;
	}
	var grafico_volumenR = grafico_volumen.getSVG({
									chart: {
										width: 1100,
										height: 400
									}
								});
	var grafico_origenR = grafico_origen.getSVG({
										chart: {
											width: 450,
											height: 300
										}
									});
	$("#svg_volumen").val(grafico_volumenR);
	$("#svg_origen").val(grafico_origenR);
}
guardaSVG();
<?php if($ID_INMOBILIARIA == 96){ ?>
	$("#filtro_zona").val("<?= $idZona; ?>").selectpicker('refresh');		
<?php } ?>
$("#filtro_anio").val("<?= $fechaAnio; ?>").selectpicker('refresh');	
$("#filtro_mes").val("<?= $fechaMes; ?>").selectpicker('refresh');
/*$("#caja_4 g.highcharts-series-group g.highcharts-series g.highcharts-label text tspan").html('');*/

function resumen6Rojos(){
	for (var i = 0; i < <?= $total6; ?>; i++) {
		for (var e = 4; e < 8; e++) {
			valor1 = $('#td_'+i+'_'+e).html();
			valor2 = $('#td_t_'+e).html();
			if( parseFloat(valor1) < parseFloat(valor2) ){
				$('#td_'+i+'_'+e).css('color','red');
			}
			/*if(valor.indexOf(".")==-1){
				$('#td_'+i+'_'+e).html(valor+'.0');
			}*/
		}
	}
}
resumen6Rojos();
</script>
<?php
?>
