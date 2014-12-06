WBBPRESET = {
	buttons: 'bold,italic,underline,strike,|,justifyleft,justifycenter,justifyright,|,smilebox,|,code,quote,spoiler,hide,bullist,numlist,|,link,img,video,|,fontcolor,fontsize,fontheader,removeFormat',
	traceTextarea: false,
	imgupload: false,
	allButtons: {
        link: {
            transform: {
                '<a href="{SELTEXT}">{SELTEXT}</a>':'[url]{SELTEXT}[/url]',
                '<a data-quoted="true" href="{URL}">{SELTEXT}</a>':'[url="{URL}"]{SELTEXT}[/url]',
                '<a href="{URL}">{SELTEXT}</a>':'[url={URL}]{SELTEXT}[/url]'
            }
        },
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
		code: {title:"CODE", transform: {'<div class="bbCodeBlock"><div class="bbCodeName"><b>Code:</b></div><div class="codeMessage" style="border: 1px inset ; overflow: auto;">{SELTEXT}</div></div>':'[code]{SELTEXT}[/code]'}},
		code_php: {title:"PHP", transform: {'<div class="codePHP">{SELTEXT}</div>':'[php]{SELTEXT}[/php]'}},
		code_sql: {title:"SQL", transform: {'<div class="codeSQL">{SELTEXT}</div>':'[sql]{SELTEXT}[/sql]'}},
		code_js: {title:"JS", transform: {'<div class="codeJS">{SELTEXT}</div>':'[js]{SELTEXT}[/js]'}},
		code_css: {title:"CSS", transform: {'<div class="codeCSS">{SELTEXT}</div>':'[css]{SELTEXT}[/css]'}},
		code_html: {title:"HTML", transform: {'<div class="codeHTML">{SELTEXT}</div>':'[html]{SELTEXT}[/html]'}},
		code_xml: {title:"HTML", transform: {'<div class="codeHTML codeXML">{SELTEXT}</div>':'[xml]{SELTEXT}[/xml]'}},
		code: {
			type: "select",
			title: "CODE",
			buttonText: 'CODE',
			options: "code,code_php,code_sql,code_js,code_css,code_html,code_xml",
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
		},
		h1: {title:"Header1", transform: {'<h1>{SELTEXT}</h1>':'[h1]{SELTEXT}[/h1]'}},
		h2: {title:"Header2", transform: {'<h2>{SELTEXT}</h2>':'[h2]{SELTEXT}[/h2]'}},
		h3: {title:"Header3", transform: {'<h3>{SELTEXT}</h3>':'[h3]{SELTEXT}[/h3]'}},
		h4: {title:"Header4", transform: {'<h4>{SELTEXT}</h4>':'[h4]{SELTEXT}[/h4]'}},
		h5: {title:"Header5", transform: {'<h5>{SELTEXT}</h5>':'[h5]{SELTEXT}[/h5]'}},
		h6: {title:"Header6", transform: {'<h6>{SELTEXT}</h6>':'[h6]{SELTEXT}[/h6]'}},
		fontsize: {
			type: 'select',
			title: 'Formatting',
			buttonText: 'formatting',
			options: "fs_verysmall,fs_small,fs_normal,fs_big,fs_verybig,h1,h2,h3,h4,h5,h6"
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