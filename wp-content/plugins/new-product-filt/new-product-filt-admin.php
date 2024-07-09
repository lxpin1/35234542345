<style>
	.filopt {
    margin-bottom: 15px;
}
.filopt label {
    width: 200px;
    display: inline-block;
	float: left;
}
textarea.filkateg {
    min-width: 200px;
    min-height: 100px;
}
.filcolvo {
    margin-top: 40px;
}
button.button.button-default {
    margin-top: 40px;
}

.filposled {
  width: 500px;
  height: 150px;
}

	</style>
<?
$options=get_option('_new_product_filt', $default = false );
$opt_colvo=get_option('_new_product_filt_colvo', $default = false );
$opt_title=get_option('_new_product_filt_title', $default = false );
$opt_button=get_option('_new_product_filt_button', $default = false );
$opt_focus=get_option('_new_product_filt_focus', $default = false );
$opt_hideprod=get_option('_new_product_filt_hideprod', $default = false );
$opt_kateg=get_option('_new_product_filt_kateg', $default = false );
$opt_size=get_option('_new_product_filt_size', $default = false );
$opt_vmb=get_option('_new_product_filt_vmb', $default = false );
$opt_vklad=get_option('_new_product_filt_vklad', $default = false );
$opt_posled=get_option('_new_product_filt_posled', $default = false );
?>

<div class="wrap">
<h1 class="wp-heading-inline">Настройки фильтра товаров</h1>
<p></p>


<p>Шорткод: [new_prod_filt]</p>

<p></p>
<h2>Добавить атрибут для фильтра:</h2>

<table class="iksweb">
	<tbody>
		<tr>
        <td>Название</td>
			<td>Ярлык(slug)  атрибута</td>
			<td>Минимум</td>
			<td>Максимум</td>
            <td>Обязательный?</td>
			<td></td>
		</tr>
		<tr>
            <form method="POST">
            <td><input type="text" name="atr-name"></td>
			<td><input type="text" name="atr-slug"></td>
			<td><input type="number" name="atr-min"></td>
			<td><input type="number" name="atr-max"></td>
            <td><input type="checkbox" name="atr-obz"></td>
			<td><button type="submit" class="button button-default">Добавить</button></td>
            <input type="hidden" name="filt-ret" value="1">
</form>
		</tr>
	</tbody>
</table>



<p></p>
<h2>Атрибуты для фильтра:</h2>

<table class="iksweb">
	<tbody>
		<tr>
        <td>Название</td>
			<td>Ярлык(slug)  атрибута</td>
			<td>Минимум</td>
			<td>Максимум</td>
            <td>Обязательный</td>
			<td></td>
		</tr>
        <? 
        if($options){
        for($i=0;$i<count($options);$i++){?>
            <form method="POST">
		<tr>
        <td><?=$options[$i]['atr-name'];?></td>
			<td><?=$options[$i]['atr-slug'];?></td>
			<td><?=$options[$i]['atr-min'];?></td>
			<td><?=$options[$i]['atr-max'];?></td>
            <td><? if($options[$i]['atr-obz']=='on') echo 'Да'; else echo 'Нет';?></td>
			<td><button type="submit" class="button button-default">Удалить</button></td>
            <input type="hidden" name="filt-del" value="<?='x'.$i?>">
        		</tr>
        </form>
        <? }}?>
	</tbody>
</table>


<p></p>

<div class="filcolvo">
<form method="POST">
<div class="filopt">
<label>Отображать фильтр по размерам:</label>
<input type="checkbox" <? if($opt_size=='1'){?>checked<? }?> name="filsize" value="1">
		</div>
		<div style="clear: both;"></div>
		<p></p>
<div class="filopt">
<label>Заголовок формы:</label>
<input type="text" name="filtitle" value="<?=$opt_title;?>">
		</div>
		<div class="filopt">
<label>Заголовок кнопки:</label>
<input type="text" name="filbutton" value="<?=$opt_button;?>">
		</div>
		<hr>
		<h2>Скрывать товаров до применения фильтра:</h2>
		<div class="filopt">
<label>Скрывать товары:</label>
<input type="checkbox" <? if($opt_hideprod=='1'){?>checked<? }?> name="filhideprod" value="1">
		</div>
		<div style="clear: both;"></div>
<div class="filopt">
<label>Не скрывать товары в категориях(slug категорий через запятую, без пробелов):</label>
<textarea class="filkateg" name="filkateg"><?=$opt_kateg?></textarea>
</div>
<div class="filopt">
<label>Количесто выводимых товаров:</label>
<input type="text" name="filcolvo" value="<?=$opt_colvo;?>">
		</div>
<hr>
<h2>WoodMart Nav Filter:</h2>
<div class="filopt">
<textarea class="filposled" name="filposled"><?=$opt_posled?></textarea>	
		</div>

<div class="filopt">
<label>Не видно фильтра модели, пока не применен фильтр Бренда:</label>
<input type="checkbox" <? if($opt_vmb=='1'){?>checked<? }?> name="filvmb" value="1">
		</div>
		<div style="clear: both;"></div>
		<p></p>
		<div class="filopt">
<label>Задать класс для списка атрибутов при фокусе на поле ввода (filter-list-focus):</label>
<input type="checkbox" <? if($opt_focus=='1'){?>checked<? }?> name="filfocus" value="1">
		</div>
		<div style="clear: both;"></div>
		<p></p>

		<hr>
<h2>Другое:</h2>




		
<div class="filopt">
<label> Остаемся на своей вкладке после применения фильтров:</label>
<input type="checkbox" <? if($opt_vklad=='1'){?>checked<? }?> name="filvklad" value="1">
		</div>
		<div style="clear: both;"></div>
		<p></p>
<p>Вывод кнопки (Отправить запрос на подбор): [ref_button]</p>


		<div style="clear: both;"></div>
		
<input type="hidden" name="filopt" value="1">		
<button type="submit" class="button button-default">Сохранить</button>
</form>
</div>

</div>

<??>