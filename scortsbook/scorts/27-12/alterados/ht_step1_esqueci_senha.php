<?php

$path = dirname(dirname(dirname(dirname(dirname(__DIR__))))) ;
    // /home/scortsbookcom/public_html/

include_once $path . '/wp-config.php';
include_once $path . '/wp-load.php';

include_once $path . '/wp-includes/wp-db.php';
include_once $path . '/wp-includes/pluggable.php';

global $wpdb;

$tel = $_REQUEST["tel"];

$conta_cadastrada = $wpdb->get_results("
	SELECT meta_value FROM wp_usermeta
	INNER JOIN wp_users ON (wp_users.ID = wp_usermeta.user_id)
	WHERE meta_key = 'ht_conta_cadastrada' AND user_login = '".$tel."'
", ARRAY_A);

if ( username_exists($tel) && $conta_cadastrada[0]['meta_value'] == 'true' ) {

	$ht_usermeta = $wpdb->prefix . 'usermeta'; // Prefixo da tabela para não haver erro no plugin em diferentes intalações de Wordpress

	$user = get_user_by('login', $tel);

	if ( $user ) { 
		$key = strtoupper(wp_generate_password( 5, false));

		update_user_meta( $user->data->ID, 'ht_cod_editar_senha', $key);
		send_sms('55'.$tel, 'Segue o codigo de verificacao em nosso site: '.$key);
	}

    $res[0] = 1;
	print json_encode($res);
} else if ( username_exists($tel) && $conta_cadastrada[0]['meta_value'] == 'false' ) {
	$res[0] = 2;
	print json_encode($res);
} else if ( ! username_exists($tel) ) {
    $res[0] = 3;
	print json_encode($res);
}else
{
	$res[0] = 4;
	print json_encode($res);
}




/*if ( !username_exists( $tel ) ) {

	$ht_usermeta = $wpdb->prefix . 'usermeta'; // Prefixo da tabela para não haver erro no plugin em diferentes intalações de Wordpress

	$user = get_user_by('login', $tel);

	if ( $user ) { // Se usuario for criado sera criado tambem seu código para validar o celular
		$key = wp_generate_password( 5, true);

		update_user_meta( $user->data->ID, 'ht_cod', $key);
	}

	echo 1;
} else if ( username_exists( $tel ) ) {
	echo 2;
}*/