var Tagmemo = Class.create();
Tagmemo.prototype = {

	initialize: function(url){
		this.memo = $('tagmemo_memo');
	
		this._baseurl = url;
		this._tag = new TagmemoTags(this._baseurl);
		this.memo.focus();
	},

	autosave: function(){
		//@todo do something in future!
	},
	
	autosaveResponseHandler: function(){
		//@todo do something in future!
	}
}

var TagmemoTags = Class.create();
TagmemoTags.prototype = {

	initialize: function(baseurl){
		
		this._baseurl = baseurl;
		this.input    = $('tagmemo_tag_input');
		this.list     = $('tagmemo_tag_list');
		this.hidden   = $('tagmemo_tag_hidden');

		this.tags = this.getTagArrayFromHtml();
		$('tagmemo_tag_hidden').value = this.getTagsAsString();
			
		this.input.onkeypress = this.add.bindAsEventListener(this);
	},
	
	add: function(e){
		var tag = this.input.value;
		if (e.keyCode == Event.KEY_RETURN && tag != ""){
			if(!this.alreadyExist(tag)){
				this.tags[this.tags.length] = tag;				
				var url = this._baseurl + '/modules/tagmemo/ajax_tagcheck.php';
				var pars = 'tag=' + encodeURIComponent(tag);
	    		var tagCheckAjax = new Ajax.Request(url, {method: 'get', parameters: pars, onComplete: this.tagCheckResponseHandler.bind(this)});     
			}
			$('tagmemo_tag_hidden').value = this.getTagsAsString();
			this.input.value = '';
			this.input.focus();
			Event.stop(e);
			return;
		}
	},
	
	remove: function(e){
		var element = Event.element(e);
		var tag = element.firstChild.data.replace(/[\t\n\r ]+/g, "");
		for(var i = 0; i < this.tags.length; i++){
			if(this.tags[i] == tag){
				this.tags.splice(i, 1);
				tag = null;
			}
		}
		$('tagmemo_tag_hidden').value = this.getTagsAsString();
		this.list.removeChild(element);
		return;
	},

	getTagsAsString: function(){
		var query = '';
		if(this.tags.length != 0){
			for(var i=0; i<this.tags.length; i++){
				var tag = this.tags[i] + ' ';
				query += tag;
			}
			return query.replace(/ $/, '');;
		}
		return '';
	},
	
	getTagArrayFromHtml: function(){
		var tags = Array();
		for(var i=0;i<this.list.childNodes.length;i++){
			if(this.list.childNodes[i].nodeName == 'SPAN'){
			    //@ref http://developer.mozilla.org/en/docs/Whitespace_in_the_DOM
				tags[tags.length] = this.list.childNodes[i].firstChild.data.replace(/[\t\n\r ]+/g, "");
				var tag = this.list.childNodes[i];
				tag.onclick = this.remove.bindAsEventListener(this);
			}
		}
		return tags;
	},
	
	alreadyExist: function(tag){
		for(var i=0;i<this.tags.length;i++){
			if(this.tags[i] == tag){
				return true;
			}
		}
		return false;
	},
	
	tagCheckResponseHandler: function(request){
		var xmlDoc = request.responseXML;
        if (xmlDoc.documentElement) {
                  
            var tag   = xmlDoc.documentElement.childNodes[0].firstChild.data;				
            var exist = xmlDoc.documentElement.childNodes[1].firstChild.data;				

			var tagSpan = document.createElement('span');
			// http://weblogs.macromedia.com/flashjavascript/readme.html
			var unique = new Date().getTime();
			tagSpan.id = 's_tag_id_' + unique;
			tagSpan.className = exist;
			
			var tagText = document.createTextNode(tag + ' ');
			
			//@todo is #text better to be replaced?
			tagSpan.insertBefore(tagText, null);			
			this.list.insertBefore(tagSpan, null);
			
			tagSpan.onclick = this.remove.bindAsEventListener(this);	
		}
	}
	
}