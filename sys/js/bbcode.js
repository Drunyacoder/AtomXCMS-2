var opens=[];
var isSel=0;
var bbtags   = new Array();
var myAgent   = navigator.userAgent.toLowerCase();
var myVersion = parseInt(navigator.appVersion);

var is_ie   = ((myAgent.indexOf("msie") != -1)  && (myAgent.indexOf("opera") == -1));
var is_nav  = ((myAgent.indexOf('mozilla')!=-1) && (myAgent.indexOf('spoofer')==-1)
&& (myAgent.indexOf('compatible') == -1) && (myAgent.indexOf('opera')==-1)
&& (myAgent.indexOf('webtv') ==-1)       && (myAgent.indexOf('hotjava')==-1));

var is_win   =  ((myAgent.indexOf("win")!=-1) || (myAgent.indexOf("16bit")!=-1));
var is_mac    = (myAgent.indexOf("mac")!=-1);

function cstat(fi){
if (!fi){fi='';}
var c = stacksize(bbtags);

if ( (c < 1) || (c == null) ) {
c = 0;
}

if ( ! bbtags[0] ) {
c = 0;
}
eval('document.getElementById("tagcount'+fi+'").value='+c);
}

function stacksize(thearray){
for (i = 0 ; i < thearray.length; i++ ) {
if ( (thearray[i] == "") || (thearray[i] == null) || (thearray == 'undefined') ) {
return i;
}
}

return thearray.length;
}

function pushstack(thearray,newval,fi){
arraysize = stacksize(thearray);
thearray[arraysize] = newval;
}

function popstack(thearray){
arraysize = stacksize(thearray);
theval = thearray[arraysize - 1];
delete thearray[arraysize - 1];
return theval;
}

function closeall(wh,fi){
if (!fi){fi='';}
if (!wh){wh='message';}	
if (bbtags[0]) {
try {
while (bbtags[0]) {
tagRemove = popstack(bbtags)
document.getElementById(wh).value += "[/" + tagRemove + "]";
if ( (tagRemove != 'font') && (tagRemove != 'size') && (tagRemove != 'color') ){
if (tagRemove=='code'){
eval("document.getElementById('codes"+fi+"').value = ' " + tagRemove + " '");
}
else {
eval("document.getElementById('"+tagRemove+fi+"').value = ' " + tagRemove + " '");
}
opens[tagRemove+fi]=0;
}
}
} catch(e){}
}

eval('document.getElementById("tagcount'+fi+'").value=0');
bbtags = new Array();
document.getElementById(wh).focus();
}


function emoticon(theSmilie,wh){
doInsert(" " + theSmilie + " ","",false,wh);
}

function add_code(NewCode,wh){
if (!wh){wh='message';}
document.getElementById(wh).value += NewCode;
document.getElementById(wh).focus();
}

function alterfont(theval,thetag,wh,fi){
if (!fi){fi='';}
if (theval == 0)
return;

if(doInsert("[" + thetag + "=" + theval + "]", "[/" + thetag + "]",true,wh))
pushstack(bbtags,thetag);

cstat(fi);
}

function simpletag(thetag,fid,chtxt,wh,fi){
if(!fi){fi='';}
var tagOpen;
tagOpen = opens[thetag+fid];

if (!tagOpen){
	if(doInsert("[" + thetag + "]", "[/" + thetag + "]",true,wh)){
		opens[thetag+fid]=1;	
		if (fid){
			document.getElementById(fid).value=chtxt+'*';
		}
		else {
			if (thetag=='code'){
				eval("document.getElementById('codes"+fi+"').value += '*'");
			}
			else {                        
				eval("document.getElementById('"+thetag+fi+"').value += '*'");
			}
		}
		pushstack(bbtags,thetag,fi);
		cstat(fi);
	}
}
else {
	lastindex = 0;
	for (i = 0 ; i < bbtags.length; i++ ){
		if ( bbtags[i] == thetag ){
			lastindex = i;
		}
	}

	while (bbtags[lastindex]){
		tagRemove = popstack(bbtags);
		doInsert("[/" + tagRemove + "]", "",false,wh)
		if ( (tagRemove != 'font') && (tagRemove != 'size') && (tagRemove != 'color') ){
			if (fid){
				document.getElementById(fid).value=chtxt;
			}
			else {
				if (thetag=='code'){
					eval("document.getElementById('codes"+fi+"').value = '"+tagRemove+"'");
				}
				else {
					eval("document.getElementById('"+tagRemove+fi+"').value = '"+tagRemove+"'");
				}
			}
			opens[tagRemove+fid]=0;
		}
	}

	cstat(fi);
}
}

