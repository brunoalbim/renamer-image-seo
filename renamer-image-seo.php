<?php
/*
Plugin Name: Renamer Image SEO
Description: Renomeia as imagens enviadas para incluir o nome do site e otimiza o texto alternativo.
Version: 0.1.3
Author: Bruno A
*/

function custom_image_renamer($file) {
    $site_name = get_bloginfo('name');
    $site_name_slug = sanitize_title($site_name);
    
    $info = pathinfo($file['name']);
    $ext = empty($info['extension']) ? '' : '.' . $info['extension'];
    $name = basename($file['name'], $ext);

    // Armazena o nome original do arquivo em uma variável temporária para uso posterior
    $original_name = $name;

    // Remove caracteres não otimizados para SEO, exceto números, convertendo para minúsculas e substituindo espaços por hífens
    $name = strtolower(preg_replace('/[^a-zA-Z0-9\s]/', '', $name));
    $name = sanitize_title($name);

    // Adiciona o nome do site ao final do nome do arquivo
    $new_name = $name . '-' . $site_name_slug . $ext;
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

    // Adiciona o nome do site ao alt text
    $site_name = get_bloginfo('name');
    $alt_text = $original_name . ' - ' . $site_name;
    
    // Atualiza o alt text da imagem
    update_post_meta($post_ID, '_wp_attachment_image_alt', $alt_text);
    wp_update_post(array(
        'ID' => $post_ID,
        'post_title' => $alt_text,
    ));
}
add_action('add_attachment', 'custom_image_alt_text');
