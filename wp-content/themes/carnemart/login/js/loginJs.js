jQuery(function($){

  $( "form#loginform" ).wrap( "<div class='box-login'></div>" );
  $( "form#lostpasswordform" ).wrap( "<div class='box-login'></div>" );
  $( "#login h1" ).wrap( "<div class='box-logo'></div>" );
  $( "#login" ).append( $( "<div class='placa-wp-sp'></div>" ) );

  var $form = $('#loginform');
  if ($form.length && !$form.children('h3.login-bienvenido').length) {
    $('<h3>', { text: 'Bienvenido', class: 'login-bienvenido' }).prependTo($form);
  }
  
  $(".message").delay(5000).fadeOut(500);
  $("#login_error").delay(5000).fadeOut(500);

});
