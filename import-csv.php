<?php
/*
 * Plugin Name: Импорт CSV контента с кастомными полями
 * Description: <strong>Импорт данных из .csv файлов</strong>
 * Version: 1.0
 */

define( 'IMPORT_CSV_DIR', plugin_dir_path( __FILE__ ) );
define( 'IMPORT_CSV_URL', plugin_dir_url( __FILE__ ) );

add_action('admin_menu', 'import_csv_menu' );
function import_csv_menu() {
    add_menu_page('Импорт данных в .csv', 'Import CSV', 'manage_options', 'import-csv/import-csv-admin.php', '', 'dashicons-edit' );
}

add_action( 'admin_head', 'change_color_admin_bar' );
function change_color_admin_bar(){
    echo '<style>#wpadminbar{background-color: #0073aa;}</style>'; // выводим стили
}

function import_csv_include() {
    echo '<link rel="stylesheet" href="'.IMPORT_CSV_URL.'css/style.css" type="text/css" />'.PHP_EOL;
    echo '<script src="'.IMPORT_CSV_URL.'js/script.js" type="text/javascript"></script>'.PHP_EOL;
}

function import_csv_js() {
    check_admin_referer( 'import_csv', 'nonce' ); // Security check
    $result = array();

    $input_file = $_FILES['csv-file']; // Входящий файл
    if($input_file['name']) {
        $name = explode(".", $input_file['name']);
        $exp = end($name);
        $upload_file = IMPORT_CSV_DIR . "/tmp/" . $input_file['name'];
        $delimiter = $_POST['delimiter'];
        switch($delimiter) {
            case 'comma':
                $delimiter = ',';
                break;
            case 'semicolon':
                $delimiter = ';';
                break;
            case 'spacebar':
                $delimiter = " ";
                break;
            case 'tab':
                $delimiter = "\t";
                break;
            default:
                $result['error'] = "Неверный разделитель";
                print json_encode($result);
                die();
        }

        if($exp == "csv") { // Принимаем только .csv файлы
            move_uploaded_file($input_file['tmp_name'], $upload_file);

            $rows = array_filter(explode("\n",file_get_contents($upload_file)));
            $custom_fields = str_getcsv(array_shift($rows),$delimiter);
            $post_parent = intval($_POST['post_parent']) ? $_POST['post_parent'] : 0 ;
            $post_template = basename($_POST['template']);
            $args = [
                'post_type'     => 'page',
                'post_parent'   => $post_parent,
                'post_status'   => 'publish',
            ];

            foreach($rows as $row) {
                $columns = str_getcsv($row, $delimiter);
                $thumbnail = '';
                foreach($columns as $i=>$field_value) { // $i - номер столбца
                     $field_key = $custom_fields[$i];
                    switch($field_key) {
                        case "post_title":
                            $args['post_title'] = $field_value;
                            break;
                        case "post_name":
                            $args['post_name'] = $field_value;
                            break;
                        case "post_thumbnail":
                            $thumbnail = $field_value;
                            break;
                        default:
                            $args['meta_input'][$field_key] = $field_value;
                            break;
                    }
                }
                if($post_template != 'default') {
                    $args['meta_input']['_wp_page_template'] = $post_template;
                }

                if($thumbnail) {
                    $post_id = wp_insert_post($args);

                    $upload_dir = wp_upload_dir();
                    $file = $upload_dir['basedir'] . "/" . $thumbnail;
                    $filetype = wp_check_filetype( basename( $file ), null );
                    $filename = basename($file);

                    $attachment = array(
                        'post_mime_type' => $filetype['type'],
                        'post_title'     => sanitize_file_name($filename),
                        'post_content'   => '',
                        'post_status'    => 'inherit'
                    );
                    $attach_id = wp_insert_attachment( $attachment, $file, $post_id );
                    require_once(ABSPATH . 'wp-admin/includes/image.php'); // Если нужно, чтобы работало во фронте
                    $attach_data = wp_generate_attachment_metadata( $attach_id, $file );
                    wp_update_attachment_metadata( $attach_id, $attach_data );

                    set_post_thumbnail($post_id, $attach_id);
                    $result['success'] = "Импотрировано строк и картинок: " . count($rows);
                } else {
                    $post_id = wp_insert_post($args);
                    $result['success'] = "Импотрировано строк: " . count($rows);
                }
            }
        }
        else {
            $result['error'] = "Допускаются только .csv файлы";
        }
    } else {
        $result['error'] = "Не выбран файл";
    }

    print json_encode($result);

    die();
}
add_action( 'wp_ajax_import_csv', 'import_csv_js' );
