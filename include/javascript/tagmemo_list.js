function tagmemo_getTagslist(tag_id,e,start)
{
	//alert(Event.pointerX(e));
	//if (! $('tagmemo_tagslist'))
	//{
	//	new Draggable('tagmemo_tagslist');
	//}
	list = $('tagmemo_tagslist');
	list.innerHTML = "Now loading.....";
	
	if (start == undefined)
	{
		list.style.top = (Event.pointerY(e) + 20) + "px";
		list.style.left = Event.pointerX(e) + "px";
	}
		
	var pars = 'tag_id='+tag_id;
	if (start) pars += '&start='+start;
	
	var myAjax = new Ajax.Request(
		tagmemo_tagslist_url, 
		{
			method: 'get', 
			parameters: pars, 
			onComplete: tagmemo_showResponse
		});
}

function tagmemo_showResponse(originalRequest)
{
	list = $('tagmemo_tagslist');
	list.style.visibility = "hidden";
	list.innerHTML = originalRequest.responseText;
	tagmemo_list_body_hide();
	list.style.visibility = "visible";
	//height = list.offsetHeight;
	//if (height > 30)
	//{
	//	$('tagmemo_list_ajax').style.height = "30px";
	//}
}

function tagmemo_hideTagslist()
{
	list = $('tagmemo_tagslist');
	list.style.top = "-1000px";
	list.style.left = "-1000px";
}

function tagmemo_list_body_toggle(id)
{
	Toggle.display($('tagmemo_list_body_ajax_'+id));
}

function tagmemo_list_body_hide()
{
	var elms = $('tagmemo_list_ajax');
	for (var i = 0; i < elms.childNodes.length; i++)
	{
		for (var i2 = 0; i2 < elms.childNodes[i].childNodes.length; i2++)
		{
			var tgt = elms.childNodes[i].childNodes[i2];
			if (tgt.id && tgt.id.indexOf('tagmemo_list_body_ajax_') == 0)
			{
				Toggle.display(tgt);
			}
		}
	}
}