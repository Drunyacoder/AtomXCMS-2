
// Startup variables
var imageTag = false;
var theSelection = false;

// Check for Browser & Platform for PC & IE specific bits
// More details from: http://www.mozilla.org/docs/web-developer/sniffer/browser_type.html
var clientPC = navigator.userAgent.toLowerCase(); // Get client info
var clientVer = parseInt(navigator.appVersion); // Get browser version

var is_ie = ((clientPC.indexOf("msie") != -1) && (clientPC.indexOf("opera") == -1));
var is_nav = ((clientPC.indexOf('mozilla')!=-1) && (clientPC.indexOf('spoofer')==-1)
                && (clientPC.indexOf('compatible') == -1) && (clientPC.indexOf('opera')==-1)
                && (clientPC.indexOf('webtv')==-1) && (clientPC.indexOf('hotjava')==-1));
var is_moz = 0;

var is_win = ((clientPC.indexOf("win")!=-1) || (clientPC.indexOf("16bit") != -1));
var is_mac = (clientPC.indexOf("mac")!=-1);

// Helpline mainTexts
h_help = "Подсказка: Можно быстро применить стили к выделенному тексту"
b_help = "Жирный текст: [b]текст[/b]";
i_help = "Наклонный текст: [i]текст[/i]";
u_help = "Подчёркнутый текст: [u]текст[/u]";
q_help = "Цитата: [quote]текст[/quote]";
c_help = "Код (программа): [code]код[/code]";
p_help = "Код PHP: [php]код[/php]";
l_help = "Список: [list]текст[/list]";
o_help = "Нумерованный список: [list=]текст[/list]";
m_help = "Вставить картинку: [img]http://image_url[/img]";
w_help = "Вставить ссылку: [url]http://url[/url] или [url=http://url]текст ссылки[/url]";
a_help = "Закрыть все открытые теги bbCode";
s_help = "Цвет шрифта: [color=red]текст[/color]";
hd_help = "Скрыть от гостей: [hide]скрытый текст[/hide]";
le_help = "Текст с лева: [left]текст[/left]";
ce_help = "Текст по центру: [center]текст[/center]";
ri_help = "Текст с права: [right]текст[/right]";
si_help = "Размер текста: [size=15]текст[/size]";

// Define the bbCode tags
bbcode = new Array();
bbtags = new Array('[b]','[/b]','[i]','[/i]','[u]','[/u]','[quote]','[/quote]','[code]','[/code]','[php]','[/php]','[list]','[/list]','[list=]','[/list]','[img]','[/img]','[url]','[/url]','[hide]','[/hide]','[left]','[/left]','[center]','[/center]','[right]','[/right]');
imageTag = false;
packIds = [];

maxAttachedFiles = 5;


//create smiles buttons. id - id of container for smiles
function getSmiles(id) {
	//array with  smiles
	var advsmiles = new Array('wall','baks','bis','girl','gordo','gy','girlgy','haha','helpme','hm','hnyk','idea','hrap','ispug','jahu','girlhnyk','mat','mda','mdya','or','pardon','plak','plaksa','plaksa2','rzhu','sad','sarkastik','sorri','stranno','tanz','umora','ura','vopros','wink','wutka','ww','yeh','zharko','zlaya','zloy');
	var container = document.getElementById(id);
	if (container !== 'undefined') {
		for (i = 0; i < advsmiles.length; i++) {
			container.innerHTML = container.innerHTML + '<div><a href="javascript://" onClick="emoticon(\':' + advsmiles[i] + ':\');"><img src="/sys/img/smiles/' 
			+ advsmiles[i] + '.gif" /></a></div>';
		}
	}
	return;
}


// Shows the help mainTexts in the helpline window
function helpline(help) {
	if (document.getElementById("sendForm").helpbox) {
		document.getElementById("sendForm").helpbox.value = eval(help + "_help");
	}
}


// Replacement for arrayname.length property
function getarraysize(thearray) {
	for (i = 0; i < thearray.length; i++) {
		if ((thearray[i] == "undefined") || (thearray[i] == "") || (thearray[i] == null))
			return i;
		}
	return thearray.length;
}

// Replacement for arrayname.push(value) not implemented in IE until version 5.5
// Appends element to the array
function arraypush(thearray,value) {
	thearray[ getarraysize(thearray) ] = value;
}

