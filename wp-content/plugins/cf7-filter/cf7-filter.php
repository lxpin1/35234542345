<?
/*
 * Plugin Name: Плагин cf7-filter
 */

 //ini_set('display_errors','On');
 error_reporting(0);
 ini_set('memory_limit', '9999M');
 global $paged, $wp_query;


 if(!empty($_POST['cf7-filt-del'])){
  $filt_del=$_POST['cf7-filt-del'];
  $options=get_option('_cf7_filter', $default = false );
  $options_new=array();
  for($i=0;$i<count($options);$i++){
      if ('x'.$i!=$filt_del)
      $options_new[]=$options[$i];
  }
  update_option( '_cf7_filter', $options_new);
}  


 if(!empty($_POST['cf7-filt-ret'])){
  if(!empty($_POST['cf7-atr-slug'])){
  $opt_value=array();


  $options=get_option('_cf7_filter', $default = false );
  if($options){
      $opt_value_x=array();  
      $opt_value['cf7-atr-id']=$_POST['cf7-atr-id']; 
      $opt_value['cf7-atr-slug']=$_POST['cf7-atr-slug']; 
      $opt_value['cf7-atr-min']=$_POST['cf7-atr-min'];
      $opt_value['cf7-atr-max']=$_POST['cf7-atr-max'];  
      $opt_value['cf7-atr-min-proc']=$_POST['cf7-atr-min-proc'];
      $opt_value['cf7-atr-max-proc']=$_POST['cf7-atr-max-proc']; 
      $options[]=$opt_value;
      update_option( '_cf7_filter', $options);
  }else{
      $opt_value[0]['cf7-atr-slug']=$_POST['cf7-atr-slug']; 
  $opt_value[0]['cf7-atr-min']=$_POST['cf7-atr-min'];
  $opt_value[0]['cf7-atr-max']=$_POST['cf7-atr-max'];  
  update_option( '_cf7_filter', $opt_value);

  }}}


add_action('admin_menu', 'cf7_filter_menu' ); 

function cf7_filter_menu() {
  add_menu_page('cf7 фильтр', 'cf7 фильтр', 'manage_options', 'cf7-filter/cf7-filter-admin.php', '', 'dashicons-edit' );
  if ( function_exists ( 'add_menu_page' ) ) {
  } }


 function my_skip_mail($contact_form){
   $submission = WPCF7_Submission::get_instance();
   $posted_data = $submission->get_posted_data();
   //if(){
   //  $title = $contact_form->title;
  if ($posted_data['filtn'])
     file_put_contents('cf7-filter.txt',$posted_data['filtn']);
     if ($posted_data['filtn'])
       return true; 
       else
       return false; 
 //  }
}
add_filter('wpcf7_skip_mail','my_skip_mail');

add_action( 'wp_footer', 'cf7_filter_js' );



function cf7_filter_js(){

?>
<style>
 /* 
.wpcf7 form.sent .wpcf7-response-output {
display: none;
}
*/
</style>
<script>
  jQuery(document).ready(function ($) {
   document.addEventListener( 'wpcf7submit', function( event ) {
	//if ( '40031' == event.detail.contactFormId ) {
      var inputs = event.detail.inputs;

  /*    for ( var i = 0; i < inputs.length; i++ ) {
		if ( 'calculator-419' == inputs[i].name ) {
			break;
		}
	}*/

  if (event.detail.status=='mail_sent'){
   
 st='#wpcf7-f'+event.detail.contactFormId+'-o1 .wpcf7-form'; 
$(st).attr('method','get');
//$('#wpcf7-f40031-o1 [name="_wpcf7"]').remove();

st='#wpcf7-f'+event.detail.contactFormId+'-o1 .wpcf7-response-output';
$(st).hide();

st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_version"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_locale"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_unit_tag"]'
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_container_pos"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_posted_data_hash"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_hidden_group_fields"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_hidden_groups"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_visible_groups"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_repeaters"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_steps"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7cf_options"]';
$(st).remove();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 [name="_wpcf7_container_post"]';
$(st).remove();


	//$('#wpcf7-f40031-o1 .wpcf7-form').removeClass('wpcf7-form');
//	console.log();
st='#wpcf7-f'+event.detail.contactFormId+'-o1 .wpcf7-form';
$(st).submit();

  }

	
	
}, false );


});
</script>   

