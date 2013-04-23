function NewOdnaknopka2() {
	this.domain=location.href+'/';
	this.domain=this.domain.substr(this.domain.indexOf('://')+3);
	this.domain=this.domain.substr(0,this.domain.indexOf('/'));
	this.location=false;
	this.selection=function() {
		var sel;
		if (window.getSelection) sel=window.getSelection();
		else if (document.selection) sel=document.selection.createRange();
		else sel='';
		if (sel.text) sel=sel.text;
		return encodeURIComponent(sel);
	}
	this.redirect=function() {
	if (this.location) location.href=this.location;
	this.location=false;
	}
	this.go=function(i) {
		this.location=this.url(i);
		window.open(this.location,'odnaknopka');
		return false;
	}
	this.url=function(system) {
	var title=encodeURIComponent(document.title);
	var url=encodeURIComponent(location.href);
	switch (system) {
	case 1: return 'http://vkontakte.ru/share.php?url='+url;
	case 2: return 'http://www.facebook.com/sharer.php?u='+url;
	case 3: return 'http://twitter.com/home?status='+title+' '+url;
	case 4: return 'http://friendfeed.com/?title='+title+'&url='+url;
	case 5: return 'http://connect.mail.ru/share?share_url='+url;
	case 6: return 'http://www.livejournal.com/update.bml?event='+url+'&subject='+title;
	case 7: return 'http://memori.ru/link/?sm=1&u_data[url]='+url+'&u_data[name]='+title;
	case 8: return 'http://bobrdobr.ru/addext.html?url='+url+'&title='+title;
	case 9: return 'http://www.google.com/bookmarks/mark?op=add&bkmk='+url+'&title='+title;
	case 10: return 'http://zakladki.yandex.ru/userarea/links/addfromfav.asp?bAddLink_x=1&lurl='+url+'&lname='+title;
	case 11: return 'http://www.mister-wong.ru/index.php?action=addurl&bm_url='+url+'&bm_description='+title;
	case 12: return 'http://del.icio.us/post?v=4&noui&jump=close&url='+url+'&title='+title;
	}
	}
	this.hide=function() {
	if (this.timeout) clearTimeout(this.timeout);
	document.getElementById('odnaknopka').style.visibility='hidden';
	}
	this.show=function(element) {
	if (this.timeout) clearTimeout(this.timeout);
	var left=0,top=0;
	var style=document.getElementById('odnaknopka').style;
	while (element) {
	left+=element.offsetLeft;
	top+=element.offsetTop;
	element=element.offsetParent;
	}
	style.left=left+'px';
	style.top=top+'px';
	style.visibility='visible';
	}
	this.init=function() {
	var titles=new Array('&#1042; &#1050;&#1086;&#1085;&#1090;&#1072;&#1082;&#1090;&#1077;','Facebook','Twitter','FriendFeed','&#1052;&#1086;&#1081; &#1052;&#1080;&#1088;','LiveJournal','Memori','&#1041;&#1086;&#1073;&#1088;&#1044;&#1086;&#1073;&#1088;','&#1047;&#1072;&#1082;&#1083;&#1072;&#1076;&#1082;&#1080; Google','&#1071;&#1085;&#1076;&#1077;&#1082;&#1089;.&#1047;&#1072;&#1082;&#1083;&#1072;&#1076;&#1082;&#1080;','Mister Wong','Delicious');
	if (!document.getElementById('odnaknopka')) {
	var div=document.createElement('div');
	div.id='odnaknopka';
	div.style.position='absolute';
	div.style.visibility='hidden';
	div.style.width='264px';
	div.style.height='182px';
	div.style.backgroundColor='transparent';
	div.style.backgroundImage='url(/sys/img/share-panel.png)';
	div.style.border='0';
	div.style.margin='0';
	div.style.padding='0 1px 4px 1px';
	div.style.overflow='hidden';
	div.style.zIndex='1000';
	div.style.font='normal 12px arial';
	div.style.lineHeight='20px';
	div.style.color='#666';
	html='<div style="display:block;float:left;width:258px;height:20px;overflow:hidden;margin:1px 0;padding:0;background-color:transparent;font:bold 11px arial;color:#666;text-decoration:none"></div>';
	for (var i=0;i<12;i++) {
	html+='<noindex><a rel="nofollow" href="'+this.url(i+1)+'" style="display:block;float:left;width:108px;height:16px;overflow:hidden;margin:1px 0;padding:0 0 0 24px;background-color:transparent;background:url(/sys/img/share-panel.png) no-repeat -266px '+(-i*16)+'px;font:normal 12px arial;color:#666;text-decoration:none;text-align:left" onmouseover="this.style.textDecoration=\'underline\'" onmouseout="this.style.textDecoration=\'none\'" onclick="return odnaknopka2.go('+(i+1)+');">'+titles[i]+'</a></noindex>';
	}
	div.innerHTML=html;
	div.onmouseover=function() {if (odnaknopka2.timeout) clearTimeout(odnaknopka2.timeout)}
	div.onmouseout=function() {odnaknopka2.timeout=setTimeout('odnaknopka2.hide()',500)};
	document.body.insertBefore(div,document.body.firstChild);
	}
	document.write('<img src="/sys/img/share-button.gif" width="136" height="16" alt="Ìû ג סמצסועÿץ" title="Ìû ג סמצסועÿץ" style="border:0;margin:0;padding:0" onmouseover="odnaknopka2.show(this);" onmouseout="odnaknopka2.timeout=setTimeout(\'odnaknopka2.hide()\',500);">');
	}
}
odnaknopka2=new NewOdnaknopka2();
odnaknopka2.init();