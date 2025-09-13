(function(){
  var mq = window.matchMedia("(max-width: 992px)");

  // Helpers de animación (sin jQuery)
  function slideUp(el, duration){
    duration = duration || 220;
    el.style.overflow = "hidden";
    el.style.visibility = "visible";
    el.style.height = el.scrollHeight + "px";
    el.offsetHeight; // reflow
    el.style.transition = "height " + duration + "ms ease";
    el.style.height = "0";
    setTimeout(function(){
      el.style.removeProperty("transition");
      el.style.visibility = "hidden";
    }, duration);
  }
function slideDown(el, duration){
  duration = duration || 220;
  el.style.overflow = "hidden";
  el.style.visibility = "visible";
  // partimos de 0 para animar
  el.style.height = "0";
  el.offsetHeight; // reflow
  var h = el.scrollHeight;
  el.style.transition = "height " + duration + "ms ease";
  el.style.height = h + "px";
  setTimeout(function(){
    el.style.removeProperty("transition");
    el.style.height = "auto";     // <-- clave: mantener abierto
  }, duration);
}


  function setMobileState(root){
    root.querySelectorAll(".wc-cats-menu__group").forEach(function(g){
      var btn  = g.querySelector(".wc-cats-menu__acc-toggle");
      var list = g.querySelector(".wc-cats-menu__list");
      if(!btn || !list) return;
      btn.style.display = "inline-flex";
      var open = g.classList.contains("is-open");
      btn.setAttribute("aria-expanded", open ? "true" : "false");
      list.style.height     = open ? list.scrollHeight + "px" : "0";
      list.style.visibility = open ? "visible" : "hidden";
      list.setAttribute("aria-hidden", open ? "false" : "true");
    });
  }

  function setDesktopState(root){
    root.querySelectorAll(".wc-cats-menu__group").forEach(function(g){
      var btn  = g.querySelector(".wc-cats-menu__acc-toggle");
      var list = g.querySelector(".wc-cats-menu__list");
      if(!btn || !list) return;
      btn.style.display = "none";
      btn.setAttribute("aria-expanded","false");
      g.classList.remove("is-open");
      list.style.removeProperty("height");
      list.style.removeProperty("visibility");
      list.style.removeProperty("overflow");
      list.setAttribute("aria-hidden","false");
    });
  }

  function init(){
    document.querySelectorAll(".wc-cats-menu").forEach(function(root){
      if(mq.matches) setMobileState(root); else setDesktopState(root);
    });
  }

  // Toggle (solo móvil) + cerrar hermanos
  document.addEventListener("click", function(e){
    var btn = e.target.closest(".wc-cats-menu__acc-toggle");
    if(!btn || !mq.matches) return;

    var group = btn.closest(".wc-cats-menu__group");
    var root  = btn.closest(".wc-cats-menu");
    var list  = group && group.querySelector(".wc-cats-menu__list");
    if(!group || !list) return;

    var isOpen = group.classList.contains("is-open");

    // Cerrar otros
    root.querySelectorAll(".wc-cats-menu__group.is-open").forEach(function(other){
      if(other === group) return;
      var obtn  = other.querySelector(".wc-cats-menu__acc-toggle");
      var olist = other.querySelector(".wc-cats-menu__list");
      other.classList.remove("is-open");
      if(obtn)  obtn.setAttribute("aria-expanded","false");
      if(olist) { slideUp(olist,220); olist.setAttribute("aria-hidden","true"); }
    });

    // Toggle actual
    if(isOpen){
      group.classList.remove("is-open");
      btn.setAttribute("aria-expanded","false");
      slideUp(list,220);
      list.setAttribute("aria-hidden","true");
    } else {
      group.classList.add("is-open");
      btn.setAttribute("aria-expanded","true");
      slideDown(list,220);
      list.setAttribute("aria-hidden","false");
    }
  });

  // Inicial y cambios de breakpoint
  window.addEventListener("load", init);
  if(mq.addEventListener){ mq.addEventListener("change", init); }
  else { window.addEventListener("resize", init); } // fallback viejito
})();
