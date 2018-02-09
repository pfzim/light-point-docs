<?php include("tpl.header.php"); ?>
		<h3 align="center">Документ</h3>
<div>
<?php include("tpl.menu.php"); ?>
	<div style="overflow: hidden;">
		<div class="doc-info">
		<table>
			<tbody>
			<tr>
				<td>Наименование:</td><td><?php eh($doc[0]['name']); ?></td>
			</tr>
			<tr>
				<td>Статус:</td><td><?php eh($g_doc_status[intval($doc[0]['status'])]); ?></td>
			</tr>
			<tr>
				<td>Бизнес юнит:</td><td><?php eh($doc[0]['bis_unit']); ?></td>
			</tr>
			<tr>
				<td>Региональное управление:</td><td><?php eh($g_doc_reg_upr[intval($doc[0]['reg_upr'])]); ?></td>
			</tr>
			<tr>
				<td>Региональное отделение:</td><td><?php eh($g_doc_reg_otd[intval($doc[0]['reg_otd'])]); ?></td>
			</tr>
			<tr>
				<td>Контрагент:</td><td><?php eh($doc[0]['contr_name']); ?></td>
			</tr>
			<tr>
				<td>Ордер:</td><td><?php eh($doc[0]['order']); ?></td>
			</tr>
			<tr>
				<td>Дата ордера:</td><td><?php eh($doc[0]['order_date']); ?></td>
			</tr>
			<tr>
				<td>Тип документа:</td><td><?php eh(doc_type_to_string(intval($doc[0]['doc_type']))); ?></td>
			</tr>
			<tr>
				<td>Описание:</td><td><?php eh($doc[0]['info']); ?></td>
			</tr>
			</tbody>
		</table>
		<button class="button-accept" type="button" onclick="f_edit(<?php eh($id); ?>, 'form1');">Изменить</button>
		</div>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%">Имя файла</th>
				<th width="10%">Дата создания</th>
				<th width="10%">Дата изменения</th>
				<th width="10%">Операции</th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($files as &$row) { $i++; ?>
			<tr id="<?php eh("row".$row['id']); ?>" data-id=<?php eh($row['id']);?>>
				<td><a href="?action=download&id=<?php eh($row['id']); ?>"><?php eh($row['name']); ?></a></td>
				<td><?php eh($row['create_date']); ?></td>
				<td><?php eh($row['modify_date']); ?></td>
				<td>
					<span class="command" onclick="f_delete_file(event);">Удалить</span>
					<span class="command" onclick="f_replace_file(event);">Заменить</span>
				</td>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<form method="post" id="file-upload" name="file-upload">		
			<input type="hidden" id="file-upload-id" name="id" value="0"/>
			<input type="hidden" name="pid" value="<?php eh($id); ?>"/>
			<input id="upload" type="file" name="file[]" multiple="multiple" onchange="f_upload();" style="display: none"/>
		</form>
		<div id="dropzone">Перетащите сюда или <a href="#" onclick="gi('file-upload-id').value = 0; gi('upload').click(); return false;">выберите</a> файлы для загрузки</div>
		<script type="text/javascript">
			lpd_init();
		</script>

	</div>
</div>
		<br />
		<br />
<?php
	include("tpl.form-doc.php"); 
	include("tpl.footer.php"); 
?>