<?php 
/*
 * Plugin Name: Плагин фильтра
 */
//error_reporting(0);



global $paged, $wp_query;



function wcproduct_set_attributes($id) {

    $options=get_option('_new_product_filt', $default = false );
    if($options){

        for($i=0;$i<count($options);$i++){
	$aslug='pa_'.$options[$i]['atr-slug'];

    $material = get_the_terms( $id, $aslug);

    $material = $material[0]->name;

    update_post_meta($id, '_'.$options[$i]['atr-slug'], $material);

        }}

}

add_action( 'save_post_product', 'wcproduct_set_attributes', 10);



//###### Кнопка Отправить запрос на подбор ###########
function ref_button_shortcode() {
    $ref_button="<div class='nrezbut'>
    <a class='btn btn-style-default btn-style-semi-round btn-size-default btn-color-primary btn-icon-pos-right' href='/battery-request?lenght=".$_GET['f_pa_length']."&width=".$_GET['f_pa_width']."&height=".$_GET['f_pa_height']."&brand=".$_GET['filter_brand']."&model=".$_GET['filter_model']."'>
				<span class='wd-btn-text'>
                Отправить запрос на подбор				</span>

							</a>
    </div>";

    return $ref_button;
}
add_shortcode('ref_button', 'ref_button_shortcode');
//###### end  ###########


//###### Переопределение редиректа если не найдено ###########
if (strpos($_SERVER['REQUEST_URI'],'request')!==false) {}else{
    if(empty($_GET['no-rez']))
add_action( 'template_redirect', 'no_products_found_redirect' );
}

function no_products_found_redirect() {
    global $wp_query; 
   if( $wp_query->post_count == 0 && !is_admin() ) {
    $rpath=explode('/',$_SERVER['REQUEST_URI']);
    $pathx='';
    for($i=0;$i<count($rpath)-1;$i++){
        $pathx.=$rpath[$i].'/';
    }

    $dstr=str_replace('filt=1&','?',$_SERVER['REQUEST_URI']);
  $zstr='//'.$_SERVER['HTTP_HOST'].$dstr.'&no-rez=1';
    }
}
//###### end  ###########


//###### Задание сортировки по наличию ###########
add_filter( 'woocommerce_get_catalog_ordering_args', 'truemisha_sort_by_stock', 25 );
function truemisha_sort_by_stock( $args ) {
 
	$args[ 'meta_key' ] = '_stock_status';
	$args[ 'orderby' ] = 'meta_value';
	$args[ 'order' ] = 'ASC';
 

return $args;
 
}
//###### end  ###########


