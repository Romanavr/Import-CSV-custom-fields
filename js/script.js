jQuery(document).ready(function() {
    function uploadCsv() {
        var $form = jQuery("#import-csv");
        var formData = new FormData($form[0]);
        var output_result = document.getElementById('import-csv-result');

        jQuery.ajax({
            type: $form.attr('method'),
            url: $form.attr('action'),
            data: formData,
            processData: false,
            contentType: false
        }).done(function(response) {
            console.log(response);
            var result = jQuery.parseJSON(response);
            if(result.error) {
                output_result.innerHTML = '<strong>Ошибка: </strong>' + result.error;
            } else {
                output_result.innerHTML = '<strong>' + result.success + '<strong';
            }

        }).fail(function() {
            console.log('fail');
        });
    }

    jQuery('#import-csv').on('submit', function(e) {
        e.preventDefault();
        uploadCsv();
    });
});