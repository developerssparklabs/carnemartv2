wp.blocks.registerBlockType('wcmlim/wcmlim-lflv-block', {
    title: "Location Finder List View",
    icon: 'location',
    category: 'amultilocation',
    attributes: {
        companyName: { type: "string"}
    },
    
    edit: function(props){
        return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("img", {
            src: "../wp-content/plugins/bafar-WooCommerce-Multi-Locations-Inventory-Management/admin/blocks/img/lflv-block.jpg"
          }));
          
    },
    save: function(props){
        const { RawHTML } = wp.element;
        return htmlToElem("[wcmlim_location_finder_list_view]");    

    }

})