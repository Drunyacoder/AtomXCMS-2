var wRight = 0;
var wLeft = 0;
var wStep = 10;
var winTimeout = 50;
var openedWindows = new Array();
//var wObj = document.getElementById('test');


AtomX = new function() {
	this.toggle = function(id){
		var el = $('#'+id);
		if (el.is(':visible')) 
			el.hide();
		else
			el.show();
	};
	
	this.toggleByClass = function(class_name){
		var el = $('.'+class_name);
		if (el.is(':visible')) 
			el.hide();
		else
			el.show();
	};
	
	this.hideAll = function(class_name) {
		$('.'+class_name).hide();
	};
	
	// autocompleter @url - pathtosite/admin/find_users.php?name=<name>
	this.findUsers = function(url, id) {
		var output = '';
		
		$.get(url, {}, function(response){
			var users = $.parseJSON(response);
			
			$(users).each(function(k, user){
				output += '<li><a href="../admin/users_rules.php?new_sp=' + user.id + '">' + user.name + '</a> (' + user.id + ')</li>';
			});
			
			$('#'+id).html('<ul>'+output+'</ul>');
		});
	};
	
	/**
	 * autocompleter 
	 * @url - pathtosite/admin/find_users.php?name=<name>
	 * @id - block ID to past content
	 * @to_url - for each user
	 */
	this.findUsersForForums = function(url, id, to_url) {
		var output = '';
		
		$.get(url, {}, function(response){
			var users = $.parseJSON(response);
			
			
			$(users).each(function(k, user){
				str = to_url.replace(/%id/, user.id);
				str = str.replace(/%name/, user.name);
				
				output += '<li><a href="' + str + '">' + user.name + '</a> (' + user.id + ')</li>';
			});
			
			$('#'+id).html('<ul>'+output+'</ul>');
		});
	};
	

}





function resizeWrapper(id) {
	var nheight = $(id).height();
	var wrapheight = $('#content-wrapper').height();
	
	if (nheight > wrapheight) {
		$('#content-wrapper').height(nheight);
	}
}

function selectAclTab(id) {
	$('div.acl-perms-collection').each(function(){
		$(this).hide();
	});
	
	$('div#aclset' + id).show();
}


function openPopup(id) {
	resizeWrapper($('#'+id));
	var datatop = parseInt((typeof $('#'+id).data('top') != 'undefined') ? $('#'+id).data('top') : 0);
	var newtop = window.scrollY + datatop;
	$('#'+id).css({
		'top': newtop
	});

	$('#' + id).fadeIn(1000);
	
	if (!$('div#overlay').is(':visible')) {
		$('div#overlay').fadeIn();
	}
}

function closePopup(id) {
	$('#' + id).fadeOut(300, function(){
		if (!$('div.popup').is(':visible') && $('div#overlay').is(':visible')) {
			$('#overlay').fadeOut('fast', function(){
				$('#overlay').hide();
			});
		}
	});
}



function wiOpen(pref) {
	$('#' + pref + '_dWin').fadeIn(1000);
}


function hideWin(pref) {
	$('#' + pref + '_dWin').fadeOut(500);
}

function addWin(prefix) {
	document.getElementById(prefix + '_add').style.display = '';
	document.getElementById(prefix + '_view').style.display = 'none';	
}

function subMenu(id) {
	menu_item_over = true;
	hideAll();

	if (!$('#'+id).is(':visible')) {
		$('#'+id).slideDown();
	} else {
		$('#'+id).slideUp();
	}
	
	
}


function save(prefix) {

	var inp = document.getElementById(prefix + '_inp').value;
	if (prefix == 'cat')
		var id_sec = document.getElementById(prefix + '_secId').value;
	else
		var id_sec = '';
	if (typeof inp == 'undefined' || typeof inp == '' || inp.length < 2) {
		alert('Слишком короткое название');
		return;
	} else {
		$.post('load_cat.php?ac=add', {title : inp, type: prefix, id_sec: id_sec}, function(data) { window.location.href = ''; });
		
	}
}
function _confirm() {
	return confirm('Вы уверены?');
}


/* help window */
function showHelpWin(text, title) {
	var helpWin = document.createElement('div');
	
	
	helpWin.innerHTML = '<div class="popup" id="help-window" style="display:block;">' +
		'<div class="top">' +
			'<div class="title">' + title + '</div>' +
			'<div class="close" onClick="closeHelpPopup(\'help-window\')"></div>' +
		'</div>' +
		'<div class="items text">' +
			text +
		'</div>' +
	'</div>';
	
	$('#content-wrapper').append(helpWin);
}

function closeHelpPopup(id) {
	$('#'+id).fadeOut(400, function(){
		$('#'+id).remove();
	})
}




/* ****** TOP MENU ******* */
var drunya_menu = false;
var menu_item_over = false;
document.onclick = function() {
	if (menu_item_over == false) {
		drunya_menu = false;
		hideAll();
	}
}
function drunyaMenu(params) {

	this.content = '<ul>';

	
	for(var key in params){
		var param = params[key];

			
		this.content = this.content + '<li onClick="subMenu(\'topsub' + key + '\');"><a href="#">' + param[0] + '</a>'
			+ '<div id="topsub' + key + '" class="sub">'
			+ '<div class="shadow">'
			+ '<ul>';
			
		for(var _key in param[1]){
			var line = param[1][_key];
			if (line == 'sep') {
				this.content = this.content + 
				'<li class="top-menu-sep"></li>';
			} else {
				this.content = this.content + 
				'<li>' + line + '</li>';
			}
		}
		this.content = this.content + '</ul></div></div></li>';
	}

	document.getElementById('topmenu').innerHTML = this.content + '</ul>';
}



function hideAll() {
	$('#topmenu > ul > li .sub').each(function(){
		$(this).slideUp('fast');
	});
	
	
	$('.side-menu > ul > li .sub').each(function(){
		$(this).slideUp('fast');
	});
	
	return;
}




/* ************  MENU BLOCK ************ */



function hideSubMenu(id) {
	/*
	setTimeout(function() {
		document.getElementById(id).style.display = 'none';
	}, 3000);
	*/
	return;
}

function showSubMenu(id) {
	hideAll();
	document.getElementById(id).style.display = '';
}

/**
 * Change image when changing template
 */
function showScreenshot(path) {
	var img = document.getElementById('screenshot');
	if (img != 'undefined') {
		img.src = path;
	}
}



FpsLib = new function(){
	this.showLoader = function(){
		$('#ajax-loader').show();
	};
	this.hideLoader = function(){
		$('#ajax-loader').hide();
	};
};