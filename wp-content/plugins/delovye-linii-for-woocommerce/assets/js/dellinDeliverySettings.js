
jQuery(function(){
    console.log('admin_init');
    dellinVars.langVars = JSON.parse(dellinVars.langVars);
    init();
});

function init() {
    if (getUrlParameter('cid') !== '0') {
        showLoading();
        jQuery.when(getCounteragents(), getOpf(),getRequestTerminalsDerival()).done(function(){
            hideLoading();
        });
    }

    // jQuery('[data-type="ajax_select"]').change(function(event){
    //     jQuery('#params_'+jQuery(this).data('prop_code')).val(jQuery(this).val());
    // });
    jQuery('#woocommerce_dellin_shipping_calc_opf_country').change(function(event){
        getOpf();
    });
    console.log(dellinVars.langVars);
    var findCityKladrBtn = jQuery('<input>',{
        class:'findCityKladr btn btn-primary button-apply btn-success',
        type:'button',
        value: dellinVars.langVars.WC_DELLIN_SHIPPING_FIND_KLADR_CITY_BUTTON,
        on:
            {
                click:function(event){
                    createFindKladrPopup(jQuery(this),'search_city',10);
                }
            }
    });
    var findStreetKladrBtn = jQuery('<input>',{
        class:'findStreetKladr btn btn-primary button-apply btn-success',
        type:'button',
        value: dellinVars.langVars.WC_DELLIN_SHIPPING_FIND_KLADR_STREET_BUTTON,
        on:
            {
                click:function(event){
                    createFindKladrPopup(jQuery(this),'search_street',10);
                }
            }
    });
    jQuery(findCityKladrBtn).insertAfter(jQuery('.cityKladrInput'));
    jQuery(findStreetKladrBtn).insertAfter(jQuery('.streetKladrInput'));

    jQuery('#woocommerce_dellin_shipping_calc_login,#woocommerce_dellin_shipping_calc_password').change(function () {
        getCounteragents(true);
    });
    jQuery('#woocommerce_dellin_shipping_calc_appkey').change(function () {
        getCounteragents(true);
        checkCounteragent();
        getOpf();
        getRequestTerminalsDerival();
    });
    jQuery('#woocommerce_dellin_shipping_calc_kladr_code_delivery_from').change(function(){
        getRequestTerminalsDerival();
    });
}
function showLoading(){
    hideLoading();
    jQuery('#mainform').append('<div class="loading-wrap"></div>');
    jQuery('#mainform').append('<div class="loading"><span class="loading-content"><img src="'+dellinVars.spinnerSrc+'"> '+dellinVars.langVars.WC_DELLIN_SHIPPING_PROCESSING+'</span></div>');
}
function hideLoading(){
    jQuery('.loading-wrap').remove();
    jQuery('.loading').remove();
}

function getUrlParameter(name){
    name = name.replace(/[\[]/, '\\[').replace(/[\]]/, '\\]');
    var regex = new RegExp('[\?&]cid.+?=([^&#]*)');
    var results = regex.exec(location.search);
    return results === null ? '' : decodeURIComponent(results[1].replace(/\+/g, ' '));
};

function validation(input=false){
    if(!input){
        jQuery('input.error').removeClass('error');
        var iserror = false;
        jQuery('[required]').each(function(){
            if(jQuery(this).val() !== null && jQuery(this).val().length == 0){
                iserror = true;
                console.log(this);
                jQuery(this).addClass('error');
            }
        });
        if(!checkCounteragent()){
            iserror = true;
        }
    }
    else{
        iserror = false;
        if(jQuery(input).val() !== null && jQuery(input).val().length == 0){
            iserror = true;
            if(!jQuery(input).hasClass('error')) jQuery(input).addClass('error');
        }else{
            if(jQuery(input).hasClass('error')) jQuery(input).removeClass('error');
        }
        if(!checkCounteragent()){
            iserror = true;
        }
    }
    console.log(iserror);
    if(iserror){
        return false;
    }
    return true;
}