// Replacement for arrayname.pop() not implemented in IE until version 5.5
// Removes and returns the last element of an array
function arraypop(thearray) {
	thearraysize = getarraysize(thearray);
	retval = thearray[thearraysize - 1];
	delete thearray[thearraysize - 1];
	return retval;
}


function checkForm() {

	formErrors = false;

	if (document.getElementById("sendForm").mainText.value.length < 2) {
		formErrors = "Вы должны ввести текст сообщения";
	}

	if (formErrors) {
		alert(formErrors);
		return false;
	} else {
		bbstyle(-1);
		//formObj.preview.disabled = true;
		//formObj.submit.disabled = true;
		return true;
	}
}

function emoticon(text) {
	var txtarea = document.getElementById("editor");
	var text = ' ' + text + ' ';
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

function bbfontstyle(bbopen, bbclose) {
	var txtarea = document.getElementById("sendForm").mainText;

	if ((clientVer >= 4) && is_ie && is_win) {
		theSelection = document.selection.createRange().text;
		if (!theSelection) {
			txtarea.value += bbopen + bbclose;
			txtarea.focus();
			return;
		}
		document.selection.createRange().text = bbopen + theSelection + bbclose;
		txtarea.focus();
		return;
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbopen, bbclose);
		return;
	}
	else
	{
		txtarea.value += bbopen + bbclose;
		txtarea.focus();
	}
	storeCaret(txtarea);
}


