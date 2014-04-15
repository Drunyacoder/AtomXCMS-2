function initAtomMultiload(module_text, error_text)
{
	var parseResponse = function(module, data){
		if (typeof data.errors != 'undefined' && data.errors.length > 0) {
			fpsWnd('add-attach-errors', error_text, data.errors);
			return;
		}
		
		var getTitle = function(data) {
			return "Click to insert\n" + 
				"Name: " + data.filename + 
				"\nSize: " + AtomX.getSimpleFileSize(data.size) + 
				"\nDate: " + data.date +
				((data.user && data.user.length) ? "\nUser: " + data.user.name : '');
		}
	
		$(data).each(function(key, value){
			if (value.is_image == 1) {
				$('#attaches-info').html($('#attaches-info').html() + 
					'<img id="attach-' + value.id + '" title="' + getTitle(value) + '" src="/sys/files/' + module + 
					'/' + value.filename + '" ' + 
					' onClick="AtomX.insetAtomImage(' + value.id + ');" /><div class="attach-delete" ' + 
					' onClick="AtomX.deleteAttach(\'' + module + '\', ' + value.id + ')"></div>');
			} else {
				$('#attaches-info').html($('#attaches-info').html() + 
					'<img id="attach-' + value.id + '" title="' + getTitle(value) + '" src="/template/default/img/' + 
					'/atm-file-icon.png" ' + 
					' onClick="AtomX.insetAtomImage(' + value.id + ');" /><div class="attach-delete" ' + 
					' onClick="AtomX.deleteAttach(\'' + module + '\', ' + value.id + ')"></div>');
			}
		});
	};
	AtomX.initMultiFileUploadHandler(module_text, parseResponse);
}