wp.blocks.registerBlockType('wcmlim/wcmlim-popup-block', {
    title: "Location Popup Widget",
    icon: 'location',
    category: 'amultilocation',
    attributes: {
        companyName: { type: "string"}
    },
    
    edit: function(props){
        return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("a", {
            id: "set-def-store-popup-btn",
            href: "#set-def-store",
          }, "Pune"));
          
    },
    save: function(props){
        const { RawHTML } = wp.element;
        return htmlToElem("[wcmlim_locations_popup]");
       


    }

})