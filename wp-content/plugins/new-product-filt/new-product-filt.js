jQuery(document).ready(function($) {



    $("#new_prod_filt_form").submit(function(event) {

        $("#new_prod_filt_form").find(".filter-fild-req").each(function() {
            if ($(this).val() != '') {
                $(this).removeClass('err');
                $('#form_mass').hide();
            } else {
                $(this).addClass('err');
                $('#form_mass').show();
            }

        });


        $("#new_prod_filt_form").find(".filter-fild-req.err").each(function() {
            $('#form_mass').show();

        });
        if ($('#form_mass').is(':visible')) {
            return false;
        }
    });
});