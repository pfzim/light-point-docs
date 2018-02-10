<?php include("tpl.header.php"); ?>
<script type="text/javascript">
	g_pid = <?php eh($id); ?>;
</script>
		<h3 align="center">Портал</h3>
<div>
<?php include("tpl.menu.php"); ?>
	<div class="content-box">
		<span class="command" onclick="f_edit(null, 'form1');">Создать документ</span>
		<table id="table" class="main-table" width="100%">
			<thead>
			<tr>
				<th width="20%"><a href="?id=<?php eh($id); ?>&amp;offset=<?php eh($offset); ?>&amp;sort=1&amp;direction=<?php eh((!$direction && ($sort==1))?1:0); ?>">Наименование</a></th>
				<th width="10%">Статус</th>
				<th width="10%"><a href="?id=<?php eh($id); ?>&amp;offset=<?php eh($offset); ?>&amp;sort=2&amp;direction=<?php eh((!$direction && ($sort==2))?1:0); ?>">Бизнес юнит</a></th>
				<th width="25%"><a href="?id=<?php eh($id); ?>&amp;offset=<?php eh($offset); ?>&amp;sort=3&amp;direction=<?php eh((!$direction && ($sort==3))?1:0); ?>">Региональное управление</a></th>
				<th width="10%"><a href="?id=<?php eh($id); ?>&amp;offset=<?php eh($offset); ?>&amp;sort=4&amp;direction=<?php eh((!$direction && ($sort==4))?1:0); ?>">Региональное отделение</a></th>
				<th width="10%">Контрагент</th>
				<th width="10%">Ордер</th>
				<th width="10%"><a href="?id=<?php eh($id); ?>&amp;offset=<?php eh($offset); ?>&amp;sort=5&amp;direction=<?php eh((!$direction && ($sort==5))?1:0); ?>">Дата ордера</a></th>
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
		
		<?php for($i = 0; $i < ($docs_count/50); $i++) { ?>
		<a class="page-number<?php if($offset == $i) eh(' boldtext'); ?>" href="?id=<?php eh($id); ?>&amp;offset=<?php eh($i); ?>&amp;sort=<?php eh($sort); ?>&amp;direction=<?php eh($direction); ?>"><?php eh($i+1); ?></a>
		<?php } ?>
	</div>
</div>
		<br />
		<br />
<?php
	include("tpl.form-doc.php");
	include("tpl.footer.php");
?>
