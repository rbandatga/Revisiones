<script src='https://www.google.com/recaptcha/api.js'></script>
<section class="gleadsMenu">
    <div class="info">
        <div id="IcoCaptcha"></div><h3>Descargar</h3>
    </div>
</section>
<section id="actualizar_captcha">
    <form action="<?php echo site_url();?>boletin_mes/boletin_pdf" method="post" id="form_descarga_boletin" target="_blank">
        <input name="idZona" 		value="" type="hidden">
        <input name="fechaMes" 		value="" type="hidden">
        <input name="fechaAnio" 	value="" type="hidden">
        <input name="fechaCompleta" value="" type="hidden">
        <input name="svg_origen" 	value="" type="hidden">
        <input name="svg_volumen" 	value="" type="hidden">
        <div class="center_captcha">
        	<div class="g-recaptcha" data-sitekey="6LfNplIUAAAAAF6poaOKqauvKjYZBHfXIB02e39e" data-callback="imNotARobot"></div>
        </div>
        <div id="errCaptcha"></div>
        <div id="okCaptcha"></div>
		<div class="btn_descargar_captcha" onclick="enviaCaptchaBoletin();" style="display: none;">
	        <img class="icono_descargar" src="http://gleads.desarrollo.cool//images/sitio/reporte_pro/boletin/icono_descargar.png">
	        <p class="descargar">DESCARGAR</p>
	    </div>
    </form>
</section>
<script type="text/javascript">
	$('input[name=idZona]').val($('input[name=idZonaCaptcha]').val());
	$('input[name=fechaMes]').val($('input[name=fechaMesCaptcha]').val());
	$('input[name=fechaAnio]').val($('input[name=fechaAnioCaptcha]').val());
	$('input[name=fechaCompleta]').val($('input[name=fechaCompletaCaptcha]').val());
	$('input[name=svg_origen]').val($('input[name=svg_origenCaptcha]').val());
	$('input[name=svg_volumen]').val($('input[name=svg_volumenCaptcha]').val());
	function enviaCaptchaBoletin(){
		if(grecaptcha.getResponse().length == 0){
			$('#errCaptcha').html('Resuelve el captcha');
			$('#okCaptcha').html('');
		}else{
			$('#errCaptcha').html('');
			$("#form_descarga_boletin").submit();
			popSalir('2');
		}
	}
	var imNotARobot = function() {
		$('#errCaptcha').html('');
		$('.btn_descargar_captcha').fadeIn(2000);
	};
</script>
<style type="text/css" media="screen">
    .popSalirBtn{
        top: -6px !important;
    }
</style>
