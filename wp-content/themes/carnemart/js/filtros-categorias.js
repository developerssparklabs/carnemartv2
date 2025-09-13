document.addEventListener("DOMContentLoaded", function () {
  const form = document.getElementById("filtros-categorias");

  if (form) {
    // Manejo del slider de precios
    const minPrice = document.getElementById("min-price");
    const maxPrice = document.getElementById("max-price");
    const minLabel = document.getElementById("min-price-label");
    const maxLabel = document.getElementById("max-price-label");
    const track = document.querySelector(".slider-track");

    function updateTrack() {
      const minVal = parseInt(minPrice.value);
      const maxVal = parseInt(maxPrice.value);

      // Asegurar que min no sea mayor que max - 500
      if (minVal > maxVal - 500) {
        minPrice.value = maxVal - 500;
      }
      // Asegurar que max no sea menor que min + 500
      if (maxVal < minVal + 500) {
        maxPrice.value = minVal + 500;
      }

      // Actualizar etiquetas
      minLabel.textContent = `$${parseInt(minPrice.value).toLocaleString()}`;
      maxLabel.textContent = `$${parseInt(maxPrice.value).toLocaleString()}`;

      // Actualizar visual del track
      const minPercent = ((minPrice.value - minPrice.min) / (minPrice.max - minPrice.min)) * 100;
      const maxPercent = ((maxPrice.value - maxPrice.min) / (maxPrice.max - maxPrice.min)) * 100;
      track.style.left = minPercent + "%";
      track.style.right = (100 - maxPercent) + "%";
    }

    // Eventos para actualizar el track
    minPrice.addEventListener("input", updateTrack);
    maxPrice.addEventListener("input", updateTrack);

    // Inicializar el track
    updateTrack();

    form.addEventListener("submit", function (e) {
      e.preventDefault();

      const formData = new FormData(form);
      const params = [];

      // Process categorias
      const categorias = formData.getAll("categorias[]");
      if (categorias.length) params.push(`categorias=${encodeURIComponent(categorias.join(","))}`);

      // Process tipos de vino
      const tiposVino = formData.getAll("tiposvino[]");
      if (tiposVino.length) params.push(`tiposvino=${encodeURIComponent(tiposVino.join(","))}`);

      // Process pais_filtro
      const paises = formData.getAll("pais_filtro[]");
      if (paises.length) params.push(`pais_filtro=${encodeURIComponent(paises.join(","))}`);

      // Process paladar
      const paladar = formData.getAll("paladar[]");
      if (paladar.length) params.push(`paladar=${encodeURIComponent(paladar.join(","))}`);

      // Process price range
      const minPriceVal = minPrice.value;
      const maxPriceVal = maxPrice.value;
      if (minPriceVal !== minPrice.min) params.push(`min_price=${minPriceVal}`);
      if (maxPriceVal !== maxPrice.max) params.push(`max_price=${maxPriceVal}`);

      // Get current URL path
      const currentPath = window.location.pathname;

      // Create the new URL with parameters
      const newUrl = currentPath + (params.length ? "?" + params.join("&") : "");

      // Navigate to the new URL
      window.location.href = newUrl;
    });
  }
});