//###### Переопределяем функцию вывода товаров внутри темы ###########
if ( ! function_exists( 'woodmart_woocommerce_main_loop' ) ) {
add_action( 'woodmart_woocommerce_main_loop', 'woodmart_woocommerce_main_loop' );

function woodmart_woocommerce_main_loop( $fragments = false ) {
    global $paged, $wp_query;
 


function getClosest($search, $arr) {
    $closest = null;
    $q=0;
    if($arr){
    foreach ($arr as $item) {
       if ($closest === null || abs($search - $closest) > abs($item - $search)) {
          $closest = $item;
          $closestx = $q;
       }
       $q++;
    }

    return $closestx;
 }}


$pposts=$wp_query->posts;


$postx=$wp_query->posts[0];
$wp_queryx=$wp_query;
$filt_opr='0';
$opt_focus=get_option('_new_product_filt_focus', $default = false );


foreach($pposts as $ppost){
   $ppostid=$ppost->ID;
   $pa_width = get_post_meta( $ppostid, 'pa_length');

   if(get_the_terms($ppostid,'pa_length') && get_the_terms($ppostid,'pa_height') && get_the_terms($ppostid,'pa_width'))
if (count(get_the_terms($ppostid,'pa_length'))>0 && count(get_the_terms($ppostid,'pa_height'))>0 && count(get_the_terms($ppostid,'pa_width'))>0) $filt_opr='1';

}  
//###### Скрываем поля после фильтрации по бренду/модели ###########
?>

<?php  if(empty($_GET['filter_brand']) && $opt_focus==0)  {?>
<style>
    .wd-swatches-filter {
display: none;

}
</style>    
  <?php }elseif(empty($_GET['filter_model']) && $opt_focus==0){?>  
<style>
   .wd-size-small {
display: none;

} 
</style> 
<?php  }
//###### end  ###########
?>


<script>
    jQuery(document).ready(function ($) {

        $('.add_to_cart_button.ajax_add_to_cart.add-to-cart-loop').click(function(){	
            $(".cart-widget-side.wd-side-hidden").addClass("wd-opened");
            $(".wd-close-side").addClass("wd-close-side-opened");
            
});	 

<?php  if(!empty($_GET['filter_model'])){?>
            $(".wd-swatches-filter.wd-swatches-brands a.layered-nav-link").attr('href','#');
            $(".wd-swatches-filter.wd-swatches-brands a.layered-nav-link").addClass('no-after');
          <?php  }?>  

//###### Обрабатываем клики по полям фильтра по бренду/модели фокус, добавление классов ###########
/*
$(document).click(function (e) {
    if ($(e.target).closest(".wd-filter-wrapper").length) 
    q=1;
        else
        $(".wd-filter-wrapper .wd-swatches-filter").removeClass('filter-list-focus');
        
       return;
    



 

    
   
});*/

$('.wd-swatches-filter .wc-layered-nav-term.wd-active').parent().addClass('wd-filt-selected');

$('.wd-filter-search:after').css('display', 'none');

$('.wd-filter-wrapper').click(function(){	
        if($(this).find(".wd-swatches-filter").is(":visible")){



        }
        else{
            <?php  if($opt_focus=get_option('_new_product_filt_focus', $default = false )==1){?>

             
                   $(this).find(".wd-swatches-filter").addClass('filter-list-focus');
                    <?php  }else{?>         	
    $(this).find(".wd-swatches-filter").fadeIn();
<?php  }?>
        }

            
});	

        $('.wd-filter-search input').click(function(){	
            $(this).parent().parent().addClass('wd-filt-focus');

            
});	
//###### end  ###########


<?php 
//###### Ищем ###########
$opt_vmb=get_option('_new_product_filt_vmb', $default = false );

$opt_posled=get_option('_new_product_filt_posled', $default = false );
$opt_posled = explode("\n", $opt_posled);
foreach ($opt_posled as $spisok){
$felements=explode(",", $spisok);
if (count($felements)>1){
for ($i=0;$i<count($felements);$i++){
$el1=$felements[0];
$el2=$felements[1];
?>
<?php 
}
}
?>
<?php 
}


?>

<?php  if(!empty($_GET['filter_brand']) && $opt_vmb==1) {?>      
    
//$('input[aria-label$="Марка техники"]').parent().hide();
$('.wd-filter-search input').focus();
$('input[aria-label$="Выберите Бренд техники из списка"]').parent().hide();
$('input[aria-label$="Выберите Модель техники из списка"]').parent().show();
$(".wd-filter-wrapper .wd-swatches-filter").addClass('filter-list-focus');
$(".wd-filter-search .wd-search").addClass('filter-list-focus2');
$('a[href*="filter_model"]').parent().show();
$('input[aria-label$="Выберите Модель техники из списка"]').addClass('filter-list-focusdddsss');
// $('input[aria-label$="Выберите Модель техники из списка"]').attr('aria-label', 'new area label');

<?php  }else{?>
    <?php  if(!empty($_GET['filter_model'])  && $opt_vmb==1) {?>
        
$('input[aria-label$="Выберите Модель техники из списка"]').parent().hide();
<?php  }?>
<?php  if(empty($_GET['filter_model'])  && $opt_vmb==1){?>
$('a[href*="filter_model"]').parent().parent().parent().parent().parent().hide();
<?php  }?>
<?php }?>
<?php  if(!empty($_GET['filter_model']) && $opt_vmb==1) {?>      
$('input[aria-label$="Выберите Модель техники из списка"]').parent().hide();
<?php  }?>
<?php  ?>

    });   
 </script>       
<?php 


if(!empty($_GET['filt'])) {
$xlink='//'.$_SERVER['HTTP_HOST'].'/?'.$_SERVER['QUERY_STRING'];
if(!empty($_GET['shop_view'])) {
$temp2=substr($xlink,strpos($xlink,'&shop_view'));
$xlinkz=str_replace($temp2,'',$xlink);
$temp3=substr($xlink,strpos($xlink,'&per_row'));
$xlinkz=str_replace($temp3,'',$xlink);
}else{
    $xlinkz=$xlink;
}
?>
<script>


    jQuery(document).ready(function ($) {
   

      if($(".shop-view.per-row-list").length){        
        grid_link='';
grid_link=$('.shop-view.per-row-list').attr('href');
grid_link='&'+grid_link.substr(grid_link.indexOf('?')+1);
grid_link='<?=$xlinkz?>'+grid_link;

$('.shop-view.per-row-list').attr('href',grid_link);
      }
      if($(".shop-view.per-row-4").length){      
grid_link='';
grid_link=$('.shop-view.per-row-4').attr('href');
grid_link='&'+grid_link.substr(grid_link.indexOf('?')+1);
grid_link='<?=$xlinkz?>'+grid_link;

$('.shop-view.per-row-4').attr('href',grid_link);
      }


      




        xhtml='';
      <?php  if(!empty($_GET['f_pa_length'])){
        $delst='&f_pa_length='.$_GET['f_pa_length'];
        $xlinkz=str_replace($delst,'', $xlink);
       if(empty($_GET['f_pa_width']) && empty($_GET['f_pa_height']))
        $xlinkz=str_replace('filt=1','filt=', $xlinkz);
        ?>
     xhtml=xhtml+'<div class=" woocommerce widget_layered_nav_filters"><ul class="asdfg"><li class="chosen chosen-brand chosen-brand-crown"><a rel="nofollow" aria-label="Очистить фильтр" href="<?=$xlinkz?>">Длина <?=$_GET['f_pa_length']?></a></li></ul></div>'
   <?php  }?>

   <?php  if(!empty($_GET['f_pa_width'])){
        $delst='&f_pa_width='.$_GET['f_pa_width'];
        $xlinkz=str_replace($delst,'', $xlink);
       if(empty($_GET['f_pa_length']) && empty($_GET['f_pa_height']))
        $xlinkz=str_replace('filt=1','filt=', $xlinkz);
        ?>
     xhtml=xhtml+'<div class=" woocommerce widget_layered_nav_filters"><ul class="asdfg"><li class="chosen chosen-brand chosen-brand-crown"><a rel="nofollow" aria-label="Очистить фильтр" href="<?=$xlinkz?>">Ширина <?=$_GET['f_pa_width']?></a></li></ul></div>'
   <?php  }?>

   <?php  if(!empty($_GET['f_pa_height'])){
        $delst='&f_pa_height='.$_GET['f_pa_height'];
        $xlinkz=str_replace($delst,'', $xlink);
       if(empty($_GET['f_pa_length']) && empty($_GET['f_pa_width']))
        $xlinkz=str_replace('filt=1','filt=', $xlinkz);
        ?>
     xhtml=xhtml+'<div class=" woocommerce widget_layered_nav_filters"><ul class="asdfg"><li class="chosen chosen-brand chosen-brand-crown"><a rel="nofollow" aria-label="Очистить фильтр" href="<?=$xlinkz?>">Высота <?=$_GET['f_pa_height']?></a></li></ul></div>'
   <?php  }?>

   xhtml=$(".wd-active-filters").html()+xhtml;
        $(".wd-active-filters").html(xhtml);
});
    </script>
<?php 
} 

if(!empty($_GET['filt'])) {
$pa_length_ar=array();
$pa_length_id_ar=array();
$options=get_option('_new_product_filt', $default = false );
$kf=0;
for($i=0;$i<count($options);$i++){
 $xfild='f_pa_'.$options[$i]['atr-slug'];
 if (!empty($_GET[$xfild])){
    if($kf==0){
$pa_length=$_GET[$xfild];
$pa_length_par=str_replace('f_','',$xfild);
    }
if($kf==1){
$pa_width=$_GET[$xfild];
$pa_width_par=str_replace('f_','',$xfild);
}
 $kf++;
}
}


$i=0;
$fix_prod='';
if ($kf==1){
    foreach($pposts as $ppost){
        $ppostid=$ppost->ID;
        $ppostatrs=get_the_terms( $ppostid, $pa_length_par);
        foreach($ppostatrs as $ppostatr){
            $pa_length_ar[]=$ppostatr->slug;
            $pa_length_id_ar[]=$ppostid;
        }
        $i++;       
    }  
    $x=$pa_length_ar;
    $y=$pa_length;
    
  $lenght_o1=$pa_length_id_ar[getClosest($y, $x)];
 $lenght_o1_ind=getClosest($y, $x);

$fix_prod=$lenght_o1;
$fix_prod_ind=$lenght_o1_ind;
}   
if ($kf>1){
foreach($pposts as $ppost){
$ppostid=$ppost->ID;

$ppostatrs=get_the_terms( $ppostid, $pa_length_par);
$ppostatrs_x=get_the_terms( $ppostid, $pa_width_par);

foreach($ppostatrs as $ppostatr){
    $pa_length_ar[]=$ppostatr->slug;
    $pa_length_id_ar[]=$ppostid;
}

foreach($ppostatrs_x as $ppostatr){
    $pa_width_ar[]=$ppostatr->slug;
    $pa_width_id_ar[]=$ppostid;
}

$i++;
}




$x=$pa_length_ar;
$y=$pa_length;

$lenght_o1=$pa_length_id_ar[getClosest($y, $x)];
$lenght_o1_ind=getClosest($y, $x);

$x=$pa_width_ar;
$y=$pa_width;

$width_o1=$pa_width_id_ar[getClosest($y, $x)];
$width_o1_ind=getClosest($y, $x);

$fix_prod='';
if($lenght_o1==$width_o1){
    $fix_prod_ind=$lenght_o1_ind;
 $fix_prod=$lenght_o1;
}else{


    $pa_width_ar2=array();
    $pa_width_ar2_ind=array();
    for($j=0;$j<count($pa_length_ar);$j++){
        if($pa_length_ar[$j]==$pa_length_ar[$lenght_o1_ind]){
        $pa_width_ar2_ind[]=$j;
        $pa_width_ar2[]=$pa_width_ar[$j];
        }
    
    }




 $width_o3_ind=getClosest($y, $pa_width_ar2);
 $fin_width=$pa_width_id_ar[$pa_width_ar2_ind[$width_o3_ind]];


$fix_prod=$fin_width;
$fix_prod_ind=$pa_width_ar2_ind[$width_o3_ind];



}
}

if($fix_prod!=''){

    $fix_prod_obj=$wp_query->posts[$fix_prod_ind];
   
   if (count($wp_query->posts)==1){
echo '<style>
.product-list-item.fix-prod::after, .product-list-item.fix-prod-x.fix-prod-after::after {
display: none !important;
}   
</style> ';
   }

$ppostid=$fix_prod_obj->ID;

$fix_lenght=get_the_terms( $ppostid, 'pa_length')[0]->slug;
$fix_width=get_the_terms( $ppostid, 'pa_width')[0]->slug;
$fix_height=get_the_terms( $ppostid, 'pa_height')[0]->slug;

 



    echo '<script>
    jQuery(document).ready(function ($) {
       
    

     ';
    $i=0;
    $xpind=array();
    foreach($pposts as $ppost){
        $ppostid=$ppost->ID;
    
    $f_lenght=get_the_terms( $ppostid, 'pa_length')[0]->slug;
    $f_width=get_the_terms( $ppostid, 'pa_width')[0]->slug;
    $f_height=get_the_terms( $ppostid, 'pa_height')[0]->slug;
    if ($fix_lenght==$f_lenght && $fix_width==$f_width && $fix_height==$f_height){

        $fix_prod_obj= $wp_query->posts[$i];  
    unset($wp_query->posts[$i]);
    array_unshift($wp_query->posts, $fix_prod_obj);
    echo '$(".post-'.$ppostid.'").addClass("fix-prod-x");';
    $xpind[]=$ppostid;
    }
    $i++;
    } 
    echo '$(".post-'.array_shift($xpind).'").addClass("fix-prod-after");';
  echo '$(".post-'.$wp_query->posts[0]->ID.'").addClass("fix-prod");';
 echo '});
    </script>';
}

}

    $max_page = $wp_query->max_num_pages;

    if ( $fragments ) {
        ob_start();
    }

    if ( $fragments && isset( $_GET['loop'] ) ) {
        woodmart_set_loop_prop( 'woocommerce_loop', (int) sanitize_text_field( $_GET['loop'] ) );
    }

    if ( woocommerce_product_loop() ) : ?>

<?php 
   $gparams=$_GET;

$gopr=0;     
foreach($_GET as $key => $getx){

}

if(strpos($_SERVER['REQUEST_URI'],'/cat/')!==false) $gopr=0;
if(!empty($_GET['filter_brand']) || !empty($_GET['filter_model']) )$gopr=1;

if(get_option('_new_product_filt_hideprod', $default = false )!='1')$gopr=1;else{
$cat_id = get_queried_object()->term_id;

$term = get_term($cat_id);
$selkat=$term->slug;

$opt_kategs=get_option('_new_product_filt_kateg', $default = false );
$opt_kategs=explode(',',$opt_kategs);

if(in_array($selkat,$opt_kategs))$gopr=1;
}


?>

<?php   if(!empty($_GET['filt'])) {
   
   $rpath=explode('/',$_SERVER['REQUEST_URI']);
   $pathx='';
   for($i=0;$i<count($rpath)-1;$i++){
       $pathx.=$rpath[$i].'/';
   }
 $zstr='//'.$_SERVER['HTTP_HOST'].$pathx 
    ?>
<div class="wd-active-filters">
				<div class="wd-clear-filters wd-action-btn wd-style-text wd-cross-icon">
					<a href="<?=$zstr?>?reset=1">Сбросить фильтры</a>
				</div>
        </div>
<?php  }?>
        <?php 
        if ( ! $fragments ) {
            woocommerce_product_loop_start();}
        ?>
        <?php 
            if ( wc_get_loop_prop( 'total' ) || $fragments ) {
                if($wp_query->found_posts == 0){
                    echo "<div class='ntext'>Товаров по таким условиям не найдено, необходимо поменять условия</div>";
                }

                
                if($wp_query->found_posts > 0 && empty($_GET['no-rez']) &&  ($gopr==1 || !empty($_GET['filt'] )) ){  
                    if(!empty($_GET['filter_brand']) && empty($_GET['filter_model'])){
                        echo "<div class='ntext'>Укажите модель, чтобы увидеть подходящие товары</div>";
                    }elseif(!empty($_GET['filter_model']) && empty($_GET['filter_brand'])){
                        echo "<div class='ntext'>Укажите марку, чтобы увидеть подходящие товары</div>";


                    }               
                    else{
                        if (!empty($_GET['filt']) ) { 
                      $oqtyx='';
                        if($options){
                            for($i=0;$i<count($options);$i++){
                         
                           if($options[$i]["atr-obz"]=='on') {
                            if(empty($_GET['f_pa_'.$options[$i]['atr-slug']])) 
                            $oqtyx='1';
                           }
                           }}}
                           
  if ($oqtyx=='1' && !empty($_GET['filt']) ) { 
   
     echo "<div class='ntext'>Заполните обязательные поля *</div>";
  }else{

                    while ( have_posts() ) :


                       
                        the_post();

                        
     
                        do_action( 'woocommerce_shop_loop' );
                        ?>
                        <?php 
                        wc_get_template_part( 'content', 'product' );
?>

<?php 
                    endwhile; // end of the loop.
                }
                }
               
            }else{

if (strpos($_SERVER['REQUEST_URI'],'no-rez=1')!==false) {

    echo "<div class='nrez'><div class='nreztext'>Подходящих результатов не найдено</div>";

    echo do_shortcode('[ref_button]');

 } else
echo "<div class='ntext'>Сделайте отбор, чтобы увидеть подходящие товары</div>";          
                }				
            }
        ?>

        <?php 
        if ( ! $fragments ) {
            woocommerce_product_loop_end();}
        ?>

        <?php 

        if ( ! $fragments ) {
            do_action( 'woocommerce_after_shop_loop' );
        }
        ?>

    <?php  else : ?>

        <?php 

        
        do_action( 'woocommerce_no_products_found' );
        ?>

        <?php 
    endif;

    if ( $fragments ) {
        $output = ob_get_clean();
    }

    if ( $fragments ) {
        $output = array(
            'items'       => $output,
            'status'      => ( $max_page > $paged ) ? 'have-posts' : 'no-more-posts',
            'nextPage'    => str_replace( '&#038;', '&', next_posts( $max_page, false ) ),
            'currentPage' => strtok( woodmart_get_current_url(), '?' ),
            'breadcrumbs' => woodmart_current_breadcrumbs( 'shop', true ),
        );

        echo json_encode( $output );
    }
}

}





if(!empty($_POST['filt-del'])){
    $filt_del=$_POST['filt-del'];
    $options=get_option('_new_product_filt', $default = false );
    $options_new=array();
    for($i=0;$i<count($options);$i++){
        if ('x'.$i!=$filt_del)
        $options_new[]=$options[$i];
    }
    update_option( '_new_product_filt', $options_new);
}  

if(!empty($_POST['filopt'])){
    update_option( '_new_product_filt_colvo', $_POST['filcolvo']);
    update_option( '_new_product_filt_title', $_POST['filtitle']);
    update_option( '_new_product_filt_button', $_POST['filbutton']);

    if(!empty($_POST['filvklad']))
        update_option( '_new_product_filt_vklad', '1');
        else
        update_option( '_new_product_filt_vklad', '0');
    
    if(!empty($_POST['filvmb']))
        update_option( '_new_product_filt_vmb', '1');
        else
        update_option( '_new_product_filt_vmb', '0');
    
    if(!empty($_POST['filsize']))
        update_option( '_new_product_filt_size', '1');
        else
        update_option( '_new_product_filt_size', '0');
    
    if(!empty($_POST['filfocus']))
        update_option( '_new_product_filt_focus', '1');
        else
        update_option( '_new_product_filt_focus', '0');

    if(!empty($_POST['filhideprod']))
        update_option( '_new_product_filt_hideprod', '1');
        else
        update_option( '_new_product_filt_hideprod', '0');
        
        update_option( '_new_product_filt_kateg', $_POST['filkateg']);
        update_option( '_new_product_filt_posled', $_POST['filposled']);
}


if(!empty($_POST['filt-ret'])){
    if(!empty($_POST['atr-slug'])){
    $opt_value=array();


    $options=get_option('_new_product_filt', $default = false );
    if($options){
        $opt_value_x=array();  
        $opt_value['atr-name']=$_POST['atr-name'];
        $opt_value['atr-slug']=$_POST['atr-slug']; 
        $opt_value['atr-min']=$_POST['atr-min'];
        $opt_value['atr-max']=$_POST['atr-max'];  
        $opt_value['atr-obz']=$_POST['atr-obz'];

        $options[]=$opt_value;
        update_option( '_new_product_filt', $options);
    }else{
        $opt_value[0]['atr-name']=$_POST['atr-name'];
        $opt_value[0]['atr-slug']=$_POST['atr-slug']; 
    $opt_value[0]['atr-min']=$_POST['atr-min'];
    $opt_value[0]['atr-max']=$_POST['atr-max'];  
    $opt_value[0]['atr-obz']=$_POST['atr-obz'];
    update_option( '_new_product_filt', $opt_value);
  
    }}}


add_action('admin_menu', 'new_product_filt_menu' ); 
function change_plugin_styles() {
	wp_register_style( 'new-product-filt', plugins_url( 'new-product-filt/new-product-filt-admin.css' ) );
	wp_enqueue_style( 'new-product-filt' );
}
add_action('admin_enqueue_scripts', 'change_plugin_styles');

add_action( 'wp_enqueue_scripts', 'true_stili_frontend', 25 );
 
function true_stili_frontend() {
 	wp_enqueue_style( 'true_stili', plugins_url( 'new-product-filt/new-product-filt.css' ),'16','33' );
}

add_action( 'wp_enqueue_scripts', 'my_scripts_method' );
function my_scripts_method(){
	wp_enqueue_script( 'newscript', plugins_url( 'new-product-filt/new-product-filt.js'),'500','12',true);
}



function new_product_filt_menu() {
    add_menu_page('Фильтр товаров', 'Фильтр товаров', 'manage_options', 'new-product-filt/new-product-filt-admin.php', '', 'dashicons-edit' );
    if ( function_exists ( 'add_menu_page' ) ) {
    } }


add_action( 'pre_get_posts', 'action_function_name' );

function action_function_name( $query ) {
    if ( !is_admin() && $query->is_main_query() ){
        $opt_colvo=get_option('_new_product_filt_colvo', $default = false );
      
$query->set('posts_per_page', $opt_colvo);


    }

        }
add_shortcode( 'new_prod_filt', 'new_prod_filt' );







function custom_pre_get_posts_query( $q ) {
$qx=$q;
    if ( !is_admin()){

 

if(is_front_page()){



        }
        if(!empty($_GET['filt']))   {



            if (preg_match('/^\+?\d+$/', $_GET['filt'])) {
        $tax_query=array('relation' => 'AND');      
        $options=get_option('_new_product_filt', $default = false );
   
        for($i=0;$i<count($options);$i++){
           $xfild='f_pa_'.$options[$i]['atr-slug'];
if(!empty($_GET[$xfild])){
    
    $atrib_value=$_GET[$xfild];
    $max=$options[$i]['atr-max'];
if($max=='') $max=10;
    $min=$options[$i]['atr-min'];
if($min=='') $min=10;     
    $atrib_value_max=$atrib_value+$max; 
    $atrib_value_min=$atrib_value-$min; 

$term_slug='pa_'.$options[$i]['atr-slug'];

$terms = get_terms( [
	'taxonomy' => $term_slug,
	'hide_empty' => false,
] );

$sel_terms=array();
foreach($terms as $term){
  $slug=(float)$term->slug; 
if ($slug>=$atrib_value_min && $slug<=$atrib_value_max){
    $sel_terms[]=$slug;

}}

$tax_query_item=array(
    'taxonomy' => $term_slug,
    'field'    => 'slug',
    'terms'    => $sel_terms
        );
array_push($tax_query,$tax_query_item);


}

        }
        }

        
        $opt_colvo=get_option('_new_product_filt_colvo', $default = false );
      
        $q->set('tax_query', array($tax_query) );


               $orderby = array(
                'capacity' => 'ASC'
            );



        $q->set('orderby', $orderby);
    }else{
 
        if(empty($_GET['s'])){

   
                }
            }
      
         
            }
}


    add_action( 'woocommerce_product_query', 'custom_pre_get_posts_query' );




function new_prod_filt( $atts ) {
?>
<script>

jQuery(function( $ ) {

 

  <?php      $opt_vklad=get_option('_new_product_filt_vklad', $default = false );
   $opt_vklad=1;
    if ($opt_vklad==1){?>

  $('#qfilt .wd-nav-tabs li:nth-of-type(1) .wd-nav-link').click(function(){	

    $("#qfilt .wd-nav-tabs li:nth-of-type(1)").addClass('wd-activex');
    $("#qfilt .wd-nav-tabs li:nth-of-type(1)").removeClass('wd-active-no-z');
   $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").removeClass('wd-active-no'); 
   
   $("#qfilt .wd-nav-tabs li:nth-of-type(2)").addClass('wd-active-no-z');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").addClass('wd-activex');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").addClass('wd-inx');
    $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").addClass('wd-active-no');
 
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").removeClass('wd-in');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").removeClass('wd-activex');

  <?php 
  }
if(!empty($_GET['filter_brand']) || !empty($_GET['filter_model'])){
?>
 $(".wd-clear-filters.wd-action-btn").show();
<?php 
}
?>

  });
  <?php      $opt_vklad=get_option('_new_product_filt_vklad', $default = false );
  $opt_vklad=1;
    if ($opt_vklad==1){?>
  $('#qfilt .wd-nav-tabs li:nth-of-type(2) .wd-nav-link').click(function(){	
    $("#qfilt .wd-nav-tabs li:nth-of-type(2)").addClass('wd-activex');

    $("#qfilt .wd-nav-tabs li:nth-of-type(2)").removeClass('wd-active-no-z');
   $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").removeClass('wd-active-no'); 

  $("#qfilt .wd-nav-tabs li:nth-of-type(1)").addClass('wd-active-no-z');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").addClass('wd-activex');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").addClass('wd-inx');

  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").addClass('wd-active-no');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").removeClass('wd-in');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").removeClass('wd-activex');

<?php 
}
if(!empty($_GET['filter_brand']) || !empty($_GET['filter_model'])){
?>
 $(".wd-clear-filters.wd-action-btn").hide();
<?php 
}
?>

  });

});
   
    </script>

