<?php

$path = dirname(dirname(dirname(dirname(dirname(__DIR__)))));

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';
include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

require_once( $path . '/wp-admin/includes/image.php' );
require_once( $path . '/wp-admin/includes/file.php' );
require_once( $path . '/wp-admin/includes/media.php' );


global $wpdb;
global $post;


$estado = $_POST["estado"];
$cidade = $_POST["cidade"];
$endereco = $_POST["endereco"];
$bairro = $_POST["bairro"];
$numero = $_POST["numero"];
$nickname = $_POST["nickname"];
$descricao = $_POST["descricao"];
$genero = $_POST["genero"];
$idade = $_POST["idade"];
$etnia = $_POST["etnia"];
$url_gpguia = $_POST['url_gpguia'];
$mult['atende'] = $_POST["atende"]; /* */
$mult['locais'] = $_POST["locais"]; /* */
$mult['servicos'] = $_POST["servicos"]; /* */
$mult['idiomas'] = $_POST["idiomas"]; /* */
$mult['pagamentos'] = $_POST["pagamentos"]; /* */
	$mult['deb_cartoes'] = $_POST["deb_cartoes"]; /* */
	$mult['cre_cartoes'] = $_POST["cre_cartoes"]; /* */
$mult['cache1'] = $_POST["cache1"];/* */
$mult['cache30'] = $_POST["cache30"]; /* */
$mult['cache2'] = $_POST["cache2"]; /* */
$mult['cache4'] = $_POST["cache4"]; /* */
$mult['cacheNoite'] = $_POST["cacheNoite"]; /* */
$mult['atendimento'] = $_POST["atendimento"]; /* */
	$mult['semana'] = $_POST["semana"]; /* */
		$mult['HorarioInicial_Seg'] = $_POST["HorarioInicial_Seg"]; //
		$mult['HorarioFinal_Seg'] = $_POST["HorarioFinal_Seg"]; //
		$mult['HorarioInicial_Ter'] = $_POST["HorarioInicial_Ter"]; //
		$mult['HorarioFinal_Ter'] = $_POST["HorarioFinal_Ter"]; //
		$mult['HorarioInicial_Qua'] = $_POST["HorarioInicial_Qua"]; //
		$mult['HorarioFinal_Qua'] = $_POST["HorarioFinal_Qua"]; //
		$mult['HorarioInicial_Qui'] = $_POST["HorarioInicial_Qui"]; //
		$mult['HorarioFinal_Qui'] = $_POST["HorarioFinal_Qui"]; //
		$mult['HorarioInicial_Sex'] = $_POST["HorarioInicial_Sex"]; //
		$mult['HorarioFinal_Sex'] = $_POST["HorarioFinal_Sex"]; //
		$mult['HorarioInicial_Sab'] = $_POST["HorarioInicial_Sab"]; //
		$mult['HorarioFinal_Sab'] = $_POST["HorarioFinal_Sab"]; //
		$mult['HorarioInicial_Dom'] = $_POST["HorarioInicial_Dom"]; //
		$mult['HorarioFinal_Dom'] = $_POST["HorarioFinal_Dom"]; //
$email = $_POST["email"];

$user_id = get_current_user_id();

