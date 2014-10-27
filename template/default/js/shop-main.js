/**
 * ©AtomX CMS
 * Project site: http://atomx.net/
 * The Shop module scripts.
 *
 */
$(document).ready(function() {
  $('.atm_shop-add_to_basket').on('click', function(event) {
	$('.shop-modal-window').hide();
	$(this).next('.shop-modal-window').show();
  });
  
  $('.atm_shop-cancel_basket').on('click', function(event) {
	$(this).parent('.shop-modal-window').hide();
  });
  
  $('.atm_shop-put_to_basket').on('click', function(event) {
    var formContainer = $(this).parent('.shop-modal-window');
    var productId = $(this).data('product-id');
    var quantity = formContainer.find('input[name="quantity"]').val();
    if (!quantity)
      quantity = 1;

    $.ajax({
      url: '/' + AtomX.module + '/edit_basket/' + productId + '/' + quantity + '/',
      type: 'GET',
      dataType: 'json',
      beforeSend: function() {
        $('#main-overlay').show();
        formContainer.find('input[name="quantity"]').val(1);
        formContainer.hide();
      },
      success: function(data){
        if (data.errors && data.errors.length) {
          fpsWnd("atm-shop-cart-error", AtomX.messages.error, data.errors);
          $('#main-overlay').hide();
          return false;
        }

        data = data.data;

        if (data.total > 0)
          $('#mini-basket').addClass('active');
        else
          $('#mini-basket').removeClass('active');
        $('#mini-basket #products-cnt').html(parseInt(data.total_products, 10));
        $('#mini-basket .total .sum').html(data.total.toFixed(2).replace(/(\d)(?=(\d\d\d)+(?!\d))/g, "$1,"));
        $('#main-overlay').hide();
      },
      error: errorHandler = function() {
        $('#main-overlay').hide();
        fpsWnd("atm-shop-cart-error", AtomX.messages.error, AtomX.messages.add_to_basket_error);
      }
    });
  });

  $('.tab-switcher').on('click', function() {
    var tabId = 'tab-' + $(this).data('tab');

    $('.tab').hide();
    $('.tab').removeClass('active');
    $('.tab-switcher').removeClass('active');

    $('#' + tabId).show();
    $('#' + tabId).addClass('active');
    $(this).addClass('active');
  });
});