function getCounteragents(changeSettings){
    var apikey = jQuery('#woocommerce_dellin_shipping_calc_appkey').val();
    var login = jQuery('#woocommerce_dellin_shipping_calc_login').val();
    var password = jQuery('#woocommerce_dellin_shipping_calc_password').val();
    showLoading();
    var data = {'ajax':'y','action':'get_counteragents','login':login,'password':password, 'appkey':apikey,'reset_session':changeSettings};
    return jQuery.ajax({
        url:dellinVars.url,
        data: data,
        method: "POST",
        dataType: "json"
    }).done(function(response){
        var options = '';
        var select = jQuery('#woocommerce_dellin_shipping_calc_counteragent');
        if(undefined != response['error'] && jQuery(select).closest('tr').find('.error').length == 0){
            jQuery(select).closest('tr').find('.titledesc label').append('<span class="error">'+response['error']+'</span>')
        }
        if(null !== response.counteragents){
            console.log(response.count);
            console.log(response.count > 1);
            if(response.count > 1){
                jQuery(select).closest('tr').find('.error').remove();
            }
            jQuery.each(response.counteragents,function(id,value){
                var selected = '';
                if((null !== jQuery(select).val() &&  id == jQuery(select).val()) || (null == jQuery(select).val() && id == jQuery(select).data('selected_value')) ){
                    selected = "selected";
                }
                options +='<option '+selected+' value="'+id+'">'+value.name+'</option>';
            });
        }
        if(select.length > 0) {
            jQuery(select).html(options);
        }
        checkCounteragent();
        hideLoading();
    });
}

function checkCounteragent(){
    if(jQuery('#woocommerce_dellin_shipping_calc_login').val().length > 0 && jQuery('#woocommerce_dellin_shipping_calc_password').val().length > 0 && jQuery('#woocommerce_dellin_shipping_calc_appkey').val().length > 0){
        jQuery('#woocommerce_dellin_shipping_calc_counteragent').closest('tr').show();
        return true;
    }else{
        jQuery('#woocommerce_dellin_shipping_calc_counteragent').closest('tr').hide();
        return false;
    }
}


function getOpf(){

    showLoading();
    var apikey = jQuery('#woocommerce_dellin_shipping_calc_appkey').val();
    var data = {'ajax':'y','action':'get_opf','appkey':apikey};
    return jQuery.ajax({
        url:dellinVars.url,
        data: data,
        method: "POST",
        dataType: "json"
    }).done(function(response){

        //страна ОПФ--------------------------------------------------------------
        var select = jQuery('#woocommerce_dellin_shipping_calc_opf_country');
        if(undefined != response['error'] && jQuery(select).closest('tr').find('.error').length == 0){
            jQuery(select).closest('tr').find('.titledesc label').append('<span class="error">'+response['error']+'</span>')
        }
        var options = getSelectOptions(response.countries, select);
        if(select.length > 0) {
            jQuery(select).html(options);
        }
        //-------------------------------------------------------------------------------------------------------

        //ОПФ----------------------------------------------------------------------------------------------------
        var opfList;
        var select = jQuery('#woocommerce_dellin_shipping_calc_sender_form');
        if(undefined != response['error'] && jQuery(select).closest('tr').find('.error').length == 0){
            jQuery(select).closest('tr').find('.titledesc label').append('<span class="error">'+response['error']+'</span>')
        }
        if(response.countries !== null && response.countries !== undefined){
            opfList = response.opf[jQuery('#woocommerce_dellin_shipping_calc_opf_country').val()];
        }else{
            if( jQuery(select).closest('tr').find('.error').length == 0){
                jQuery(select).closest('tr').find('.titledesc label').append('<span class="error">'+response['error']+'</span>')
            }

        }
        if(jQuery(opfList).length > 0){
            var options = getSelectOptions(opfList,select);
        }
        if(select.length > 0) {
            jQuery(select).html(options);
        }
        hideLoading();
    });

}

function getRequestTerminalsDerival(){
    var kladr = jQuery('#woocommerce_dellin_shipping_calc_kladr_code_delivery_from').val();
    var apikey = jQuery('#woocommerce_dellin_shipping_calc_appkey').val();
    showLoading();
    return jQuery.ajax({
        url:dellinVars.url,
        data: {'ajax':'y','action':'get_terminals','mode':'request_terminals','kladr':kladr, 'appkey':apikey},
        method: "POST",
        dataType: "json"
    }).done(function(response){
        var select = jQuery('#woocommerce_dellin_shipping_calc_terminal_id');
        if(undefined != response['error'] && jQuery(select).closest('tr').find('.error').length == 0){
            jQuery(select).closest('tr').find('.titledesc label').append('<span class="error">'+response['error']+'</span>')
        }
        var options = getSelectOptions(response.terminals,select);

        if(select.length > 0) {
            jQuery(select).html(options);
        }
        hideLoading();
    });
}

function getSelectOptions(data,select){
    var options = "";
    jQuery.each(data,function(id,name){
        var selected = '';
        if((null !== jQuery(select).val() &&  id == jQuery(select).val()) || (null == jQuery(select).val() && id == jQuery(select).data('selected_value'))){
            selected = "selected";
        }
        options += '<option '+selected+' value="'+id+'">'+name+'</option>>';
    });
    return options;
}

