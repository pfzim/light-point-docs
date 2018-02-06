var g_pid = 0;

function gi(name)
{
	return document.getElementById(name);
}

function escapeHtml(text)
{
  return text
      .replace(/&/g, "&amp;")
      .replace(/</g, "&lt;")
      .replace(/>/g, "&gt;")
      .replace(/"/g, "&quot;")
      .replace(/'/g, "&#039;");
}

function json2url(data)
{
	return Object.keys(data).map(
		function(k)
		{
			return encodeURIComponent(k) + '=' + encodeURIComponent(data[k])
		}
	).join('&');
}

function formatbytes(bytes, decimals) {
   if(bytes == 0) return '0 B';
   var k = 1024;
   var dm = decimals || 2;
   var sizes = ['B', 'KB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB'];
   var i = Math.floor(Math.log(bytes) / Math.log(k));
   return parseFloat((bytes / Math.pow(k, i)).toFixed(dm)) + ' ' + sizes[i];
}

function f_xhr()
{
	try { return new XMLHttpRequest(); } catch(e) {}
	try { return new ActiveXObject("Msxml3.XMLHTTP"); } catch(e) {}
	try { return new ActiveXObject("Msxml2.XMLHTTP.6.0"); } catch(e) {}
	try { return new ActiveXObject("Msxml2.XMLHTTP.3.0"); } catch(e) {}
	try { return new ActiveXObject("Msxml2.XMLHTTP"); } catch(e) {}
	try { return new ActiveXObject("Microsoft.XMLHTTP"); } catch(e) {}
	console.log("ERROR: XMLHttpRequest undefined");
	return null;
}

function f_http(url, _f_callback, _callback_params, content_type, data)
{
	var f_callback = null;
	var callback_params = null;

	if(typeof _f_callback !== 'undefined') f_callback = _f_callback;
	if(typeof _callback_params !== 'undefined') callback_params = _callback_params;
	if(typeof content_type === 'undefined') content_type = null;
	if(typeof data === 'undefined') data = null;

	var xhr = f_xhr();
	if(!xhr)
	{
		if(f_callback)
		{
			f_callback({code: 1, message: "AJAX error: XMLHttpRequest unsupported"}, callback_params);
		}

		return false;
	}

	xhr.open((content_type || data)?"post":"get", url, true);
	xhr.onreadystatechange = function()
	{
		if(xhr.readyState == 4)
		{
			var result;
			if(xhr.status == 200)
			{
				try
				{
					result = JSON.parse(xhr.responseText);
				}
				catch(e)
				{
					result = {code: 1, message: "Response: "+xhr.responseText};
				}
			}
			else
			{
				result = {code: 1, message: "AJAX error code: "+xhr.status};
			}

			if(f_callback)
			{
				f_callback(result, callback_params);
			}
		}
	};

	if(content_type)
	{
		xhr.setRequestHeader('Content-Type', content_type);
	}

	xhr.send(data);

	return true;
}

function f_delete_doc(ev)
{
	gi('loading').style.display = 'block';
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http("lpd.php?"+json2url({'action': 'delete_doc', 'id': id }),
		function(data, el)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = el.parentNode.parentNode;
				row.parentNode.removeChild(row);
			}
		},
		el_src
	);
};

function f_delete_file(ev)
{
	gi('loading').style.display = 'block';
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http("lpd.php?"+json2url({'action': 'delete_file', 'id': id }),
		function(data, el)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				var row = el.parentNode.parentNode;
				row.parentNode.removeChild(row);
			}
		},
		el_src
	);
};

function f_save(form_id)
{
	var form_data = {};
	var el = gi(form_id);
	for(i = 0; i < el.elements.length; i++)
	{
		var err = gi(el.elements[i].name + '-error');
		if(err)
		{
			err.style.display='none';
		}
		if(el.elements[i].name)
		{
			if(el.elements[i].type == 'checkbox')
			{
				if(el.elements[i].checked)
				{
					form_data[el.elements[i].name] = el.elements[i].value;
				}
			}
			else if(el.elements[i].type == 'select-one')
			{
				if(el.elements[i].selectedIndex != -1)
				{
					form_data[el.elements[i].name] = el.elements[i].value;
				}
			}
			else
			{
				form_data[el.elements[i].name] = el.elements[i].value;
			}
		}
	}
	
	//alert(json2url(form_data));
	//return;

	gi('loading').style.display = 'block';
	f_http("lpd.php?action=save",
		function(data, params)
		{
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				gi(params+'-container').style.display='none';
				//f_update_row(data.id);
				window.location = window.location;
			}
			else if(data.errors)
			{
				for(i = 0; i < data.errors.length; i++)
				{
					var el = gi(data.errors[i].name + "-error");
					if(el)
					{
						el.textContent = data.errors[i].msg;
						el.style.display='block';
					}
				}
			}
		},
		form_id,
		'application/x-www-form-urlencoded',
		json2url(form_data)
	);
}

