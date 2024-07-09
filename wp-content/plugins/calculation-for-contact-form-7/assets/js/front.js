jQuery(document).ready(function() {
	
	/* Calculate value for select box and radio button */
	function CALCULATIONCF7_custom_radio_button(){
		jQuery("form.wpcf7-form select option").each(function (i) {
	       	if(jQuery(this).attr("type") === undefined) {
	       		var cal_selectval = jQuery(this).attr("value");
	       		if(cal_selectval.indexOf("--") != -1){
		       		jQuery(this).attr("value",'');
	       			jQuery(this).html('Required Pro Version for add Custom Label. <a href="https://www.plugin999.com/plugin/calculation-for-contact-form-7/" target="_blank">Click Here To Get Pro Version</a>');
	       		}		
	       	}
       	})
		jQuery("form.wpcf7-form input").each(function () { 
       		if( jQuery(this).attr("type") == "radio" || jQuery(this).attr("type") == "checkbox" ) {
       			var cal_inputval = jQuery(this).attr("value");
       			if(cal_inputval.indexOf("--") != -1){
	       			jQuery(this).val('');
       			   	jQuery(this).parent().find('span.wpcf7-list-item-label').html('Required Pro Version for add Custom Label. <a href="https://www.plugin999.com/plugin/calculation-for-contact-form-7/" target="_blank">Click Here To Get Pro Version</a>');
       			}
       		}
       	})
	}

	/* check form length */
	if ( jQuery( ".wpcf7-form" ).length ) {
		CALCULATIONCF7_custom_radio_button();
		CALCULATIONCF7_calculate_formulas();
		/* select , check box , radio button change here*/
		jQuery("body").on("change",".wpcf7 input,.wpcf7 select",function(e){
			CALCULATIONCF7_calculate_formulas();
		});
		jQuery("input[type='number']").bind('keyup', function () {
		  	CALCULATIONCF7_calculate_formulas();
		});
		jQuery("input[type='text']").bind('keyup', function () {
		  	CALCULATIONCF7_calculate_formulas();
		});
	}

	/*calculator formula setup */
	function CALCULATIONCF7_calculate_formulas(){
       	var match;
       	var reg =[]; 
	   	var total = 0;

      	jQuery("form.wpcf7-form input").each(function () { 
       		if( jQuery(this).attr("type") == "checkbox" || jQuery(this).attr("type") == "radio"  ) {
       			var name = jQuery(this).attr("name").replace("[]", "");
       			reg.push(name);
       			//alert($(this).attr("name"));
       		}else{
       			reg.push(jQuery(this).attr("name"));
       		}
       	})

       	jQuery("form.wpcf7-form select").each(function () {
       		var name_select = jQuery(this).attr("name").replace("[]", "");
       		reg.push(name_select);
       	})

       	reg = calculationcf7s_duplicates_type(reg);
       	var all_tag = new RegExp( reg.join("|"));
       	jQuery( ".calculationcf7-total" ).each(function( index ) {
       		var type = '';
       		var eq = jQuery(this).data('formulas');
       		var prefix = jQuery(this).attr("prefix");
       		var precision = jQuery(this).attr("precision");
       		if(eq != '' || eq.length != 0){
				while ( match = all_tag.exec( eq ) ){
					var perfact_match = calculationcf7s_word_In_String(eq,match[0]);
					if(perfact_match != false){
						var type = jQuery("input[name="+match[0]+"]").attr("type");
						if( type === undefined ) {
							var type = jQuery("input[name='"+match[0]+"[]']").attr("type");
						}
						//console.log(type);
						if( type =="checkbox" ){
							var vl = 0;
							jQuery("input[name='"+match[0]+"[]']:checked").each(function () {
								var attr = jQuery(this).attr('price');
								//console.log(attr);
								if (typeof attr !== typeof undefined && attr !== false) {
									vl += new Number(attr);
								} else {
									vl += new Number(jQuery(this).val());
								}
							});
						}else if( type == "radio"){
							var attr = jQuery("input[name='"+match[0]+"']:checked").attr('price');
							if (typeof attr !== typeof undefined && attr !== false) {
								var vl = attr;
							} else {
								 var vl = jQuery("input[name='"+match[0]+"']:checked").val();
							}
						}else if( type === undefined ){
							var check_select = jQuery("select[name="+match[0]+"]").val();
							//console.log('--'+check_select+'--')
							if(check_select === undefined){
								var vl = 0;
								jQuery("select[name='"+match[0]+"[]'] option:selected").each(function () {
									var attr = jQuery(this).attr('price');
									if (typeof attr !== typeof undefined && attr !== false) {
										vl += new Number(attr);
									} else {
										vl += new Number(jQuery(this).val());
									}
								});
							} else {
								var vl = 0;
								jQuery("select[name="+match[0]+"] option:selected").each(function () {
									var attr = jQuery(this).attr('price');
									if (typeof attr !== typeof undefined && attr !== false) {
										vl += new Number(attr);
									} else {
										vl += new Number(jQuery(this).val());
									}
								});
							}
						}else{
							var attr = jQuery("input[name="+match[0]+"]").attr('price');
							if (typeof attr !== typeof undefined && attr !== false) {
							    var vl = attr;
							} else {
								var vl = jQuery("input[name="+match[0]+"]").val();	
							}
						}
						if(!jQuery.isNumeric(vl)){
							vl = 0;
							//alert("value must be numeric");
						}
					}else{
					 	var error = 1;
					}
					nueq = '';
					eq = eq.replace( match[0], vl );
					neq = eq;
					var neqf = '';
					if(eq.indexOf("sqrt") != -1){
						var neweq = eq.split(" ");
						for(var i = 0; i < neweq.length; i++){
							if(neweq[i].indexOf("sqrt") != -1){
							   var sqrt = neweq[i].match("sqrt((.*))");
							   var sqrtd = sqrt[1].replace(/[()]/g,'')
							   var sqrtroot = Math.sqrt(parseInt(sqrtd));
							   neq = neq.replace( sqrt[1], sqrtroot );
							}     
					     }
					     nueq = neq.split("sqrt").join('');
					}
					if(nueq === ''){
						neqf = eq;
					} else {
					 	neqf = nueq;
					}
				}
       		}else{
       			alert("Please Enter Formula in Calculator");
       			return false;
       		}
       		if(error == 1){
       			alert("Please Enter Valid Formula in Calculator");
       			return false;
       		}
			try{
				var fresult = ''; 
			    var r = eval( neqf ); // Evaluate the final equation
			    if( precision != undefined ) {
			      	fresult = r.toFixed(precision);
				} else {
					fresult = r;
				}

				total = fresult;
				//console.log('--'+total);

			}
			catch(e)
			{
				alert( "Error:" + neqf );
			}
			
			if(index === 0){
				if( prefix != undefined ) {
					jQuery(this).val(prefix + total);
				}else{
					jQuery(this).val(total);
				}
			} else {
				jQuery(this).closest('span').html('Required Pro Version for add Custom Label. <a href="https://www.plugin999.com/plugin/calculation-for-contact-form-7/" target="_blank">Click Here To Get Pro Version</a>');
			}
		});
	}

	/*contact form 7 duplicator type  */
	function calculationcf7s_duplicates_type(arr) {
	    var ret_arr = [];
	    var obj = {};
	    for (var i = 0; i < arr.length; i++) {
	        obj[arr[i]] = true;
	    }
	    //console.log(obj);
	    for (var key in obj) {
	    	if("_wpcf7" == key || "_wpcf7_version" == key  || "_wpcf7_locale" == key  || "_wpcf7_unit_tag" == key || "_wpnonce" == key || "undefined" == key  || "_wpcf7_container_post" == key || "_wpcf7_nonce" == key  ){

	    	}else {
	    		ret_arr.push(key);
	    	}
	    }
	    return ret_arr;
	}

	/* calculator word In String */
	function calculationcf7s_word_In_String(s, word){
	  return new RegExp( '\\b' + word + '\\b', 'i').test(s);
	}
	
});