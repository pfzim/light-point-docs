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
	return Object.keys(data).map
	(
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

function f_sw_img(ev)
{
	var el_src = ev.target || ev.srcElement;
	var img = el_src.parentNode.getAttribute('data-photo');
	if(parseInt(img, 10))
	{
		var el = gi('userphoto');
		el.src = 'photos/t'+el_src.parentNode.getAttribute('data-id')+'.jpg';
		el = gi('imgblock');
		imgblock.style.display = 'block';
		imgblock.style.left = (ev.clientX+10)  + "px";
		imgblock.style.top = (ev.clientY+10)  + "px";
	}
}

function f_mv_img(ev)
{
	var el = gi('imgblock');
	if(el)
	{
		el.style.left = (ev.clientX+10)  + "px";
		el.style.top = (ev.clientY+10)  + "px";
	}
}

function f_sw_map(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = parseInt(el_src.parentNode.getAttribute('data-map'), 10);
	if(id)
	{
		var el = gi('map-container');
		var x = parseInt(el_src.parentNode.getAttribute('data-x'), 10);
		var y = parseInt(el_src.parentNode.getAttribute('data-y'), 10);
		el.style.display = 'block';
		el.onclick = function() {gi('map-container').style.display = 'none';};
		var map = gi('map-image');
		map.src = '';
		map.onload = function(x, y)
		{
			return function(ev)
			{
				var el = gi('map-marker');
				var el_src = gi('map-image');
				if(el)
				{
					el.onclick = null;
					el.style.display = 'block';
					el.style.left = (el_src.offsetLeft + x - el.width/2)  + "px";
					el.style.top = (el_src.offsetTop + y - el.height/2)  + "px";
					//alert("    x: "+(el_src.offsetLeft + x) +"    y: "+(el_src.offsetTop + y));
				}
			}
		}(x, y);
		map.src = 'templ/map' + id + '.png';
	}
}

function f_map_set(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	var map = el_src.getAttribute('data-map');
	gi('map-container').onclick = null;
	gi('map-image').onload = null;
	gi('map-image').src = 'templ/map'+map+'.png';
	gi('map-container').style.display='block';
	gi('map-marker').style.display='none';
	gi('map-image').onclick = function(event)
	{
		gi('map-marker').style.display='block';
		gi('map-marker').style.left = (event.clientX - gi('map-marker').width/2)  + "px";
		gi('map-marker').style.top = (event.clientY - gi('map-marker').height/2)  + "px";
		gi('map-marker').onclick = function()
		{
			f_set_location(id, map, (gi('map-marker').offsetLeft + gi('map-marker').width/2) - gi('map-image').offsetLeft, (gi('map-marker').offsetTop + gi('map-marker').height/2) - gi('map-image').offsetTop);
			gi('map-container').style.display='none';
			gi('map-image').onclick = null;
		};
	};
};

function f_hide(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'hide', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Show';
				el.onclick = function(event) { f_show(event); };
			}
		},
		el_src
	);
};

function f_show(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'show', 'id': id }),
		function(data, el)
		{
			f_notify(data.message, data.code?"error":"success");
			if(!data.code)
			{
				el.textContent = 'Hide';
				el.onclick = function(event) { f_hide(event); };
			}
		},
		el_src
	);
};

function f_delete(ev)
{
	var el_src = ev.target || ev.srcElement;
	var id = el_src.parentNode.parentNode.getAttribute('data-id');
	f_http("pb.php?"+json2url({'action': 'delete', 'id': id }),
		function(data, el)
		{
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

	f_http("lpd.php?action=save",
		function(data, params)
		{
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
		var el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.getAttribute('data-id');
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
		f_http("pb.php?"+json2url({'action': 'get', 'id': id }),
			function(data, params)
			{
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
							if(el.elements[i].type == 'checkbox')
							{
								el.elements[i].checked = (parseInt(data[el.elements[i].name], 10) != 0);
							}
							else
							{
								el.elements[i].value = data[el.elements[i].name];
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

function f_upload(id)
{
	var fd = new FormData(gi("file-upload"));
	f_http("lpd.php?action=upload&id="+id,
		function(data, params)
		{
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

function f_photo(ev)
{
	var id = 0;
	if(ev)
	{
		var el_src = ev.target || ev.srcElement;
		id = el_src.parentNode.parentNode.getAttribute('data-id');
	}
	if(id)
	{
		gi('upload').onchange = function(id) {
			return function() {
				f_upload(id);
			}
		}(id);
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

function si(ev)
{
	var el_src = ev.target || ev.srcElement;
	var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
	var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
	document.getElementById('popup').style.display = 'block';
	document.getElementById('popup').style.left = (pX+10)  + "px";
	document.getElementById('popup').style.top = (pY+10)  + "px";
	if(parseInt(el_src.getAttribute('data-photo'), 10))
	{
		document.getElementById('u_photo').src = 'photos/t'+el_src.getAttribute('data-id')+'.jpg';
	}
	else
	{
		document.getElementById('u_photo').src = 'templ/nophoto.png';
	}
	document.getElementById('u_name').innerHTML = escapeHtml(el_src.getAttribute('data-name'));
	document.getElementById('u_position').innerHTML = escapeHtml(el_src.getAttribute('data-position'));
	document.getElementById('u_phone').innerHTML = escapeHtml(el_src.getAttribute('data-phone'));
}

function mi(ev)
{
	var el = document.getElementById('popup');
	if(el)
	{
		var pX = ev.pageX || (ev.clientX + (document.documentElement && document.documentElement.scrollLeft || document.body && document.body.scrollLeft || 0) - (document.documentElement.clientLeft || 0));
		var pY = ev.pageY || (ev.clientY + (document.documentElement && document.documentElement.scrollTop || document.body && document.body.scrollTop || 0) - (document.documentElement.clientTop || 0));
		el.style.left = (pX+10)  + "px";
		el.style.top = (pY+10)  + "px";
	}
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

function lpd_init(_pid)
{
	g_pid = _uid;
	gi("upload").onchange = function(event)
	{
		var files = event.target.files;
		for(var i=0, n=files.length;i<n;i++)
		{
			f_upload(uidd, idd, files[i], k++, 0, 0);
		}
		return false;
	};

	window.onbeforeunload = function(e)
	{
		for(var i=0, n=xhttp.length;i<n;i++)
		{
			if(xhttp[i])
			{
				var message = "Your have active uploads. Are you sure want to leave the page and terminate all uploads?";
				f_popup('Confirm exit', message);

				if (typeof e == "undefined")
				{
					e = window.event;
				}
				if (e)
				{
					e.returnValue = message;
				}
				return message;
			}
		}
	};

	var filedrag = document.getElementsByTagName('body')[0];

	filedrag.addEventListener("dragover", DragOver, false);
	filedrag.addEventListener("dragleave", DragLeave, false);
	filedrag.addEventListener("drop", function(event) { FileDrop(event, uid, id); }, false);
}