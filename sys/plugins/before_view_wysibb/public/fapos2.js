WBBPRESET = {
	buttons: 'bold,italic,underline,strike,|,justifyleft,justifycenter,justifyright,|,smilebox,|,code,quote,spoiler,hide,bullist,numlist,|,link,img,video,|,fontcolor,fontsize,removeFormat',
	traceTextarea: true,
	imgupload: false,
	allButtons: {
		spoiler : {
			title: CURLANG.spoiler,
			buttonText: 'spoiler',
			transform : {
				'<div><div><b>Сворачиваемый текст</b></div><div style="border: 1px inset ; overflow: auto;">{SELTEXT}</div></div>':"[spoiler]{SELTEXT}[/spoiler]"
			}
		},
		hide : {
			title: 'Скрытый текст',
			buttonText: 'hide',
			transform : {
				'<div><div><b>Скрытый текст</b></div><div style="border: 1px inset ; overflow: auto;">{SELTEXT}</div></div>':"[hide]{SELTEXT}[/hide]"
			}
		},
		quote : {
			transform : { 
				'<div class="bbQuoteBlock"><div class="bbQuoteName"><b>Цитата</b></div><div class="quoteMessage">{SELTEXT}</div></div>':'[quote]{SELTEXT}[/quote]',
				'<div class="bbQuoteBlock"><div class="bbQuoteName"><b>{AUTHOR} пишет:</b></div><div class="quoteMessage">{SELTEXT}</div></div>':'[quote="{AUTHOR}"]{SELTEXT}[/quote]',
				'<div style="" class="bbQuoteBlock"><div class="bbQuoteName"><b>{AUTHOR} пишет:</b></div><div class="quoteMessage">{SELTEXT}</div></div>':'[quote={AUTHOR}]{SELTEXT}[/quote]'
			}
		},
		code: {
			transform: {
				'<div class="bbCodeBlock"><div class="bbCodeName"><b>Code:</b></div><div class="codeMessage" style="border: 1px inset ; overflow: auto;">{SELTEXT}</div></div>':'[code]{SELTEXT}[/code]',
				'<div class="codePHP">{SELTEXT}</div>':'[php]{SELTEXT}[/php]',
				'<div class="codeSQL">{SELTEXT}</div>':'[sql]{SELTEXT}[/sql]',
				'<div class="codeJS">{SELTEXT}</div>':'[js]{SELTEXT}[/js]',
				'<div class="codeCSS">{SELTEXT}</div>':'[css]{SELTEXT}[/css]',
				'<div class="codeHTML">{SELTEXT}</div>':'[html]{SELTEXT}[/html]',
				'<div class="codeHTML codeXML">{SELTEXT}</div>':'[xml]{SELTEXT}[/xml]'
			}
		},
		bullist: {
			transform: {
				'<ul>{SELTEXT}</ul>':'[list]{SELTEXT}[/list]',
				'<li>{SELTEXT}</li>':'[*]{SELTEXT[^\[\]\*]}'
			}
		},
		numlist: {
			transform: {
				'<ol>{SELTEXT}</ol>':'[list=1]{SELTEXT}[/list]',
				'<ol type="a">{SELTEXT}</ol>':'[list=a]{SELTEXT}[/list]',
				'<li>{SELTEXT}</li>':'[*]{SELTEXT[^\[\]\*]}'
			}
		},
		img : {
			transform : {
				'<img src="{SRC}" style="max-width:400px; max-height:400px; float:left;" />':"[imgl]{SRC}[/imgl]",
				'<img src="{SRC}" style="max-width:400px; max-height:400px; float:inherit;" />':"[img]{SRC}[/img]"
			}
		},
		video: {
			transform: {
				'<iframe src="http://www.youtube.com/embed/{SRC}" width="640" height="480" frameborder="0"></iframe>':'[video]http://www.youtube.com/watch?v={SRC}[/video]'
			}
		}
	}
}

function catchSelection() {
	if (window.getSelection) {
		selection = window.getSelection().toString();
	} else if (document.getSelection) {
		selection = document.getSelection();
	} else if (document.selection) {
		selection = document.selection.createRange().text;
	}
}


function quoteSelection(name) {
	l_no_text_selected = "Выделите текст на странице и попробуйте еще раз";
	if (selection) {
		$('#editor').execCommand('quote',{author:name,seltext:selection});
		selection = '';
		return; 
	} else { 
		alert(l_no_text_selected);
		return; 
	} 
}


function checkForm() {
	l_empty_message = "Вы должны ввести текст сообщения";

	$('#editor').sync();
	if (document.getElementById("sendForm").mainText.value.length < 2) {
		alert(l_empty_message);
		return false;
	} else {
		return true;
	}
}