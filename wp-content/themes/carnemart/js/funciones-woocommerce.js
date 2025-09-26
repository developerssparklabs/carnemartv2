

//  Manejo de boton de añadir al carrito -------------------------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
  // Manejo del botón "Añadir al carrito"
  document.querySelectorAll('.add-to-cart').forEach(button => {
    // button.addEventListener('click', function (event) {
    //   event.preventDefault();
    //   const productId = this.getAttribute('data-product-id');
    //   const quantityInput = this.closest('.quantity-wrapper')?.querySelector('.quantity-input');
    //   const quantity = quantityInput ? parseInt(quantityInput.value) : 1;

    //   if (!productId || isNaN(quantity) || quantity < 1) {
    //     return;
    //   }

    //   // Enviar solicitud AJAX
    //   const ajaxUrl = `${window.location.origin}/lanaval/?wc-ajax=add_to_cart`;

    //   fetch(ajaxUrl, {
    //     method: 'POST',
    //     headers: {
    //       'Content-Type': 'application/x-www-form-urlencoded',
    //     },
    //     body: `product_id=${productId}&quantity=${quantity}`,
    //   })
    //     .then(response => {
    //       if (!response.ok) {
    //         throw new Error();
    //       }
    //       return response.json();
    //     })
    //     .then(data => {
    //       if (data.fragments) {
    //         actualizarCarritoLateral(data.fragments);
    //       }
    //     });
    // });
  });

  // Manejo de los botones "+" y "-" en el input
  document.querySelectorAll('.quantity-wrapper').forEach(wrapper => {
    const input = wrapper.querySelector('.quantity-input');
    const decreaseButton = wrapper.querySelector('.quantity-decrease');
    const increaseButton = wrapper.querySelector('.quantity-increase');
    const min = parseInt(input.getAttribute('min')) || 1;
    const step = parseInt(input.getAttribute('step')) || 1;

    if (decreaseButton) {
      decreaseButton.addEventListener('click', function () {
        const currentValue = parseInt(input.value) || 1;
        if (currentValue > min) {
          input.value = currentValue - step;
          //input.setAttribute('data-cantidad-carrito', parseFloat(input.value) || 0);
        }
      });
    }

    if (increaseButton) {
      increaseButton.addEventListener('click', function () {
        const currentValue = parseInt(input.value) || 1;
        input.value = currentValue + step;
        //  input.setAttribute('data-cantidad-carrito', parseFloat(input.value) || 0);
      });
    }
  });

  // Función para actualizar el carrito lateral con los fragments
  function actualizarCarritoLateral(fragments) {
    for (const selector in fragments) {
      const element = document.querySelector(selector);
      if (element) {
        element.outerHTML = fragments[selector];
      }
    }
  }
});




//   Función para actualizar manualmente el contador del carrito ----------------------------------------------------------

document.addEventListener('DOMContentLoaded', function () {
  /**
   * Función para actualizar manualmente el contador del carrito
   */
  function actualizarContador(cantidadARestar) {
    const contador = document.querySelector('.cart-contents-count');
    if (contador) {
      const cantidadActual = parseInt(contador.textContent.trim()) || 0;
      const nuevaCantidad = cantidadActual - cantidadARestar;
      contador.textContent = nuevaCantidad >= 0 ? nuevaCantidad : 0;
    }
  }

  /**
   * Función para actualizar el monto total del carrito en el sidebar
   */
  async function actualizarMontoTotal() {
    const totalElement = document.querySelector('.side__sidebar-carrito-costo-total .woocommerce-Price-amount.amount');
    if (totalElement) {
      try {
        const response = await fetch('/lanaval/wp-admin/admin-ajax.php?action=get_cart_total', {
          method: 'GET',
        });
        const data = await response.json();

        if (data.success && data.data.total) {
          totalElement.innerHTML = data.data.total;
        }
      } catch (error) {
        // No mostramos errores para mantener limpio
      }
    }
  }

  /**
   * Función para eliminar un producto del carrito desde el sidebar
   */
  async function eliminarProductoSidebar(target) {
    const cartItemKey = target.dataset.cartItemKey;

    if (!cartItemKey) {
      return;
    }

    try {
      const cantidadProducto = parseInt(
        target.closest('.site__sidebar-carrito-item')
          ?.querySelector('.site__sidebar-carrito-precios')
          ?.textContent.match(/x\s(\d+)/)?.[1] || 1
      );

      const response = await fetch('/lanaval/wp-admin/admin-ajax.php', {
        method: 'POST',
        headers: {
          'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: `action=remove_cart_item&cart_item_key=${cartItemKey}`,
      });

      const data = await response.json();

      if (data.success) {
        const item = target.closest('.site__sidebar-carrito-item');
        if (item) {
          item.remove();
        }

        actualizarContador(cantidadProducto);
        actualizarMontoTotal();
      }
    } catch (error) {
      // No mostramos errores para mantener limpio
    }
  }

  // Detectar clic en los botones de eliminar producto en el sidebar
  document.body.addEventListener('click', function (event) {
    const eliminarSidebar = event.target.closest('.eliminar-producto');
    if (eliminarSidebar) {
      event.preventDefault();
      eliminarProductoSidebar(eliminarSidebar);
    }
  });

  // Detectar clic en el botón para cerrar el sidebar
  document.body.addEventListener('click', function (event) {
    const cerrarSidebarBtn = event.target.closest('#ctaCloseBarCarrito');
    if (cerrarSidebarBtn) {
      const sidebar = document.getElementById('barraCarrito');
      if (sidebar) {
        sidebar.classList.remove('sideBarActiva');
      }
    }
  });
});






//   Función para mostrar el mensaje flotante 
document.addEventListener("DOMContentLoaded", function () {
  /**
   * Función para mostrar el mensaje flotante
   * @param {string} mensaje - El mensaje que se desea mostrar
   */
  function mostrarMensajeFlotante(mensaje) {
    const mensajeElemento = document.createElement("div");
    mensajeElemento.className = "mensaje-flotante";
    mensajeElemento.innerHTML = mensaje;

    // Estilos en línea para el mensaje flotante
    mensajeElemento.style.position = "fixed";
    mensajeElemento.style.bottom = "20px";
    mensajeElemento.style.right = "20px";
    mensajeElemento.style.backgroundColor = "#19a500";
    mensajeElemento.style.color = "white";
    mensajeElemento.style.padding = "10px 30px";
    mensajeElemento.style.borderRadius = "300px";
    mensajeElemento.style.boxShadow = "0 2px 10px rgba(0, 0, 0, 0.2)";
    mensajeElemento.style.fontSize = "18px";
    mensajeElemento.style.zIndex = "1000";
    mensajeElemento.style.opacity = "1";
    mensajeElemento.style.transition = "opacity 0.5s ease, transform 0.5s ease";

    document.body.appendChild(mensajeElemento);

    setTimeout(() => {
      mensajeElemento.style.opacity = "0";
      mensajeElemento.style.transform = "translateY(-20px)";
      mensajeElemento.addEventListener("transitionend", () => {
        mensajeElemento.remove();
      });
    }, 2000); // 2 segundos
  }

  // Detectar clic en cualquier botón de añadir al carrito
  document.body.addEventListener("click", function (event) {
    // Detectar el botón estándar o personalizado
    const botonAñadir = event.target.closest(".add-to-cart, .add-to-cart-button");
    if (botonAñadir) {
      // Mostrar el mensaje genérico
      mostrarMensajeFlotante("<i class='bi bi-check-circle-fill'></i> Producto añadido al carrito");
    }
  });
});