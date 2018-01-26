<?php include("tpl.header.php"); ?>
		<h3 align="center">Light Point Docs</h3>
		<div id="imgblock" class="user-photo"><img id="userphoto" src=""/></div>
		<input type="text" id="search" class="form-field" onkeyup="filter_table()" placeholder="Search..">
		<?php if($uid) { ?>
		<span class="command f-right" onclick="f_edit(null);">Create document</span>
		<?php } ?>
<div>
	<div style="float: left">
		<ul style="list-style-type: none;margin-bottom: 0px;margin-left: 0px;margin-right: 0px;margin-top: 0px;overflow-wrap: break-word;padding-left: 0px;">
		<?php $i = 0; foreach($sections as $row) { $i++; ?>
		<li><a href="?id=<?php eh($i);?>"><?php eh($row[1]); ?></a></li>
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
		<?php $i = 0; foreach($db->data as $row) { $i++; ?>
			<tr id="<?php eh("row".$row[0]);?>" data-id=<?php eh($row[0]);?> data-map=<?php eh($row[11]); ?> data-x=<?php eh($row[12]); ?> data-y=<?php eh($row[13]); ?> data-photo=<?php eh($row[10]); ?>>
				<?php if($uid) { ?>
				<td><input type="checkbox" name="check" value="<?php eh($row[0]); ?>"/></td>
				<?php } ?>
				<td onclick="f_sw_map(event);" onmouseenter="f_sw_img(event);" onmouseleave="gi('imgblock').style.display = 'none'" onmousemove="f_mv_img(event);" style="cursor: pointer;" class="<?php if(intval($row[10])) { eh('userwithphoto'); } ?>"><?php eh($row[2].' '.$row[3]); ?></td>
				<td class="command" onclick="f_get_acs_location(event);"><?php eh($row[7]); ?></td>
				<td><?php eh($row[8]); ?></td>
				<td><a href="mailto:<?php eh($row[9]); ?>"><?php eh($row[9]); ?></a></td>
				<td><?php eh($row[6]); ?></td>
				<td><?php eh($row[4]); ?></td>
				<?php if($uid) { ?>
				<td>
					<?php if(empty($row[1])) { ?>
						<span class="command" onclick="f_edit(event);">Edit</span>
						<span class="command" onclick="f_delete(event);">Delete</span>
						<span class="command" onclick="f_photo(event);">Photo</span>
					<?php } ?>
					<span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span>
					<?php for($i = 2; $i <= PB_MAPS_COUNT; $i++) { ?>
						<span class="command" data-map="<?php eh($i); ?>" onclick="f_map_set(event);"><?php eh($i); ?></span>
					<?php } ?>
					<?php if($row[14]) { ?>
						<span class="command" onclick="f_hide(event);">Hide</span>
					<?php } else { ?>
						<span class="command" onclick="f_show(event);">Show</span>
					<?php } ?>
				</td>
				<?php } ?>
			</tr>
		<?php } ?>
			</tbody>
		</table>
		<?php if($uid) { ?>
		<form id="contacts" method="post" action="?action=export_selected">
			<input id="list" type="hidden" name="list" value="" />
		</form>
		<a href="#" onclick="f_export_selected(event); return false;">Export selected</a>
		<?php } ?>
	</div>
</div>
		<br />
		<br />
		<div id="edit-container" class="modal-container" style="display: none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<div class="modal-content">
				<h3>Create document</h3>
				<input id="edit_id" type="hidden" value=""/>
				<div class="form-title"><label for="reg_upr">Региональное управление*:</label></div>
				<input class="form-field" id="reg_upr" type="edit" value=""/>
				<div class="form-title"><label for="reg_otd">Региональное отделение*:</label></div>
				<input class="form-field" id="reg_otd" type="edit" value=""/>
				<div class="form-title"><label for="bis_unit">Бизнес юнит*:</label></div>
				<input class="form-field" id="bis_unit" type="edit" value=""/>
				<div class="form-title"><label for="doc_type">Тип документа*:</label></div>
				<input class="form-field" id="doc_type" type="edit" value=""/>
				<div class="form-title"><label for="order">Номер ордера*:</label></div>
				<input class="form-field" id="order" type="edit" value=""/>
				<div class="form-title"><label for="order_date">Дата ордера*:</label></div>
				<input class="form-field" id="order_date" type="edit" value=""/>
				<div class="form-title"><label for="contr_name">Наименование контрагента*:</label></div>
				<input class="form-field" id="contr_name" type="edit" value=""/>
				<div class="form-title"><label for="info">Описание:</label></div>
				<input class="form-field" id="info" type="edit" value=""/><br />
				<button class="form-button" type="button" onclick="f_save();">Save</button>
			</div>
		</div>
		<div id="map-container" class="modal-container" style="display:none">
			<span class="close" onclick="this.parentNode.style.display='none'">&times;</span>
			<img id="map-image" class="map-image" src="templ/map1.png"/>
			<img id="map-marker" class="map-marker" src="templ/marker.gif"/>
		</div>
		<form method="post" id="photo-upload" name="photo-upload">		
			<input id="upload" type="file" name="photo" style="display: none"/>
		</form>
<?php include("tpl.footer.php"); ?>