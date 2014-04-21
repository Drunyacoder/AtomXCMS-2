function initAtomMultiload(module_text, error_text, template_path)
{
	var parseResponse = function(module, data){
		if (typeof data.errors != 'undefined' && data.errors.length > 0) {
			fpsWnd('add-attach-errors', error_text, data.errors);
			return;
		}
		
		if (typeof data == 'undefined' || data.length == 0 || data == false) {
			fpsWnd('add-attach-errors', 'Information', 'Файлов не нашлось');
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
					'<img id="attach-' + value.id + '" title="' + getTitle(value) + '" src="/image/' + module + 
					'/' + value.filename + '/150/" ' + 
					' onClick="AtomX.insetAtomImage(' + value.id + ');" /><div class="attach-delete" ' + 
					' onClick="AtomX.deleteAttach(\'' + module + '\', ' + value.id + ')"></div>');
			} else {
				$('#attaches-info').html($('#attaches-info').html() + 
					'<img id="attach-' + value.id + '" title="' + getTitle(value) + '" src="' + template_path + '/img/' + 
					'/atm-file-icon.png" ' + 
					' onClick="AtomX.insetAtomImage(' + value.id + ');" /><div class="attach-delete" ' + 
					' onClick="AtomX.deleteAttach(\'' + module + '\', ' + value.id + ')"></div>');
			}
		});
	};
	AtomX.initMultiFileUploadHandler(module_text, parseResponse);
}