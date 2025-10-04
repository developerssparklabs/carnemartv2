const htmlToElem = ( html ) => wp.element.RawHTML( { children: html } );

wp.blocks.registerBlockType('wcmlim/wcmlim-switch-block', {
    title: "Location Switch Widget",
    icon: 'location',
    category: 'amultilocation',
    attributes: {
        companyName: { type: "string"}
    },
    
    edit: function(props){
        return /*#__PURE__*/React.createElement("div", null, /*#__PURE__*/React.createElement("div", {
            class: "wcmlim-lc-switch"
          }, /*#__PURE__*/React.createElement("form", {
            id: "lc-switch-form",
            class: "inline_wcmlim_lc",
            method: "post"
          }, /*#__PURE__*/React.createElement("div", {
            class: "wcmlim_form_box"
          }, /*#__PURE__*/React.createElement("div", {
            class: "wcmlim_sel_location wcmlim_storeloc"
          }, /*#__PURE__*/React.createElement("p", {
            class: "wcmlim_change_lc_to"
          }, "Location:"), /*#__PURE__*/React.createElement("select", {
            name: "wcmlim_change_lc_to",
            class: "wcmlim-lc-select",
            id: "wcmlim-change-lc-select"
          }, /*#__PURE__*/React.createElement("option", {
            value: "-1"
          }, "Select"), /*#__PURE__*/React.createElement("option", {
            class: "wclimloc_akola",
            value: "0",
            "data-lc-address": "YWtvbGEgTWFoYXJhc2h0cmEgNDQ0MDA1IEluZGlh",
            "data-lc-term": "22"
          }, "Akola "), /*#__PURE__*/React.createElement("option", {
            class: "wclimloc_pune",
            value: "1",
            "data-lc-address": "UHVuZSBNYWhhcmFzaHRyYSA0MTEwMjEgSW5kaWE=",
            "data-lc-term": "23",
            selected: "selected"
          }, "Pune ")), /*#__PURE__*/React.createElement("div", {
            class: "er_location"
          }),/*#__PURE__*/
           /*#__PURE__*/React.createElement("div", {
            class: "rlist_location"
          }))))));
          
    },
    save: function(props){
        const { RawHTML } = wp.element;
        return htmlToElem("[wcmlim_locations_switch]");
       


    }

})