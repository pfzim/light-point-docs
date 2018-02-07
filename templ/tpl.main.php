<?php include("tpl.header.php"); ?>
<script type="text/javascript">
	g_pid = <?php eh($id); ?>;
</script>
		<h3 align="center">Light Point Docs</h3>
<div>
<?php include("tpl.menu.php"); ?>
	<div style="overflow: hidden;">
		<span class="command" onclick="f_edit(null, 'form1');">Создать документ</span>
		<table id="table" class="main-table" width="100%">
			<thead>
			<tr>
				<th width="20%">Нименование</th>
				<th width="10%">Статус</th>
				<th width="10%">Бизнес юнит</th>
				<th width="25%">Региональное управление</th>
				<th width="10%">Региональное отделение</th>
				<th width="10%">Контрагент</th>
				<th width="10%">Ордер</th>
				<th width="10%">Дата ордера</th>
				<th width="10%">Тип документа</th>
				<th width="10%">Операции</th>
			</tr>
			</thead>
			<tbody id="table-data">
		<?php $i = 0; foreach($docs as &$row) { $i++; ?>
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
				<td>
					<span class="command" onclick="f_delete_doc(event);">Удалить</span>
				</td>
			</tr>
		<?php } ?>
			</tbody>
		</table>
	</div>
</div>
		<br />
		<br />
<?php
	include("tpl.form-doc.php"); 
	include("tpl.footer.php"); 
?>