<?php include("tpl.header.php"); ?>
		<h3 align="center">Light Point Docs</h3>
<div>
	<div style="float: left; width: 200px;">
		<ul style="list-style-type: none;margin-bottom: 0px;margin-left: 0px;margin-right: 0px;margin-top: 0px;overflow-wrap: break-word;padding-left: 0px;">
		<?php $i = 0; foreach($sections as &$row) { $i++; ?>
		<li><a href="?id=<?php eh($row[0]); ?>"><?php eh($row[1]); ?></a></li>
		<?php } ?>
		</ul>
	</div>
	<div style="overflow: hidden;">
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
		<span class="command" onclick="f_edit(<?php eh($id); ?>, 'form1');">Изменить</span>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%">Name</th>
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
		<div id="form1-container" class="modal-container" style="display: none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<form id="form1">
				<h3>Create document</h3>
				<input name="id" type="hidden" value=""/>
				<input name="pid" type="hidden" value=""/>
				<div class="form-title"><label for="reg_upr">Региональное управление*:</label></div>
				<select class="form-field" id="reg_upr" name="reg_upr">
				<?php for($i = 1; $i < count($g_doc_reg_upr); $i++) { ?>
					<option value="<?php eh($i); ?>"><?php eh($g_doc_reg_upr[$i]); ?></option>
				<?php } ?>
				</select>
				<div id="reg_upr-error" class="form-error"></div>
				<div class="form-title">Региональное отделение*:</div>
				<select class="form-field" name="reg_otd">
				<?php for($i = 1; $i < count($g_doc_reg_otd); $i++) { ?>
					<option value="<?php eh($i); ?>"><?php eh($g_doc_reg_otd[$i]); ?></option>
				<?php } ?>
				</select>
				<div id="reg_otd-error" class="form-error"></div>
				<div class="form-title"><label for="bis_unit">Бизнес юнит*:</label></div>
				<input class="form-field" id="bis_unit" name="bis_unit" type="edit" value=""/>
				<div id="bis_unit-error" class="form-error"></div>
				<div class="form-title">Тип документа*:</div>
				<span><input id="doc_type_1" name="doc_type_1" type="checkbox" value="1"/><label for="doc_type_1">Торг12</label></span>
				<span><input id="doc_type_2" name="doc_type_2" type="checkbox" value="1"/><label for="doc_type_2">СФ</label></span>
				<span><input id="doc_type_3" name="doc_type_3" type="checkbox" value="1"/><label for="doc_type_3">1Т</label></span>
				<span><input id="doc_type_4" name="doc_type_4" type="checkbox" value="1"/><label for="doc_type_4">Доверенность</label></span>
				<span><input id="doc_type_5" name="doc_type_5" type="checkbox" value="1"/><label for="doc_type_5">Справка А</label></span>
				<span><input id="doc_type_6" name="doc_type_6" type="checkbox" value="1"/><label for="doc_type_6">Справка Б</label></span>
				<div id="doc_type_1-error" class="form-error"></div>
				<div class="form-title"><label for="order">Номер ордера*:</label></div>
				<input class="form-field" id="order" name="order" type="edit" value=""/>
				<div id="order-error" class="form-error"></div>
				<div class="form-title"><label for="order_date">Дата ордера*:</label></div>
				<input class="form-field" id="order_date" name="order_date" type="edit" value=""/>
				<div id="order_date-error" class="form-error"></div>
				<div class="form-title"><label for="contr_name">Наименование контрагента*:</label></div>
				<input class="form-field" id="contr_name" name="contr_name" type="edit" value=""/>
				<div id="contr_name-error" class="form-error"></div>
				<div class="form-title">Статус документа*:</div>
				<select class="form-field" name="status">
				<?php for($i = 1; $i < count($g_doc_status); $i++) { ?>
					<option value="<?php eh($i); ?>"><?php eh($g_doc_status[$i]); ?></option>
				<?php } ?>
				</select>
				<div id="status-error" class="form-error"></div>
				<div class="form-title"><label for="info">Описание:</label></div>
				<input class="form-field" id="info" name="info" type="edit" value=""/><br />
				<div id="info-error" class="form-error"></div>
				<button class="form-button" type="button" onclick="f_save('form1');">Save</button>
				</form>
			</div>
		</div>
<?php include("tpl.footer.php"); ?>