function bbstyle(bbnumber) {
	var txtarea = document.getElementById("sendForm").mainText;

	txtarea.focus();
	donotinsert = false;
	theSelection = false;
	bblast = 0;

	if (bbnumber == -1) { // Close all open tags & default button names
		while (bbcode[0]) {
			butnumber = arraypop(bbcode) - 1;
			txtarea.value += bbtags[butnumber + 1];
			buttext = eval('document.getElementById("sendForm").addbbcode' + butnumber + '.value');
			eval('document.getElementById("sendForm").addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
		}
		imageTag = false; // All tags are closed including image tags :D
		txtarea.focus();
		return;
	}

	if ((clientVer >= 4) && is_ie && is_win)
	{
		theSelection = document.selection.createRange().text; // Get text selection
		if (theSelection) {
			// Add tags around selection
			document.selection.createRange().text = bbtags[bbnumber] + theSelection + bbtags[bbnumber+1];
			txtarea.focus();
			theSelection = '';
			return;
		}
	}
	else if (txtarea.selectionEnd && (txtarea.selectionEnd - txtarea.selectionStart > 0))
	{
		mozWrap(txtarea, bbtags[bbnumber], bbtags[bbnumber+1]);
		return;
	}
	
	// Find last occurance of an open tag the same as the one just clicked
	for (i = 0; i < bbcode.length; i++) {
		if (bbcode[i] == bbnumber+1) {
			bblast = i;
			donotinsert = true;
		}
	}

	if (donotinsert) {		// Close all open tags up to the one just clicked & default button names
		while (bbcode[bblast]) {
			butnumber = arraypop(bbcode) - 1;
			//txtarea.value += bbtags[butnumber + 1];
			var startPos = txtarea.selectionStart;
			var endPos = txtarea.selectionEnd;
			txtarea.value = txtarea.value.substring(0, startPos) + bbtags[butnumber + 1] + txtarea.value.substring(endPos, txtarea.value.length);
			buttext = eval('document.getElementById("sendForm").addbbcode' + butnumber + '.value');
			eval('document.getElementById("sendForm").addbbcode' + butnumber + '.value ="' + buttext.substr(0,(buttext.length - 1)) + '"');
			imageTag = false;
		}
		txtarea.focus();
		txtarea.selectionStart = startPos;
		txtarea.selectionEnd = endPos;
		return;
	} else { // Open tags

		if (imageTag && (bbnumber != 16)) {		// Close image tag before adding another
			txtarea.value += bbtags[17];
			lastValue = arraypop(bbcode) - 1;	// Remove the close image tag from the list
			document.getElementById("sendForm").addbbcode16.value = "Img";	// Return button back to normal state
			imageTag = false;
		}

		// Open tag
		if(document.selection){
			txtarea.focus();
			sel = document.selection.createRange();
			sel.text = bbtags[bbnumber];
		} else {//MOZILLA/NETSCAPE support
			if (txtarea.selectionStart || txtarea.selectionStart == '0') {
				var startPos = txtarea.selectionStart;
				var endPos = txtarea.selectionEnd;
				txtarea.value = txtarea.value.substring(0, startPos) + bbtags[bbnumber] + txtarea.value.substring(endPos, txtarea.value.length);
			} else {
				txtarea.value += bbtags[bbnumber];
			}
		}
		//txtarea.value += bbtags[bbnumber];
		
		if ((bbnumber == 16) && (imageTag == false)) imageTag = 1; // Check to stop additional tags after an unclosed image tag
		arraypush(bbcode,bbnumber+1);
		eval('document.getElementById("sendForm").addbbcode'+bbnumber+'.value += "*"');
		txtarea.focus();
		txtarea.selectionStart = startPos;
		txtarea.selectionEnd = endPos+(bbtags[bbnumber].length*2+1);
		return;
	}
	storeCaret(txtarea);
}

// From http://www.massless.org/mozedit/
function mozWrap(txtarea, open, close)
{
	var selLength = txtarea.textLength;
	var selStart = txtarea.selectionStart;
	var selEnd = txtarea.selectionEnd;
	if (selEnd == 1 || selEnd == 2)
		selEnd = selLength;

	var s1 = (txtarea.value).substring(0,selStart);
	var s2 = (txtarea.value).substring(selStart, selEnd)
	var s3 = (txtarea.value).substring(selEnd, selLength);
	txtarea.value = s1 + open + s2 + close + s3;
	
	txtarea.focus();
	txtarea.selectionStart = selStart;
	txtarea.selectionEnd = selEnd+(open.length*2+1);
	storeCaret(txtarea);
	return;
}

// Insert at Claret position.
function storeCaret(textEl) {
	if (textEl.createTextRange) textEl.caretPos = document.selection.createRange().duplicate();
}

/* paste smile */
/*
function smile(img) {
	var txtarea = document.getElementById("sendForm").mainText;
	txtarea.focus();
	
	txtarea.i
}
*/




var selection = false; // Selection data


function emoticon_wospaces(text) {
	var txtarea = document.getElementById("sendForm").mainText;
	if (txtarea.createTextRange && txtarea.caretPos) {
		var caretPos = txtarea.caretPos;
		caretPos.text = caretPos.text.charAt(caretPos.text.length - 1) == ' ' ? caretPos.text + text + ' ' : caretPos.text + text;
		txtarea.focus();
	} else {
		txtarea.value  += text;
		txtarea.focus();
	}
}

// Catching selection
function catchSelection()
{
	if (window.getSelection)
	{
		selection = window.getSelection().toString();
	}
	else if (document.getSelection)
	{
		selection = document.getSelection();
	}
	else if (document.selection)
	{
		selection = document.selection.createRange().text;
	}
}

// Putting username to the post box
function putName(name)
{ 
	emoticon_wospaces('[b]'+name+'[/b]\n'); 
	document.getElementById("sendForm").mainText.focus(); 
	return; 
}

// Putting selection to the post box
function quoteSelection(name)
{
	if (selection)
	{ 
		emoticon_wospaces('[quote="'+name+'"]' + selection + '[/quote]\n'); 
		selection = '';
		document.getElementById("sendForm").mainText.focus(); 
		return; 
	}
	else
	{ 
		alert(l_no_text_selected);
		return; 
	} 
}

/* add file field */
function addFileField(elementId) {
	var container = document.getElementById(elementId);
	var fields = [];
	
    if (container.getElementsByClassName == undefined) {
        var myclass = new RegExp('\\b'+'attachField'+'\\b');
        var elem = container.getElementsByTagName('*');
        for (var i = 0; i < elem.length; i++) {
            var classes = elem[i].className;
            if (myclass.test(classes)) {
                fields.push(elem[i]);
            }
        }
    } else {
        fields = container.getElementsByClassName('attachField');
    }
    var cntFields = fields.length + 1;
    if (cntFields <= maxAttachedFiles) {
        if (cntFields < 1) {
            cntFields = 1;
        }
        var new_div = document.createElement('div');
        new_div.innerHTML = ' [' + cntFields + '] ';
        new_div.innerHTML += '<input type="file" id="attach' + cntFields + '" name="attach' + cntFields + '" class="attachField" onChange="getFile(' + cntFields + ')" /><span id="attachMeta' + cntFields + '"></span>';
        container.appendChild(new_div);
    }
}

/* add text field */
function addTextField(elementId) {
	var container = document.getElementById(elementId);
	var fields = [];
	
    if (container.getElementsByClassName == undefined) {
        var myclass = new RegExp('\\b'+elementId+'Field'+'\\b');
        var elem = container.getElementsByTagName('*');
        for (var i = 0; i < elem.length; i++) {
            var classes = elem[i].className;
            if (myclass.test(classes)) {
                fields.push(elem[i]);
            }
        }
    } else {
        fields = container.getElementsByClassName(elementId+'Field');
    }
    var cntFields = fields.length + 1;
    if (cntFields <= maxAttachedFiles) {
        if (cntFields < 1) {
            cntFields = 1;
        }
        var new_div = document.createElement('div');
        new_div.innerHTML = ' [' + cntFields + '] ';
        new_div.innerHTML += '<input type="text" id="' + elementId + cntFields + '" name="' + elementId + cntFields + '" class="' + elementId + 'Field" />';
        container.appendChild(new_div);
    }
}

/* get and identific file */
function getFile(n){
    var t = document.getElementById('attach'+n);
    if (t.value){
        ext = new Array('png','jpg','gif','jpeg');
        var img = t.value.replace(/\\/g,'/');
        var pic = img.toLowerCase();
        var ok=0;
        for (i=0;i<ext.length;i++){
            m = pic.indexOf('.' + ext[i]);
            if (m != -1){
                ok=1;
                break;
            }
        }
        var d = document.getElementById('attachMeta'+n);
        if (d) {
            if (ok==1){
                var code='{IMAGE'+n+'}';
                document.getElementById('attachMeta'+n).innerHTML=' <input type="text" readonly value="'+code+'" title="Вставьте этот код в любое место сообщения" size="'+(code.length)+'" style="font-family:monospace;color:#FF8E00;" />';
            } else {
                document.getElementById('attachMeta'+n).innerHTML='';
            }
        }
    } else {
        document.getElementById('attach'+n).innerHTML='';
    }
} 




// Добавить в Избранное
function add_favorite(a) {
	title=document.title;
	url=document.location;
	try {
		// Internet Explorer
		window.external.AddFavorite(url, title);
	} catch (e) {
		try {
			// Mozilla
			window.sidebar.addPanel(title, url, "");
		} catch (e) {
			// Opera
			if (typeof(opera) == "object") {
				a.rel="sidebar";
				a.title=title;
				a.url=url;
				return true;
			} else {
				// Unknown
				alert('Нажмите Ctrl-D чтобы добавить страницу в закладки');
			}
		}
	}
	return false;
}



/**
 * Users rating
 */
function setRating(uid, formId) {
	var fpoints = $('#' + formId + ' input[name=points]:checked:first');
	if (fpoints[0] != undefined) var points = fpoints[0].value;
	else var points = 0;
	
	var fcomm = $('#' + formId + ' textarea[name=comment]:first');
	if (fcomm[0] != undefined) var comm = fcomm[0].value;
	else var comm = '';
	
	$.post('/users/rating/' + uid + '/' + points, {"points":points,"comment":comm}, function(data){
		if (data == 'ok') {
			var infomess = 'Голос добавлен';
		} else {
			var infomess = data;
		}
		$('#infomess_' + uid).html(infomess);
		setTimeout("$('#setRating_"+uid+"').hide()", 2000);
		return true;
	});
}
function addWarning_(uid, formid) {
	var str = $('#'+formid).serialize();
	$.post('/users/add_warning/'+uid, str, function(data){
		if (data == 'ok') {
			var infomess = 'Голос добавлен';
		} else {
			var infomess = data;
		}
		$('#winfomess_'+uid).html(infomess);
		setTimeout("$('#addWarning_"+uid+"').hide()", 2000);
		return true;
	});
}
/**
 * Ajax window
 */
function showFpsWin(url, params, title) {
	$.get(url, params, function(data){
		var div = document.createElement('div');
		div.innerHTML = createFpsWin(title, data, '');
		document.body.appendChild(div);
		return true;
	});
}

/**
 * Create fps window
 */
function createFpsWin(title, data, params) {
        var blid = 'setRating_' + Math.floor((Math.random()*9999));
        var fpsWin = '<div id="' + blid + '" class="fps-fwin" style="'+params+'"><div class="drag_window"><div class="fps-title" onmousedown="drag_object(event, this.parentNode)">' + title + '</div><div onClick="$(\'#' + blid + '\').hide()" class="fps-close"></div><div class="fps-cont">' + data + '</div></div></div>';
        return fpsWin;
}

/**
 * Delete user vote
 */
function deleteUserVote(voteID) {
	$.get('/users/delete_vote/' + voteID, '', function(data){
		if (data == 'ok') {
			$('#uvote_' + voteID).hide();
		}
	});
}
function deleteUserWarning(wID) {
	$.get('/users/delete_warning/' + wID, '', function(data){
		if (data == 'ok') {
			$('#uvote_w' + wID).hide();
		}
	});
}

/**
 * For Fps Windows
 */
function drag_object( evt, obj )
{
	evt = evt || window.event;
	
	// флаг, которые отвечает за то, что мы кликнули по объекту (готовность к перетаскиванию)
	obj.clicked = true;
	
	// устанавливаем первоначальные значения координат объекта
	obj.mousePosX = evt.clientX;
	obj.mousePosY = evt.clientY;

	// отключаем обработку событий по умолчанию, связанных с перемещением блока (это убирает глюки с выделением текста в других HTML-блоках, когда мы перемещаем объект)
	if( evt.preventDefault ) evt.preventDefault(); 
	else evt.returnValue = false;
	
	// когда мы отпускаем кнопку мыши, убираем «проверочный флаг»
	document.onmouseup = function(){ obj.clicked = false }
	
	// обработка координат указателя мыши и изменение позиции объекта
	document.onmousemove = function( evt )
	{
		evt = evt || window.event;
		if( obj.clicked )
		{
			posLeft = !obj.style.left ? obj.offsetLeft : parseInt( obj.style.left );
			posTop = !obj.style.top ? obj.offsetTop : parseInt( obj.style.top );

			mousePosX = evt.clientX;
			mousePosY = evt.clientY;

			obj.style.left = posLeft + mousePosX - obj.mousePosX + 'px';
			obj.style.top = posTop + mousePosY - obj.mousePosY + 'px';
			
			obj.mousePosX = mousePosX;
			obj.mousePosY = mousePosY;
		}
	}
}



/**
 * Selector for package actions
 */
function addToPackage(id) {
	packIds.push(id);
	var button = document.getElementById('packButton');
	button.value = '(' + packIds.length + ')';
	if (packIds.length > 0) button.disabled = false;
}
function delFromPackage(id) {
	for(key in packIds) {
		if(packIds[key] == id) {
			packIds.splice(key, 1);
		}
	}
	var button = document.getElementById('packButton');
	button.value = '(' + packIds.length + ')';
	if (packIds.length < 1) button.disabled = true;
}
function sendPack(action) {
	var pack = document.getElementById('actionPack');
	pack.action = action;
	for(key in packIds) {
		pack.innerHTML += '<input type="hidden" name="ids[]" value="' + packIds[key] + '">';
	}
	pack.submit();
}
function checkAll(_className, check) {
	var f = $('input.' + _className);
	for (key in f) {
		var ent = f[key];
		if (typeof ent.value != 'undefined') {
			ent.checked = check;
			if(check) addToPackage(ent.value);
			else delFromPackage(ent.value);
		}
	}
}

function check_pm(uid){
	if (uid > 0) {
		$.get('/users/get_count_new_pm/'+uid, {}, function(data){
			if (typeof data != 'undefined' && parseInt(data) == data && data > 0) {
				$('body').append(createFpsWin('Новые сообщения', '<div style="text-align:center;">' + data + ' Новых сообщений!<br><br><a href="/users/in_msg_box/'+uid+'">Прочитать</a></div>', 'top:0px;left:0px;')); 
			} else {
				setTimeout("check_pm("+uid+")", 20000);
			}
		});
	}
}