function createFindKladrPopup(element,ajaxAction,limit){
    removeThisPopup(jQuery('.kladrDiv.popup-container'));
    var msg = dellinVars.langVars.WC_DELLIN_SHIPPING_SEARCH_MSG;
    var popup = jQuery('<div>',{
        class: 'kladrDiv popup-container',
        html:
            jQuery('<span>',{
                html:msg+'<br/>'
            }),
    });
    var kladrInput =  jQuery('<input>',{
        type:'text',
        name:'query',
        class:'kladr_autocomplete',
        on:{

            keyup:function(event){
                var bannedInputs = [16,17,18,20,33,34,35,36,44,45];
                if(jQuery.inArray(event.keyCode,bannedInputs)!== -1 ||(event.keyCode>=112 && event.keyCode<=123)){
                    event.preventDefault();
                }else{
                    findKladr(this,ajaxAction,limit);
                }
            }
        }
    });
    var kladrInputButton = jQuery('<input>',{
        type:'button',
        class:'setKladr btn btn-small button-apply btn-success',
        value: dellinVars.langVars.WC_DELLIN_SHIPPING_BUTTON_SELECT,
        on:{
            click: function(){
                setKladr(this);
            }
        }
    });
    var closePopupButton = jQuery('<input>',{
        type:'button',
        class:'close-popup btn btn-small button-cancel',
        value: dellinVars.langVars.WC_DELLIN_SHIPPING_BUTTON_CLOSE,
        on:{
            click: function(){
                removeThisPopup(this);
            }
        }
    });
    jQuery(popup).append(kladrInput,kladrInputButton,closePopupButton);
    jQuery(popup).insertAfter(element);
}
function findKladr(element,ajaxaction,limit){
    var data = {'ajax':'y','action':ajaxaction,'query':jQuery(element).val(),'count':limit};
    if(ajaxaction == 'search_street'){
        var relatedCityKladr = jQuery('.for_'+jQuery(element).closest('tr').find('.streetKladrInput').attr('id')).val();
        if(jQuery.trim(relatedCityKladr).length > 0){
            data.kladr = jQuery('.for_'+jQuery(element).closest('tr').find('.streetKladrInput').attr('id')).val();
        }else{
            data.kladr = jQuery('#woocommerce_dellin_shipping_calc_kladr_code_delivery_from').val();
        }
        data.apikey = jQuery('#woocommerce_dellin_shipping_calc_appkey').val();
        //data.cid = getUrlParameter('cid');
        data.login =  jQuery('#woocommerce_dellin_shipping_calc_login').val();
        data.password =  jQuery('#woocommerce_dellin_shipping_calc_password').val();
    }
    if(jQuery(element).val().length >= 2){
        jQuery.ajax({
            url:dellinVars.url,
            data: data,
            method: "POST",
            dataType: "json"
        }).done(function(response){
            jQuery(element).closest('.kladrDiv').find('.autocomplete').remove();
            if(undefined != response.error && jQuery(element).closest('.kladrDiv').find('.error').length == 0){
                jQuery(element).closest('.kladrDiv').find('span').append('<span class="error">'+response['error']+'</span>')
            }
            if(undefined == response.error){
                var autocompletepopup = jQuery('<div>',{
                    class:'autocomplete',
                }).append(jQuery('<div>',{
                    class:'rows'
                }));
                jQuery(autocompletepopup).insertAfter(jQuery(element));
                jQuery.each(response,function(index,el){
                    var name = "";
                    if(ajaxaction == 'search_street'){
                        name = el.aString;
                    }else{
                        name = el.city;
                    }
                    jQuery(element).siblings('.autocomplete').find('.rows').append(
                        jQuery('<div>',{
                            class:'autocomplete-row',
                            on:{
                                click:function(){
                                    setAutoCompleteKladr(this);
                                }
                            },
                            'data-id': el.code,
                            text:name+' ['+el.code+']'
                        })
                    );
                });
            }


        });
    }
}
function setAutoCompleteKladr (element){
    jQuery(element).closest('.kladrDiv').find('input[type=text]').first().val(jQuery(element).html()).attr('kladr',jQuery(element).data('id'));
    jQuery(element).closest('.autocomplete').remove();
}

function setKladr (element){
    jQuery(element).closest('tr').find('input[type=number]').first().val(jQuery(element).siblings('input[type=text]').attr('kladr'));
    jQuery(element).closest('tr').find('input[type=number]').first().trigger('change');
    jQuery(element).closest('.popup-container').remove();
}

function removeThisPopup(element){
    console.log('remove');
    jQuery(element).closest('.kladrDiv').remove();
}
