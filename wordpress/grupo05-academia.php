<?php
/**
 * Plugin Name: Grupo 05 Academia
 * Description: Identificação e conteúdo inicial do blog da academia.
 */
declare(strict_types=1);

function grupo05_configurar_blog(): void
{
    update_option('blogname', 'Grupo 05 — Academia');
    update_option('blogdescription', 'Planos e orientações da academia');
    update_option('timezone_string', 'America/Fortaleza');

    $slug = 'planos-da-academia';
    if (get_page_by_path($slug, OBJECT, 'post')) {
        return;
    }

    $post = [
        'post_title' => 'Conheça os planos da academia',
        'post_name' => $slug,
        'post_type' => 'post',
        'post_status' => 'publish',
        'comment_status' => 'closed',
        'ping_status' => 'closed',
        'post_content' => '<p>O Grupo 05 oferece três opções de cadastro para a demonstração da academia. Escolha o plano que combina com sua rotina.</p>'
            . '<ul><li><strong>Mensal</strong> – inscrição por um mês.</li>'
            . '<li><strong>Trimestral</strong> – inscrição por três meses.</li>'
            . '<li><strong>Anual</strong> – inscrição por doze meses.</li></ul>'
            . '<p>Acesse o <a href="http://academia.local:8005/">cadastro de alunos</a> para registrar o aluno e o plano escolhido.</p>',
    ];
    $existente = get_page_by_path('hello-world', OBJECT, 'post') ?: get_page_by_path('ola-mundo', OBJECT, 'post');
    if ($existente) {
        $post['ID'] = $existente->ID;
        wp_update_post($post);
        return;
    }

    $administradores = get_users(['role' => 'administrator', 'fields' => 'ID', 'number' => 1]);
    $post['post_author'] = (int) ($administradores[0] ?? 0);
    wp_insert_post($post);
}
add_action('wp_install', 'grupo05_configurar_blog');

add_action('wp_footer', static function (): void {
    $agora = wp_date('d/m/Y H:i:s', null, new DateTimeZone('America/Fortaleza'));
    echo '<aside aria-label="Identificação da evidência" style="position:fixed;z-index:9;bottom:0;'
        . 'right:0;margin:0;padding:8px 14px;border-radius:10px 0 0 0;background:#122135;'
        . 'color:#fff;font:14px/1.5 system-ui,sans-serif">'
        . '<strong>Grupo 05 — Academia</strong> · ' . esc_html($agora) . ' BRT'
        . '</aside>';
});
