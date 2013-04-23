/*
CopyRight by:
	Lebedenko Nikolay Nikolayevich (Lebnik)
How use:
	$("FORM").placeholderLebnik();
Contact with me:
	http://yapro.ru/
	http://vkontakte.ru/club23074103
	http://www.facebook.com/pages/YaPro-CMS/184609554891388
	http://twitter.com/lebnik
	http://yalebnik.livejournal.com/
	http://odnoklassniki.ru/group/50396187918481
	http://totx.ya.ru/
	http://lebnik.habrahabr.ru/
*/
;(function($){
	
	$.fn.placeholderLebnik = function(){
		
		var i = document.createElement('input');// проверка поддержки
		if('placeholder' in i){ return true; }// placeholder support
		
		$("input:text,textarea",this).each(function(){
			
			var placeholder = $(this).attr("placeholder");
			
			if(placeholder){
				
				$(this).data("placeholder",placeholder).removeAttr("placeholder").focus(function(){
					
					if($(this).val()==placeholder){
						$(this).val("");
					}
					
					$(this).removeClass("placeholder");
					
				}).blur(function(){
					var v = $(this).val();
					if(v=="" || v==" " || v==placeholder){
						$(this).addClass("placeholder").val(placeholder);
						
					}
				}).trigger("blur");
			}
		});
		
		$(this).each(function(){
			
			if(this.tagName == "FORM"){
				var f = this;
			}else{
				var f = $(this).closest("FORM");
			}
			
			if(f && f.tagName == "FORM"){
				$(f).submit(function(){
					$(".placeholder", this).each(function(){
						var placeholder = $(this).data("placeholder");
						if($(this).val()==placeholder){
							$(this).val("");
						}
					});
				});
			}
		});
		
		return this;
	};
})(jQuery);