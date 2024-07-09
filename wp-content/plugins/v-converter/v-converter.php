<?
/*
 * Plugin Name: Плагин v-converter
 */

 ini_set('display_errors','On');
// error_reporting(0);

if(!empty($_POST['proc-converter'])){
  $proc_converter=$_POST['proc-converter'];
  update_option( '_proc_converter', $proc_converter);
}
if(!empty($_POST['okrugl'])){
  $okrugl=$_POST['okrugl'];
  update_option( '_okrugl', $okrugl);
}
 
 add_action('admin_menu', 'vconverter_menu' ); 

 if(!empty($_POST['log-price'])){
  $sale_args = array(
    'posts_per_page' => 50000000,
        'post_type'      => 'product',
    'meta_query'     => array(
        'relation' => 'OR',
            array(
                'key'           => '_sale_price',
                'value'         => 0,
                'compare'       => '>',
                'type'          => 'numeric'
            ),
            array(
                'key'           => '_min_variation_sale_price',
                'value'         => 0,
                'compare'       => '>',
                'type'          => 'numeric'
        )
    )
);
$posts = get_posts( $sale_args );
$html='';
foreach ( $posts as $post ) {
$html.='ID '.$post->ID.' | '.$post->post_title.'<br>';
}
file_put_contents(WP_PLUGIN_DIR . '/v-converter/date_price.html',$html);
 } 







function vconverter_menu() {
  add_menu_page('v-converter', 'v-converter', 'manage_options', 'v-converter/v-converter-admin.php', '', 'dashicons-edit' );
  if ( function_exists ( 'add_menu_page' ) ) {
  } }


  function get_currency($currency_code, $format) {


    $date = date('d/m/Y'); // Текущая дата
    $cache_time_out = 14400; // Время жизни кэша в секундах
  
    $file_currency_cache = './currency.xml'; // Файл кэша
  
    if(!is_file($file_currency_cache) || filemtime($file_currency_cache) < (time() - $cache_time_out)) {
  
      $ch = curl_init();
  
      curl_setopt($ch, CURLOPT_URL, 'https://www.cbr.ru/scripts/XML_daily.asp?date_req='.$date);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1); 
      curl_setopt($ch, CURLOPT_HEADER, 0);
  
      $out = curl_exec($ch);
  
      curl_close($ch);
  
      file_put_contents($file_currency_cache, $out);
  
    }
  
    $content_currency = simplexml_load_file($file_currency_cache);
  
    return number_format(str_replace(',', '.', $content_currency->xpath('Valute[CharCode="'.$currency_code.'"]')[0]->Value), $format);
  
  }

  function my_price($price, $_product)
{
  //update_option( '_old_curs', '99');
  $pid=get_the_ID();
  $valuta=get_field('валюты',$pid);
  if ($valuta=='') $valuta='USD';
  if ($valuta!='Рубли' && get_field('цена_в_валюте',$pid)!='' ){
    $proc_converter=get_option('_proc_converter', $default = false );
$price=get_field('цена_в_валюте',$pid);

if($valuta=='USD')
$old_curs=get_option('_old_curs', $default = false );
if($valuta=='EUR')
$old_curs=get_option('_old_curs_eur', $default = false );
if($valuta=='CNY')
$old_curs=get_option('_old_curs_cny', $default = false );


$okrugl=get_option('_okrugl', $default = false );

$kurs = get_currency($valuta, 0);

$proc=($kurs/100)*$proc_converter;
 $proc=round($proc, 0, PHP_ROUND_HALF_UP);
$plus=$old_curs+$proc;

$minus=$old_curs-$proc;
 if ($old_curs!=''){

$new_price = $price * ($old_curs);

$today=date("d.m.Y");
$current = file_get_contents(WP_PLUGIN_DIR . '/v-converter/date_kurs.txt');

if($kurs>=$plus || $kurs<=$minus){

  if($valuta=='USD')
  update_option( '_old_curs', $kurs);
  if($valuta=='EUR')
  update_option( '_old_curs_eur', $kurs);
  if($valuta=='CNY')
  update_option( '_old_curs_cny', $kurs);

  $today=date("d.m.Y");
  $current = file_get_contents(WP_PLUGIN_DIR . '/v-converter/date_kurs.txt');
  if ($today!=$current){

   file_put_contents(WP_PLUGIN_DIR . '/v-converter/date_kurs.txt', $today);
 $fh = fopen(WP_PLUGIN_DIR . '/v-converter/update_kurs.txt', 'c');
  fseek($fh, 0, SEEK_END); 
  fwrite($fh, PHP_EOL . $today.'-----------------------------------'); 
  fwrite($fh, PHP_EOL . $valuta.' Было: '.$old_curs.' Стало: '.$kurs); 
  fclose($fh);
  }
}
  }else{
  $new_price=$price;
  if($valuta=='USD')
  update_option( '_old_curs', $kurs);
  if($valuta=='EUR')
  update_option( '_old_curs_eur', $kurs);
  if($valuta=='CNY')
  update_option( '_old_curs_cny', $kurs);
  
  }

  if($okrugl=='' || $okrugl=='0') $okrugl=100;
  $new_price=ceil($new_price/$okrugl)*$okrugl;

return $new_price; // новая цена
}else return $price;
}



add_filter('woocommerce_product_get_price', 'my_price',100,2);
