<?php
setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
date_default_timezone_set('America/Sao_Paulo');

function theme_enqueue_styles() {

    $parent_style = 'parent-style';

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css' );
    //wp_enqueue_style('bootstrap-css', get_stylesheet_directory_uri() .'/css/bootstrap.min.css');  

    wp_enqueue_script('scripts-wp', get_stylesheet_directory_uri() .'/js/scripts.js', 'jquery');  
    wp_enqueue_script('infinite-scroller', get_stylesheet_directory_uri() .'/js/infinite_scroller.js', 'jquery'); 
    wp_enqueue_script('bootstrap-js', get_stylesheet_directory_uri() .'/js/bootstrap.bundle.min.js', 'jquery');     
    //wp_enqueue_script('jquery-ui', get_stylesheet_directory_uri() .'/js/jquery-ui.min.js');
    wp_enqueue_script('slider-js', get_stylesheet_directory_uri() .'/js/jquery.slides.js');
     
    if ( is_singular('perfil') || is_page('linha-do-tempo') || is_search() ) {
        wp_enqueue_script('w3-js', get_stylesheet_directory_uri() .'/js/w3.js');
    }        
    
    wp_enqueue_script('masked-input', get_stylesheet_directory_uri() .'/js/jquery.maskedinput.min.js');

    wp_enqueue_style('fontawesome-all', get_stylesheet_directory_uri() .'/web-fonts-with-css/css/fontawesome-all.min.css');

}
add_action( 'wp_enqueue_scripts', 'theme_enqueue_styles' );

function admin_enqueue_styles() {
    wp_enqueue_style( 'admin-styles', get_stylesheet_directory_uri() . '/css/admin-styles.css' );
    wp_enqueue_script( 'admin-scripts', get_stylesheet_directory_uri() . '/js/scripts-admin.js' );
}
add_action( 'admin_enqueue_scripts', 'admin_enqueue_styles' );

add_action('init', 'add_anunciantes_page_layout');
function add_anunciantes_page_layout() {

    register_post_type(
        'perfil',
        array(
            'labels' => array(
                'name'               => 'Anunciantes',
                'singular_name'      => 'Anunciantes',
                'add_new'            => 'Adicionar Novo Anunciante',
                'add_new_item'       => 'Adicionar Novo Anunciante',
                'edit_item'          => 'Editar Anunciante',
                'new_item'           => 'Novo Anunciante',
                'all_items'          => 'Todos os Anunciantes',
                'view_item'          => 'Visualizar Anunciante',
                'search_items'       => 'Procurar Anunciantes',
                'not_found'          => 'Nenhum Anunciante encontrado',
                'not_found_in_trash' => 'Nenhum Anunciante encontrado na lixeira',
            ),
            'public' => true,
            'has_archive' => false,
            'menu_icon' => 'dashicons-admin-users',
            'supports' => array('title', 'editor')
        )
    );
}

add_action('init', 'add_verificar_perfil_page_layout');
function add_verificar_perfil_page_layout() {

    register_post_type(
        'ht_verifica_perfil',
        array(
            'labels' => array(
                'name'               => 'Verificar Perfil',
                'singular_name'      => 'Verificar Perfil',
                'edit_item'          => 'Editar Perfil',
                'new_item'           => 'Novo Perfil',
                'all_items'          => utf8_encode('Perfis para AprovaÁ„o'),
                'view_item'          => 'Visualizar Perfil',
                'search_items'       => 'Procurar Perfis',
                'not_found'          => 'Nenhum Perfil encontrado',
                'not_found_in_trash' => 'Nenhum Perfil encontrado na lixeira',
            ),
            'public' => false,
            'has_archive' => true,
            'menu_icon' => 'dashicons-admin-users',
            'supports' => array('title'),
            'publicly_queryable' => true,
            'show_ui' => true,
            'exclude_from_search' => true,
            'show_in_nav_menus' => false,
            'rewrite' => false
        )
    );
}

add_filter( 'post_row_actions', 'ht_verifica_perfil_remove_row_actions', 10, 1 );
function ht_verifica_perfil_remove_row_actions( $actions )
{
    if( get_post_type() === 'ht_verifica_perfil' || get_post_type() === 'perfil' )
    {
        global $post;
        //unset( $actions['edit'] );
        unset( $actions['view'] );
        unset( $actions['trash'] );
        unset( $actions['inline hide-if-no-js'] );
        $actions['edit'] = '<a href="'.get_bloginfo('url').'/wp-admin/post.php?post='.$post->ID.'&action=edit">Ver</a> '; 
    }
    return $actions;
}

add_action( 'add_meta_boxes', 'ht_meta_boxes' );
function ht_meta_boxes() {
    add_meta_box(
        'ht_verificar_perfil_meta_box',
        utf8_encode('Fotos para comparaÁ„o'),
        'ht_verificar_perfil_meta_box_content',
        'ht_verifica_perfil',
        'normal',
        'high'
    );

    add_meta_box(
        'ht_verificar_perfil_meta_box_aprovacao',
        utf8_encode('AprovaÁ„o do Perfil'),
        'ht_verificar_perfil_meta_box_aprovacao',
        'ht_verifica_perfil',
        'side',
        'high'
    );
    
    add_meta_box(
        'ht_verificar_perfil_celular',
        'Celular',
        'ht_verificar_perfil_meta_box_celular',
        'ht_verifica_perfil',
        'side',
        'high'
    );

    add_meta_box(
        'ht_ensaio_site',
        'Ensaio do Site',
        'ht_perfil_meta_box_ensaio_site',
        'perfil',
        'side',
        'high'
    );
}

function ht_verificar_perfil_meta_box_content() {
    global $wpdb,$post;

    $link_foto = $wpdb->get_results("
                    SELECT meta_value AS link 
                    FROM wp_postmeta
                    INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_id)
                    WHERE post_type = 'attachment' AND post_name NOT LIKE '%-2' AND meta_key = '_wp_attached_file' AND post_parent = ".$post->ID.";
        ", ARRAY_A);

    echo '<div class="box_foto_comparacao">';
    foreach ( $link_foto as $key ) {
        echo '<img src="'.home_url('wp-content/uploads/').$key['link'].'">';
    }
    echo '</div>';
}

function ht_verificar_perfil_meta_box_aprovacao() {
    global $wpdb,$post;

    ?>
    <div class="box_aprovar_foto_comparacao">
        <div class="button button-primary button-large btn_aprovar_foto_comparacao">Aprovar</div>
        <div class="button button-primary button-large btn_rejeitar_foto_comparacao">Rejeitar</div>
    </div>
    <input type="hidden" name="ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>">
    <?php
}

