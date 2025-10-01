jQuery(function ($) {
  /**
   * Utilidades
   */
  const $dateInput   = $('#pickup_date');
  const $timeSelect  = $('#pickup_time');
  const $dateWrap    = $('#pickup_date_field');
  const $timeWrap    = $('#pickup_time_field');
  const ERROR_CLASS  = 'woocommerce-invalid woocommerce-invalid-required-field';
  const MSG_CLASS    = 'checkout-inline-error-message';

  function clearError($wrap) {
    $wrap.removeClass(ERROR_CLASS);
    $wrap.find('.' + MSG_CLASS).remove();
    // accesibilidad
    $wrap.find('input, select').attr('aria-invalid', 'false');
  }

  function setError($wrap, msg) {
    clearError($wrap);
    $wrap.addClass(ERROR_CLASS);
    $wrap.append('<p class="' + MSG_CLASS + '">' + msg + '</p>');
    $wrap.find('input, select').attr('aria-invalid', 'true');
  }

  /**
   * Validaciones
   */
  function validateDateField() {
    const val     = ($dateInput.val() || '').trim();
    const minDate = $dateInput.attr('min');
    const maxDate = $dateInput.attr('max');

    clearError($dateWrap);

    if (!val) {
      setError($dateWrap, 'Por favor, selecciona una fecha de recogida.');
      return false;
    }

    // Comparación simple usando el formato YYYY-MM-DD que ya traen min/max
    if ((minDate && val < minDate) || (maxDate && val > maxDate)) {
      setError($dateWrap, 'Por favor, selecciona una fecha dentro del rango permitido.');
      return false;
    }

    return true;
  }

  function validateTimeField() {
    const val = ($timeSelect.val() || '').trim();

    clearError($timeWrap);

    if (!val) {
      setError($timeWrap, 'Por favor, selecciona una hora de recogida.');
      return false;
    }

    return true;
  }

  function validateAll() {
    const okDate = validateDateField();
    const okTime = validateTimeField();
    return okDate && okTime;
  }

  /**
   * Enlazar eventos
   */
  // Validación inmediata al entrar
  validateAll();

  // Validación al cambiar los campos
  $dateInput.on('change input', function () {
    validateDateField();
    // si cambias la fecha, revalida también la hora por si dependiera de stocks/slots
    validateTimeField();
  });

  $timeSelect.on('change', function () {
    validateTimeField();
  });

  // Interceptar el envío del checkout
  // WooCommerce usa por defecto `form.checkout`
  const $checkoutForm = $('form.checkout').length ? $('form.checkout') : $('form').first();

  $checkoutForm.on('submit', function (e) {
    if (!validateAll()) {
      e.preventDefault();
      e.stopImmediatePropagation();

      // Llevar al primer error y enfocar
      const $firstError = $('.' + ERROR_CLASS).first();
      if ($firstError.length) {
        $('html, body').animate({ scrollTop: $firstError.offset().top - 100 }, 250);
        const $focusable = $firstError.find('input, select').first();
        if ($focusable.length) $focusable.trigger('focus');
      }
    }
  });
});