<?


}
if(!empty($_GET['filtn'])){
  add_filter( 'wpcf7_form_elements', function( $form ) {

    preg_match_all('|wpcf7-calculator.*?<\/span>|si', $form, $calculators);
		$calculators=$calculators[0];
for($i=0;$i<count($calculators);$i++){
  $izn=$calculators[$i];
    preg_match('|name=".*?"|si', $calculators[$i], $calc_name);
    $calc_name=$calc_name[0];
    $calc_name=str_replace( array('name="','"'),'', $calc_name);
    preg_match('|value=".*?"|si', $calculators[$i], $calc_val);
    $calc_val=$calc_val[0];
$fin_val='value="'.$_GET[$calc_name].'"';
 $calculators[$i]=str_replace($calc_val,$fin_val,$calculators[$i]);
$form=str_replace($izn,$calculators[$i],$form);
  }

  preg_match_all('|wpcf7-range.*?<\/span>|si', $form, $calculators);
  $calculators=$calculators[0];
for($i=0;$i<count($calculators);$i++){
$izn=$calculators[$i];
  preg_match('|name=".*?"|si', $calculators[$i], $calc_name);
  $calc_name=$calc_name[0];
  $calc_name=str_replace( array('name="','"'),'', $calc_name);
  preg_match('|value=".*?"|si', $calculators[$i], $calc_val);
  $calc_val=$calc_val[0];
$fin_val='value="'.$_GET[$calc_name].'"';
$calculators[$i]=str_replace($calc_val,$fin_val,$calculators[$i]);
$form=str_replace($izn,$calculators[$i],$form);
}



    return $form;
  } );
}

function custom_pre_get_posts_query_fn( $q ) {
  $qx=$q; global $wpdb;
      if ( !is_admin()){
         if(!empty($_GET['filtn']))   {
          $tax_query=array('relation' => 'AND'); 

          $options_filt=get_option('_cf7_filter', $default = false );
 $filt_term_slug=array();

foreach($_GET as $key => $options){

if (strpos($key,'fn_')!==false){
 

  $term_slug=str_replace('fn_','pa_',$key);
  if (gettype($options)=='array'){
    $sel_term=array();
    foreach($options as $option){
    $srezer=$wpdb->get_results("SELECT `slug` FROM `wp_terms` WHERE `name`='$option'");	
 $sel_term[]= $srezer[0]->slug;

    } 
  }else{
  $srezer=$wpdb->get_results("SELECT `slug` FROM `wp_terms` WHERE `name`='$options'");	
 $sel_term= $srezer[0]->slug;
  }
 //$sel_term=str_replace('.',',',$srezer[0]->slug);
   // print_r( $options);
   // echo '<br>---------------<br>';
//$sel_term=(float)$sel_term;
//$sel_term= ceil($sel_term);
for($j=0;$j<count($options_filt);$j++){
if($options_filt[$j]['cf7-atr-id']==$_GET['_wpcf7']){
  $tt='pa_'.$options_filt[$j]['cf7-atr-slug'];
if ($tt==$term_slug) 
{
  $filt_term_slug[]=$term_slug;
  $max=$options_filt[$j]['cf7-atr-max'];
       if($options_filt[$j]['cf7-atr-max-proc']=='1') $max=ceil($options/100*$max);
  if($max=='') $max=0;
      $min=$options_filt[$j]['cf7-atr-min'];
      if($options_filt[$j]['cf7-atr-min-proc']=='1') $min=ceil($options/100*$min);
  if($min=='') $min=0;     
   $atrib_value_max=$options+$max; 
      $atrib_value_min=$options-$min; 
      $terms = get_terms( [
        'taxonomy' => $term_slug,
        'hide_empty' => false,
      ] );
      
      $sel_terms=array();
      foreach($terms as $term){
   //     $slug=(float)$term->slug; 
   $slug=$term->slug;
   //$slug=str_replace('-','.',$slug);
 //  $slug=ceil($slug);
      if ($slug>=$atrib_value_min && $slug<=$atrib_value_max){
          $sel_terms[]=$slug;
      
      }}

//print_r($sel_terms);
//echo '<br>---------------<br>';    
      $tax_query_item=array(
        'taxonomy' => $term_slug,
        'field'    => 'slug',
        'terms'    => $sel_terms
            );
            array_push($tax_query,$tax_query_item);

 
}
}
}
if (!in_array($term_slug,$filt_term_slug)){
  $tax_query_item=array(
    'taxonomy' => $term_slug,
    'field'    => 'slug',
    'terms'    => $sel_term
        );
        array_push($tax_query,$tax_query_item);
}
}



}

$q->set('tax_query', array($tax_query) );



$meta_query= array(
'capacity' => array(
    'key' => '_pa_capacity',
    'compare' => 'EXISTS',
)
);
$orderby = array(
    'capacity' => 'ASC'
);



//$q->set('meta_query', $meta_query);
//$q->set('orderby', $orderby);




//******************* */
/*

global $wpdb;
$srezer=$wpdb->get_results("SELECT `post_id`,`meta_value` FROM `wp_postmeta` WHERE `meta_key`='_product_attributes'");	
foreach($srezer as $srez){

   $xprod=wc_get_product($srez->post_id); 

   foreach($xprod->get_attributes() as $key=>$attr){
     $val=wc_get_product_terms( $xprod->get_id(), $attr->get_name(), array( 'fields' => 'all' ) );
    // if($key=='capacity' || $key=='battery_type' || $key=='hours' || $key=='voltage' || $key=='chargesp'){
    if($key=='bat_qty'){
       update_post_meta($xprod->get_id(),'_'.$key,$val[0]->name);
//echo $attr->get_name();
   }
}
}


//******************* */
}}



}



  if(!empty($_GET['filtn']))   {
     add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query_fn' );


  }

  