function ht_verificar_perfil_meta_box_celular() {
    global $wpdb,$post;

    $celular = $wpdb->get_var("
                    SELECT user_login FROM wp_users 
                    INNER JOIN wp_posts ON (wp_posts.post_author = wp_users.ID)
                    WHERE post_type = 'ht_verifica_perfil' AND wp_posts.ID = ".$post->ID."
                ");

    $iphone  = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
    $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
    $palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
    $berry   = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
    $ipod    = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");

    // check if is a mobile
    if ($iphone || $android || $palmpre || $ipod || $berry == true)
    {
        $url_whatsapp = 'https://api.whatsapp.com/';
    }

    // all others
    else {
        $url_whatsapp = 'https://web.whatsapp.com/';
    }

    ?>
    <a class="img_whatsapp admin_verifica_perfil" href="<?php echo $url_whatsapp; ?>send?phone=<?php echo $celular; ?>" target="_blank">
        <img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/whatsapp.png"> <?php echo $celular; ?>
    </a>
    <?php
}

function ht_perfil_meta_box_ensaio_site() {
    global $post;

    $ensaio_site = get_post_meta( $post->ID, 'ensaio_site', true);

    ?>
    <input type="hidden" name="ajax_url" value="<?php echo admin_url('admin-ajax.php'); ?>">

    <div class="box_ensaio_site">
        <input type="radio" id="sim_ensaio_site" name="verifica_ensaio_site" value="1" <?php if ( $ensaio_site == 1 ) echo 'checked="checked"' ?>><label for="sim_ensaio_site">Sim</label> <br>
        <input type="radio" id="nao_ensaio_site" name="verifica_ensaio_site" value="0" <?php if ( $ensaio_site == 0 || empty($ensaio_site) ) echo 'checked="checked"' ?>><label for="nao_ensaio_site"><?php echo utf8_encode('N„o'); ?></label>
    </div>

    <div class="button button-primary button-large atualiza_ensaio_site">Atualizar</div>
    

    <?php
}

/*add_shortcode('mapa', 'iframe_maps_api');
function iframe_maps_api($atts) {
    $pais = $estado = $cidade = $bairro = $numero = '';

    $pais   = str_replace( "-", "+", sanitize_title($atts['pais']) );
    $estado = str_replace( "-", "+", sanitize_title($atts['estado']) );
    $cidade = str_replace( "-", "+", sanitize_title($atts['cidade']) );
    $bairro = str_replace( "-", "+", sanitize_title($atts['bairro']) );
    $numero = str_replace( "-", "+", sanitize_title($atts['numero']) );


    return '  <iframe id="maps_api"                 
               src="https://www.google.com/maps/embed/v1/place?key='.$atts['key'].'&q='.$pais.','.$estado.','.$cidade.','.$bairro.','.$numero.'" 
                allowfullscreen>
           </iframe>'; 

    }*/

       

add_action('woocommerce_checkout_update_order_meta', 'woocommerce_add_my_order_meta');
function woocommerce_add_my_order_meta( $order_id, $post_values ) {
    global $wpdb;
    
    $id_presenteado = $wpdb->get_var("SELECT meta_value AS id_presenteado FROM wp_usermeta WHERE user_id = ".get_current_user_id()." AND meta_key = 'id_presenteado'");

    update_post_meta( $order_id, 'id_presenteado', $id_presenteado );
}

add_action( 'woocommerce_order_status_completed', 'mysite_woocommerce_order_status_completed', 10, 1 );
function mysite_woocommerce_order_status_completed( $order_id ) {
    global $wpdb;
    
    $order = wc_get_order( $order_id );
    $user = $order->get_user();
    $id_presenteado = get_post_meta( $order_id, 'id_presenteado', true );
    
    //isso sÛ faz quando tem alguÈm dando presente
    if ( ! empty( $id_presenteado ) ) {
        update_post_meta( $order_id, 'id_presenteador', $user->data->ID );
        update_post_meta( $order_id, '_customer_user', $id_presenteado );
        
        $order_subscription_id = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_type = 'shop_subscription' AND post_parent = ".$order_id."");
        
        /*$wpdb->insert(
            'wp_postmeta',
            array(
                'post_id' => $order_subscription_id,
                'meta_key' => 'id_presenteador',
                'meta_value' => $user->data->ID
            )
        );
        
        $wpdb->update(
            'wp_postmeta',
            array(
                'meta_value' => $id_presenteado
            ),
            array(
                'meta_key' => '_customer_user',
                'post_id' => $order_subscription_id
            )
        );*/
        update_post_meta( $order_subscription_id, 'id_presenteador', $user->data->ID );
        update_post_meta( $order_subscription_id, '_customer_user', $id_presenteado );
    }
}

/*add_action( 'woocommerce_payment_complete', 'mysite_woocommerce_payment_completed', 99999, 1 );
function mysite_woocommerce_payment_completed( $order_id ) {
    global $wpdb;

    $id_presenteado = get_post_meta( $order_id, 'id_presenteado', true );
    
    //isso sÛ faz quando tem alguÈm dando presente
    if ( ! empty( $id_presenteado ) ) {
        
        $order_subscription_id = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_type = 'shop_subscription' AND post_parent = ".$order_id."");
        
        //pausa o plano presenteado
        $order_item_id = $wpdb->get_var("
                            SELECT order_item_id 
                            FROM wp_woocommerce_order_items 
                            WHERE order_id = ".$order_subscription_id."
                            ");
        
        $date_now = gmdate('Y-m-d H:i:s', time());
        
        $wpdb->insert(
            'wp_woocommerce_order_itemmeta',
            array(
                'order_item_id' => $order_item_id,
                'meta_key' => 'pause_assinatura',
                'meta_value' => $date_now
            )
        );
        
        $subscription = new WC_Subscription($order_subscription_id);        
        $subscription->update_status('on-hold');
    }
}*/

add_action( 'woocommerce_subscription_payment_complete', 'woocommerce_subscription_payment_completed' );
function woocommerce_subscription_payment_completed( $subscription ) {
    global $wpdb;
    
    $order_id = $subscription->get_parent_id();
    
    $id_presenteado = get_post_meta( $order_id, 'id_presenteado', true );
    
    //isso sÛ faz quando tem alguÈm dando presente
    if ( ! empty( $id_presenteado ) ) {
        
        $order_subscription_id = $wpdb->get_var("SELECT ID FROM wp_posts WHERE post_type = 'shop_subscription' AND post_parent = ".$order_id."");
        
        //pausa o plano presenteado
        $order_item_id = $wpdb->get_var("
                            SELECT order_item_id 
                            FROM wp_woocommerce_order_items 
                            WHERE order_id = ".$order_subscription_id."
                            ");
        
        $date_now = gmdate('Y-m-d H:i:s', time());
        
        $wpdb->insert(
            'wp_woocommerce_order_itemmeta',
            array(
                'order_item_id' => $order_item_id,
                'meta_key' => 'pause_assinatura',
                'meta_value' => $date_now
            )
        );
              
        $subscription->update_status('on-hold');
    }
}

function wp_login_form_person( $args = array() ) {
    $defaults = array(
        'echo' => true,
        // Default 'redirect' value takes the user back to the request URI.
        'redirect' => ( is_ssl() ? 'https://' : 'http://' ) . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'],
        'form_id' => 'loginform',
        'label_username' => __( 'Username or Email Address' ),
        'label_password' => __( 'Password' ),
        'label_remember' => __( 'Remember Me' ),
        'label_log_in' => __( 'Log In' ),
        'id_username' => 'user_login',
        'id_password' => 'user_pass',
        'id_remember' => 'rememberme',
        'id_submit' => 'wp-submit',
        'remember' => true,
        'value_username' => '',
        // Set 'value_remember' to true to default the "Remember me" checkbox to checked.
        'value_remember' => false,
    );
 
    /**
     * Filters the default login form output arguments.
     *
     * @since 3.0.0
     *
     * @see wp_login_form()
     *
     * @param array $defaults An array of default login form arguments.
     */
    $args = wp_parse_args( $args, apply_filters( 'login_form_defaults', $defaults ) );
 
    /**
     * Filters content to display at the top of the login form.
     *
     * The filter evaluates just following the opening form tag element.
     *
     * @since 3.0.0
     *
     * @param string $content Content to display. Default empty.
     * @param array  $args    Array of login form arguments.
     */
    $login_form_top = apply_filters( 'login_form_top', '', $args );
 
    /**
     * Filters content to display in the middle of the login form.
     *
     * The filter evaluates just following the location where the 'login-password'
     * field is displayed.
     *
     * @since 3.0.0
     *
     * @param string $content Content to display. Default empty.
     * @param array  $args    Array of login form arguments.
     */
    $login_form_middle = apply_filters( 'login_form_middle', '', $args );
 
    /**
     * Filters content to display at the bottom of the login form.
     *
     * The filter evaluates just preceding the closing form tag element.
     *
     * @since 3.0.0
     *
     * @param string $content Content to display. Default empty.
     * @param array  $args    Array of login form arguments.
     */
    $login_form_bottom = apply_filters( 'login_form_bottom', '', $args );
 
    $form = '
        <form name="' . $args['form_id'] . '" id="' . $args['form_id'] . '" action="' . esc_url( site_url( 'wp-login.php', 'login_post' ) ) . '" method="post">
            ' . $login_form_top . '
            <p class="login-username">
                <label for="' . esc_attr( $args['id_username'] ) . '">' . esc_html( $args['label_username'] ) . '</label>
                <input type="tel" name="log" id="' . esc_attr( $args['id_username'] ) . '" class="input" value="' . esc_attr( $args['value_username'] ) . '" size="20" />
            </p>
            <p class="login-password">
                <label for="' . esc_attr( $args['id_password'] ) . '">' . esc_html( $args['label_password'] ) . '</label>
                <input type="password" name="pwd" id="' . esc_attr( $args['id_password'] ) . '" class="input" value="" size="20" />
            </p>
            ' . $login_form_middle . '
            ' . ( $args['remember'] ? '<p class="login-remember"><label><input name="rememberme" type="checkbox" id="' . esc_attr( $args['id_remember'] ) . '" value="forever"' . ( $args['value_remember'] ? ' checked="checked"' : '' ) . ' /> ' . esc_html( $args['label_remember'] ) . '</label></p>' : '' ) . '
            <p class="login-submit">
                <input type="submit" name="wp-submit" id="' . esc_attr( $args['id_submit'] ) . '" class="button button-primary" value="' . esc_attr( $args['label_log_in'] ) . '" />
                <input type="hidden" name="redirect_to" value="' . esc_url( $args['redirect'] ) . '" />
            </p>
            ' . $login_form_bottom . '
        </form>';
 
    if ( $args['echo'] )
        echo $form;
    else
        return $form;
}

/*add_filter( 'woocommerce_checkout_fields' , 'removendo_campos_checkout' ); 
function removendo_campos_checkout ( $fields ) { 
    //unset($fields['billing']['billing_first_name']); // Nome 
    //unset($fields['billing']['billing_last_name']); // Sobrenome 
    unset($fields['billing']['billing_company']); // Empresa 
    //unset($fields['billing']['billing_address_1']); // EndereÁo 
    unset($fields['billing']['billing_address_2']); // EndereÁo de entrega 
    //unset($fields['billing']['billing_city']); // Cidade 
    //unset($fields['billing']['billing_postcode']); // CEP 
    //unset($fields['billing']['billing_country']); // PaÌs 
    //unset($fields['billing']['billing_state']); // Estado 
    //unset($fields['billing']['billing_phone']); // Telefone 
    unset($fields['order']['order_comments']); // Coment·rios 
    //unset($fields['billing']['billing_email']); // E-mail 
    unset($fields['account']['account_username']); // Usu·rio 
    unset($fields['account']['account_password']); // Senha 
    unset($fields['account']['account_password-2']); // ConfirmaÁ„o de Senha
    return $fields;
}*/

/*add_filter( 'woocommerce_default_address_fields' , 'custom_override_default_address_fields' );
function custom_override_default_address_fields( $address_fields ) {
    $address_fields['country']['required'] = false;
    $address_fields['first_name']['required'] = false;
    $address_fields['last_name']['required'] = false;
    $address_fields['company']['required'] = false;
    $address_fields['address_1']['required'] = false;
    $address_fields['address_2']['required'] = false;
    $address_fields['city']['required'] = false;
    $address_fields['state']['required'] = false;
    $address_fields['postcode']['required'] = false;
    $address_fields['phone']['required'] = false;

    return $address_fields;
}*/

/*add_filter( 'woocommerce_billing_fields' , 'custom_billing_fields' );
function custom_billing_fields( $address_fields ) {
    $address_fields['billing_phone']['required'] = false;
    $address_fields['billing_email']['required'] = false;

    return $address_fields;
}*/

function nomeia_coluna_anunciantes($columns) {
    return array_merge( 
                    $columns, 
                    array( 
                        'cb'      => $columns['cb'],
                        'celular' => ( 'Celular' ) 
                    ) 
    );
}
add_filter('manage_perfil_posts_columns', 'nomeia_coluna_anunciantes');

function conteudo_coluna_anunciantes($column, $post_ID) {
    global $wpdb;

    if ( $column == 'celular' ) {

        $user_id = $wpdb->get_var("
                    SELECT SUBSTRING(post_title, 1, POSITION( ' ' IN post_title) - 1) AS ID 
                    FROM wp_posts WHERE ID = ".$post_ID."
                ");

        $user = get_user_by('ID', $user_id);

        echo $user->data->user_login;
    }
}
add_action('manage_perfil_posts_custom_column', 'conteudo_coluna_anunciantes', 10, 2);

function bloglite_breadcrumb() {
    global $post;
    echo '<ul id="trilha">';
    if (!is_page( array('linha-do-tempo', 'planos') )) {
        echo '<li><a class="breadcrumb-home" href="';
        echo get_option('home');
        echo '">';
        echo 'Home';
        echo '</a></li><li class="separador">></li>';
        if ( is_page() ) {
            if($post->post_parent){
                $anc = get_post_ancestors( $post->ID );
                $title = get_the_title();
                foreach ( $anc as $ancestor ) {
                    $output = '<li><a href="'.get_permalink($ancestor).'" title="'.get_the_title($ancestor).'">'.get_the_title($ancestor).'</a></li> <li class="separador">/</li>';
                }
                echo $output;
                echo '<strong title="'.$title.'"> '.$title.'</strong>';
            } else if ( ! is_page('finalizar-compra') ) {
                echo '<li><strong>'.get_the_title().'</strong></li>';
            } else {
                echo '<li><a class="breadcrumb-home" href="'.home_url('/planos').'">Planos</a></li><li class="separador">></li><li><strong>'.get_the_title().'</strong></li>';
            }
        }
        if ( is_singular('perfil') ) {
            $title = get_the_title();
            $title = explode(' ', $title);
            
            for ( $x = 1; $x < count($title); $x++ ) {
                if ( $x == 1 ) {
                    $titulo = $title[$x];
                } else {
                    $titulo .= ' '.$title[$x];
                }
            }
            
            echo '<li><strong>Perfil de '.$titulo.'</strong></li>'; 
        }
    }
    echo '</ul>';
}

//Shortcode para pesquisar por cidade na Home.
add_shortcode('search-cidade', 'pesquisa_por_cidade');
function pesquisa_por_cidade($atts) {
    return '
            <div class="box-search-cidade">
                <div class="titulo-search-cidade">
                    Encontre na sua cidade
                </div>

                <div class="box-search-input">
                    <input type="search" id="search-cidade" name="search-cidade" placeholder="Todas as cidades">
                    <div><img class="img-btn-localizacao" src="'.get_stylesheet_directory_uri().'/imagens/btn-localizacao.png"></div>
                </div>

                <div class="box-tipos-genero">
                    <a href="#" class="tipo-genero-homem"></a>
                    <a href="#" class="tipo-genero-mulher"></a>
                    <a href="#" class="tipo-genero-transex"></a>
                </div>

                <div class="advanced-search"> 
                    <p>Pesquisa Avan√ßada <span class="fa fa-chevron-down"></span></p>                   
                </div>
            </div>
    ';
}

function paginacao_rank($total_reg = 1, $cidade = "", $bairro = "", $estado = "", $genero = "") {
    setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    global $wpdb;
    
    $sql_cidade = "";
    $sql_bairro = "";
    $sql_estado = "";
    
    if ( ! empty($cidade) ) {
        $sql_cidade = " AND (wp_ht_anunciante.cidade like '%".$cidade."%' ";
        $sql_cidade .= " OR wp_ht_anunciante.bairro like '%".$cidade."%' ";
        $sql_cidade .= " OR wp_ht_anunciante.estado like '%".$cidade."%') ";
    }

    if ( ! empty($bairro) ) {
        $sql_bairro = " AND (wp_ht_anunciante.cidade like '%".$bairro."%' ";
        $sql_bairro .= " OR wp_ht_anunciante.bairro like '%".$bairro."%' ";
        $sql_bairro .= " OR wp_ht_anunciante.estado like '%".$bairro."%') ";
    }

    if ( ! empty($estado) ) {
        $sql_estado = " AND (wp_ht_anunciante.cidade like '%".$estado."%' ";
        $sql_estado .= " OR wp_ht_anunciante.bairro like '%".$estado."%' ";
        $sql_estado .= " OR wp_ht_anunciante.estado like '%".$estado."%') ";
    }
    
    $sql_genero = "";
    
    if ( ! empty($genero) ) {
        
        $sql_genero = " AND (";
        
        $x = 0;        
        foreach ( $genero as $gen ) {
            $sql_genero .= "wp_ht_anunciante.sexo = '".$gen."'";
                        
            $x++;
            
            if ( count($genero) > $x ) {
                $sql_genero .= " OR ";
            }
            
        }
        $sql_genero .= ")";

    }

    /****************************** SLIDE PLANO SUPER TOP ****************************/
    $busca_super_top = "
                SELECT t1.ID AS id_usuario, t1.nome, IF(t1.idade LIKE '60+' OR t1.idade = '',t1.idade,CONCAT(t1.idade,' anos')) AS idade, t1.sexo, t1.estado, IF(t1.cidade = 'padrao',(NULL),LOWER(t1.cidade)) AS cidade, t1.descricao, CONCAT('/wp-content/uploads/',t4.img_perfil) AS img_perfil, IF(t3.cache1,CONCAT('R$ ',t3.cache1,'/H'),t3.cache1) AS cache1, 
                       t2.name AS caracteristicaProduto, t2.order_item_name AS nomeProduto,
                       ( 
                       CASE
                       WHEN t2.name LIKE '%Posicao1%' THEN 1
                       WHEN t2.name LIKE '%Posicao2%' THEN 2
                       WHEN t2.name LIKE '%Posicao3%' THEN 3
                       WHEN t2.name LIKE '%Posicao4%' THEN 4
                       ELSE 5
                       END) AS Posicao,                           
                       IF( t2.name LIKE '%DestaqueCor%',1,0) AS DestaqueCor,
                       IF(t7.user_id,1,0) AS AtualizacaoDiaria,
                       t5.Curtidas,
                       IF(t1.verificado = 'v',1,0) AS VerificacaoPerfil,
                       IF(t6.EnsaioSite = 1, 1, 0) AS EnsaioSite,
                       IF( t2.name LIKE '%VerTelefone%',1,0) AS VerTelefone
                FROM (SELECT wp_users.ID, wp_users.display_name AS nome, wp_ht_anunciante.idade, wp_ht_anunciante.sexo, wp_ht_anunciante.estado,
                      wp_ht_anunciante.cidade, wp_ht_anunciante.descricao, wp_ht_anunciante.principal_foto_id, wp_ht_anunciante.verificado
                      FROM wp_ht_anunciante
                      INNER JOIN wp_users ON (wp_users.ID = wp_ht_anunciante.user_id)
                      WHERE ativo = 's' ".$sql_cidade.$sql_bairro.$sql_estado.$sql_genero."
                      ) t1
                INNER JOIN (SELECT wp_postmeta.meta_value AS id_usuario, GROUP_CONCAT(wp_terms.name SEPARATOR ';') AS NAME, GROUP_CONCAT(DISTINCT(order_item_name) SEPARATOR ';') AS order_item_name
                       FROM wp_postmeta
                           INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_id) 
                       INNER JOIN wp_woocommerce_order_items ON (wp_woocommerce_order_items.order_id = wp_postmeta.post_id)
                       INNER JOIN wp_woocommerce_order_itemmeta ON (wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id)
                       INNER JOIN wp_term_relationships ON (wp_term_relationships.object_id = wp_woocommerce_order_itemmeta.meta_value)
                       INNER JOIN wp_term_taxonomy ON (wp_term_taxonomy.term_id = wp_term_relationships.term_taxonomy_id)
                       INNER JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id)
                       WHERE post_type = 'shop_subscription' AND post_status = 'wc-active' AND wp_postmeta.meta_key = '_customer_user' AND wp_woocommerce_order_itemmeta.meta_key = '_product_id' 
                            AND wp_term_taxonomy.taxonomy = 'product_tag'
                       GROUP BY id_usuario) t2
                ON t1.ID = t2.id_usuario
                LEFT JOIN (SELECT ID_anunciante, meta_value AS cache1
                       FROM wp_ht_anunciantemeta
                       WHERE meta_key = 'cache1'
                       ) t3
                ON t1.ID = t3.ID_anunciante
                LEFT JOIN (SELECT post_id, meta_value AS img_perfil
                       FROM wp_postmeta
                       WHERE meta_key = '_wp_attached_file'
                       ) t4
                ON t4.post_id = t1.principal_foto_id
                LEFT JOIN (SELECT wp_ht_publications.user_id, COUNT(publication_id) AS Curtidas
                       FROM wp_ht_publications
                       LEFT JOIN wp_ht_likes ON (wp_ht_likes.publication_id = wp_ht_publications.ID)
                       WHERE DAY(SYSDATE()) = DAY(data) AND MONTH(SYSDATE()) = MONTH(data) AND YEAR(SYSDATE()) = YEAR(data)
                       GROUP BY wp_ht_publications.user_id
                      ) t5
                ON t5.user_id = t1.ID
                LEFT JOIN (SELECT post_id, meta_value AS EnsaioSite, SUBSTRING(post_title, 1, POSITION( ' ' IN post_title) - 1) AS user_id
                       FROM wp_postmeta
                       INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_ID)
                       WHERE meta_key = 'ensaio_site'
                      ) t6
                ON t6.user_id = t1.ID
                LEFT JOIN(SELECT user_id FROM wp_ht_publications WHERE DAY(SYSDATE()) = DAY(date_publish) AND MONTH(SYSDATE()) = MONTH(date_publish) AND YEAR(SYSDATE()) = YEAR(date_publish)
                       GROUP BY user_id   
                      ) t7
                ON t7.user_id = t1.ID
                WHERE t2.order_item_name LIKE '%super top%'
                ORDER BY Posicao, DestaqueCor DESC, AtualizacaoDiaria DESC, Curtidas DESC, VerificacaoPerfil DESC, EnsaioSite DESC, VerTelefone DESC
            ";
            
    $todos_super_top = $wpdb->get_results( $busca_super_top, ARRAY_A );
    
    if ( ! empty($todos_super_top) ) {
        echo '<div class="titulo_slides_acompanhantes">Super TOPS</div><div id="box_slides_acompanhantes"><div id="slides_acompanhantes">';

        foreach ( $todos_super_top as $value ) {
            $url_perfil = sanitize_title($value['id_usuario'].' '.$value['nome']);
            ?>
            <div class="box_slide_super_top">
                <div class="box_info_perfil_home slide_super_top <?php if ( $value['DestaqueCor'] == 1 ) echo 'destaque_cor'; ?>">
                    <div class="box_imagem_perfil">
                        <?php
                        if ( ! empty($value['img_perfil']) ) {
                            ?>
                            <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><img class="imagem_perfil" src="<?php echo get_home_url() . $value['img_perfil'] ?>"></a>
                            <?php
                        } else {
                            ?>
                            <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><img class="imagem_perfil" src="<?php echo get_home_url(); ?>/wp-content/uploads/2018/07/img-perfil-padrao.png"></a>
                            <?php
                        }
                        ?>
                    </div>
                    <div class="box_conteudo_perfil">
                        <table>
                            <tr>                        
                                <td class="info_perfil_nome" colspan="3">
                                    <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><?php echo $value['nome']; ?></a>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                    <div style="height: 15px;"></div>
                                </td>
                                <td class="info_perfil_cache1" colspan="2">
                                    <?php echo $value['cache1']; ?>
                                </td>
                            </tr>
                            <tr>
                                <td class="box_info_perfil_idade">
                                    <div class="info_perfil_idade">
                                    <?php if ( ! empty($value['sexo']) ) echo ucfirst($value['sexo']).'<br>'; ?>
                                    <?php if ( ! empty($value['idade']) )  {  
                                        if ( $value['idade'] === "60+" ) {
                                            echo 'Mais de 60 anos <br>';
                                        } else {
                                            echo $value['idade'].'<br>';
                                        }
                                    }
                                    ?>
                                    <span class="info_perfil_cidade"><?php echo $value['cidade'].'</span> - '.$value['estado']; ?>
                                    </div>
                                </td>
                                <td class="info_perfil_descricao">
                                    <?php 
                                    $qtd_letras = 200;
                                    $ultima_pos = substr($value['descricao'], 0, $qtd_letras);
                                    $ultima_pos_espaco = strrpos($ultima_pos, ' ');                               
    
                                    if ( strlen($value['descricao']) > $qtd_letras ) {
                                        echo substr($value['descricao'], 0, $ultima_pos_espaco).'...';
                                    } else {
                                        echo $value['descricao'];
                                    }
                                    ?>
                                    &nbsp;
                                </td>
                                <td class="info_perfil_plano">
                                    <?php
                                    if ( strpos($value['caracteristicaProduto'], 'Posicao2') !== false ) {
                                        ?>
                                        <div class="nome_posicao">TOP</div>
                                        <div class="fas fa-star"></div>
                                        <?php
                                    }
                                    ?>
                                </td>
                            </tr>
                            <tr>
                                <td>
                                
                                </td>
                                <td class="info_perfil_curtidas">
                                    <?php 
                                    if ( ! empty($value['Curtidas']) ) {
                                        ?>
                                        <div class="fas fa-heart"></div>
                                        <?php
                                        echo ' '.$value['Curtidas'].utf8_encode(' gostei em publicaÁıes hoje');
                                    }
                                    ?>
                                </td>
                                <td>
                                
                                </td>
                            </tr>
                            
                        </table>
                    </div>
                </div>   
                
                <?php
                if ( $value['VerificacaoPerfil'] == 1 ) {
                ?>
                    <a href="<?php echo home_url('/perfil/').$url_perfil; ?>">
                        <img class="icon-perfil-verificado" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icon-perfil-verificado.png">
                    </a>
                <?php
                }
                ?>
                
            </div>
            <?php
        }
        
        if ( count($todos_super_top) > 1 ) {
        ?>
            <div class="w3js-previous slide_super_top_previous"><i class="fas fa-chevron-circle-left"></i></div>
            <div class="w3js-next slide_super_top_next"><i class="fas fa-chevron-circle-right"></i></div>
        <?php
        }
        echo '</div></div>';
    }
    /****************************** FIM SLIDE PLANO SUPER TOP ****************************/

    $busca = "
                SELECT t1.ID AS id_usuario, t1.nome, IF(t1.idade LIKE '60+' OR t1.idade = '',t1.idade,CONCAT(t1.idade,' anos')) AS idade, t1.sexo, t1.estado, IF(t1.cidade = 'padrao',(NULL),LOWER(t1.cidade)) AS cidade, t1.descricao, CONCAT('/wp-content/uploads/',t4.img_perfil) AS img_perfil, IF(t3.cache1,CONCAT('R$ ',t3.cache1,'/H'),t3.cache1) AS cache1, t2.order_item_name, 
                       t2.name AS caracteristicaProduto, 
                       ( 
                       CASE
                       WHEN t2.name LIKE '%Posicao1%' THEN 1
                       WHEN t2.name LIKE '%Posicao2%' THEN 2
                       WHEN t2.name LIKE '%Posicao3%' THEN 3
                       WHEN t2.name LIKE '%Posicao4%' THEN 4
                       ELSE 5
                       END) AS Posicao,                           
                       IF( t2.name LIKE '%DestaqueCor%',1,0) AS DestaqueCor,
                       IF(t7.user_id,1,0) AS AtualizacaoDiaria,
                       t5.Curtidas,
                       IF(t1.verificado = 'v',1,0) AS VerificacaoPerfil,
                       IF(t6.EnsaioSite = 1, 1, 0) AS EnsaioSite,
                       IF( t2.name LIKE '%VerTelefone%',1,0) AS VerTelefone
                FROM (SELECT wp_users.ID, wp_users.display_name AS nome, wp_ht_anunciante.idade, wp_ht_anunciante.sexo, wp_ht_anunciante.estado,
                      wp_ht_anunciante.cidade, wp_ht_anunciante.descricao, wp_ht_anunciante.principal_foto_id, wp_ht_anunciante.verificado
                      FROM wp_ht_anunciante
                      INNER JOIN wp_users ON (wp_users.ID = wp_ht_anunciante.user_id)
                      WHERE ativo = 's' ".$sql_cidade.$sql_bairro.$sql_estado.$sql_genero."
                      ) t1
                LEFT JOIN (SELECT wp_postmeta.meta_value AS id_usuario, GROUP_CONCAT(wp_terms.name SEPARATOR ';') AS NAME, GROUP_CONCAT(DISTINCT(order_item_name) SEPARATOR ';') AS order_item_name
                       FROM wp_postmeta
                           INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_id) 
                       INNER JOIN wp_woocommerce_order_items ON (wp_woocommerce_order_items.order_id = wp_postmeta.post_id)
                       INNER JOIN wp_woocommerce_order_itemmeta ON (wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id)
                       INNER JOIN wp_term_relationships ON (wp_term_relationships.object_id = wp_woocommerce_order_itemmeta.meta_value)
                       INNER JOIN wp_term_taxonomy ON (wp_term_taxonomy.term_id = wp_term_relationships.term_taxonomy_id)
                       INNER JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id)
                       WHERE post_type = 'shop_subscription' AND post_status = 'wc-active' AND wp_postmeta.meta_key = '_customer_user' 
                       AND wp_woocommerce_order_itemmeta.meta_key = '_product_id' AND wp_term_taxonomy.taxonomy = 'product_tag'
                       GROUP BY id_usuario) t2
                ON t1.ID = t2.id_usuario
                LEFT JOIN (SELECT ID_anunciante, meta_value AS cache1
                       FROM wp_ht_anunciantemeta
                       WHERE meta_key = 'cache1'
                       ) t3
                ON t1.ID = t3.ID_anunciante
                LEFT JOIN (SELECT post_id, meta_value AS img_perfil
                       FROM wp_postmeta
                       WHERE meta_key = '_wp_attached_file'
                       ) t4
                ON t4.post_id = t1.principal_foto_id
                LEFT JOIN (SELECT wp_ht_publications.user_id, COUNT(publication_id) AS Curtidas
                       FROM wp_ht_publications
                       LEFT JOIN wp_ht_likes ON (wp_ht_likes.publication_id = wp_ht_publications.ID)
                       WHERE DAY(SYSDATE()) = DAY(data) AND MONTH(SYSDATE()) = MONTH(data) AND YEAR(SYSDATE()) = YEAR(data)
                       GROUP BY wp_ht_publications.user_id
                      ) t5
                ON t5.user_id = t1.ID
                LEFT JOIN (SELECT post_id, meta_value AS EnsaioSite, SUBSTRING(post_title, 1, POSITION( ' ' IN post_title) - 1) AS user_id
                       FROM wp_postmeta
                       INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_ID)
                       WHERE meta_key = 'ensaio_site'
                      ) t6
                ON t6.user_id = t1.ID   
                LEFT JOIN(SELECT user_id FROM wp_ht_publications WHERE DAY(SYSDATE()) = DAY(date_publish) AND MONTH(SYSDATE()) = MONTH(date_publish) AND YEAR(SYSDATE()) = YEAR(date_publish)
                       GROUP BY user_id   
                      ) t7
                ON t7.user_id = t1.ID
                WHERE order_item_name IS NULL OR order_item_name NOT LIKE '%super top%'
                ORDER BY Posicao, DestaqueCor DESC, AtualizacaoDiaria DESC, Curtidas DESC, VerificacaoPerfil DESC, EnsaioSite DESC, VerTelefone DESC
                LIMIT 10 OFFSET 0
            ";
            
    $todos = $wpdb->get_results( $busca, ARRAY_A );
    
    ?>
    <div class="box-acompanhantes">
    <?php

    if ( empty($todos) ) {
        ?>
        <div class="acompanhantesVazio"></div>
        <?php
    }
    
    foreach ( $todos as $value ) {
        $url_perfil = sanitize_title($value['id_usuario'].' '.$value['nome']);
        ?>
        <div class="box_info_perfil_home <?php if ( $value['DestaqueCor'] == 1 ) echo 'destaque_cor'; ?>">
            <div class="box_imagem_perfil">
                <?php
                if ( ! empty($value['img_perfil']) ) {
                    ?>
                    <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><img class="imagem_perfil" src="<?php echo get_home_url() . $value['img_perfil'] ?>"></a>
                    <?php
                } else {
                    ?>
                    <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><img class="imagem_perfil" src="<?php echo get_home_url(); ?>/wp-content/uploads/2018/07/img-perfil-padrao.png"></a>
                    <?php
                }
                ?>
            </div>
            <div class="box_conteudo_perfil">
                <table>
                    <tr>                        
                        <td class="info_perfil_nome" colspan="3">
                            <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><?php echo $value['nome']; ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="height: 15px;"></div>
                        </td>
                        <td class="info_perfil_cache1" colspan="2">
                            <?php echo $value['cache1']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="box_info_perfil_idade">
                            <div class="info_perfil_idade">
                            <?php if ( ! empty($value['sexo']) ) echo ucfirst($value['sexo']).'<br>'; ?>
                            <?php if ( ! empty($value['idade']) )  {  
                                if ( $value['idade'] === "60+" ) {
                                    echo 'Mais de 60 anos <br>';
                                } else {
                                    echo $value['idade'].'<br>';
                                }
                            }
                            ?>
                            <span class="info_perfil_cidade"><?php echo $value['cidade'].'</span> - '.$value['estado']; ?>
                            </div>
                        </td>
                        <td class="info_perfil_descricao">
                            <?php 
                            $qtd_letras = 200;
                            $ultima_pos = substr($value['descricao'], 0, $qtd_letras);
                            $ultima_pos_espaco = strrpos($ultima_pos, ' ');                               

                            if ( strlen($value['descricao']) > $qtd_letras ) {
                                echo substr($value['descricao'], 0, $ultima_pos_espaco).'...';
                            } else {
                                echo $value['descricao'];
                            }
                            ?>
                            &nbsp;
                        </td>
                        <td class="info_perfil_plano">
                            <?php
                            if ( strpos($value['caracteristicaProduto'], 'Posicao2') !== false ) {
                                ?>
                                <div class="nome_posicao">TOP</div>
                                <div class="fas fa-star"></div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        
                        </td>
                        <td class="info_perfil_curtidas">
                            <?php 
                            if ( ! empty($value['Curtidas']) ) {
                                ?>
                                <div class="fas fa-heart"></div>
                                <?php
                                echo ' '.$value['Curtidas'].utf8_encode(' gostei em publicaÁıes hoje');
                            }
                            ?>
                        </td>
                        <td>
                        
                        </td>
                    </tr>
                    
                </table>
            </div>
            
            <?php
            if ( $value['VerificacaoPerfil'] == 1 ) {
            ?>
                <a href="<?php echo home_url('/perfil/').$url_perfil; ?>">
                    <img class="icon-perfil-verificado" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icon-perfil-verificado.png">
                </a>
            <?php
            }
            ?>
            
        </div>
        <?php
    }
    
    ?>
    </div>
    <?php

    return $todos;
}

