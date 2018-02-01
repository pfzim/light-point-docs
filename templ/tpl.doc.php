<?php include("tpl.header.php"); ?>
		<h3 align="center">Light Point Docs</h3>
		<span class="command f-right" onclick="f_edit(null, 'form1');">Create document</span>
<div>
	<div style="float: left">
		<ul style="list-style-type: none;margin-bottom: 0px;margin-left: 0px;margin-right: 0px;margin-top: 0px;overflow-wrap: break-word;padding-left: 0px;">
		<?php $i = 0; foreach($sections as $row) { $i++; ?>
		<li><a href="?id=<?php eh($row[0]); ?>"><?php eh($row[1]); ?></a></li>
		<?php } ?>
		</ul>
	</div>
	<div>
		<table id="table" class="main-table">
			<thead>
			<tr>
				<th width="20%">Name</th>
				<th width="10%">Дата создания</th>
				<th width="10%">Дата изменения</th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($docs as $row) { $i++; ?>
			<tr id="<?php eh("row".$row['id']); ?>" data-id=<?php eh($row['id']);?>>
				<td><a href="?action=download&id=<?php eh($row['id']); ?>"><?php eh($row['name']); ?></a></td>
				<td><?php eh($row['create_date']); ?></td>
				<td><?php eh($row['modify_date']); ?></td>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<form method="post" id="file-upload" name="file-upload">		
			<input type="hidden" name="id" value="<?php eh($id); ?>"/>
			<input id="upload" type="file" name="file" style="display: none"/>
		</form>
		<!--<input id="upload" type="file" name="myfile" size="100" multiple="multiple" style="display: none">-->
		<div id="dropzone">Перетащите сюда или <a href="#" onclick="gi('upload').click(); return false;">выберите</a> файлы для загрузки</div>
<script type="text/javascript">
	gi('upload').onchange = function() { f_upload(0) };
</script>

	</div>
</div>
		<br />
		<br />
		<div id="edit-container" class="modal-container" style="display: none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<form id="form1">
				<h3>Create document</h3>
				<input id="edit_id" name="edit_id" type="hidden" value=""/>
				<div class="form-title"><label for="reg_upr">Региональное управление*:</label></div>
				<input class="form-field" name="reg_upr" type="edit" value=""/>
				<div class="form-title"><label for="reg_otd">Региональное отделение*:</label></div>
				<input class="form-field" name="reg_otd" type="edit" value=""/>
				<div class="form-title"><label for="bis_unit">Бизнес юнит*:</label></div>
				<input class="form-field" name="bis_unit" type="edit" value=""/>
				<div class="form-title">Тип документа*:</div>
				<span><input name="doc_type_1" type="checkbox" value="1"/><label for="doc_type_1">Торг12</label></span>
				<span><input name="doc_type_2" type="checkbox" value="1"/><label for="doc_type_2">СФ</label></span>
				<span><input name="doc_type_3" type="checkbox" value="1"/><label for="doc_type_3">1Т</label></span>
				<span><input name="doc_type_4" type="checkbox" value="1"/><label for="doc_type_4">Доверенность</label></span>
				<span><input name="doc_type_5" type="checkbox" value="1"/><label for="doc_type_5">Справка А</label></span>
				<span><input name="doc_type_6" type="checkbox" value="1"/><label for="doc_type_6">Справка Б</label></span>
				<div class="form-title"><label for="order">Номер ордера*:</label></div>
				<input class="form-field" name="order" type="edit" value=""/>
				<div class="form-title"><label for="order_date">Дата ордера*:</label></div>
				<input class="form-field" name="order_date" type="edit" value=""/>
				<div class="form-title"><label for="contr_name">Наименование контрагента*:</label></div>
				<input class="form-field" name="contr_name" type="edit" value=""/>
				<div class="form-title"><label for="info">Описание:</label></div>
				<input class="form-field" name="info" type="edit" value=""/><br />
				<button class="form-button" type="button" onclick="f_save();">Save</button>
				</form>
			</div>
		</div>
<?php include("tpl.footer.php"); ?>