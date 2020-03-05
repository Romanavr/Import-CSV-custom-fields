<?php
    import_csv_include();
    wp_nonce_field( basename(__FILE__), $nonce_key );
?>

<div class="wrap">
    <h2>Импорт кастомных полей в .csv формате</h2>
    <div class="import-csv-instructions">
        <ol>
            <li>Преобразовать файл в .csv формат, в 1-й строке прописать названия кастомных полей</li>
            <li>Выбрать файл, который нужно импортировать</li>
            <li>Выбрать разделитель, лучше использовать табуляцию ("\t"), т.к почти нигде не задействована</li>
            <li>Выбрать шаблон страницы(page), по умолчанию будет использован основной шаблон для страницы</li>
            <li>Прописать ID родительской страницы(категории), опционально</li>
        </ol>
        <strong>Следующие поля 1-й строки будут преобразованы, как:</strong>
        <ul>
            <li><strong>post_title</strong> — название страницы</li>
            <li><strong>post_name</strong> — УРЛ(алиас) страницы</li>
            <li><strong>post_thumbnail</strong> — миниатюра страницы, нужно указать путь (выделен красным для примера), файлы загрузжать в wp-content/uploads/<strong style="color: red">nazvanie_papki/picture.jpg</strong></li>
        </ul>
    </div>
    <form id="import-csv" method="post" action="<?php echo admin_url('admin-ajax.php'); ?>" enctype="multipart/form-data">
        <div class="form-wrap">
            <div class="import-csv-input">
                <label for="csv-file">
                    <strong>Выберите файл</strong>
                </label>
                <input name="csv-file" id="csv-file" type="file" accept=".csv">
            </div>
            <div class="import-csv-input">
                <label for="delimiter">
                    Выберите разделитель
                </label>
                <select name="delimiter" id="delimiter">
                    <option value="tab" selected>Табуляция</option>
                    <option value='comma'>Запятая ( , )</option>
                    <option value="semicolon">Точка с запятой ( ; )</option>
                    <option value="spacebar">Пробел</option>
                </select>
            </div>

            <div class="import-csv-input">
                <label for="template">
                    Выберите шаблон
                </label>
                <select name="template" id="template">
                    <option value="default" selected>По умолчанию</option>
                    <?php
                        $custom_templates = get_page_templates();
                        if($custom_templates) {
                            foreach($custom_templates as $k=>$template) : ?>
                                <option value="<?php echo $template; ?>"><?php echo $k; ?> (<?php echo $template; ?>)</option>
                            <?php endforeach;
                        }
                    ?>
                </select>
            </div>

            <div class="import-csv-input">
                <label for="post_parent">
                    ID родительской страницы (при необходимости)
                </label>
                <input type="text" name="post_parent" id="post_parent" placeholder="Введите число">
            </div>

            <div class="import-csv-input">
                <input type="submit" value="Импортировать" class="button button-primary">
            </div>
            <input type="hidden" name="action" value="import_csv">
            <?php wp_nonce_field( 'import_csv','nonce' ); ?>
        </div
    </form>
    <div id="import-csv-result"></div>
</div>