function scrollInfinitoAcompanhantes() {
    global $wpdb;
    
    $limit = $_REQUEST['limit'];
    $offset = $_REQUEST['offset'];
    $search = $_REQUEST['search'];
    $genero = $_REQUEST['genero'];
    
    if ( ! empty($search) ) {
        $search = str_replace('%2C', ',', $search); //%2C È a vÌrgula(,)
        $search = explode(",", sanitize_text_field($search));

        if ( count($search) == 3 ) {
            $get_cidade = $search[0];
            $get_bairro = $search[1];
            $get_estado = $search[2];
        } else if ( count($search) == 2 ) {
            $get_cidade = $search[0];
            $get_estado = $search[1];
        }
    } else {
        $get_cidade = "";
        $get_estado = "";
    }
    
    if ( ! empty($genero) ) {
        $get_genero = explode("-", sanitize_text_field($genero));
    } else {
        $get_genero = "";
    }
    
    $sql_cidade = "";
    $sql_bairro = "";
    $sql_estado = "";
    $sql_genero = "";
    
    /*
    if ( ! empty($get_cidade) ) {
        $sql_cidade = " AND wp_ht_anunciante.cidade like '%".$get_cidade."%' ";
        $cont_where++;
    }

    if ( ! empty($get_bairro) ) {
        $sql_bairro = " AND wp_ht_anunciante.bairro like '%".$get_bairro."%' ";
    }

    if ( ! empty($get_estado) ) {
        $sql_estado = " AND wp_ht_anunciante.estado like '%".$get_estado."%' ";
    }
    */

    //Este cÛdigo pode n„o funcionar, o cÛdigo acima È o antigo
    if ( ! empty($get_cidade) ) {
        $sql_cidade = " AND (wp_ht_anunciante.cidade like '%".$get_cidade."%' ";
        $sql_cidade .= " OR wp_ht_anunciante.bairro like '%".$get_cidade."%' ";
        $sql_cidade .= " OR wp_ht_anunciante.estado like '%".$get_cidade."%') ";
        $cont_where++;
    }

    if ( ! empty($get_bairro) ) {
        $sql_bairro = " AND (wp_ht_anunciante.cidade like '%".$get_bairro."%' ";
        $sql_bairro .= " OR wp_ht_anunciante.bairro like '%".$get_bairro."%' ";
        $sql_bairro .= " OR wp_ht_anunciante.estado like '%".$get_bairro."%') ";
    }

    if ( ! empty($get_estado) ) {
        $sql_estado = " AND (wp_ht_anunciante.cidade like '%".$get_estado."%' ";
        $sql_estado .= " OR wp_ht_anunciante.bairro like '%".$get_estado."%' ";
        $sql_estado .= " OR wp_ht_anunciante.estado like '%".$get_estado."%') ";
    }

    
    $sql_genero = "";
    
    if ( ! empty($genero) ) {

        $sql_genero = " AND (";
        
        $x = 0;        
        foreach ( $get_genero as $gen ) {
            $sql_genero .= "wp_ht_anunciante.sexo = '".$gen."'";
                        
            $x++;
            
            if ( count($get_genero) > $x ) {
                $sql_genero .= " OR ";
            }
            
        }
        $sql_genero .= ")";

    }
    
    $busca = "
                SELECT t1.ID AS id_usuario, t1.nome, IF(t1.idade LIKE '60+' OR t1.idade = '',t1.idade,CONCAT(t1.idade,' anos')) AS idade, t1.sexo, t1.estado, IF(t1.cidade = 'padrao',(NULL),LOWER(t1.cidade)) AS cidade, t1.descricao, CONCAT('/wp-content/uploads/',t4.img_perfil) AS img_perfil, IF(t3.cache1,CONCAT('R$ ',t3.cache1,'/H'),t3.cache1) AS cache1, t2.order_item_name,
                       t2.name AS caracteristicaProduto, 
                       ( 
                       CASE
                       WHEN t2.name LIKE '%Posicao1%' THEN 1
                       WHEN t2.name LIKE '%Posicao2%' THEN 2
                       WHEN t2.name LIKE '%Posicao3%' THEN 3
                       WHEN t2.name LIKE '%Posicao4%' THEN 4
                       ELSE 5
                       END) AS Posicao,                           
                       IF( t2.name LIKE '%DestaqueCor%',1,0) AS DestaqueCor,
                       IF(t7.user_id,1,0) AS AtualizacaoDiaria,
                       t5.Curtidas,
                       IF(t1.verificado = 'v',1,0) AS VerificacaoPerfil,
                       IF(t6.EnsaioSite = 1, 1, 0) AS EnsaioSite,
                       IF( t2.name LIKE '%VerTelefone%',1,0) AS VerTelefone
                FROM (SELECT wp_users.ID, wp_users.display_name AS nome, wp_ht_anunciante.idade, wp_ht_anunciante.sexo, wp_ht_anunciante.estado,
                      wp_ht_anunciante.cidade, wp_ht_anunciante.descricao, wp_ht_anunciante.principal_foto_id, wp_ht_anunciante.verificado
                      FROM wp_ht_anunciante
                      INNER JOIN wp_users ON (wp_users.ID = wp_ht_anunciante.user_id)
                      WHERE ativo = 's' ".$sql_cidade.$sql_bairro.$sql_estado.$sql_genero."
                      ) t1
                LEFT JOIN (SELECT wp_postmeta.meta_value AS id_usuario, GROUP_CONCAT(wp_terms.name SEPARATOR ';') AS NAME, GROUP_CONCAT(DISTINCT(order_item_name) SEPARATOR ';') AS order_item_name
                       FROM wp_postmeta
                           INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_id) 
                       INNER JOIN wp_woocommerce_order_items ON (wp_woocommerce_order_items.order_id = wp_postmeta.post_id)
                       INNER JOIN wp_woocommerce_order_itemmeta ON (wp_woocommerce_order_itemmeta.order_item_id = wp_woocommerce_order_items.order_item_id)
                       INNER JOIN wp_term_relationships ON (wp_term_relationships.object_id = wp_woocommerce_order_itemmeta.meta_value)
                       INNER JOIN wp_term_taxonomy ON (wp_term_taxonomy.term_id = wp_term_relationships.term_taxonomy_id)
                       INNER JOIN wp_terms ON (wp_terms.term_id = wp_term_taxonomy.term_id)
                       WHERE post_type = 'shop_subscription' AND post_status = 'wc-active' AND wp_postmeta.meta_key = '_customer_user' 
                       AND wp_woocommerce_order_itemmeta.meta_key = '_product_id' AND wp_term_taxonomy.taxonomy = 'product_tag'
                       GROUP BY id_usuario) t2
                ON t1.ID = t2.id_usuario
                LEFT JOIN (SELECT ID_anunciante, meta_value AS cache1
                       FROM wp_ht_anunciantemeta
                       WHERE meta_key = 'cache1'
                       ) t3
                ON t1.ID = t3.ID_anunciante
                LEFT JOIN (SELECT post_id, meta_value AS img_perfil
                       FROM wp_postmeta
                       WHERE meta_key = '_wp_attached_file'
                       ) t4
                ON t4.post_id = t1.principal_foto_id
                LEFT JOIN (SELECT wp_ht_publications.user_id, COUNT(publication_id) AS Curtidas
                       FROM wp_ht_publications
                       LEFT JOIN wp_ht_likes ON (wp_ht_likes.publication_id = wp_ht_publications.ID)
                       WHERE DAY(SYSDATE()) = DAY(data) AND MONTH(SYSDATE()) = MONTH(data) AND YEAR(SYSDATE()) = YEAR(data)
                       GROUP BY wp_ht_publications.user_id                       
                      ) t5
                ON t5.user_id = t1.ID
                LEFT JOIN (SELECT post_id, meta_value AS EnsaioSite, SUBSTRING(post_title, 1, POSITION( ' ' IN post_title) - 1) AS user_id
                       FROM wp_postmeta
                       INNER JOIN wp_posts ON (wp_posts.ID = wp_postmeta.post_ID)
                       WHERE meta_key = 'ensaio_site'
                      ) t6
                ON t6.user_id = t1.ID
                LEFT JOIN(SELECT user_id FROM wp_ht_publications WHERE DAY(SYSDATE()) = DAY(date_publish) AND MONTH(SYSDATE()) = MONTH(date_publish) AND YEAR(SYSDATE()) = YEAR(date_publish)
                       GROUP BY user_id   
                      ) t7
                ON t7.user_id = t1.ID
                WHERE order_item_name IS NULL OR order_item_name NOT LIKE '%super top%'
                ORDER BY Posicao, DestaqueCor DESC, AtualizacaoDiaria DESC, Curtidas DESC, VerificacaoPerfil DESC, EnsaioSite DESC, VerTelefone DESC
                LIMIT ".$limit." OFFSET ".$offset."
            ";
            
    $todos = $wpdb->get_results( $busca, ARRAY_A );
    
    foreach ( $todos as $value ) {
        ?>
        <div class="box_info_perfil_home <?php if ( $value['DestaqueCor'] == 1 ) echo 'destaque_cor'; ?>">
            <div class="box_imagem_perfil">
                <?php
                if ( ! empty($value['img_perfil']) ) {
                    ?>
                    <img class="imagem_perfil" src="<?php echo get_home_url() . $value['img_perfil'] ?>">
                    <?php
                } else {
                    ?>
                    <img class="imagem_perfil" src="<?php echo get_home_url(); ?>/wp-content/uploads/2018/07/img-perfil-padrao.png">
                    <?php
                }
                ?>
            </div>
            <div class="box_conteudo_perfil">
                <table>
                    <tr>
                        <?php
                        $url_perfil = sanitize_title($value['id_usuario'].' '.$value['nome']);
                        ?>
                        <td class="info_perfil_nome" colspan="3">
                            <a href="<?php echo home_url('/perfil/').$url_perfil; ?>"><?php echo $value['nome']; ?></a>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <div style="height: 15px;"></div>
                        </td>
                        <td>
                        
                        </td>
                        <td class="info_perfil_cache1">
                            <?php echo $value['cache1']; ?>
                        </td>
                    </tr>
                    <tr>
                        <td class="box_info_perfil_idade">
                            <div class="info_perfil_idade">
                            <?php if ( ! empty($value['sexo']) ) echo ucfirst($value['sexo']).'<br>'; ?>
                            <?php if ( ! empty($value['idade']) )  {  
                                if ( $value['idade'] === "60+" ) {
                                    echo 'Mais de 60 anos <br>';
                                } else {
                                    echo $value['idade'].'<br>';
                                }
                            }
                            ?>
                            <span class="info_perfil_cidade"><?php echo $value['cidade'].'</span> - '.$value['estado']; ?>
                            </div>
                        </td>
                        <td class="info_perfil_descricao">
                            <?php 
                            $qtd_letras = 200;
                            $ultima_pos = substr($value['descricao'], 0, $qtd_letras);
                            $ultima_pos_espaco = strrpos($ultima_pos, ' ');                               

                            if ( strlen($value['descricao']) > $qtd_letras ) {
                                echo substr($value['descricao'], 0, $ultima_pos_espaco).'...';
                            } else {
                                echo $value['descricao'];
                            }
                            ?>
                            &nbsp;
                        </td>
                        <td class="info_perfil_plano">
                            <?php
                            if ( strpos($value['caracteristicaProduto'], 'Posicao2') !== false ) {
                                ?>
                                <div class="nome_posicao">TOP</div>
                                <div class="fas fa-star"></div>
                                <?php
                            }
                            ?>
                        </td>
                    </tr>
                    <tr>
                        <td>
                        
                        </td>
                        <td class="info_perfil_curtidas">
                            <?php 
                            if ( ! empty($value['Curtidas']) ) {
                                ?>
                                <div class="fas fa-heart"></div>
                                <?php
                                echo ' '.$value['Curtidas'].utf8_encode(' gostei em publicaÁıes hoje');
                            }
                            ?>
                        </td>
                        <td>
                        
                        </td>
                    </tr>
                    
                </table>
            </div>
            
            <?php
            if ( $value['VerificacaoPerfil'] == 1 ) {
            ?>
                <a href="<?php echo home_url('/perfil/').$url_perfil; ?>">
                    <img class="icon-perfil-verificado" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icon-perfil-verificado.png">
                </a>
            <?php
            }
            ?>
            
        </div>
        <?php
    }

    wp_die();
}

