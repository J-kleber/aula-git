<?php
/*ini_set('display_errors', true);
error_reporting(E_ALL);*/

global $wpdb;

$estados = $wpdb->get_results("SELECT cod_estados, sigla FROM estados ORDER BY sigla", ARRAY_A);

?>

<script>
jQuery(document).ready(function() {

    jQuery("input[name='telefone']").on('click', function() {
        jQuery('.alerta-invalido').remove();
        jQuery('input[name="telefone"]').css('border', 'unset');
    });
    
    var telefone;

	jQuery(".btn-step-1").click(function (){ // Ajax do passo 1 que cria a conta com o numero de telefone e ja cria um código de ativação para tal conta      
        telefone = jQuery("input[name='telefone']").val();
        telefone = telefone.replace(') ', '');
        telefone = telefone.replace('(', '');
        telefone = telefone.replace('-', '');
        
        var data = {
                    "tel" : telefone
                };
        
        if ( telefone.length == 10 || telefone.length == 11 ) {
        
            //coloca um fundo branco para a pessoa n�o poder mexer enquanto o ajax carregar
            var $form = jQuery('form[name="registro_celular"]');
            $form.addClass( 'processing2' );
            $form.block({
    			message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
        
            jQuery.ajax({
                type: "POST",
                url: "../wp-content/plugins/ht-subscriptions/front/ajax/ht_step1_tel.php",
                data: data,
                success: function(data)
                {
                    if ( data == 1 ) { 
                        jQuery('.alerta-invalido').remove();
                        jQuery('input[name="telefone"]').css('border', 'unset');
                        jQuery(".step-1").hide();
        			    jQuery(".step-2").show();
                    } else if ( data == 2 ) {
                        jQuery('.alerta-invalido').remove();
                        jQuery('input[name="telefone"]').css('border', '1px solid red');
                        jQuery('.box-input-registro').append('<div class="alerta-invalido">'+unescape('Celular j%E1 est%E1 cadastrado!')+'</div>');
                    }    
                    $form.removeClass( 'processing2' ).unblock();
                },
                error: function(erro) {
                    console.log(erro);
                    $form.removeClass( 'processing2' ).unblock();
                    alert('Houve um erro ao tentar cadastrar, tente novamente mais tarde.');
                }                             
            });
        }
    });
    
    jQuery("input[name='key']").on('input', function() {
        jQuery('.alerta-invalido').remove();
        jQuery('input[name="key"]').css('border', 'unset');
    });

    jQuery(".btn-step-2").click(function (){ // Ajax do passo 2 que verifica a conta atravez do sms

        var data2 = {
                    "tel" : telefone,
                    "key" : jQuery("input[name='key']").val()
                };
        
        if ( jQuery("input[name='key']").val() != "" && jQuery("input[name='telefone']").val() != "" ) {
        
            //coloca um fundo branco para a pessoa n�o poder mexer enquanto o ajax carregar
            var $form = jQuery('form[name="confirma_celular"]');
            $form.addClass( 'processing2' );
            $form.block({
    			message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
        
            jQuery.ajax({
                type: "POST",
                url: "../wp-content/plugins/ht-subscriptions/front/ajax/ht_step2_confirmation.php",
                data: data2,
                success: function(data2)
                {
                    console.log(data2);
                    if ( data2 == 1 ) {
                        console.log(1);
                        jQuery('.alerta-invalido').remove();
                        jQuery('input[name="key"]').css('border', 'unset');
                        jQuery(".step-2").hide();
        			    jQuery(".step-3").show();
                    } else if ( data2 == 2 ) {
                        console.log(2);
                        jQuery('.alerta-invalido').remove();
                        jQuery('input[name="key"]').css('border', '1px solid red');
                        jQuery('.box-input-registro-2').append('<div class="alerta-invalido">'+unescape('C%F3digo Inv%E1lido!')+'</div>');
                    }
                    $form.removeClass( 'processing2' ).unblock();
                },
                error: function(erro) {
                    $form.removeClass( 'processing2' ).unblock();
                    alert('Houve um erro ao tentar cadastrar, tente novamente mais tarde.');
                }
            });
        } else if ( telefone == "" ) {
            jQuery('.box-input-registro-2').append('<div class="alerta-invalido">'+unescape('N%FAmero do Celular N%E3o Econtrado!')+'</div>');
        }         
    });
    
    jQuery("input[name='name']").on('input', function() {        
        var classe = jQuery(this).parent()[0].className;
        jQuery('.'+classe+' .alerta-invalido').remove();       
        jQuery('input[name="name"]').css('border', 'unset');
    });
    
    jQuery("select[name='country']").on('change', function() {        
        var classe = jQuery(this).parent().parent()[0].className;
        jQuery('.'+classe+' .alerta-invalido').remove();       
        jQuery('.'+classe+' .ls-custom-select').css('border', 'unset');
        
        jQuery('.box-input-city .alerta-invalido').remove();       
        jQuery('.box-input-city .ls-custom-select').css('border', 'unset');
    });
    
    jQuery("select[name='city']").on('change', function() {        
        var classe = jQuery(this).parent().parent()[0].className;
        jQuery('.'+classe+' .alerta-invalido').remove();       
        jQuery('.ls-custom-select.city').css('border', 'unset');
    });
    
    jQuery("input[name='pass']").on('input', function() {        
        var classe = jQuery(this).parent()[0].className;
        jQuery('.'+classe+' .alerta-invalido').remove();       
        jQuery('input[name="pass"]').css('border', 'unset');
    });
    
    jQuery("input[name='passconfirmation']").on('input', function() {        
        var classe = jQuery(this).parent()[0].className;
        jQuery('.'+classe+' .alerta-invalido').remove();       
        jQuery('input[name="passconfirmation"]').css('border', 'unset');
    });

    jQuery(".btn-step-3").click(function (){ // Ajax do passo 3 que define informações necessarias como nome de exibição e senha de acesso para sua respectiva conta

        var data3 = {
        			"tel" : telefone,
                    "name" : jQuery("input[name='name']").val(),
                    "gender" : jQuery("select[name='gender']").val(),
                    "country" : jQuery("select[name='country']").val(),
                    "city" : jQuery("select[name='city']").val(),
                    "bairro": jQuery("input[name='bairro']").val(),
                    "pass" : jQuery("input[name='pass']").val(),
                    "passconfirmation" : jQuery("input[name='passconfirmation']").val()
                };  
                
        jQuery('.alerta-invalido').each(function() {
            jQuery(this).remove();
        });
                
        if ( data3['name'] == '' ) {
            jQuery('input[name="name"]').css('border', '1px solid red');
            jQuery('.box-input-name').append('<div class="alerta-invalido">'+unescape('Nome de Exibi%E7%E3o Est%E1 Vazio')+'</div>');
        }  
        
        if ( data3['country'] == '' ) {
            jQuery('.box-input-estado .ls-custom-select').css('border', '1px solid red');
            jQuery('.box-input-estado').append('<div class="alerta-invalido">'+unescape('Estado Est%E1 Vazio')+'</div>');
        }
        
        if ( data3['city'] == 'padrao' ) {
            jQuery('.ls-custom-select.city').css('border', '1px solid red');
            jQuery('.box-input-city').append('<div class="alerta-invalido">'+unescape('Cidade Est%E1 Vazia')+'</div>');
        } 

        if ( data3['bairro'] == '' ) {
            jQuery('input[name="bairro"]').css('border', '1px solid red');
            jQuery('.box-input-bairro').append('<div class="alerta-invalido">'+unescape('Bairro Est%E1 Vazio')+'</div>');
        } 
        
        if ( data3['pass'] == '' ) {
            jQuery('input[name="pass"]').css('border', '1px solid red');
            jQuery('.box-input-pass').append('<div class="alerta-invalido">'+unescape('Senha Est%E1 Vazia')+'</div>');
        } 
        
        if ( data3['passconfirmation'] == '' ) {
            jQuery('input[name="passconfirmation"]').css('border', '1px solid red');
            jQuery('.box-input-passconfirmation').append('<div class="alerta-invalido">'+unescape('Senha de Confirma%E7%E3o Est%E1 Vazia')+'</div>');
        } 
        
        if ( data3['pass'] != '' && data3['passconfirmation'] != '' && data3['pass'] !== data3['passconfirmation'] ) {
            jQuery('input[name="pass"]').css('border', '1px solid red');
		    jQuery('input[name="passconfirmation"]').css('border', '1px solid red');
            jQuery('.box-input-passconfirmation').append('<div class="alerta-invalido">'+unescape('Senhas N%E3o Conferem!')+'</div>');
        }
        
        if ( data3['tel'] != '' && data3['name'] != '' && data3['country'] != '' && data3['city'] != 'padrao' && data3['bairro'] != '' && data3['pass'] != '' && data3['passconfirmation'] != '' && data3['pass'] === data3['passconfirmation']  ) {
            
            //coloca um fundo branco para a pessoa n�o poder mexer enquanto o ajax carregar
            var $form = jQuery('form[name="registro_acompanhante"]');
            $form.addClass( 'processing2' );
            $form.block({
        		message: null,
				overlayCSS: {
					background: '#fff',
					opacity: 0.6
				}
			});
            
            jQuery.ajax({
                type: "POST",
                url: "../wp-content/plugins/ht-subscriptions/front/ajax/ht_step3_necessaryinformations.php",
                data: data3,
                success: function(data3)
                {
                    if ( data3 == 1 ) {
                        window.location.replace(jQuery('input[name="home_url"]').val()+"/login-acompanhante");
                    } else if ( data3 == 2 ) {
                        jQuery('.box-input-passconfirmation').append('<div class="alerta-invalido">'+unescape('C%F3digo de Verifica%E7%E3o N%E3o Encontrado!')+'</div>');	
                        $form.removeClass( 'processing2' ).unblock();		
    				}
                },
                error: function(erro) {
                    $form.removeClass( 'processing2' ).unblock();
                    alert('Houve um erro ao tentar cadastrar, tente novamente mais tarde.');
                }
            });
        }
    });
    
    jQuery('.estados').change(function() { // Aqui é o filtro de cidades a partir do estado selecionado

      //alert( this.value );
	  var data = {
	  				"ES" : jQuery("select[name='country']").val()
                };
                //console.log( data );
    	jQuery.ajax({
            type: "POST",
            url: "../wp-content/plugins/ht-subscriptions/front/ajax/ht_filter_city.php",
            data: data,
            success: function(data)
            {
                jQuery('.cidades').empty(); // Apaga os dados anteriores se fazer uma nova busca por estado
                jQuery(".cidades option[value='padrao']").empty(); // Remove o aviso para selecionar o estado antes da cidade caso selecione o estado
                jQuery('.cidades').append( data ); // Apos fazer o filtro das cidades pelo seu refrente estado nesta linha tais cidades são colocadas no html caso haja alguma mudança
            }
        });

    });
    
});

</script>

<div class="step-1">
	<form method="post" name="registro_celular">
        <h2 class="titulo-registro-login"><?php echo utf8_encode('Fa�a Seu Cadastro'); ?></h2>        
		<div class="box-input-registro">
			<label for="nome">Celular DDD</label><br>
			<input type="tel" name="telefone" id="tel" value="<?php ?>" placeholder="<?php echo utf8_encode('n� do celular'); ?>">
		</div>

		<div class="box-submit-registro">
			<div class="button button-primary btn-step-1" id="wp-submit">Enviar</div>
            <a class="link-redefinir-senha" href="<?php echo home_url('/login-acompanhante'); ?>"><?php echo utf8_encode('J� tenho um cadastro'); ?></a>

		</div>
	</form>
</div>


<div class="step-2" style="display: none;">
	<form method="post" name="confirma_celular">
        <h2 class="titulo-registro-login"><?php echo utf8_encode('Verifique o c�digo'); ?></h2>  
		<div class="box-input-registro-2">
			<label for="nome">Insira o código que foi enviado para o seu celular</label><br>
			<input type="text" name="key" id="key" placeholder="Código">
		</div>

		<div>
			<div class="button button-primary btn-step-2" id="wp-submit">Verificar</div>
		</div>
	</form>
</div>


<div class="step-3" style="display: none;">
	<form method="post" action="<?php echo home_url('/login-acompanhante'); ?>" name="registro_acompanhante">
        <h2 class="titulo-registro-login"><?php echo utf8_encode('Cadastre suas informa��es'); ?></h2>  
        
        <div class="box-input-name">
    		<label for="name">Nome de exibição</label><br>
    		<input type="text" name="name" id="name" placeholder="Nome de exibição">
        </div>
        
        <div style="height: 15px"></div>

		<label for="gender">Eu sou</label>
        <div class="ls-custom-select">
			<select name="gender">
				<option value="homem">Homem</option>
				<option value="mulher">Mulher</option>
                <option value="transex">Transex</option>
			</select>
        </div>
        
        <div style="height: 15px"></div>

        <div class="box-input-estado">
            <label for="country">Estado</label>
        	<div class="ls-custom-select">
    			<select class="estados" name="country">
                    <option value="">Selecione seu estado</option>
    				<?php
    					foreach ($estados as $estado) {
                            ?>
    						<option value="<?php echo $estado['cod_estados']; ?>"><?php echo $estado['sigla']; ?></option>
                            <?php
    					}
    				?>
    			</select>
    		</div>
        </div>
        
        <div style="height: 15px"></div>

        <div class="box-input-city">
            <label for="city">Cidade</label>
        	<div class="ls-custom-select city">
    			<select class="cidades" name="city">
                    <option value="padrao">Selecione seu estado primeiro</option> <!-- Os options são adicionados via ajax no arquivo ht_profile.php este arquivo é o require_once -->			
    			</select>
    		</div>
        </div>
        
        <div style="height: 15px"></div>

        <div class="box-input-bairro">
            <label for="bairro">Bairro</label><br>
            <input type="text" name="bairro" id="bairro" placeholder="Seu Bairro">
        </div>

        <div style="height: 15px"></div>
        
        <div class="box-input-pass">
    		<label for="pass">Senha</label><br>
    		<input type="password" name="pass" id="pass" placeholder="Sua nova senha">
        </div>
        
        <div style="height: 15px"></div>
        
        <div class="box-input-passconfirmation">
    		<label for="passconfirmation">Confirmar Senha</label><br>
    		<input type="password" name="passconfirmation" id="pass2" placeholder="Sua senha novamente">
        </div>

		<div>
			<div class="button button-primary btn-step-3" id="wp-submit">Enviar</div>
		</div>
	</form>
    <input type="hidden" name="home_url" value="<?php echo home_url(); ?>">
</div>