function tag_list(wh){
var listvalue = "init";
var thelist = "";

while ( (listvalue != "") && (listvalue != null) )
{
listvalue = prompt('List item', "");
if ( (listvalue != "") && (listvalue != null) )
{
thelist = thelist+"[*]"+listvalue+"\n";
}
}

if ( thelist != "" )
{
doInsert( "[list]\n" + thelist + "[/list]\n", "",false,wh);
}
}

function tag_url(wh){
var enterURL  = prompt('Site address', "http://");
var enterTITLE=isSelected(wh);
if (enterTITLE.length==0){
	enterTITLE = prompt('Site name',"My WebPage"); 		
}
if (!enterURL || enterURL=='http://'){
	return;
}
else if (!enterTITLE) {
	return;
}

doInsert("[url="+enterURL+"]"+enterTITLE+"[/url]","",false,wh);	
}

function tag_image(wh){
var FoundErrors = '';
var enterURL   = prompt('Image URL',"http://");

if (!enterURL || enterURL=='http://' || enterURL.length<20) {
return;
}

doInsert("[img]"+enterURL+"[/img]","",false,wh);
}

function tag_email(wh) {
var emailAddress = prompt('E-mail address',"");

if (!emailAddress) {return;}
var enterTITLE=isSelected(wh);
if (enterTITLE.length>0){
	doInsert("[email="+emailAddress+"]"+enterTITLE+"[/email]","",false,wh);	
}
else {
	doInsert("[email]"+emailAddress+"[/email]","",false,wh);	
}

}

function doInsert(ibTag,ibClsTag,isSingle,wh){
if (!wh){wh='message';}
var isClose = false;
var obj_ta = document.getElementById(wh);

if ( (myVersion >= 4) && is_ie && is_win)
{
if(obj_ta.isTextEdit){
obj_ta.focus();
var sel = document.selection;
var rng = sel.createRange();
rng.colapse;
if((sel.type == "Text" || sel.type == "None") && rng != null){
if(ibClsTag != "" && rng.text.length > 0)
ibTag += rng.text + ibClsTag;
else if(isSingle)
isClose = true;

rng.text = ibTag;
}
}
else{
if(isSingle)
isClose = true;
obj_ta.value += ibTag;
}
}
else try {
var scr = obj_ta.scrollTop;

var txtStart = obj_ta.selectionStart;
if(!(txtStart >= 0)) throw 1;
var txtEnd   = obj_ta.selectionEnd;
if(ibClsTag != "" && obj_ta.value.substring(txtStart,txtEnd).length>0) {
obj_ta.value = obj_ta.value.substring(0,txtStart) + ibTag + obj_ta.value.substring(txtStart,txtEnd) + ibClsTag + obj_ta.value.substring(txtEnd,obj_ta.value.length);
} else {
if(isSingle) isClose = true;  
if (isSel==1){obj_ta.value = obj_ta.value.substring(0,txtStart) + ibTag + obj_ta.value.substring(txtEnd,obj_ta.value.length);}
else {obj_ta.value = obj_ta.value.substring(0,txtStart) + ibTag + obj_ta.value.substring(txtStart,obj_ta.value.length);}
}
obj_ta.scrollTop=scr;
} catch(e) {
if(isSingle){isClose = true;}
obj_ta.value += ibTag;
}
obj_ta.focus();
return isClose;
}



function isSelected(wh){
if (!wh){wh='message';}
var obj_ta = document.getElementById(wh);

if ( (myVersion >= 4) && is_ie && is_win){
	if(obj_ta.isTextEdit){
		obj_ta.focus();
		var sel = document.selection;
		var rng = sel.createRange();
		rng.colapse;
		if((sel.type == "Text" || sel.type == "None") && rng != null){
			if(rng.text.length > 0){
				isSel=1;
				return rng.text;		
			}
		}
	}
	return '';
}
try {

	var txtStart = obj_ta.selectionStart;
	if(!(txtStart >= 0)) throw 1;
	var txtEnd   = obj_ta.selectionEnd;
	if(obj_ta.value.substring(txtStart,txtEnd).length>0) {
		isSel=1;
		return obj_ta.value.substring(txtStart,txtEnd);
	}
} catch(e) {}
return '';
}