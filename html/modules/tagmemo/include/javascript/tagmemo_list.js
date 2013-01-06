var TagmemoList = Class.create();
TagmemoList.prototype = {

	initialize: function(){
	},
	

    getTagslist: function(tag_id,e,start)
    {
    	list = $('tagmemo_tagslist');
    	list.style.zIndex = '1000';
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
			    onComplete: this.showResponse.bind(this)
		    }
		);
		return false;
    },


    showResponse: function(originalRequest)
    {
	    list = $('tagmemo_tagslist');
	    list.style.visibility = "hidden";
	    list.innerHTML = originalRequest.responseText;
	    this.list_body_hide();
	    list.style.visibility = "visible";
        new Draggable('tagmemo_tagslist');	    
    },


    hideTagslist: function()
    {
	    list.style.top = "-1000px";
	    list.style.left = "-1000px";
	    return false;
    },


    list_body_toggle: function(id)
    {
	    Toggle.display($('tagmemo_list_body_ajax_'+id));
	    return false;
    },


    list_body_hide: function()
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
}

// init

var tagmemoList = new TagmemoList();