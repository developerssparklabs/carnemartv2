wp.blocks.registerBlockType('wcmlim/wcmlim-locinfo-block', {
    title: "Location Info",
    icon: 'location',
    category: 'amultilocation',
    attributes: {
        LocId: { type: "string"}
    },
    
    edit: function(props){

        function updateLocId(event){props.setAttributes({LocId: event.target.value})}
        return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", null, " Location ID: "), /*#__PURE__*/React.createElement("input", {
            type: "number",
            value: props.attributes.LocId,
            onChange: updateLocId,
            placeholder: "Enter Location ID Here",
            required: true
          }));
          
    },
    save: function(props){
        const { RawHTML } = wp.element;
        return htmlToElem("[wcmlim_location_info id="+props.attributes.LocId+"]");    

    }

})