add_action( 'wp_ajax_nopriv_scrollInfinitoAcompanhantes', 'scrollInfinitoAcompanhantes' );
add_action( 'wp_ajax_scrollInfinitoAcompanhantes', 'scrollInfinitoAcompanhantes' );

function curtirPub() {
    global $wpdb;

    $id_pub = $_REQUEST['id_pub'];
    $ip_user = $_SERVER['REMOTE_ADDR'];
    
    $sql = $wpdb->get_var("
                    SELECT ID FROM wp_ht_likes WHERE user_ip = '$ip_user'  AND publication_id = $id_pub
                    ");
                    
    if ( $sql == "" ) {
        $wpdb->insert(
                'wp_ht_likes',
                array(
                    'user_ip' => $ip_user,
                    'publication_id' => $id_pub,
                    'data' => current_time( 'mysql', 0 )
                ),
                array(
                    '%s',
                    '%d',
                    '%s'
                )
            );
        $res[0] = 1;
    } else {  
        $wpdb->delete(
                'wp_ht_likes',
                array(
                    'user_ip' => $ip_user,
                    'publication_id' => $id_pub                   
                ),
                array(
                    '%s',
                    '%d'
                )
            );
        $res[0] = 2;
    }
    
    $res[1] = $wpdb->get_var("
                        SELECT COUNT(publication_id) AS likes FROM wp_ht_likes WHERE publication_id = ".$id_pub
                        );
                        
    print json_encode($res);

    wp_die();
}

add_action( 'wp_ajax_nopriv_curtirPub', 'curtirPub' );
add_action( 'wp_ajax_curtirPub', 'curtirPub' );

function scrollInfinitoPerfil() {
    setlocale(LC_ALL, 'pt_BR', 'pt_BR.utf-8', 'pt_BR.utf-8', 'portuguese');
    global $wpdb;
    
    $limit = $_REQUEST['limit'];
    $offset = $_REQUEST['offset'];
    $tipo = $_REQUEST['tipo'];
    $user_id = $_REQUEST['user_id'];
    $tempoGMT = $_REQUEST['tempoGMT'];
    $tempoLocal = $_REQUEST['tempoLocal'];
    
    $sql_tipo = "";
    
    if ( $tipo != 'tudo' ) {

        $sql_tipo = " AND post.post_mime_type like '%".$tipo."%' ";

    }
    
    $sql = $wpdb->get_results("SELECT
                        pub.ID AS ID,
                        adv.principal_foto_id the_img,
                        user.display_name name,
                        adv.cidade city,
                        adv.estado country,
                        adv.sexo,
                        pub.date_publish date_pub,
                        pub.post_content content,
                        post.ID id_file,
                        post.post_mime_type type,
                        IF(t1.likes,t1.likes,0) AS likes
                        FROM
                          wp_ht_publications pub
                            INNER JOIN wp_ht_anunciante adv ON (pub.user_id = adv.user_id)
                            INNER JOIN wp_users user ON (pub.user_id = user.ID)
                            LEFT JOIN wp_posts post ON (pub.id_file = post.ID)
                        LEFT JOIN (SELECT publication_id, COUNT(publication_id) AS likes 
                               FROM wp_ht_likes 
                               GROUP BY publication_id) t1
                        ON pub.ID = t1.publication_id 
                        WHERE user.ID = ".$user_id.$sql_tipo."       
                        ORDER BY date_publish DESC
                        LIMIT ".$limit." OFFSET ".$offset." 
                    ", ARRAY_A);        
               
    if ( ! empty($sql) ) {
        foreach ($sql as $post) {
    ?>
            <div class="pub">
                <?php
                    $mime = $post['type'];
                    if(strstr($mime, "video/")){ // se o formato do tipo do arquivo for video...
                    ?>
                        <div class="mid-pub">
                            <video controls>
                                <source src="<?php if( !empty($post['id_file']) ) { echo wp_get_attachment_url( $post['id_file'] ); } ?>" type="video/mp4">
                            </video>
                        </div>
                    <?php
                    } else if(strstr($mime, "image/")){
                    ?>
                        <div class="mid-pub">
                            <img src="<?php echo wp_get_attachment_url( $post['id_file'] ); ?>">
                        </div>
                    <?php
                    } else if( empty($mime) ) {
                        // publicaÁ„o sem arquivos
                    } else {
                        // error
                        echo "Houve um problema ao carregar o arquivo!";
                    }
                    
                    $curtir = $wpdb->get_var(
                                                "SELECT ID FROM wp_ht_likes WHERE user_ip = '".$_SERVER['REMOTE_ADDR']."' AND publication_id = ".$post['ID']
                                            );
                    
                    $iphone = strpos($_SERVER['HTTP_USER_AGENT'],"iPhone");
                    $android = strpos($_SERVER['HTTP_USER_AGENT'],"Android");
                    $palmpre = strpos($_SERVER['HTTP_USER_AGENT'],"webOS");
                    $berry = strpos($_SERVER['HTTP_USER_AGENT'],"BlackBerry");
                    $ipod = strpos($_SERVER['HTTP_USER_AGENT'],"iPod");

                    // check if is a mobile
                    if ($iphone || $android || $palmpre || $ipod || $berry == true)
                    {
                        $url_whatsapp = 'https://api.whatsapp.com/';
                    }

                    // all others
                    else {
                        $url_whatsapp = 'https://web.whatsapp.com/';
                    }


                    ?>
                    <div class="bot-pub">
                        <?php
                        $hora_local = strtotime($post['date_pub']) - ( $tempoGMT - $tempoLocal )/1000;
                        
                        $url_perfil = home_url('/perfil/').$user_id.'-'.sanitize_title($post['name']);
                        ?>
                        <div class="date-pub"><?php echo ucfirst( utf8_encode( strftime("%d de %B de %Y ‡s %H:%M", $hora_local ) ) ); ?></div>
                        <div class="content-pub"><?php echo $post['content']; ?></div>
                        <div class="actions-pub">
                            <div class="like-pub"><div class="<?php echo empty($curtir) ? 'far' : 'fas'; ?> fa-heart"></div>  Gostei <span class="qtd_likes">(<?php echo $post['likes']; ?>)</span> <input type="hidden" name="id_pub" value="<?php echo $post['ID']; ?>"></div>                               
                            
                            <div class="dropdown">
                                <div class="share-pub" id="dropdownCompartilhar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <div class="fa fa-share-alt"></div>   Compartilhar
                                </div>
                                <div class="dropdown-menu" aria-labelledby="dropdownCompartilhar">
                                    <a class="compartilhar-pub img_whatsapp dropdown-item" href="<?php echo $url_whatsapp; ?>send?text=<?php echo $url_perfil; ?>" target="_blank"><img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/whatsapp.png">Whatsapp</a>
                                    <a class="compartilhar-pub dropdown-item" href="https://twitter.com/intent/tweet?url=<?php echo $url_perfil; ?>" target="_blank"><img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/twitter.png">Twitter</a>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                </div>           
    <?php    
        }
    }
    
    wp_die();

}
add_action( 'wp_ajax_nopriv_scrollInfinitoPerfil', 'scrollInfinitoPerfil' );
add_action( 'wp_ajax_scrollInfinitoPerfil', 'scrollInfinitoPerfil' );

/*function scrollInfinitoPub() {
    global $wpdb;
    
    $publications = $wpdb->prefix . 'ht_publications';
    $advertiser = $wpdb->prefix . 'ht_anunciante';
    $users = $wpdb->prefix . 'users';
    $posts = $wpdb->prefix . 'posts';
    $likes = $wpdb->prefix . 'likes';
    
    $limit = $_REQUEST['limit'];
    $offset = $_REQUEST['offset'];
    $search = $_REQUEST['search'];
    $genero = $_REQUEST['genero'];
    $tipo = $_REQUEST['tipo'];
    
    if ( ! empty($search) ) {
        $search = explode("-", sanitize_text_field($search));
        
        $get_cidade = $search[0];
        $get_estado = $search[1];
    } else {
        $get_cidade = "";
        $get_estado = "";
    }
    
    if ( ! empty($genero) ) {
        $get_genero = explode("-", sanitize_text_field($genero));
    } else {
        $get_genero = "";
    }
    
    if ( ! empty($tipo) ) {
        $get_tipo = explode("-", sanitize_text_field($tipo));
    } else {
        $get_tipo = "";
    }    
    
    $sql_cidade = "";
    $sql_estado = "";
    $sql_genero = "";
    $sql_tipo = "";
    
    $cont_where = 0;
    
    if ( ! empty($get_cidade) ) {
        $sql_cidade = " WHERE adv.cidade = '".$get_cidade."' ";
        $cont_where++;
    }
    
    if ( ! empty($get_estado) ) {
        $sql_estado = " AND adv.estado = '".$get_estado."' ";
    }
    
    if ( ! empty($get_genero) ) {    
        // se n tiver usado where ainda, aqui È colocado
        if ( $cont_where == 0 ) {
            $sql_genero = " WHERE (";   
        } else {
            $sql_genero = " AND (";
        }
        
        $x = 0;        
        foreach ( $get_genero as $gen ) {
            $sql_genero .= "adv.sexo = '".$gen."'";
                        
            $x++;
            
            if ( count($get_genero) > $x ) {
                $sql_genero .= " OR ";
            }
            
        }
        $sql_genero .= ")";
        $cont_where++;
    }
    
    if ( ! empty($get_tipo) && $get_tipo[0] != 'tudo' ) {    
        // se n tiver usado where ainda, aqui È colocado
        if ( $cont_where == 0 ) {
            $sql_tipo = " WHERE (";   
        } else {
            $sql_tipo = " AND (";
        }
        
        $x = 0;        
        foreach ( $get_tipo as $tipo ) {
            $sql_tipo .= "post.post_mime_type like '%".$tipo."%'";
                        
            $x++;
            
            if ( count($get_tipo) > $x ) {
                $sql_tipo .= " OR ";
            }
            
        }
        $sql_tipo .= ")";
        $cont_where++;
    }
    
    $sql = $wpdb->get_results("
    
            SELECT
                pub.ID as ID,
                adv.principal_foto_id the_img,
                user.display_name name,
                adv.cidade city,
                adv.estado country,
                adv.sexo,
                pub.date_publish date_pub,
                pub.post_content content,
                post.ID id_file,
                post.post_mime_type type,
                IF(t1.likes,t1.likes,0) AS likes
            FROM
              $publications pub
                INNER JOIN $advertiser adv ON (pub.user_id = adv.user_id)
                INNER JOIN $users user ON (pub.user_id = user.ID)
                LEFT JOIN $posts post ON (pub.id_file = post.ID)
                LEFT JOIN (SELECT publication_id, COUNT(publication_id) AS likes 
                           FROM wp_ht_likes 
                           GROUP BY publication_id) t1
                    ON pub.ID = t1.publication_id
            ".$sql_cidade.$sql_estado.$sql_genero.$sql_tipo."        
            ORDER BY date_publish DESC
            LIMIT ".$limit." OFFSET ".$offset."
               ", ARRAY_A);        
               
    if ( !empty($sql) ) {
        foreach ($sql as $post) {
    ?>
            <div class="pub">
                <div class="top-pub">
                    <div class="img-user">
                        <img src="<?php
                            if ($post['the_img']) {
                                echo wp_get_attachment_url( $post['the_img'] );
                            } else {
                                echo home_url()."/wp-content/uploads/2018/07/img-perfil-padrao.png"; // url de imagem de usuario padrao
                            }
                        ?>" width="50px" >
                    </div>
                    <div class="inf-user">
                        <div class="name-user"><?php echo $post['name']; ?></div>
                        <div class="location-user"><?php echo $post['city'] . ", " . $post['country']; ?></div>
                    </div>
                </div>
                <?php
                    $mime = $post['type'];
                    if(strstr($mime, "video/")){ // se o formato do tipo do arquivo for video...
                    ?>
                        <div class="mid-pub">
                            <video controls>
                                <source src="<?php if( !empty($post['id_file']) ) { echo wp_get_attachment_url( $post['id_file'] ); } ?>" type="video/mp4">
                            </video>
                        </div>
                    <?php
                    } else if(strstr($mime, "image/")){
                    ?>
                        <div class="mid-pub">
                            <img src="<?php echo wp_get_attachment_url( $post['id_file'] ); ?>">
                        </div>
                    <?php
                    } else if( empty($mime) ) {
                        // publica√É¬ß√É¬£o sem arquivos
                    } else {
                        // error
                        echo "Houve um problema ao carregar o arquivo!";
                    }
                    
                    $curtir = $wpdb->get_var(
                                        "SELECT ID FROM wp_ht_likes WHERE user_id = ".get_current_user_id()." AND publication_id = ".$post['ID']
                                    );
                    
                ?>
                <div class="bot-pub">
                    <div class="date-pub"><?php echo ucfirst( utf8_encode( strftime("%d de %B as %H:%M", strtotime($post['date_pub']) ) ) ); ?></div>
                    <div class="content-pub"><?php echo $post['content']; ?></div>
                    <div class="actions-pub">
                        <div class="like-pub"><div class="<?php echo empty($curtir) ? 'far' : 'fas'; ?> fa-heart"></div>  Gostei <span class="qtd_likes">(<?php echo $post['likes']; ?>)</span> <input type="hidden" name="id_pub" value="<?php echo $post['ID']; ?>"></div>
                        <div class="share-pub">Compartilhar   <div class="fa fa-share"></div></div>
                    </div>
                </div>
            </div>             
    <?php    
        }
    }

    wp_die();
}

add_action( 'wp_ajax_nopriv_scrollInfinitoPub', 'scrollInfinitoPub' );
add_action( 'wp_ajax_scrollInfinitoPub', 'scrollInfinitoPub' );*/

function verificaCookie()
{
    if(empty($_COOKIE['ip']))
    {
        echo 1;
    }
    wp_die();
}
add_action( 'wp_ajax_nopriv_verificaCookie', 'verificaCookie' );
add_action( 'wp_ajax_verificaCookie', 'verificaCookie' );

function armazenaCookie()
{
    if(empty($_COOKIE['ip']))
    {
        $ip = $_SERVER['REMOTE_ADDR'];
        setcookie('ip', $ip, time()+72000);  //20 horas atÈ que apareÁa a confirmaÁ„o de novo
        echo 1;
    }
    wp_die();
}
add_action( 'wp_ajax_nopriv_armazenaCookie', 'armazenaCookie' );
add_action( 'wp_ajax_armazenaCookie', 'armazenaCookie' );

function verificaEmail() {

    $email_user = $_REQUEST['email_user'];  

    if ( email_exists( $email_user ) || username_exists( $email_user ) ) {      
        
        if ( email_exists( $email_user ) ) {

            $user = get_user_by('email', $email_user);

        } else if ( username_exists( $email_user ) ) {

            $user = get_user_by('login', $email_user);

        }

        wp_new_user_notification($user->data->ID, '', 'both');

        echo 1;
    } else {
        echo 2;
    }
    wp_die();
}
add_action( 'wp_ajax_nopriv_verificaEmail', 'verificaEmail' );
add_action( 'wp_ajax_verificaEmail', 'verificaEmail' );

function verificaUsuarios() {

    $email_user = $_REQUEST['email_user'];   

    if ( !is_email( $email_user ) ) {
        echo 1;
    } else if ( !email_exists( $email_user ) ) {      
        
        $usuarioSenhaRandomica = wp_generate_password( $length=12, $include_standard_special_chars=false ); // gera uma senha qualquer
        $userId = null;
        $userdata = array(
            'user_login' => $email_user,
            'user_pass' => $usuarioSenhaRandomica,
            'user_email' => $email_user,
            'role' => 'ht_visitante'
        );

        $userId = wp_insert_user( $userdata ); // Faz o insert de usuario

        if ( $userId != null ) { // Se usuario foi criado...
            // notifica o usuario com seu login e senha
            $notify = 'both';
            wp_new_user_notification( $userId, null, $notify );
        }
        echo 2;
    } else if ( email_exists( $email_user ) ) {
        echo 3;     
    }

    wp_die();
}
add_action( 'wp_ajax_nopriv_verificaUsuarios', 'verificaUsuarios' );
add_action( 'wp_ajax_verificaUsuarios', 'verificaUsuarios' );

function ht_aprovar_verificacao_perfil() {
    global $wpdb;
    
    $post_id = $_REQUEST['post_id'];

    $user_id = $wpdb->get_var("
                        SELECT wp_users.ID FROM wp_users 
                        INNER JOIN wp_posts ON (wp_posts.post_author = wp_users.ID)
                        WHERE post_type = 'ht_verifica_perfil' AND wp_posts.ID = ".$post_id."
                    ");
    
    $sql = $wpdb->update(
        'wp_ht_anunciante',
        array(
    		'verificado' => 'v'
    	),
    	array(
    		'user_id' => $user_id
    	)
    );
    
    if ( $sql === false || $sql = 0 ) {
        echo 1;
    } else {
        $id_attachment = $wpdb->get_results("
                					SELECT ID FROM wp_posts 
        							WHERE post_type = 'attachment' AND post_parent = ".$post_id."
        						", ARRAY_A);
                                
        $wpdb->delete(
            'wp_ht_foto_comparacao',
            array(
                'user_id' => $user_id
            )
        );    
        
        wp_delete_post($post_id, true);
        
        foreach ( $id_attachment as $key ) {
        	wp_delete_attachment($key['ID']);

            wp_delete_post($key['ID'], true);
        }
        
        echo 2;
    }
    
    wp_die();
}
add_action( 'wp_ajax_nopriv_ht_aprovar_verificacao_perfil', 'ht_aprovar_verificacao_perfil' );
add_action( 'wp_ajax_ht_aprovar_verificacao_perfil', 'ht_aprovar_verificacao_perfil' );

function ht_rejeitar_verificacao_perfil() {
    global $wpdb;
    
    $post_id = $_REQUEST['post_id'];

    $user_id = $wpdb->get_var("
                        SELECT wp_users.ID FROM wp_users 
                        INNER JOIN wp_posts ON (wp_posts.post_author = wp_users.ID)
                        WHERE post_type = 'ht_verifica_perfil' AND wp_posts.ID = ".$post_id."
                    ");
                    
    $sql = $wpdb->update(
        'wp_ht_anunciante',
        array(
        	'verificado' => 'r'
    	),
    	array(
    		'user_id' => $user_id
    	)
    );
    
    if ( $sql === false || $sql = 0 ) {
        echo 1;
    } else {
        $id_attachment = $wpdb->get_results("
            						SELECT ID FROM wp_posts 
        							WHERE post_type = 'attachment' AND post_parent = ".$post_id."
        						", ARRAY_A);
        
        $wpdb->delete(
            'wp_ht_foto_comparacao',
            array(
                'user_id' => $user_id
            )
        );
        
        wp_delete_post($post_id, true);
        
        foreach ( $id_attachment as $key ) {
        	wp_delete_attachment($key['ID']);
            wp_delete_post($key['ID'], true);
        }	
        
        echo 2;
    }
    
    wp_die();
}
add_action( 'wp_ajax_nopriv_ht_rejeitar_verificacao_perfil', 'ht_rejeitar_verificacao_perfil' );
add_action( 'wp_ajax_ht_rejeitar_verificacao_perfil', 'ht_rejeitar_verificacao_perfil' );

function ht_atualiza_ensaio_site() {
    
    $post_id = $_REQUEST['post_id'];
    $ensaio_site = $_REQUEST['ensaio_site'];

    update_post_meta( $post_id, 'ensaio_site', $ensaio_site );

    wp_die();
}
add_action( 'wp_ajax_nopriv_ht_atualiza_ensaio_site', 'ht_atualiza_ensaio_site' );
add_action( 'wp_ajax_ht_atualiza_ensaio_site', 'ht_atualiza_ensaio_site' );

function redireciona_template() {
    $post_types = array('product');

    if ( in_array( get_post_type(), $post_types ) ) {
        load_template( get_template_directory().'/404.php' );
        exit;
    }
}
add_action('template_redirect', 'redireciona_template');