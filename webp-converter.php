<?php
/**
 * Plugin Name: WebP Only Uploads
 * Plugin URI: https://github.com/sharafiev/webp-only-uploads
 * Description: Конвертирует все загружаемые изображения в WebP и удаляет оригиналы. Поддержка настроек качества и массовой конверсии.
 * Version: 1.3
 * Author: Ramil Sharafiev
 * Author URI: https://github.com/sharafiev
 * License: GPLv2 or later
 * License URI: https://www.gnu.org/licenses/gpl-2.0.html
 *
 * GitHub Plugin URI: https://github.com/sharafiev/webp-only-uploads
 * Primary Branch: main
 */

add_filter('wp_handle_upload', 'convert_image_to_webp');
add_filter('wp_generate_attachment_metadata', 'convert_attachment_images_to_webp', 10, 2);
add_action('admin_menu', 'webp_plugin_menu');
add_action('admin_init', 'webp_plugin_settings');

function convert_image_to_webp($upload) {
    $file_path = $upload['file'];
    $file_type = mime_content_type($file_path);

    if (!in_array($file_type, ['image/jpeg', 'image/png'])) {
        return $upload;
    }

    $image = null;

    if ($file_type === 'image/jpeg') {
        $image = imagecreatefromjpeg($file_path);
    } elseif ($file_type === 'image/png') {
        $image = imagecreatefrompng($file_path);
        imagepalettetotruecolor($image);
        imagealphablending($image, true);
        imagesavealpha($image, true);
    }

    if ($image) {
        $quality = get_option('webp_quality', 80);
        $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file_path);
        imagewebp($image, $webp_path, $quality);
        imagedestroy($image);
        unlink($file_path);
        $upload['file'] = $webp_path;
        $upload['url'] = preg_replace('/\.(jpe?g|png)$/i', '.webp', $upload['url']);
        $upload['type'] = 'image/webp';
    }

    return $upload;
}

function convert_attachment_images_to_webp($metadata, $attachment_id) {
    $upload_dir = wp_upload_dir();
    $base_dir = trailingslashit($upload_dir['basedir']);
    $file = get_attached_file($attachment_id);

    if (!isset($metadata['sizes'])) {
        return $metadata;
    }

    foreach ($metadata['sizes'] as $size => $data) {
        $thumb_path = $base_dir . $data['file'];
        $file_type = mime_content_type($thumb_path);

        if (!in_array($file_type, ['image/jpeg', 'image/png'])) {
            continue;
        }

        $image = null;

        if ($file_type === 'image/jpeg') {
            $image = imagecreatefromjpeg($thumb_path);
        } elseif ($file_type === 'image/png') {
            $image = imagecreatefrompng($thumb_path);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        if ($image) {
            $quality = get_option('webp_quality', 80);
            $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $thumb_path);
            imagewebp($image, $webp_path, $quality);
            imagedestroy($image);
            unlink($thumb_path);
            $metadata['sizes'][$size]['file'] = basename($webp_path);
        }
    }

    update_attached_file($attachment_id, preg_replace('/\.(jpe?g|png)$/i', '.webp', $file));
    return $metadata;
}

function webp_plugin_menu() {
    add_options_page(
        'WebP Upload Settings',
        'WebP Upload',
        'manage_options',
        'webp-upload',
        'webp_plugin_settings_page'
    );
}

function webp_plugin_settings() {
    register_setting('webp-settings-group', 'webp_quality');
    add_settings_section('webp-main', 'Основные настройки', null, 'webp-upload');
    add_settings_field('webp_quality', 'Качество WebP (1–100)', 'webp_quality_field', 'webp-upload', 'webp-main');
}

function webp_quality_field() {
    $value = get_option('webp_quality', 80);
    echo '<input type="number" name="webp_quality" value="' . esc_attr($value) . '" min="1" max="100" />';
}

function webp_plugin_settings_page() {
    echo '<div class="wrap">';
    echo '<h1>Настройки WebP Upload</h1>';
    echo '<form method="post" action="options.php">';
    settings_fields('webp-settings-group');
    do_settings_sections('webp-upload');
    submit_button();
    echo '</form></div>';
}


add_action('admin_menu', 'webp_bulk_convert_menu');

function webp_bulk_convert_menu() {
    add_submenu_page(
        'webp-upload',
        'Массовая конвертация',
        'Массовая конвертация',
        'manage_options',
        'webp-bulk-convert',
        'webp_bulk_convert_page'
    );
}

function webp_bulk_convert_page() {
    if (isset($_POST['convert_all_images'])) {
        webp_convert_all_attachments();
        echo '<div class="updated"><p>Массовая конвертация завершена!</p></div>';
    }

    echo '<div class="wrap"><h1>Массовая конвертация изображений</h1>';
    echo '<form method="post">';
    echo '<p>Нажмите кнопку, чтобы конвертировать все существующие изображения в WebP. JPEG/PNG будут удалены, пути сохранятся.</p>';
    echo '<input type="submit" name="convert_all_images" class="button button-primary" value="Конвертировать все изображения" />';
    echo '</form></div>';
}

function webp_convert_all_attachments() {
    $args = array(
        'post_type'      => 'attachment',
        'post_mime_type' => array('image/jpeg', 'image/png'),
        'post_status'    => 'inherit',
        'numberposts'    => -1
    );

    $attachments = get_posts($args);
    $quality = get_option('webp_quality', 80);

    foreach ($attachments as $attachment) {
        $file = get_attached_file($attachment->ID);
        $type = mime_content_type($file);

        if (!in_array($type, ['image/jpeg', 'image/png']) || !file_exists($file)) continue;

        $image = null;

        if ($type === 'image/jpeg') {
            $image = imagecreatefromjpeg($file);
        } elseif ($type === 'image/png') {
            $image = imagecreatefrompng($file);
            imagepalettetotruecolor($image);
            imagealphablending($image, true);
            imagesavealpha($image, true);
        }

        if ($image) {
            $webp_path = preg_replace('/\.(jpe?g|png)$/i', '.webp', $file);
            imagewebp($image, $webp_path, $quality);
            imagedestroy($image);
            unlink($file);
            update_attached_file($attachment->ID, $webp_path);
        }

        $metadata = wp_generate_attachment_metadata($attachment->ID, $webp_path);
        wp_update_attachment_metadata($attachment->ID, $metadata);
    }
}