function f_update_row(id)
{
	f_http("pb.php?"+json2url({'action': 'get', 'id': id }),
		function(data, params)
		{
			if(data.code)
			{
				f_notify(data.message, "error");
			}
			else
			{
				var row = gi('row'+data.id);
				if(!row)
				{
					row = gi("table-data").insertRow(0);
					row.insertCell(0);
					row.insertCell(1);
					row.insertCell(2);
					row.insertCell(3);
					row.insertCell(4);
					row.insertCell(5);
					row.insertCell(6);
					row.insertCell(7);
				}

				row.id = 'row'+data.id;
				row.setAttribute("data-id", data.id);
				row.setAttribute("data-map", data.map);
				row.setAttribute("data-x", data.x);
				row.setAttribute("data-y", data.y);
				row.setAttribute("data-photo", data.photo);
				row.cells[0].textContent = '';
				row.cells[1].textContent = data.firstname + ' ' + data.lastname;
				if(data.photo)
				{
					row.cells[1].className = 'userwithphoto';
				}
				row.cells[1].style.cursor = 'pointer';
				row.cells[1].onclick = function(event) { f_sw_map(event); };
				row.cells[1].onmouseenter = function(event) { f_sw_img(event); };
				row.cells[1].onmouseleave = function(event) { gi('imgblock').style.display = 'none'; };
				row.cells[1].onmousemove = function(event) { f_mv_img(event); };

				row.cells[2].textContent = data.phone;
				row.cells[3].textContent = data.mobile;
				row.cells[4].innerHTML = '<a href="mailto:'+escapeHtml(data.mail)+'">'+escapeHtml(data.mail)+'</a>';
				row.cells[5].textContent = data.position;
				row.cells[6].textContent = data.department;

				var str = '<span class="command" onclick="f_edit(event);">Edit</span> <span class="command" onclick="f_delete(event);">Delete</span> <span class="command" onclick="f_photo(event);">Photo</span> <span class="command" data-map="1" onclick="f_map_set(event);">Map&nbsp;1</span>';
				for(i = 2; i <= map_count; i++)
				{
					str += ' <span class="command" data-map="'+i+'" onclick="f_map_set(event);">'+i+'</span>';
				}

				if(data.visible)
				{
					row.cells[7].innerHTML = str+' <span class="command" onclick="f_hide(event);">Hide</span>';
				}
				else
				{
					row.cells[7].innerHTML = str+' <span class="command" onclick="f_show(event);">Show</span>';
				}
				//row.cells[7].onclick = function(event) { h(event); };
			}
		}
	);
}

function f_edit(ev, form_id)
{
	var id = 0;
	if(ev)
	{
		//var el_src = ev.target || ev.srcElement;
		//id = el_src.parentNode.parentNode.getAttribute('data-id');
		id = ev;
	}
	if(!id)
	{
		var form_data = {};
		var el = gi(form_id);
		for(i = 0; i < el.elements.length; i++)
		{
			var err = gi(el.elements[i].name + '-error');
			if(err)
			{
				err.style.display='none';
			}
			if(el.elements[i].name == 'id')
			{
				el.elements[i].value = id;
			}
			else if(el.elements[i].name == 'pid')
			{
				el.elements[i].value = g_pid;
			}
			else
			{
				if(el.elements[i].type == 'checkbox')
				{
					el.elements[i].checked = false;
				}
				else
				{
					el.elements[i].value = '';
				}
			}
		}
		gi(form_id + '-container').style.display='block';
	}
	else
	{
		gi('loading').style.display = 'block';
		f_http("lpd.php?"+json2url({'action': 'get', 'id': id }),
			function(data, params)
			{
				gi('loading').style.display = 'none';
				if(data.code)
				{
					f_notify(data.message, "error");
				}
				else
				{
					var el = gi(params);
					for(i = 0; i < el.elements.length; i++)
					{
						if(el.elements[i].name)
						{
							if(data.data[el.elements[i].name])
							{
								if(el.elements[i].type == 'checkbox')
								{
									el.elements[i].checked = (parseInt(data.data[el.elements[i].name], 10) != 0);
								}
								else
								{
									el.elements[i].value = data.data[el.elements[i].name];
								}
							}
						}
					}
					gi(params+'-container').style.display='block';
				}
			},
			form_id
		);
	}
}

