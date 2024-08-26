<?php
/*
Plugin Name: Renamer Image SEO
Description: Renomeia as imagens enviadas para incluir o nome e a descrição do site, além de otimizar o texto alternativo.
Version: 0.1.6
Author: Bruno A
*/

function custom_image_renamer($file) {
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    
    // Sanitiza e cria slugs para o título e a descrição do site
    $site_name_slug = sanitize_title($site_name);
    $site_description_slug = sanitize_title($site_description);
    
    $info = pathinfo($file['name']);
    $ext = empty($info['extension']) ? '' : '.' . $info['extension'];
    $name = basename($file['name'], $ext);

    // Armazena o nome original do arquivo (sem formatação)
    $original_name = $name;

    // Remove acentos e caracteres especiais do nome do arquivo para otimização SEO
    $name = remove_accents($name);

    // Remove caracteres não otimizados para SEO e converte para minúsculas
    $name = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    $name = sanitize_title($name);

    // Adiciona o nome e a descrição do site ao final do nome do arquivo
    $new_name = $name . '-' . $site_name_slug . '-' . $site_description_slug . $ext;
    $file['name'] = $new_name;

    // Armazena o nome original na sessão para uso na função custom_image_alt_text
    session_start();
    $_SESSION['original_name'] = $original_name;
    
    return $file;
}
add_filter('wp_handle_upload_prefilter', 'custom_image_renamer');

function custom_image_alt_text($post_ID) {
    $post = get_post($post_ID);

    // Recupera o nome original do arquivo da sessão
    session_start();
    if (!isset($_SESSION['original_name'])) {
        return;
    }
    $original_name = $_SESSION['original_name'];
    unset($_SESSION['original_name']); // Limpa a variável de sessão após o uso

    // Adiciona o nome do site e a descrição ao alt text
    $site_name = get_bloginfo('name');
    $site_description = get_bloginfo('description');
    $alt_text = $original_name . ' - ' . $site_name . ' - ' . $site_description;
    
    // Atualiza o alt text da imagem
    update_post_meta($post_ID, '_wp_attachment_image_alt', $alt_text);
    wp_update_post(array(
        'ID' => $post_ID,
        'post_title' => $alt_text,
    ));
}
add_action('add_attachment', 'custom_image_alt_text');
