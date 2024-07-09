<style>
table.iksweb{
	width: 100%;
	border-collapse:collapse;
	border-spacing:0;
	height: auto;
}
table.iksweb,table.iksweb td, table.iksweb th {
	border: 1px solid #cfcccc;
    background: #fff;
    padding-left: 10px;
}
table.iksweb td,table.iksweb th {
	padding: 3px;
	width: 30px;
	height: 35px;
}
table.iksweb th {
	background: #347c99; 
	color: #fff; 
	font-weight: normal;
}

	</style>
<?
$options=get_option('_cf7_filter', $default = false );

?>

<div class="wrap">
<h1 class="wp-heading-inline">Настройки cf7 фильтра</h1>
<p></p>




<h2>Добавить диапазон для атрибута:</h2>

<table class="iksweb">
	<tbody>
		<tr>
		<td>ID формы, на которой располагается поле</td>
			<td>Ярлык(slug)  атрибута</td>
			<td>Минимум</td>
			<td>Максимум</td>
			<td></td>
		</tr>
		<tr>
            <form method="POST">
			<td><input type="text" name="cf7-atr-id"></td>
			<td><input type="text" name="cf7-atr-slug"></td>
			<td><input type="number" name="cf7-atr-min"> <input type="checkbox" name="cf7-atr-min-proc" value="1"> %</td>
			<td><input type="number" name="cf7-atr-max"> <input type="checkbox" name="cf7-atr-max-proc" value="1"> %</td>
			<td><button type="submit" class="button button-default">Добавить</button></td>
            <input type="hidden" name="cf7-filt-ret" value="1">
</form>
		</tr>
	</tbody>
</table>



<p></p>
<h2>Диапазоны для атрибутов:</h2>

<table class="iksweb">
	<tbody>
		<tr>
		<td>ID формы, на которой располагается поле</td>
			<td>Ярлык(slug)  атрибута</td>
			<td>Минимум</td>
			<td>Максимум</td>
			<td></td>
		</tr>
        <? 
        if($options){
        for($i=0;$i<count($options);$i++){?>
            <form method="POST">
		<tr>
			<td><?=$options[$i]['cf7-atr-id'];?></td>
			<td><?=$options[$i]['cf7-atr-slug'];?></td>
			<td><?=$options[$i]['cf7-atr-min']; if ($options[$i]['cf7-atr-min-proc']) echo '%';?></td>
			<td><?=$options[$i]['cf7-atr-max']; if ($options[$i]['cf7-atr-max-proc']) echo '%';?></td>
			<td><button type="submit" class="button button-default">Удалить</button></td>
            <input type="hidden" name="cf7-filt-del" value="<?='x'.$i?>">
        		</tr>
        </form>
        <? }}?>
	</tbody>
</table>


<p></p>



</div>

<??>