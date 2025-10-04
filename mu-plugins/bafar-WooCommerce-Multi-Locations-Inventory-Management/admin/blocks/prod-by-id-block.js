wp.blocks.registerBlockType('wcmlim/wcmlim-prod-by-id-block', {
    title: "Products By Location",
    icon: 'location',
    category: 'amultilocation',
    attributes: {
        attrLimit: { type: "string"},
        attrLoc_Id: { type: "string"},
        attrColoumn: { type: "string"},
    },
    

    edit: function(props){

        function updateLimit(event){props.setAttributes({attrLimit: event.target.value})}
        function updateLoc_Id(event){props.setAttributes({attrLoc_Id: event.target.value})}
        function updateColoumn(event){props.setAttributes({attrColoumn: event.target.value})}

        return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("h3", null, " Products By Location "), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", null, " Limit: "), " ", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
            placeholder: "Enter product limit to be shown",
            type: "number",
            value: props.attributes.attrLimit,
            onChange: updateLimit,
            required: true
          })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", null, " Location ID: "), " ", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
            placeholder: "Enter Location ID",
            type: "number",
            value: props.attributes.attrLoc_Id,
            onChange: updateLoc_Id,
            required: true
          })), /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("label", null, " Columns: "), " ", /*#__PURE__*/React.createElement("br", null), /*#__PURE__*/React.createElement("input", {
            placeholder: "Grid Column Count",
            type: "number",
            value: props.attributes.attrColoumn,
            onChange: updateColoumn,
            required: true
          })));
          
    },
    save: function(props){
        const { RawHTML } = wp.element;
        return htmlToElem("[products limit="+props.attributes.attrLimit+" location_id="+props.attributes.attrLoc_Id+" columns="+props.attributes.attrColoumn+" orderby='id' order='ASC']");    
        
    }

})