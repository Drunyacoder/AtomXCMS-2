function number_format(number, decimals, dec_point, thousands_sep) {
  number = (number + '')
    .replace(/[^0-9+\-Ee.]/g, '');
  var n = !isFinite(+number) ? 0 : +number,
    prec = !isFinite(+decimals) ? 0 : Math.abs(decimals),
    sep = (typeof thousands_sep === 'undefined') ? ',' : thousands_sep,
    dec = (typeof dec_point === 'undefined') ? '.' : dec_point,
    s = '',
    toFixedFix = function(n, prec) {
      var k = Math.pow(10, prec);
      return '' + (Math.round(n * k) / k)
        .toFixed(prec);
    };
  // Fix for IE parseFloat(0.55).toFixed(0) = 0;
  s = (prec ? toFixedFix(n, prec) : '' + Math.round(n))
    .split('.');
  if (s[0].length > 3) {
    s[0] = s[0].replace(/\B(?=(?:\d{3})+(?!\d))/g, sep);
  }
  if ((s[1] || '')
    .length < prec) {
    s[1] = s[1] || '';
    s[1] += new Array(prec - s[1].length + 1)
      .join('0');
  }
  return s.join(dec);
}

$(document).ready(function() {
  $('.add-to-basket').on('click', function(event) {
    var formContainer = event.target.parent();
    var productId = event.target.data('product-id');
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
        if (data.errors.length) {
          fpsWnd("atm-shop-cart-error", AtomX.messages.error, data.errors);
          $('#main-overlay').hide();
          return false;
        }

        data = data.data;

        if (data.total > 0)
          $('#mini-basket').addClass('active');
        else
          $('#mini-basket').removeClass('active');
        $('#mini-basket #products-cnt').html('(' + data.products.length + ')');
        $('#mini-basket .total').html(number_format(data.total, 2, ',', ' '));
        $('#main-overlay').hide();
      },
      error: errorHandler = function() {
        $('#main-overlay').hide();
        fpsWnd("atm-shop-cart-error", AtomX.messages.error, AtomX.messages.add_to_basket_error);
      }
    });
  });
});