(function ($) {
    const { show_in_popup: displayMode, force_to_select: forceSelection } = multi_inventory_popup;

    // Manejador de clic para el botón
    $(".set-def-store-popup-btn").on('click', function (e) {
        e.preventDefault();
        handleDisplayLogic(displayMode);
    });

    // Configuración del popup
    $(".set-def-store-popup-btn").magnificPopup({
        type: "inline",
        fixedContentPos: false,
        fixedBgPos: true,
        overflowY: "auto",
        closeBtnInside: true,
        closeOnBgClick: false,
        enableEscapeKey: false,
        preloader: false,
        midClick: true,
        removalDelay: 300,

        callbacks: {
            open: function () {

                const modal = document.querySelector('.mfp-wrap.mfp-close-btn-in.mfp-auto-cursor');
                if (modal) {
                    modal.style.top = '0px'
                    modal.style.position = 'fixed';
                    modal.style.transition = 'all 0.3s ease';
                }
            }
        }
    });

    // Lógica de selección forzada
    const selectedLocation = getCookie("wcmlim_selected_location");
    if (forceSelection === "on" && (!selectedLocation || selectedLocation === "-1")) {
        $(".mfp-close").hide();
        $(".set-def-store-popup-btn").trigger('click');
    }

    // Funciones auxiliares
    function handleDisplayLogic(mode) {
        const $store = $("#set-def-store");

        switch (mode) {
            case "select":
                $store.find(".rlist_location, .postcode-checker").hide();
                if (!$store.find("#wcmlim-change-lc-select").is(":visible")) {
                    $store.find("#wcmlim-change-lc-select")
                        .removeAttr("style")
                        .css("display", "block");
                }
                break;

            case "input":
                $store.find(".rlist_location, .wcmlim_sel_location").hide();
                $store.find(".postcode-checker").show();
                break;

            case "list":
               // console.log("Displaying location list");
                $store.find(".postcode-checker, #wcmlim-change-lc-select").hide();
                $store.find(".rlist_location").show();
                break;
        }
    }

    function getCookie(name) {
        const cookieString = `; ${document.cookie}`;
        const index = cookieString.indexOf(`; ${name}=`);
        if (index === -1) return null;

        const start = index + name.length + 3;
        let end = cookieString.indexOf(";", start);
        end = end === -1 ? cookieString.length : end;

        return decodeURIComponent(cookieString.substring(start, end));
    }
})(jQuery);