<?

/*
 * Plugin Name: Плагин generate-svg
 * Description: Отображает SVG с аттрибутами / габаритами или весом товара. Использование: [svg-dimenisons] / [svg-dimenisons_2]
 * Author: Alex Sin
 */


$product_id=get_the_ID();

function generate_svg( $atts ) {

//Атрибуты
//$pa_length=get_the_terms(get_the_ID(),'pa_length')[0]->name; //Длина    
//$pa_width=get_the_terms(get_the_ID(),'pa_width')[0]->name; //Ширина
//$pa_height=get_the_terms(get_the_ID(),'pa_height')[0]->name; //Высота    
//$pa_capacity=get_the_terms(get_the_ID(),'pa_capacity')[0]->name; //Емкость 

//Габариты
$length=get_post_meta(get_the_ID(),'_length')[0];//Длина   
$width=get_post_meta(get_the_ID(),'_width')[0];//Ширина  
$height=get_post_meta(get_the_ID(),'_height')[0];//Высота  

//Вес
$weight=get_post_meta(get_the_ID(),'_weight')[0];

$svg='
<svg width="295" height="195" viewBox="0 0 310 195" fill="none" xmlns=" (http://www.w3.org/2000/svg)http://www.w3.org/2000/svg" (http://www.w3.org/2000/svg) class="wp-block-woocommerce-product-shipping-dimensions-fields__dimensions-image">

<path d="M11.5664 134.604V35.3599C11.5664 33.9482 12.9862 32.9782 14.3014 33.4915L99.6373 66.7959C100.4 67.0935 100.905 67.8243 100.914 68.6426L102.037 171.578C102.052 173.027 100.574 174.014 99.2419 173.444L12.7831 136.448C12.0451 136.132 11.5664 135.407 11.5664 134.604Z" fill="#FFFFFF"></path>

<path d="M11.5664 134.603V35.3599C11.5664 33.9482 12.9862 32.9782 14.3014 33.4915L99.624 66.7908C100.393 67.0909 100.9 67.8314 100.901 68.6569L101.024 174.131L12.7844 136.447C12.0457 136.132 11.5664 135.406 11.5664 134.603Z" stroke="#E0E0E0" stroke-width="2.00574"></path>

<path d="M1.25977 150.388L86.0112 188.183" stroke="#CCCCCC" stroke-width="1.50431" stroke-miterlimit="16"></path>

<path d="M250.775 32.9793L100.9 66.9577V172.981C100.9 174.297 102.146 175.257 103.418 174.921L251.73 135.764C252.611 135.531 253.224 134.735 253.224 133.824V34.9354C253.224 33.6488 252.03 32.6948 250.775 32.9793Z" fill="#FFFFFF" stroke="#E0E0E0" stroke-width="2.00574"></path>

<path d="M270.402 28.9875V132.064" stroke="#CCCCCC" stroke-width="1.50431" stroke-miterlimit="16"></path>

<path d="M257.804 152.679L107.771 192.765" stroke="#CCCCCC" stroke-width="1.50431" stroke-miterlimit="16"></path>

<path d="M13.1406 33.41L161.446 1.61817C161.808 1.54066 162.184 1.56462 162.533 1.68742L251.16 32.8868" stroke="#E0E0E0" stroke-width="2.00574"></path>
<text x="280" y="96" font-size="14" fill="#949494">'.$height.'</text>
<text x="188" y="190" font-size="14" fill="#949494">'.$length.'</text>
<text x="18" y="185" font-size="14" fill="#949494">'.$width.'</text>
</svg>
';
return '<div style="display: flex; justify-content: center;">'. $svg .'</div>'.
'<table class="woocommerce-product-attributes shop_attributes">
	<tbody>
		<tr class="woocommerce-product-attributes-item">
			<th class="woocommerdce-product-attributes-item__label"> <span class="wd-adttr-name">  Вес  </span>
			</th>
			<td class="woocommerce-product-attributes-item__value">'	.$weight.	' кг
			</td>
		</tr>
		<tr class="woocommerce-product-attributes-item">
			<th class="woocommerce-product-attributes-item__label"><span class="wd-attr-name">		Габариты		</span>				
			</th>
			<td class="woocommerce-product-attributes-item__value">		'.$length.' x '.$width.' x '.$height.' мм			
			</td>
		</tr>
	</tbody>
</table>' ; 

}
add_shortcode('svg-dimenisons', 'generate_svg');


//  Вилы

function generate_svg_forks( $atts ) {

$pa_length=get_the_terms(get_the_ID(),'pa_length')[0]->name; //Длина
$pa_width=get_the_terms(get_the_ID(),'pa_width')[0]->name; //Ширина
$pa_capacity=get_the_terms(get_the_ID(),'pa_capacity')[0]->name; //Емкость
$pa_att_class=get_the_terms(get_the_ID(),'pa_att-class')[0]->name; //класс каретки
$pa_thickness=get_the_terms(get_the_ID(),'pa_thickness')[0]->name; 
$pa_height=get_the_terms(get_the_ID(),'pa_height')[0]->name; //Высота

$svg='
<svg version="1.2" width="480" height="400" viewBox="5000 0 11000 18000" fill-rule="evenodd" stroke-width="28.222" stroke-linejoin="round" xmlns="http://www.w3.org/2000/svg" xml:space="preserve"><g class="Page">
<path fill="#b1b2b4" d="m13847 7395-251 146c0-100-2-200-3-299l81-367 176-90-3 610z" class="svg-elem-1"></path>
<path fill="#dcdcdc" d="m13594 6625 234 145-163 84-74-51c0-58 1-118 3-178z" class="svg-elem-2"></path>
<path fill="#dcdcdc" d="M13590 6914v-83l61 42-59 269c-1-76-2-151-2-228z" class="svg-elem-3"></path>
<path fill="#b1b2b4" d="m13722 2125-111-13-10-484 209-192 1 604-89 85z" class="svg-elem-4"></path>
<path fill="#dcdcdc" d="m13663 1283 124 78-188 173-9-470 127 80-64 82-26 34 36 23z" class="svg-elem-5"></path>
<path fill="#b1b2b4" d="M13543 992c10 2809 41 8250 29 8427-6 85-18 167-48 240-14 36-33 69-57 100-25 30-61 55-91 82-226 150-1921 1098-3478 1971-185 104-362 203-527 296-384 215-748 420-1063 598l-1923 1028-1927 896c-89 33-188 70-267 100s-149 56-213 78c-63 21-120 38-173 49-53 12-101 18-149 18-75 0-152-34-228-51-180-76-643-313-696-349-26-17-49-34-68-51-18-14-32-27-44-40-4-15-8-28-11-39-4-12-15-56-16-71-1-7-2-16-2-25 0-5 0-11-1-17 2 2 112 85 128 95 16 9 678 382 742 406 66 26 181 44 184 44 17 0 81-13 121-19 47-12 100-29 158-50 115-43 249-101 389-168 279-132 525-277 787-415 420-242 2203-1315 3305-1972 124-85 4405-2591 4423-2601 19-9 40-21 62-35 44-29 91-67 136-121 44-54 85-123 115-213 15-45 27-95 36-150s14-116 15-183c0-16-23-5363-32-7606l384-252z" class="svg-elem-6"></path>
<path fill="#dcdcdc" d="m13543 936-411 264-1317-752 440-298 1288 786z" class="svg-elem-7"></path>
<path fill="#dcdcdc" d="M11808 8120c-3-2541-8-5799-10-7623l1310 748c9 2243 31 7638 30 7654l-1330-779z" class="svg-elem-8"></path>
<path fill="#dcdcdc" d="M11789 8363c7-42 17-184 17-185l1328 777c-5 95-17 133-46 221l-1309-766c4-14 7-30 10-47z" class="svg-elem-9"></path>
<path fill="#dcdcdc" d="M11726 8550c13-21 38-85 40-89l1303 764c-24 55-53 101-84 138-40 50-84 85-124 111-12 8-23 15-35 21l-1291-752c144-107 178-171 191-193z" class="svg-elem-10"></path>
<path fill="#dcdcdc" d="M3734 13326c204-89 5167-3025 7764-4545l1275 742c-6 2-5133 3039-7699 4558-207 120-506 281-784 413-138 66-271 124-385 166-56 20-101 33-152 49-22 5-114 17-116 17-45-3-98-17-159-40-62-24-718-393-733-403-15-9-149-101-151-104-1-1-5-16 1-31 262-215 952-740 1139-822z" class="svg-elem-11"></path>
<path fill="none" stroke="#dcdcdc" stroke-width="100" d="M14340 1875v4750" class="svg-elem-12"></path>
<path fill="none" stroke="#dcdcdc" stroke-width="100" d="m13840 10375-8750 5000" class="svg-elem-13"></path>
<path fill="none" stroke="#dcdcdc" stroke-width="100" d="m1625 14694 1250 681" class="svg-elem-14"></path>
<path fill="none" stroke="#dcdcdc" stroke-width="100" d="M14325 9125v500" class="svg-elem-15"></path>
<text x="14800" y="4500" font-size="800" fill="#000000">'.'Класс: '.$pa_att_class.'</text>
<text x="9000" y="14000" font-size="800" fill="#000000">'.$pa_length.' мм'.'</text>
<text x="80" y="16000" font-size="800" fill="#000000">'.$pa_width.' мм'.'</text>
<text x="14800" y="9600" font-size="800">'.$pa_thickness.' мм'.'</text>
</g>
</svg>





';

return $svg;
}
add_shortcode('svg-dimenisons_forks', 'generate_svg_forks');