<?php 

    if(!empty($_GET['filt']) || !empty($_GET['no-rez']) || !empty($_GET['reset'])) {
?>
  <?php      $opt_vklad=get_option('_new_product_filt_vklad', $default = false );
    if ($opt_vklad==1){?>
<script>

jQuery(function( $ ) {
  $("#qfilt .wd-nav-tabs li:nth-of-type(2)").addClass('wd-activex');
  $("#qfilt .wd-nav-tabs li:nth-of-type(1)").addClass('wd-active-no-z');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").addClass('wd-activex');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(2)").addClass('wd-inx');

  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").addClass('wd-active-no');
  $("#qfilt .wd-tab-content-wrapper .wd-tab-content:nth-of-type(1)").removeClass('wd-in');

  $('#qfilt .wd-nav-tabs li:nth-of-type(1) .wd-nav-link').click(function(){	
    

    $(".wd-clear-filters a").attr('href',$(".wd-clear-filters a").attr('href').replace('?reset=1',''));   
    $(".wd-clear-filters a").trigger('click');
  });

});
   
    </script>
<?php  }elseif(!empty($_GET['filter_brand'])){?>
    <script>

jQuery(function( $ ) {
    $('#qfilt .wd-nav-tabs li:nth-of-type(2) .wd-nav-link').click(function(){	
 });

});
   
    </script>

<?php }}
    $j=0;
   



 $filt_opr='1';