if ( $user_id != 0 ) {
    
    $user_email = $wpdb->get_var("SELECT user_email FROM wp_users WHERE ID = $user_id");

    if ( ! is_email( $email ) && ! empty($email) ) {
        echo 1;
    } else if ( ! email_exists( $email ) || $email == $user_email ) {
    	$ht_anunciante = $wpdb->prefix . 'ht_anunciante'; // Prefixo da tabela para nÃ£o haver erro no plugin em diferentes intalaÃ§Ãµes de Wordpress
    	$ht_anunciantemeta = $wpdb->prefix . 'ht_anunciantemeta';
    	$table_users = $wpdb->prefix . 'users';
    	$table_usermeta = $wpdb->prefix . 'usermeta';
    	$ht_gallery = $wpdb->prefix . 'ht_gallery';
    
    	$data = array(
    		'estado' => $estado,
    		'cidade' => $cidade,
            'endereco' => $endereco,
    		'bairro' => $bairro,
            'numero' => $numero,
    		'descricao' => $descricao,
            'sexo' => $genero,
    		'idade' => $idade,
    		'etnia' => $etnia,
            'url_gpguia' => $url_gpguia
    	);
    
    	$where = array(
    		'user_id' => $user_id
    	);
    
    	$updated = $wpdb->update( $ht_anunciante, $data, $where ); // dados da tabela ht_anunciante
    
    	$wpdb->update( $table_users, array('display_name'=>$nickname, 'user_email'=>$email), array('ID' => $user_id) ); // dados da tabela users
    	$wpdb->update( $table_usermeta, array('meta_value'=>$nickname), array('user_id' => $user_id, 'meta_key' => 'nickname') ); // dados da tabela usermeta
    
    
    	if ( false ) {
    		# code...
    	} else {
    
    		//$selecao = $wpdb->get_results(" SELECT * FROM $ht_anunciantemeta WHERE ID_anunciante = $user_id", ARRAY_A);
    
    		$wpdb->query("DELETE FROM $ht_anunciantemeta WHERE ID_anunciante = $user_id AND meta_key != 'ht_foto'"); // por hora nao atualiza a foto
    
    		foreach ($mult as $chave => $aux) {
    			if ( is_array($aux) ) {
    				foreach ($aux as $aux2) {
    					$aux3 .= "( '$user_id', '$chave', '$aux2' ), ";
    				}
    			} else {
    				$aux3 .= "( '$user_id', '$chave', '$aux' ), ";
    			}
    		}
    
    
    		$aux3 = substr($aux3,0,-2); // remove a virgula final
    
    		$test = $wpdb->query("INSERT INTO
    							$ht_anunciantemeta(ID_anunciante, meta_key, meta_value)
    
    						VALUES
    							$aux3
    						");
            
            //adiciona o endereço nos campos do woocommerce
            update_user_meta( $user_id, 'billing_country', 'BR' );
            update_user_meta( $user_id, 'billing_city', ucwords(mb_strtolower($cidade, 'UTF-8')) );
            update_user_meta( $user_id, 'billing_state', $estado );
            
            //adiciona uma página de perfil para o acompanhante se ele não tiver
            
            $sql_post = $wpdb->get_results("SELECT id, post_title, post_name FROM wp_posts WHERE post_type = 'perfil' AND post_status = 'publish'", ARRAY_A);
            
            $verifica_post = 0;
            
            foreach ( $sql_post as $post ) {
                $post_id = explode('-', $post['post_name']);
                $post_user_id = $post_id[0];
                if ( $post_user_id == $user_id ) {
                    $verifica_post = 1;
                    $verifica_post_id = $post['id'];
                    $old_page_title = $post['post_title'];
                }
            }
    
            if ( $verifica_post == 0 ) {
                $new_page_title = $user_id.' '.$nickname;
                $new_page_content = '[HT_painel_acompanhante]';
                $page_check = get_page_by_title($new_page_title);
            	$new_page = array(
            		'post_type' => 'perfil',
            		'post_title' => $new_page_title,
            		'post_content' => $new_page_content,
            		'post_status' => 'publish',
            		'post_author' => 1
            	);
                
            	if(!isset($page_check->ID)){
            		$new_page_id = wp_insert_post($new_page);
            	}
                
                update_post_meta( $new_page_id, '_et_pb_page_layout', 'et_full_width_page' );
            }
            
            $new_page_title = $user_id.' '.$nickname;
            if ( $verifica_post == 1 && $old_page_title !== $new_page_title ) {
                $post_name = sanitize_title($user_id.' '.$nickname);
                wp_update_post( array( 'ID' => $verifica_post_id, 'post_title' => $user_id.' '.$nickname, 'post_name' => $post_name ) );
            }            
    	}

        $get_post_id = $wpdb->get_results("SELECT id, post_title, post_name FROM wp_posts WHERE post_type = 'perfil' AND post_status = 'publish'", ARRAY_A);
            foreach ( $get_post_id as $post ) {
                $post_id = explode('-', $post['post_name']);
                $post_user_id = $post_id[0];
                if ( $post_user_id == $user_id ) {
                    if($idade != '' && !empty($idade))
                    {
                        $seo_description = $nickname.' '.$idade.' anos - Acompanhante em '.$cidade.'-'.$estado.' - Scortsbook';
                    }else
                    {
                        $seo_description = $nickname.' - Acompanhante em '.$cidade.'-'.$estado.' - Scortsbook';
                    }
                    if($descricao == "" || empty($descricao))
                    {
                        $descricao = $seo_description;
                    }
                    update_post_meta($post['id'], '_yoast_wpseo_metadesc', $descricao);
                    update_post_meta($post['id'], '_yoast_wpseo_title', $seo_description);
                }
            }
       

        echo 2;
    }  else if ( email_exists( $email ) ) {
    	echo 3;
    }
}







