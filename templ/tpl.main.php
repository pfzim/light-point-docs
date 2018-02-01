<?php include("tpl.header.php"); ?>
<script type="text/javascript">
	g_pid = <?php eh($id); ?>;
</script>
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
				<th width="10%">Status</th>
				<th width="10%">bis_unit</th>
				<th width="25%">reg_upr</th>
				<th width="10%">reg_otd</th>
				<th width="10%">contr_name</th>
				<th width="10%">order</th>
				<th width="10%">Date</th>
				<th width="10%">Type</th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($docs as $row) { $i++; ?>
			<tr id="<?php eh("row".$row['id']); ?>" data-id=<?php eh($row['id']);?>>
				<td><a href="?action=doc&id=<?php eh($row['id']); ?>"><?php eh($row['name']); ?></a></td>
				<td><?php eh($g_doc_status[intval($row['status'])]); ?></td>
				<td><?php eh($row['bis_unit']); ?></td>
				<td><?php eh($g_doc_reg_upr[intval($row['reg_upr'])]); ?></td>
				<td><?php eh($g_doc_reg_otd[intval($row['reg_otd'])]); ?></td>
				<td><?php eh($row['contr_name']); ?></td>
				<td><?php eh($row['order']); ?></td>
				<td><?php eh($row['order_date']); ?></td>
				<td><?php eh(doc_type_to_string(intval($row['doc_type']))); ?></td>
			</tr>
		<?php } ?>
			</tbody>
		</table>
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