function f_upload()
{
	gi('loading').style.display = 'block';
	var fd = new FormData(gi("file-upload"));
	f_http("lpd.php?action=upload",
		function(data, params)
		{
			gi('file-upload-id').value = 0;
			gi('loading').style.display = 'none';
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				//f_update_row(data.id);
				window.location = window.location;
			}
		},
		null,
		null,
		fd
	);

	return false;
}

function f_replace_file(ev)
{
	var id = 0;
	if(ev)
	{
		var el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.getAttribute('data-id');
	}
	if(id)
	{
		gi('file-upload-id').value = id;
		gi('upload').click();
	}
}

function f_select_all(ev)
{
	var el_src = ev.target || ev.srcElement;
	checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n; i++)
	{
		checkboxes[i].checked = el_src.checked;
	}
}

function f_export_selected(ev)
{
	var el;
	var postdata = "";
	var j = 0;
	var checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n;i++)
	{
		if(checkboxes[i].checked)
		{
			if(j > 0)
			{
				postdata += ",";
			}
			postdata += checkboxes[i].value;
			j++;
		}
	}

	if(j > 0)
	{
		el = gi('list');
		el.value = postdata;
		el = gi('contacts');
		el.submit();
	}

	return false;
}

function f_hide_selected(ev)
{
	var postdata = "list=";
	var j = 0;
	var checkboxes = document.getElementsByName('check');
	for(var i = 0, n = checkboxes.length; i < n;i++)
	{
		if(checkboxes[i].checked)
		{
			if(j > 0)
			{
				postdata += ",";
			}
			postdata += checkboxes[i].value;
			j++;
		}
	}
	if(j > 0)
	{
		f_http(
			"/zxsa.php?action=hide_selected",
			function(data, params)
			{
				f_notify(data.message, data.code?"error":"success");
			},
			null,
			'application/x-www-form-urlencoded',
			postdata
		);
	}
	else
	{
		f_popup("Error", "No selection");
	}
	return false;
}

function f_notify(text, type)
{
	var el;
	var temp;
	el = gi('notify-block');
	if(!el)
	{
		temp = document.getElementsByTagName('body')[0];
		el = document.createElement('div');
		el.id = 'notify-block';
		el.style.top = '0px';
		el.style.right = '0px';
		el.className = 'notifyjs-corner';
		temp.appendChild(el);
	}

	temp = document.createElement('div');
	temp.innerHTML = '<div class="notifyjs-wrapper notifyjs-hidable"><div class="notifyjs-arrow"></div><div class="notifyjs-container" style=""><div class="notifyjs-bootstrap-base notifyjs-bootstrap-'+escapeHtml(type)+'"><span data-notify-text="">'+escapeHtml(text)+'</span></div>';
	temp = el.appendChild(temp.firstChild);

	setTimeout(
		(function(el)
		{
			return function() {
				el.parentNode.removeChild(el);
			};
		})(temp),
		5000
	);
}

function f_drag_leave(e)
{
	gi('dropzone').className = "";
}

function f_drag_over(e)
{
	e.stopPropagation();
	e.preventDefault();
	gi('dropzone').className = (e.type == "dragover" ? "hover" : "");
}

function f_file_drop(e)
{
	f_drag_over(e);

	var files = e.target.files || e.dataTransfer.files;

	if (typeof files === 'undefined')
		return;

	e.stopPropagation();
	e.preventDefault();

	gi('upload').files = files;
	f_upload();
}

function lpd_init()
{
	var filedrag = document.getElementsByTagName('body')[0];

	filedrag.addEventListener("dragover", f_drag_over, false);
	filedrag.addEventListener("dragleave", f_drag_leave, false);
	filedrag.addEventListener("drop", f_file_drop, false);
}