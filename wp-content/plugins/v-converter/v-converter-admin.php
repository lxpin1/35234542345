<style>

	</style>
<?
$proc_converter=get_option('_proc_converter', $default = false );
$okrugl=get_option('_okrugl', $default = false );

?>

<div class="wrap">
<h1 class="wp-heading-inline">Настройки v-converter</h1>
<p></p>

<table class="iksweb">
	<tbody>
		<tr>
		<td>Процент для обновления курса</td>
		<td>Округление до(10,100,1000...)</td>
		</tr>
		<tr>
            <form method="POST">
			<td><input type="text" name="proc-converter" value="<?=$proc_converter?>"></td>
			<td><input type="text" name="okrugl" value="<?=$okrugl?>"></td>
			<td><button type="submit" class="button button-default">Сохранить</button></td>
</form>
		</tr>
	</tbody>
</table>
<p>
	<p>
<form method="POST">
<input type="hidden" name="log-price" value="1">
<button type="submit" class="button button-default">Создать лог товаров с двойной ценой</button>
</form>
<p>
<a href="/wp-content/plugins/v-converter/date_price.html" target="_blank">Файл логов</a>
</div>

<??>