// xoops_charset should be passed from outside of this script.
// alert(xoops_charset);

function tagmemoClearForm() {
	x = document.getElementById('tagmemo_quickform_container');
	x.parentNode.removeChild(x);
}

function IESubmit(){
    //document.charset = xoops_charset;
    tagmemo_quickform.submit();
}

function createTagmemoQuickForm(){
    
	/* CSS */

    style = document.createElement('link');
    style.href = baseurl + '/include/css/tagmemo_quickform.css';
    style.rel  = 'stylesheet';
    style.type = 'text/css';
    document.getElementsByTagName('head')[0].appendChild(style);
		
	/* Container */

	tagmemo_quickform_container    = document.createElement('div');
	tagmemo_quickform_container.id = 'tagmemo_quickform_container';

	/* Form */

	tagmemo_quickform               = document.createElement('form');
	tagmemo_quickform.id            = 'tagmemo_quickform';
	tagmemo_quickform.method        = 'post';
	tagmemo_quickform.action        = baseurl + '/quickpost.php';
	tagmemo_quickform.acceptCharset = xoops_charset;

	/* memo */
	
	tagmemo_quickform_memo      = document.createElement('textarea');
	tagmemo_quickform_memo.id   = 'tagmemo_quickform_memo';
	tagmemo_quickform_memo.name = 'tagmemo_quickform_memo';
	tagmemo_quickform_memo.cols = '40';
	tagmemo_quickform_memo.rows = '15';

	url = location.href;
	title = document.title;
    tagmemo_quickform_memo.value = title + '\n' + url + '\n\n';

	tagmemo_quickform.appendChild(tagmemo_quickform_memo);

	tagmemo_quickform.appendChild(document.createElement('br'));

	/* tags */

	tagmemo_quickform.appendChild(document.createTextNode(""));

	tagmemo_quickform_tags      = document.createElement('input');
	tagmemo_quickform_tags.name = 'tagmemo_quickform_tags'
	tagmemo_quickform_tags.id   = 'tagmemo_quickform_tags';
	tagmemo_quickform_tags.type = 'text';
	tagmemo_quickform_tags.size = '50'
	tagmemo_quickform.appendChild(tagmemo_quickform_tags);

	tagmemo_quickform.appendChild(document.createElement('br'));
	
	/* Token */
	
	tagmemo_quickform_token       = document.createElement('input');
	tagmemo_quickform_token.name  = 'tagmemo_quickform_token';
	tagmemo_quickform_token.value = token;
	tagmemo_quickform_token.type  = 'hidden';
	tagmemo_quickform.appendChild(tagmemo_quickform_token);

	/* Encode */
	
	tagmemo_quickform_item       = document.createElement('input');
	tagmemo_quickform_item.name  = 'encode';
	tagmemo_quickform_item.value = (!document.charset)? '': document.charset;
	tagmemo_quickform_item.type  = 'hidden';
	tagmemo_quickform.appendChild(tagmemo_quickform_item);
	
	/* User name */
	
	tagmemo_quickform_item       = document.createElement('input');
	tagmemo_quickform_item.name  = 'uname';
	tagmemo_quickform_item.value = uname;
	tagmemo_quickform_item.type  = 'hidden';
	tagmemo_quickform.appendChild(tagmemo_quickform_item);

	if (uname)
	{
		tagmemo_quickform_item       = document.createElement('span');
		tagmemo_quickform_item.innerHTML = "LoginPass: ";
		tagmemo_quickform.appendChild(tagmemo_quickform_item);
		/* Password */
		tagmemo_quickform_item       = document.createElement('input');
		tagmemo_quickform_item.name  = 'pass';
		tagmemo_quickform_item.value = '';
		tagmemo_quickform_item.type  = 'password';
		tagmemo_quickform_item.size = '10';
		tagmemo_quickform.appendChild(tagmemo_quickform_item);
	}
	
	/* GoBack Url */

	tagmemo_quickform_gobackurl = document.createElement('input');
	tagmemo_quickform_gobackurl.name = 'tagmemo_quickform_gobackurl';
	tagmemo_quickform_gobackurl.value = location.href;
	tagmemo_quickform_gobackurl.type = 'hidden';
	tagmemo_quickform.appendChild(tagmemo_quickform_gobackurl);
	
	/* Submit Button */

	tagmemo_quickform_submit       = document.createElement('input');
	tagmemo_quickform_submit.type  = 'button';
	tagmemo_quickform_submit.name  = 'tagmemo_quickform_submit';
	tagmemo_quickform_submit.id    = 'tagmemo_quickform_submit';
	tagmemo_quickform_submit.value = 'Submit';
	tagmemo_quickform.appendChild(tagmemo_quickform_submit);
	
	tagmemo_quickform_submit.onclick = IESubmit;

	/* Cancel Button */

	tagmemo_cancel = document.createElement('input');
	tagmemo_cancel.type    = 'button';
	tagmemo_cancel.name    = 'tagmemo_cancel';
	tagmemo_cancel.id      = 'tagmemo_cancel';	
	tagmemo_cancel.value   = 'Cancel';	
	tagmemo_cancel.onclick = tagmemoClearForm;
	tagmemo_quickform.appendChild(tagmemo_cancel);

	/* Finally create quick form */

	tagmemo_quickform_container.appendChild(tagmemo_quickform);
	document.body.appendChild(tagmemo_quickform_container);
}

createTagmemoQuickForm();
new Draggable('tagmemo_quickform_container');