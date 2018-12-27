<?php
global $wpdb;

$titulo = get_the_title();
$id = explode(' ', $titulo);
$user_id = $id[0];

function get_user_id() {
    $titulo = get_the_title();
    $id = explode(' ', $titulo);
    $user_id = $id[0];
    
    return $user_id;
}

$admin_options = $wpdb->prefix . 'ht_admin_options'; // Prefixo da tabela para não haver erro no plugin em diferentes intalações
$ht_gallery = $wpdb->prefix . 'ht_gallery';

$estados = $wpdb->get_results("SELECT cod_estados, sigla FROM estados ORDER BY sigla", ARRAY_A);

$options = $wpdb->get_results(" SELECT meta_key, meta_value, meta_name FROM $admin_options ORDER BY meta_value ", ARRAY_A);

$user_gallery = $wpdb->get_results(" SELECT file_id FROM $ht_gallery WHERE user_id = $user_id ORDER BY ID ", ARRAY_A); // Isso aqu vai ter q ir dentro de onde esta o ajax que salva os dados do form para retornar as imagens ja salvas

function get_dados_cadastro_pessoa($coluna) {
    global $wpdb;

    $result = $wpdb->get_results("
                        SELECT
                           ".$coluna."
                        FROM 
                            wp_users
                        WHERE
                            ID = ".get_user_id()."
                    ", ARRAY_A);                        

    return $result[0][$coluna];
}

function get_dados_meta_cadastro_pessoa($coluna) {
    global $wpdb;

    $result = $wpdb->get_results("
                        SELECT
                           meta_value
                        FROM 
                            wp_ht_anunciantemeta
                        WHERE
                            meta_key = '".$coluna."' AND ID_anunciante = ".get_user_id()."
                    ", ARRAY_A);

    return $result[0]['meta_value'];
}

function get_dados_cadastro_anunciante($coluna) {
    global $wpdb;

    if ( $coluna == 'cidade' ) {
        $coluna = 'lower('.$coluna.')';
    }

    $result = $wpdb->get_results("
                        SELECT
                           ".$coluna."
                        FROM 
                            wp_ht_anunciante
                        WHERE
                            user_id = ".get_user_id()."
                    ", ARRAY_A);

    return $result[0][$coluna];
}

function get_cod_estado_by_sigla($sigla) {
    global $wpdb;
    
    $result = $wpdb->get_var("
                        SELECT
                            cod_estados 
                        FROM estados 
                        WHERE sigla = '".$sigla."'
                    ");
                    
    return $result;
}

$user = get_userdata($user_id);

    $busca = "
                SELECT t1.ID AS id_usuario, t1.nome, IF(t1.idade LIKE '60+' OR t1.idade = '',t1.idade,CONCAT(t1.idade,' anos')) AS idade, t1.sexo, t1.estado, IF(t1.cidade = 'padrao',(NULL),LOWER(t1.cidade)) AS cidade, t1.descricao, CONCAT('/wp-content/uploads/',t4.img_perfil) AS img_perfil, t1.telefone, IF(t3.cache1,CONCAT('R$ ',t3.cache1,'/H'),t3.cache1) AS cache1, 
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
                      wp_ht_anunciante.cidade, wp_ht_anunciante.descricao, wp_ht_anunciante.principal_foto_id, wp_ht_anunciante.telefone, wp_ht_anunciante.verificado
                      FROM wp_ht_anunciante
                      INNER JOIN wp_users ON (wp_users.ID = wp_ht_anunciante.user_id)
                      WHERE ativo = 's' AND wp_ht_anunciante.cidade like '%".get_dados_cadastro_anunciante('cidade')."%' AND wp_ht_anunciante.estado like '%".get_dados_cadastro_anunciante('estado')."%' AND wp_ht_anunciante.sexo = '".get_dados_cadastro_anunciante('sexo')."'
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
                WHERE t2.order_item_name LIKE '%diamante%'
                ORDER BY Posicao, DestaqueCor DESC, AtualizacaoDiaria DESC, Curtidas DESC, VerificacaoPerfil DESC, EnsaioSite DESC, VerTelefone DESC
            ";
            
    $todos = $wpdb->get_results( $busca, ARRAY_A );

    if ( ! empty($todos) ) {
        echo '<div id="box_slides_acompanhantes"><div id="slides_acompanhantes">';

        foreach ( $todos as $value ) {
            $url_perfil = sanitize_title($value['id_usuario'].' '.$value['nome']);
            ?>
            <div class="box_slide_diamante">
                <div class="box_info_perfil_home slide_diamante <?php if ( $value['DestaqueCor'] == 1 ) echo 'destaque_cor'; ?>">
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
                                        echo ' '.$value['Curtidas'].' gostei em publicações hoje';
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
        
        if ( count($todos) > 1 ) {
        ?>
            <div class="w3js-previous slide_diamante_previous"><i class="fas fa-chevron-circle-left"></i></div>
            <div class="w3js-next slide_diamante_next"><i class="fas fa-chevron-circle-right"></i></div>
        <?php
        }
        echo '</div></div>';
    }
    ?>

    <div class="nome-acompanhante">
        <?php echo $user->data->display_name; ?>
    </div>

    <div class="cidade-acompanhante">
        <?php echo 'Acompanhantes '.ucwords(get_dados_cadastro_anunciante('cidade')).', '.get_dados_cadastro_anunciante('estado'); ?>
    </div>

    <div class="box-info-acompanhante">
        
        <div class="btn-padrao">
            <p class="valor"><img class="icones-info" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icone-cache.png">   
                Cachê: 
                <span class="qtd-cache">
                    <?php 
                    if ( ! empty(get_dados_meta_cadastro_pessoa('cache1')) ) {
                        echo 'R$ '.get_dados_meta_cadastro_pessoa('cache1').'/H';
                    } else {
                        echo 'N/A';
                    }
                    ?>
                <span>
            </p>
        </div>    

        <?php
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

        <div class="dropdown telefone">
            <div class="btn-padrao" id="dropdownTelefone" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"><img class="icones-info" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icone-telefone.png">   Ver Telefone
            </div>
            <div class="dropdown-menu" aria-labelledby="dropdownTelefone">
                <a class="img_whatsapp dropdown-item" href="<?php echo $url_whatsapp; ?>send?phone=<?php echo get_dados_cadastro_pessoa('user_login'); ?>" target="_blank"><img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/whatsapp.png">Falar com <?php echo get_dados_cadastro_pessoa('user_login'); ?></a>
            </div>
        </div>

        <a href="<?php echo home_url('/planos?idp=').get_user_id(); ?>">
            <div class="btn-padrao">
                <p class="presente"><img class="icones-info" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/icone-dar-presente.png">   Dar Presente</p>
            </div>
        </a>

        <div class="dropdown">
            <div class="share-pub-verde btn-padrao" id="dropdownCompartilhar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                <div class="fa fa-share-alt"></div>   Compartilhar
            </div>
            <div class="dropdown-menu" aria-labelledby="dropdownCompartilhar">
                <a class="compartilhar-pub img_whatsapp dropdown-item" href="<?php echo $url_whatsapp; ?>send?text=<?php echo $url_perfil; ?>" target="_blank"><img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/whatsapp.png">Whatsapp</a>
                <a class="compartilhar-pub dropdown-item" href="https://twitter.com/intent/tweet?url=<?php echo $url_perfil; ?>" target="_blank"><img class="img_redes_sociais" src="<?php echo get_stylesheet_directory_uri(); ?>/imagens/twitter.png">Twitter</a>
            </div>
        </div>
    </div>

    <?php
    $imagens_galeria = $wpdb->get_results("
                            SELECT wp_ht_gallery.file_id, wp_postmeta.meta_value AS url
                            FROM wp_ht_gallery 
                            INNER JOIN wp_posts ON(wp_posts.ID = wp_ht_gallery.file_id)
                            INNER JOIN wp_postmeta ON(wp_postmeta.post_id = wp_posts.ID)
                            WHERE user_id = ".get_user_id()." AND wp_postmeta.meta_key = '_wp_attached_file'
                        ",ARRAY_A);

    echo '<div id="slides">';
    foreach ( $imagens_galeria as $imagem ) {
        echo '<img class="img_slide_perfil" src="'.home_url('/wp-content/uploads/').$imagem['url'].'">';
    }
    echo '</div>';
    ?>

    <div class="box-info-acompanhante-principal">
        <div class="box-info-acompanhante">
            <div class="btn-padrao info-acompanhante info-acompanhante-perfil btn-active">
                Perfil
            </div>
            <div class="btn-padrao info-acompanhante info-acompanhante-descricao">
                Descrição
            </div>
            <div class="btn-padrao info-acompanhante info-acompanhante-servicos">
                Serviços
            </div>
            <div class="btn-padrao info-acompanhante info-acompanhante-pagamento">
                Pagamento
            </div>
            <div class="btn-padrao info-acompanhante info-acompanhante-horarios">
                Horários
            </div>
        </div>

        <div id="table-info-acompanhante-perfil">
            <table class="info-acompanhante">
                <tr>
                    <td>Cidade:</td>
                    <td><?php echo ucwords(get_dados_cadastro_anunciante('cidade')).' - '.get_dados_cadastro_anunciante('estado') ?></td>
                </tr>
                <tr>
                    <td>Endereço:</td>
                    <td><?php echo get_dados_cadastro_anunciante('endereco'); ?></td>
                </tr>
                <tr>
                    <td>Bairro:</td>
                    <td><?php echo get_dados_cadastro_anunciante('bairro'); ?></td>
                </tr>
                <?php
                $idade = get_dados_cadastro_anunciante('idade');
                if ( $idade !== '60+' && ! empty($idade) ) {
                    $idade = ' - '.$idade.' anos';
                } else if ( ! empty($idade) ) {
                    $idade = ' - mais de 60 anos';
                }
                
                $etnia = get_dados_cadastro_anunciante('etnia');
                if ( ! empty($etnia) ) {
                    $etnia = ' - '.$etnia;
                }
                ?>               
                <tr>
                    <td>Eu sou:</td>
                    <td><?php echo ucfirst(get_dados_cadastro_anunciante('sexo')).$idade.$etnia; ?></td>
                </tr>
                <?php
                $url_gpguia = get_dados_cadastro_anunciante('url_gpguia');
                if ( !empty($url_gpguia) && (strpos($url_gpguia, "http://") === false || strpos($url_gpguia, "https://") === false) ) {
                    $url_gpguia = 'http://'.$url_gpguia;
                }
                ?>
                <tr>
                    <td>GPGuia:</td>
                    <td><a href="<?php echo $url_gpguia ?>"><?php echo $url_gpguia; ?></a></td>
                </tr>
                <?php
                $atende = get_dados_meta_cadastro_pessoa('atende');
                $atende = str_replace(',', ' - ', $atende);
                ?>
                <tr>
                    <td>Atendo:</td>
                    <td><?php echo $atende; ?></td>
                </tr>
                <?php
                $locais = get_dados_meta_cadastro_pessoa('locais');
                $locais = str_replace(',', ' - ', $locais);
                ?>
                <tr>
                    <td>Locais:</td>
                    <td><?php echo $locais; ?></td>
                </tr>
                <?php
                $idiomas = get_dados_meta_cadastro_pessoa('idiomas');
                $idiomas = str_replace(',', ' - ', $idiomas);
                ?>
                <tr>
                    <td>Idiomas:</td>
                    <td><?php echo $idiomas; ?></td>
                </tr>
            </table>
        </div>

        <div id="table-info-acompanhante-descricao">
            <table class="info-acompanhante">
                <tr>
                    <td><?php echo get_dados_cadastro_anunciante('descricao'); ?></td>
                </tr>                
            </table>
        </div>
        <?php
        $servicos = get_dados_meta_cadastro_pessoa('servicos');
        $servicos = str_replace(',', ' - ', $servicos);
        ?>
        <div id="table-info-acompanhante-servicos">
            <table class="info-acompanhante">
                <tr>
                    <td><?php echo $servicos; ?></td>
                </tr>
            </table>
        </div>
        <?php  
        $cache30 = get_dados_meta_cadastro_pessoa('cache30');
        $cache1 = get_dados_meta_cadastro_pessoa('cache1');
        $cache2 = get_dados_meta_cadastro_pessoa('cache2');
        $cache4 = get_dados_meta_cadastro_pessoa('cache4');
        $cacheNoite = get_dados_meta_cadastro_pessoa('cacheNoite');
        ?>
        <div id="table-info-acompanhante-pagamento">
            <table class="info-acompanhante">
                <?php
                if ( ! empty($cache30) ) :
                ?>
                    <tr>
                        <td>Cachê por 30 minutos:</td>
                        <td><?php echo 'R$ '.$cache30; ?></td>
                    </tr>
                <?php endif;
                    
                if ( ! empty($cache1) ) :
                ?>
                    <tr>
                        <td>Cachê por hora:</td>
                        <td><?php echo 'R$ '.$cache1; ?></td>
                    </tr>
                <?php endif;
                    
                if ( ! empty($cache2) ) :
                ?>
                    <tr>
                        <td>Cachê por 2 horas:</td>
                        <td><?php echo 'R$ '.$cache2; ?></td>
                    </tr>
                <?php endif;
                    
                if ( ! empty($cache4) ) :
                ?>
                    <tr>
                        <td>Cachê por 4 horas:</td>
                        <td><?php echo 'R$ '.$cache4; ?></td>
                    </tr>
                <?php endif;
                    
                if ( ! empty($cacheNoite) ) :
                ?>
                    <tr>
                        <td>Cachê por noite:</td>
                        <td><?php echo 'R$ '.$cacheNoite; ?></td>
                    </tr>
                <?php endif;
                
                if ( empty($cache30) && empty($cache1) && empty($cache2) && empty($cache4) && empty($cacheNoite)  ) :
                ?>
                    <tr>
                        <td>Cachê:</td>
                        <td>não informado</td>
                    </tr>
                <?php endif; ?>
                
                <?php
                $pagamentos = get_dados_meta_cadastro_pessoa('pagamentos');
                
                if ( ! empty($pagamentos) && $pagamentos !== 'outros'  ) {
                    $pagamentos = explode(',', $pagamentos);
                    
                    if ( in_array('Dinheiro', $pagamentos) ) {
                        $formas_pagamento = 'Dinheiro';
                    }
                    
                    if ( in_array('Cartao - Debito', $pagamentos) ) {
                        if ( empty($formas_pagamento) ) {
                            $formas_pagamento = 'Cartão de Débito('.get_dados_meta_cadastro_pessoa('deb_cartoes').')'; 
                        } else {
                            $formas_pagamento .= ' - Cartão de Débito('.get_dados_meta_cadastro_pessoa('deb_cartoes').')'; 
                        }                    
                    }
                    
                    if ( in_array('Cartao - Credito', $pagamentos) ) {                    
                        if ( empty($formas_pagamento) ) {
                            $formas_pagamento = 'Cartão de Crédito('.get_dados_meta_cadastro_pessoa('cre_cartoes').')';
                        } else {
                            $formas_pagamento .= ' - Cartão de Crédito('.get_dados_meta_cadastro_pessoa('cre_cartoes').')';
                        }  
                    }
                } else {
                    $formas_pagamento = 'não informado';
                }
                ?>
                
                <tr>
                    <td>Formas de pagamento:</td>
                    <td><?php echo $formas_pagamento ?></td>
                </tr>
            </table>
        </div>
        <?php 
        $domingo_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Dom');
        $domingo_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Dom');
        $segunda_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Seg');
        $segunda_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Seg');
        $terca_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Ter');
        $terca_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Ter');
        $quarta_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Qua');
        $quarta_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Qua');
        $quinta_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Qui');
        $quinta_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Qui');
        $sexta_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Sex');
        $sexta_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Sex');
        $sabado_inicio = get_dados_meta_cadastro_pessoa('HorarioInicial_Sab');
        $sabado_fim = get_dados_meta_cadastro_pessoa('HorarioFinal_Sab');
        ?>
        <div id="table-info-acompanhante-horarios">
            <table class="info-acompanhante">
                <?php
                $atendimento = get_dados_meta_cadastro_pessoa('atendimento');
                if ( $atendimento === "Atendimento em horarios especificos" ) {
                    
                    $semana = get_dados_meta_cadastro_pessoa('semana');
                    $semana = explode(',' , $semana);
                    
                    if ( in_array('Domingo', $semana) ) :
                    ?>
                        <tr>
                            <td>Domingo:</td>
                            <td><?php echo $domingo_inicio.' às '.$domingo_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Segunda', $semana) ) :
                    ?>
                        <tr>
                            <td>Segunda:</td>
                            <td><?php echo $segunda_inicio.' às '.$segunda_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Terca', $semana) ) :
                    ?>
                        <tr>
                            <td>Terça:</td>
                            <td><?php echo $terca_inicio.' às '.$terca_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Quarta', $semana) ) :
                    ?>    
                        <tr>
                            <td>Quarta:</td>
                            <td><?php echo $quarta_inicio.' às '.$quarta_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Quinta', $semana) ) :
                    ?>
                        <tr>
                            <td>Quinta:</td>
                            <td><?php echo $quinta_inicio.' às '.$quinta_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Sexta', $semana) ) :
                    ?>
                        <tr>
                            <td>Sexta:</td>
                            <td><?php echo $sexta_inicio.' às '.$sexta_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                    if ( in_array('Sabado', $semana) ) :
                    ?>
                        <tr>
                            <td>Sábado:</td>
                            <td><?php echo $sabado_inicio.' às '.$sabado_fim ; ?></td>
                        </tr>
                    <?php endif;
                    
                } else if ( $atendimento === "Atendimento 24 horas" ) { ?>
                    <tr>
                        <td>Atendimento 24 horas</td>
                    </tr>
                <?php } else { ?>
                    <tr>
                        <td>Não informado</td>
                    <tr>
                <?php } ?>
            </table>
        </div>
    </div>
    <?php
    /*$sql = $wpdb->get_results("SELECT
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
                                WHERE user.ID = ".get_user_id()."       
                                ORDER BY date_publish DESC
                                LIMIT 6 OFFSET 0
                            ", ARRAY_A);

    $sql_imagem = $wpdb->get_results("SELECT
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
                                WHERE user.ID = ".get_user_id()." 
                                    AND post.post_mime_type LIKE '%image%'       
                                ORDER BY date_publish DESC
                                LIMIT 6 OFFSET 0
                            ", ARRAY_A);

    $sql_video = $wpdb->get_results("SELECT
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
                                WHERE user.ID = ".get_user_id()." 
                                    AND post.post_mime_type LIKE '%video%'       
                                ORDER BY date_publish DESC
                                LIMIT 6 OFFSET 0
                            ", ARRAY_A);

    if ( ! empty($sql) ) {
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
        }*/
    
    $cidade = get_dados_cadastro_anunciante('cidade');
    $estado = get_dados_cadastro_anunciante('estado');
    $endereco = get_dados_cadastro_anunciante('endereco');
    $bairro = get_dados_cadastro_anunciante('bairro');
    $numero = get_dados_cadastro_anunciante('numero');    

    $endereco_completo = $endereco.",+".$numero."+-+".$bairro.",+".$cidade."+-+".$estado;
    $endereco_completo = str_replace(" ", "+", $endereco_completo);
    $str =file_get_contents('https://maps.googleapis.com/maps/api/geocode/json?address='.$endereco_completo.'&key=AIzaSyCoe0tKc4azX1sSk2YYeGMYVp27nuRfy6w');
    
    $enderecodecode = json_decode($str);   
    $latitude = $enderecodecode->results[0]->geometry->location->lat;
    $longitude = $enderecodecode->results[0]->geometry->location->lng;
    $latitude = str_replace(",", ".", $latitude);
    $longitude = str_replace(",", ".", $longitude);
    ?>

    <div id="map"></div>

    <script>
        function initMap() {        
            var myLatlng = new google.maps.LatLng("<?php echo $latitude;?>", "<?php echo $longitude;?>");
            var map = new google.maps.Map(document.getElementById('map'), {
                zoom: 15,
                center: myLatlng,
            });
            var citymap = {         
                vancouver: {
                    center: myLatlng,
                    population: 7
                }
            };
            for (var city in citymap) {
                var cityCircle = new google.maps.Circle({
                    strokeColor: '#FF0000',
                    strokeOpacity: 0.8,
                    strokeWeight: 2,
                    fillColor: '#FF0000',
                    fillOpacity: 0.35,
                    map: map,
                    center: citymap[city].center,
                    radius: Math.sqrt(citymap[city].population) * 100
                });      
            }
        }
    </script>
    <script async defer
    src="https://maps.googleapis.com/maps/api/js?key=AIzaSyBBxZc2NLpikD63PndZ-LtHum2iYhkr1yw&callback=initMap">
    </script>   
  
  <?php if ( ! empty($cidade) && ! empty($estado) ) {
        echo '<div class="rota_como_chegar">
                <a href="https://www.google.com.br/maps/dir//Brasil,'.$endereco.','.$numero.' - '.$bairro.','.$cidade.' - '.$estado.'/" target="_blank">Como Chegar <i class="fas fa-map-marker-alt"></i></a>
            </div>';
        }
   ?>
        <div class="box-ultimas-publicacoes">
            <div class="box-pub">
                <div class="titulo-ultimas-publicacoes">Últimas Publicações</div>
            </div>

            <div class="box-tipo-pub">
                <div class="btn-padrao tipo-pub-todas btn-active">
                    Todas
                </div>
                <div class="btn-padrao tipo-pub-fotos">
                    Fotos
                </div>
                <div class="btn-padrao tipo-pub-videos">
                    Vídeos
                </div>
            </div>

            <div id="todas_publicacoes">
            <?php
                /*foreach ($sql as $post) {
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
                                // publicaÃ§Ã£o sem arquivos
                            } else {
                                // error
                                echo "Houve um problema ao carregar o arquivo!";
                            }
                            
                            $curtir = $wpdb->get_var(
                                                "SELECT ID FROM wp_ht_likes WHERE user_ip = '".$_SERVER['REMOTE_ADDR']."' AND publication_id = ".$post['ID']
                                            );                         

                            ?>
                            <div class="bot-pub">
                                <?php
                                $hora_local = strtotime($post['date_pub']) - ( current_time( 'timestamp', 1 ) - current_time( 'timestamp', 0 ) );
                                $url_perfil = home_url('/perfil/').$user_id.'-'.sanitize_title($post['name']);
                                ?>
                                <div class="date-pub"><?php echo ucfirst( strftime("%d de %B às %H:%M", $hora_local ) ); ?></div>
                                <div class="content-pub"><?php echo $post['content']; ?></div>
                                <div class="actions-pub">
                                    <div class="like-pub"><div class="<?php echo empty($curtir) ? 'far' : 'fas'; ?> fa-heart"></div>  Gostei <span class="qtd_likes">(<?php echo $post['likes']; ?>)</span> <input type="hidden" name="id_pub" value="<?php echo $post['ID']; ?>"></div>                               

                                    <div class="dropdown">
                                        <div class="share-pub" id="dropdownCompartilhar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Compartilhar   <div class="fa fa-share"></div>
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
                }*/
            ?>
            </div>

            <div id="fotos_publicacoes">
            <?php
                /*if ( ! empty($sql_imagem) ) {
                    foreach ($sql_imagem as $post) {
                    ?>
                        <div class="pub">
                            <?php
                                $mime = $post['type'];
                                if(strstr($mime, "image/")){
                                ?>
                                    <div class="mid-pub">
                                        <img src="<?php echo wp_get_attachment_url( $post['id_file'] ); ?>">
                                    </div>
                                <?php
                                
                                $curtir = $wpdb->get_var(
                                                    "SELECT ID FROM wp_ht_likes WHERE user_ip = '".$_SERVER['REMOTE_ADDR']."' AND publication_id = ".$post['ID']
                                                );
                                }
                            ?>
                            <div class="bot-pub">
                                <?php
                                $hora_local = strtotime($post['date_pub']) - ( current_time( 'timestamp', 1 ) - current_time( 'timestamp', 0 ) );
                                $url_perfil = home_url('/perfil/').$user_id.'-'.sanitize_title($post['name']);
                                ?>
                                <div class="date-pub"><?php echo ucfirst( strftime("%d de %B às %H:%M", $hora_local ) ); ?></div>
                                <div class="content-pub"><?php echo $post['content']; ?></div>
                                <div class="actions-pub">
                                    <div class="like-pub"><div class="<?php echo empty($curtir) ? 'far' : 'fas'; ?> fa-heart"></div>  Gostei <span class="qtd_likes">(<?php echo $post['likes']; ?>)</span> <input type="hidden" name="id_pub" value="<?php echo $post['ID']; ?>"></div>
                                    
                                    <div class="dropdown">
                                        <div class="share-pub" id="dropdownCompartilhar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Compartilhar   <div class="fa fa-share"></div>
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
                }*/
            ?>
            </div>

            <div id="videos_publicacoes">
            <?php
                /*if ( ! empty($sql_video) ) {
                    foreach ($sql_video as $post) {
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
                                }

                                $curtir = $wpdb->get_var(
                                                    "SELECT ID FROM wp_ht_likes WHERE user_ip = '".$_SERVER['REMOTE_ADDR']."' AND publication_id = ".$post['ID']
                                                );

                            ?>
                            <div class="bot-pub">
                                <?php
                                $hora_local = strtotime($post['date_pub']) - ( current_time( 'timestamp', 1 ) - current_time( 'timestamp', 0 ) );
                                $url_perfil = home_url('/perfil/').$user_id.'-'.sanitize_title($post['name']);
                                ?>
                                <div class="date-pub"><?php echo ucfirst( strftime("%d de %B às %H:%M", $hora_local ) ); ?></div>
                                <div class="content-pub"><?php echo $post['content']; ?></div>
                                <div class="actions-pub">
                                    <div class="like-pub"><div class="<?php echo empty($curtir) ? 'far' : 'fas'; ?> fa-heart"></div>  Gostei <span class="qtd_likes">(<?php echo $post['likes']; ?>)</span> <input type="hidden" name="id_pub" value="<?php echo $post['ID']; ?>"></div>
                                    
                                    <div class="dropdown">
                                        <div class="share-pub" id="dropdownCompartilhar" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                            Compartilhar   <div class="fa fa-share"></div>
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
                }*/
            ?>
            </div>

        </div>

        <div class="btn-padrao btn-nova-pesquisa">
            <a href="<?php echo home_url(); ?>">
                Fazer outra pesquisa  <i class="fas fa-map-marker-alt"></i>
            </a>
        </div>
    <?php
    //}
    ?>
</div>

<input type="hidden" name="user_id" value="<?php echo get_user_id(); ?>">
<input type="hidden" name="ajaxurl" value="<?php echo home_url(); ?>/wp-admin/admin-ajax.php">

<script>
    jQuery(document).ready( function() {
    
        jQuery(".box-ultimas-publicacoes").on("click", ".like-pub", function() {  
            var thisIconHeart = jQuery(this).find('.fa-heart');
              jQuery.ajax({
                type:"POST",
                url: jQuery('input[name="ajaxurl"]').val(),
                dataType: 'json',
                data: {
                    action: "curtirPub",
                    id_pub: jQuery('input[name="id_pub"]', this).val()                    
                },
                success:function(data){
                    if ( data[0] == 1 ) {
                        thisIconHeart.removeClass('far');                        
                        thisIconHeart.addClass('fas');
                    } else if ( data[0] == 2 ) {
                        thisIconHeart.removeClass('fas');                        
                        thisIconHeart.addClass('far');
                    }    
                    
                    thisIconHeart.parent().find('.qtd_likes').text('('+data[1]+')');
                }
            });
        });              

        var verificaQtdRequisicao = 0; //faz com que o browser faÃ§a somente uma requisiÃ§Ã£o ajax por vez no scroll
        var semPub = 0; //verifica se nÃ£o hÃ¡ mais publicaÃ§Ãµes para serem visualizadas
        var semPubTudo = 0;
        var semPubImage = 0;
        var semPubVideo = 0;
        var qtdPub = 6; //quantidade de publicaÃ§Ãµes a serem exibidas por vez
        var offset = 0;
        var offsetTudo = 0;
        var offsetImage = 0;
        var offsetVideo = 0;

        var tipo = 'tudo';

        jQuery('.box-tipo-pub > div').click( function() {
            var escalaImagem = 1147 / 87;
            if ( jQuery(this).text().trim() == "Todas" && tipo != 'tudo' ) {
                tipo = 'tudo';

                jQuery('.box-tipo-pub > div').each( function() {
                    jQuery(this).removeClass('btn-active');
                });
                jQuery(this).addClass('btn-active');            

                jQuery('#fotos_publicacoes').hide();
                jQuery('#videos_publicacoes').hide();
                jQuery('#todas_publicacoes').show();

                var heightPubVazio = jQuery('#todas_publicacoes .pubVazio').width() / escalaImagem;
                jQuery('#todas_publicacoes .pubVazio').css('height', heightPubVazio );

                offset = offsetTudo;
                semPub = semPubTudo;
            } else if ( jQuery(this).text().trim() == "Fotos" && tipo != 'image' ) {
                tipo = 'image';

                jQuery('.box-tipo-pub > div').each( function() {
                    jQuery(this).removeClass('btn-active');
                });
                jQuery(this).addClass('btn-active');

                jQuery('#videos_publicacoes').hide();
                jQuery('#todas_publicacoes').hide();
                jQuery('#fotos_publicacoes').show();

                var heightPubVazio = jQuery('#fotos_publicacoes .pubVazio').width() / escalaImagem;
                jQuery('#fotos_publicacoes .pubVazio').css('height', heightPubVazio );            

                offset = offsetImage;
                semPub = semPubImage;
            } else if ( jQuery(this).text().trim() == "Vídeos" && tipo != 'video' ) {
                tipo = 'video';

                jQuery('.box-tipo-pub > div').each( function() {
                    jQuery(this).removeClass('btn-active');
                });
                jQuery(this).addClass('btn-active');            

                jQuery('#todas_publicacoes').hide();
                jQuery('#fotos_publicacoes').hide();
                jQuery('#videos_publicacoes').show();

                var heightPubVazio = jQuery('#videos_publicacoes .pubVazio').width() / escalaImagem;
                jQuery('#videos_publicacoes .pubVazio').css('height', heightPubVazio );

                offset = offsetVideo;
                semPub = semPubVideo;
            }
        });  
        
        jQuery(document).scroll(function() {            
            var bottomDiv = jQuery('.box-ultimas-publicacoes').height() + jQuery('.box-ultimas-publicacoes').offset().top;
            var bottomWindow = jQuery(window).height() + jQuery(window).scrollTop();
            
            if (bottomWindow > bottomDiv && verificaQtdRequisicao == 0 && semPub == 0) {
                verificaQtdRequisicao = 1;
                
                var data = new Date();

                tempoGMT = data.getTime();
                tempoLocal = tempoGMT - data.getTimezoneOffset()*60*1000;

                jQuery.ajax({
                    type:'POST',
                    url:jQuery('input[name="ajaxurl"]').val(),                     
                    data: {
                        action: "scrollInfinitoPerfil",
                        limit: qtdPub,
                        offset: offset,
                        tipo: tipo,
                        user_id: jQuery('input[name="user_id"]').val(),
                        tempoGMT: tempoGMT,
                        tempoLocal: tempoLocal
                    },
                    success: function(data){
                        verificaQtdRequisicao = 0;
                        offset = offset + qtdPub;

                        if ( tipo == 'tudo' ) {
                            jQuery('#todas_publicacoes').append(data);
                            offsetTudo = offset;
                        } else if ( tipo == 'image' ) {
                            jQuery('#fotos_publicacoes').append(data);
                            offsetImage = offset;
                        } else if ( tipo == 'video' ) {
                            jQuery('#videos_publicacoes').append(data);
                            offsetVideo = offset;
                        }
                        
                        if (data == '') {
                            var escalaImagem = 1147 / 87;
                            if ( tipo == 'tudo' ) {
                                jQuery('#todas_publicacoes').append('<div class="pubVazio"></div>');
                                var heightPubVazio = jQuery('#todas_publicacoes .pubVazio').width() / escalaImagem;
                                jQuery('#todas_publicacoes .pubVazio').css('height', heightPubVazio );
                                semPubTudo = 1; 
                            } else if ( tipo == 'image' ) {
                                jQuery('#fotos_publicacoes').append('<div class="pubVazio"></div>');
                                var heightPubVazio = jQuery('#fotos_publicacoes .pubVazio').width() / escalaImagem;
                                jQuery('#fotos_publicacoes .pubVazio').css('height', heightPubVazio );
                                semPubImage = 1; 
                            } else if ( tipo == 'video' ) {
                                jQuery('#videos_publicacoes').append('<div class="pubVazio"></div>');
                                var heightPubVazio = jQuery('#videos_publicacoes .pubVazio').width() / escalaImagem;
                                jQuery('#videos_publicacoes .pubVazio').css('height', heightPubVazio );
                                semPubVideo = 1; 
                            }                        
                            semPub = 1;                                                               
                        }
                    },
                    error: function(error){
                        console.log('erro');
                        verificaQtdRequisicao = 0;
                    }
                });
            }
        }); 
    });   
    
    window.onload = function() {
        
    } 
</script>