$opt_size=get_option('_new_product_filt_size', $default = false );
if ($filt_opr=='1' && $opt_size==1){

 $filt_title=get_option('_new_product_filt_title', $default = false );

if ($filt_title!='')
$rez='<h5 class="widget-title">'.$filt_title.'</h5>';
else $rez='';

$rez.='<form method="GET" id="new_prod_filt_form" class="new_prod_filt_form" name="new_prod_filt_form">
<input type="hidden" name="filt" value="1">	';

$options=get_option('_new_product_filt', $default = false );

?>
<?php 
if($options){
 for($i=0;$i<count($options);$i++){
   if(!empty($_GET['f_pa_'.$options[$i]['atr-slug']])) 
$xval=$_GET['f_pa_'.$options[$i]['atr-slug']];
else $xval='';
    $req='';
$rez.='<div class="qwer"><label class="lab-filter-fild">'.$options[$i]["atr-name"].'</label>'; 
if($options[$i]["atr-obz"]=='on') {$rez.='<span class="atr-obz">*</span>';$req='filter-fild-req';}
$rez.='<input type="number" min="1" name="f_pa_'.$options[$i]["atr-slug"].'" value="'.$xval.'" class="ffnum filter-fild '.$req.'"></div>';
}}
$geter=$_GET;
foreach($geter as $key => $value){
if($key!=='filt'){
if(strpos($key,'f_pa_')!==false) {}else{ 
    if(strpos($key,'filter_')!==false){}else{
        if ($key!='no-rez')
$rez.='<input type="hidden" name="'.$key.'" value="'.$value.'">';
    }
}
}}
$rez.='<input type="submit" id="filter-fild-but" class="filter-fild-but filter-fild-but1" value="'.get_option('_new_product_filt_button', $default = false ).'">
</form>';
$rez.='';

$rez.='<div id="form_mass" class="form_mass" style="display: none;">Заполните обязательные поля *</div>';
 
}

	return $rez;
}

add_action('wp_footer', 'woocommerce_custom_update_checkout', 50);

function woocommerce_custom_update_checkout()
{
  if (is_checkout()) {
?>
<script type="text/javascript">

  jQuery(document).ready($ => {

  });
</script>
<?php  }}?>
<?php 
add_action( 'woocommerce_after_add_to_cart_button', 'drawing_woocommerce_after_add_to_cart_button_action' );


function drawing_woocommerce_after_add_to_cart_button_action(){
?>
				
<a href="?add-to-cart=<?=get_the_ID()?>" data-quantity="1" class="button wp-element-button single_add_to_cart_button add_to_cart_button ajax_add_to_cart add-to-cart-loop" data-product_id="<?=get_the_ID()?>"  rel="nofollow"><span>В корзину</span></a>			
            
<?php 
	
}

?>