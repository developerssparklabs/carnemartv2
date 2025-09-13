(function e(t, n, r) {
  function s(o, u) {
    if (!n[o]) {
      if (!t[o]) {
        var a = typeof require == "function" && require;
        if (!u && a) return a(o, !0);
        if (i) return i(o, !0);
        throw new Error("Cannot find module '" + o + "'");
      }
      var f = (n[o] = { exports: {} });
      t[o][0].call(
        f.exports,
        function (e) {
          var n = t[o][1][e];
          return s(n ? n : e);
        },
        f,
        f.exports,
        e,
        t,
        n,
        r
      );
    }
    return n[o].exports;
  }
  var i = typeof require == "function" && require;
  for (var o = 0; o < r.length; o++) s(r[o]);
  return s;
})(
  {
    1: [
      function (require, module, exports) {
        // global.jQuery = require('jquery');
        // bootstrap = require('bootstrap');

        jQuery(document).ready(function () {
          // Menu Mobile
          jQuery("#ctaMenuMobile").click(function () {
            jQuery("#boxMenuPrincipal").addClass("boxMenuActivo");
          });

          jQuery("#ctaCloseMenu").click(function () {
            jQuery("#boxMenuPrincipal").removeClass("boxMenuActivo");
          });

          jQuery("#menu-menu-principal a").click(function () {
            jQuery("#boxMenuPrincipal").removeClass("boxMenuActivo");
          });

          //Menú fijo on scroll ok
          jQuery(window).scroll(function () {
            if (jQuery(this).scrollTop() > 50) {
              jQuery(".site__header").addClass("isMenuScroll");
              jQuery("body").addClass("isMenuScroll-activo");
            } else {
              jQuery(".site__header").removeClass("isMenuScroll");
              jQuery("body").removeClass("isMenuScroll-activo");
            }
          });

          //Desplegar sidebar de carrito
          jQuery("#ctaOpenSideCarrito").click(function () {
            // alert('No jala');
            jQuery("#barraCarrito").addClass("sideBarActiva");
          });

          jQuery("#ctaCloseBarCarrito").click(function () {
            jQuery("#barraCarrito").removeClass("sideBarActiva");
          });

          //Landings One page Sroll
          jQuery(".one-page-link a, a.one-page-link").click(function (event) {
            event.preventDefault();

            var defaultAnchorOffset = 0;

            var $anchor = jQuery("#" + this.hash.substring(1));

            var anchorOffset = $anchor.data("anchor-offset");
            if (!anchorOffset) anchorOffset = defaultAnchorOffset;

            jQuery("html,body").animate(
              {
                scrollTop: $anchor.offset().top - 100,
              },
              600
            );
          });

          // Slider Tipo Muro
          jQuery(".slider-muro").slick({
            slidesToShow: 1,
            slidesToScroll: 1,
            infinite: true,
            prevArrow: '<div class="custom-arrow-square-muro cas-prev">',
            nextArrow: '<div class="custom-arrow-square-muro cas-next">',
            fade: true,
            asNavFor: ".slider-nav-muro",
          });
          
          jQuery(".slider-nav-muro").slick({
            slidesToShow: 4,
            slidesToScroll: 1,
            infinite: true,
            autoplay: true,
            autoplaySpeed: 8000,
            asNavFor: ".slider-muro",
            vertical: true,
            dots: false,
            arrows: false,
            centerMode: false,
            focusOnSelect: true,
            responsive: [
              {
                breakpoint: 768,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 2,
                  vertical: false,
                },
              },
              {
                breakpoint: 680,
                settings: {
                  slidesToShow: 1,
                  slidesToScroll: 1,
                  vertical: false,
                },
              },
            ],
          });

          

// Marcar la PRIMERA imagen del slider como LCP (eager + alta prioridad)
(function ($) {
  var $slider = $('.slider-banner-completo');

  // 1) Antes de inicializar slick: marca la primera imagen
  //    (soporta <img> en el hijo directo o anidado)
  var $allImgs   = $slider.find('img');
  var $firstImg  = $allImgs.first();

  $firstImg.attr({
    loading: 'eager',
    fetchpriority: 'high',
    decoding: 'async'
  });

  // El resto siguen en lazy
  $allImgs.not($firstImg).attr({
    loading: 'lazy',
    fetchpriority: 'low'
  });

  // 2) Por seguridad, tras el init (por si Slick clona slides),
  //    vuelve a marcar la imagen del slide actual como prioritaria.
  $slider.on('init', function (e, slick) {
    var $currentImg = $(slick.$slides[slick.currentSlide]).find('img').first();
    $currentImg.attr({ loading: 'eager', fetchpriority: 'high' });
  });

})(jQuery);

(function ($) {
  if (typeof $.fn.slick !== 'function') return; // por si Slick no está cargado

  var SEL = '.slider-banner-completo';
  var $sliders = $(SEL);

  // ——— Helpers ————————————————————————————————
  function fixDotsA11y(slick) {
    var $dots = $(slick.$dots);
    if (!$dots || !$dots.length) return;

    // ul como lista (no "tablist")
    $dots.attr('role', 'list').removeAttr('aria-label');

    // li decorativos
    $dots.children('li').attr('role', 'none');

    // botones con etiqueta clara
    var total = slick.slideCount || 0;
    $dots.find('button').each(function (i) {
      $(this)
        .attr({ type: 'button', 'aria-label': 'Ir al slide ' + (i + 1) + ' de ' + total })
        .removeAttr('role aria-selected aria-controls');
    });
  }

  function markSlides($wrap, slick) {
    $wrap.find('.slick-slide').each(function (i) {
      $(this)
        .attr('role', 'group')
        .attr('aria-roledescription', 'slide')
        .attr('aria-label', 'Slide ' + (i + 1) + ' de ' + slick.slideCount);
    });
  }

  // ——— Bind de eventos para corregir después de cada render ———
  $sliders.on('init reInit setPosition breakpoint afterChange', function (e, slick) {
    var $wrap = $(this);
    setTimeout(function () {
      fixDotsA11y(slick);
      markSlides($wrap, slick);
    }, 0);
  });

  // ——— Inicializa (si aún no lo está) ————————————————
  $sliders.each(function () {
    var $el = $(this);
    if (!$el.hasClass('slick-initialized')) {
      $el.slick({
        slidesToShow: 1,
        slidesToScroll: 1,
        infinite: true,
        autoplay: true,
        autoplaySpeed: 12000,
        dots: true,
        lazyLoad: 'progressive',
        accessibility: true, // OK; arriba neutralizamos el patrón de tabs
        prevArrow:
          '<button class="custom-arrow-square-muro cas-prev" type="button" aria-label="Anterior"></button>',
        nextArrow:
          '<button class="custom-arrow-square-muro cas-next" type="button" aria-label="Siguiente"></button>',
        // IMPORTANTE: devolvemos el BOTÓN completo (no spans sueltos)
        customPaging: function (slider, i) {
          var n = i + 1;
          var total = slider.slideCount || (slider.$slides ? slider.$slides.length : 0);
          return (
            '<button type="button" class="slick-dot-btn" aria-label="Ir al slide ' + n + ' de ' + total + '">' +
              '<span aria-hidden="true">' + (n < 10 ? '0' + n : n) + '</span>' +
              '<span class="sr-only">Ir al slide ' + n + '</span>' +
            '</button>'
          );
        }
      });
    } else {
      // Si ya estaba montado, corrige ahora mismo
      var inst = $el.slick('getSlick');
      fixDotsA11y(inst);
      markSlides($el, inst);
    }
  });
})(jQuery);




          

          // Slider Informacion de servicios
          jQuery(".slider-informacion").slick({
            dots: true,
            arrows: false,
            infinite: true,
            autoplaySpeed: 6000,
            autoplay: true,
            slidesToShow: 2,
            slidesToScroll: 2,

            responsive: [
              {
                breakpoint: 1200,
                settings: {
                  slidesToShow: 2,
                  slidesToScroll: 2,
                  infinite: true,
                  dots: true,
                },
              },
              {
                breakpoint: 769,
                settings: {
                  arrows: false,
                  slidesToShow: 1,
                  slidesToScroll: 1,
                },
              },
              {
                breakpoint: 680,
                settings: {
                  arrows: false,
                  centerMode: true,
                  centerPadding: "20px",
                  slidesToShow: 1,
                },
              },
            ],
          });

          // Bloque para mostrar cards por lista o por grid
          jQuery("#ctaListadoGrid").addClass("active");
          jQuery("#ctaListadoGrid").click(function () {
            jQuery(".material-de-vista").removeClass(
              "site__wrapper-cards-vertical-prov"
            );
            jQuery(".material-de-vista").addClass(
              "site__wrapper-cards-horizontal-prov"
            );
            if (jQuery(this).hasClass("active")) {
              jQuery(this).removeClass("active");
            } else {
              jQuery(this).addClass("active");
            }
            jQuery("#ctaListadoLista").removeClass("active");
          });

          jQuery("#ctaListadoLista").click(function () {
            jQuery(".material-de-vista").addClass(
              "site__wrapper-cards-vertical-prov"
            );
            jQuery(".material-de-vista").removeClass(
              "site__wrapper-cards-horizontal-prov"
            );
            if (jQuery(this).hasClass("active")) {
              jQuery(this).removeClass("active");
            } else {
              jQuery(this).addClass("active");
            }
            jQuery("#ctaListadoGrid").removeClass("active");
          });

          // Galeria de elementos

          jQuery(".popup-gallery").magnificPopup({
            delegate: "a",
            type: "image",
            tLoading: "Leyendo imagen #%curr%...",
            mainClass: "mfp-img-mobile",
            gallery: {
              enabled: true,
              navigateByImgClick: true,
              preload: [0, 1], // Will preload 0 - before current, and 1 after the current image
            },
            image: {
              tError:
                '<a href="%url%">The image #%curr%</a> could not be loaded.',
              titleSrc: function (item) {
                return item.el.attr("title") + "";
              },
            },
          });

          // Verifica si los elementos existen
          if (
            jQuery(".woocommerce-result-count, .woocommerce-ordering").length
          ) {
            // Crea el contenedor
            const headerInfoWrapper = $(
              '<div class="woocommerce-header-info"></div>'
            );

            // Mueve los elementos dentro del nuevo contenedor
            $(".woocommerce-result-count, .woocommerce-ordering").wrapAll(
              headerInfoWrapper
            );
          }

          // Funcionalidad de bloque de carrito checkout para separar en dos las columnas
          var bankDetails = $(".woocommerce-bacs-bank-details");
          var orderDetails = $(".woocommerce-order-details");

          // Verifica si ambos elementos existen en la página
          if (bankDetails.length && orderDetails.length) {
            // Verifica si ya están envueltos en un contenedor .wc-box-data
            if (
              !bankDetails.closest(".wc-box-data2").length &&
              !orderDetails.closest(".wc-box-data2").length
            ) {
              // Crea un contenedor con la clase .wc-box-data
              var container = $('<div class="wc-box-data2"></div>');

              // Mueve los elementos al contenedor correctamente sin generar elementos vacíos
              bankDetails.add(orderDetails).wrapAll(container);
            }
          }

          // Cambiar el texto de los textos de instrucciones
          jQuery(".woocommerce-order p:contains(Instrucciones)").addClass(
            "txt-intro-instrucciones"
          );

          // Funcionalidad de los accordeones de filtro
          jQuery(".filter-title").click(function () {
            let target = jQuery(this).data("target");
            jQuery(target).slideToggle();
            jQuery(this).toggleClass("open");
          });

          jQuery(document).on("input", 'input.large[type="tel"]', function () {
            // Expresión regular: Solo permite números y los caracteres ". # +"
            var valor = jQuery(this).val();
            var valorFiltrado = valor.replace(/[^0-9.#+]/g, ""); // Elimina cualquier otro carácter

            // Limita a un máximo de 10 caracteres
            if (valorFiltrado.length > 10) {
              valorFiltrado = valorFiltrado.substring(0, 10);
            }

            // Asigna el valor filtrado al campo
            jQuery(this).val(valorFiltrado);
          });


          jQuery(".btnOpenMegaMenuProductos").click(function(){
            jQuery('.box-megamenu-productos').addClass("box-active");
          });

          jQuery(".btnCloseMegaMenuProductos").click(function(){
            jQuery('.box-megamenu-productos').removeClass("box-active");
          });

          
          jQuery(".btnOpenMenuGiros").click(function(){
            jQuery('.box-megamenu-giros').toggleClass("box-active");  
          });

          jQuery(".btnCloseMegaMenuGiros").click(function(){
            jQuery('.box-megamenu-giros').removeClass("box-active");  
          });

//AOG Mensajes con alerta y boton de cierre
//
//
jQuery(".woocommerce-notices-wrapper").prepend( "<span class='msgWooClose'><i class='bi bi-x-circle-fill'></i></span>" );
	jQuery( ".msgWooClose" ).on( "click", function() {
		jQuery(this).parent( ".woocommerce-notices-wrapper" ).fadeOut();
	});
	
	jQuery(".woocommerce-message").prepend( "<span class='msgWooClose'><i class='bi bi-x-circle-fill'></i></span>" );
	jQuery( ".msgWooClose" ).on( "click", function() {
		jQuery(this).parent( ".woocommerce-message" ).fadeOut();
	});
	
	
		jQuery(".woocommerce-error").prepend( "<span class='msgWooClose'><i class='bi bi-x-circle-fill'></i></span>" );
	jQuery( ".msgWooClose" ).on( "click", function() {
		jQuery(this).parent( ".woocommerce-error" ).fadeOut();
	});
	
//AOG 28 de enero 
	jQuery(document).on('ajaxComplete', function() {
		// Agregar el botón de cierre dinámicamente cuando el mensaje de error se genera
		jQuery(".woocommerce-error").each(function() {
			// Verifica si el botón ya existe para no duplicarlo
			if (!jQuery(this).find('.msgWooClose').length) {
				jQuery(this).prepend("<span class='msgWooClose'><i class='bi bi-x-circle-fill'></i></span>");
			}
		});
	});

	// Delegar el evento de clic al botón de cierre
	jQuery(document).on("click", ".msgWooClose", function() {
		jQuery(this).parent(".woocommerce-error").fadeOut();
	});			
			
  //AOG Mensajes con alerta y boton de cierre        

          
          jQuery(function($){
            $('#btnBuscadorTienda').on('click', function(e){
              e.preventDefault();
              
              $('.buscador-contenido').slideToggle(300);

              $('.icon-down').toggleClass('giro');
            });
          });


          // Animacion tipo tooltip
          // --- Desktop (hover real) ---
          $("#showInfoPeso").on("mouseenter", function() {
            $(".cm-peso-tooltip").addClass("isActive");
          });
          $("#showInfoPeso").on("mouseleave", function() {
            $(".cm-peso-tooltip").removeClass("isActive");
          });

          // --- Mobile (tap toggle) ---
          $("#showInfoPeso").on("touchstart click", function(e) {
            // evita que se dispare doble en algunos navegadores
            e.preventDefault();
            $(".cm-peso-tooltip").toggleClass("isActive");
          });


          





          // Funcionalidad de las FAQS
        jQuery(function () {
          var $wrapper = jQuery('.wrapper-faqs');
          if (!$wrapper.length) return;

          var $items = $wrapper.find('.faq-item');

          var abrirPrimero = ($wrapper.data('abrir') === 1 || $wrapper.data('abrir') === '1');

          $items.each(function (i) {
            var $item   = jQuery(this);
            var $btn    = $item.find('.faq-btn').first();
            var $panel  = $item.find('.faq-contenido').first();

            var btnId   = 'faq-btn-' + i;
            var panelId = 'faq-panel-' + i;

            $btn.attr({
              id: btnId,
              'aria-controls': panelId,
              'aria-expanded': 'false'
            });

            $panel
              .attr({
                id: panelId,
                role: 'region',
                'aria-labelledby': btnId
              })
              .hide(); // todos cerrados al inicio
          });

          // Si se configuró abrirPrimero = true
          if (abrirPrimero && $items.length) {
            var $first = $items.first();
            $first.addClass('is-open');
            $first.find('.faq-btn').attr('aria-expanded', 'true');
            $first.find('.faq-contenido').show();
          }

          
          $wrapper.on('click', '.faq-btn', function (e) {
            e.preventDefault();

            var $btn   = jQuery(this);
            var $item  = $btn.closest('.faq-item');
            var $panel = $item.find('.faq-contenido');

            if ($item.hasClass('is-open')) {
              // Cerrar el mismo
              $item.removeClass('is-open');
              $btn.attr('aria-expanded', 'false');
              $panel.stop(true, true).slideUp(220);
            } else {
              // Cerrar cualquier otro abierto
              $items.filter('.is-open').each(function () {
                var $openItem = jQuery(this);
                $openItem.removeClass('is-open');
                $openItem.find('.faq-btn').attr('aria-expanded', 'false');
                $openItem.find('.faq-contenido').stop(true, true).slideUp(220);
              });

              // Abrir el actual
              $item.addClass('is-open');
              $btn.attr('aria-expanded', 'true');
              $panel.stop(true, true).slideDown(220);
            }
          });

          // Accesibilidad: activar con Enter o Space
          $wrapper.on('keydown', '.faq-btn', function (e) {
            if (e.key === ' ' || e.key === 'Enter') {
              e.preventDefault();
              jQuery(this).trigger('click');
            }
          });
        });


}); //ON ready

        jQuery(function($){

          // cuando abres el mega menú
          jQuery('.btnOpenMenuGiros').on('click', function(e){
            e.preventDefault();

            // si el body ya tiene la clase de scroll
            if( jQuery('body').hasClass('isMenuScroll-activo') ){
              jQuery('.box-megamenu-giros').addClass('ajustarScroll');
            } else {
              jQuery('.box-megamenu-giros').removeClass('ajustarScroll');
            }           
          });

        });


        

        // On Page Load...

        var width = jQuery(window).width();

        if (width >= 1199) {
          jQuery(document).ready(function () {});
        } else {
        }

        if (width <= 991) {
          // Menu dropdown Menu mobile
          jQuery(document).ready(function () {
            jQuery(".site__menu-principal ul.sub-menu")
              .parent()
              .children("a")
              .addClass("par-menu-a");
            jQuery('<span class="movileMenuShow"></span>').insertAfter(
              ".par-menu-a"
            );
            jQuery(".movileMenuShow").click(function () {
              //jQuery(".sub-menu").not(jQuery(this).next()).slideUp();

              jQuery(this).toggleClass("meArrowU");
              //jQuery('.movileMenuShow').removeClass('meArrowU');
              jQuery(this).next("ul.sub-menu").slideToggle();
            });

            jQuery(".footer__menu-title").on("click", function () {
              jQuery(this).next(".site__menu-footer").slideToggle();
              jQuery(this).toggleClass("Active");
            });
          });
        } else {
        }

        if (width >= 767) {
        } else {
        }
      },
      {},
    ],
  },
  {},
  [1]
);
