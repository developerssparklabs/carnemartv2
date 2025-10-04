jQuery( document ).ready( ( $ ) =>
{
    $.noConflict();
    let lat;
    let lng;
    const { ajaxurl } = multi_inventory;
    const autoDetect = multi_inventory.autodetect;
    const { enable_price } = multi_inventory;
    const restricted = multi_inventory.user_specific_location;
    const showLocationInRestricted = multi_inventory.show_location_selection;
    const { instock } = multi_inventory;
    const { soldout } = multi_inventory;
    const stock_format = multi_inventory.stock_format;
    const { widget_select_type } = multi_inventory;
    const nextloc = multi_inventory.nxtloc;
    var store_on_map_arr = multi_inventory.store_on_map_arr;
    var default_zoom = multi_inventory.default_zoom;
    var setting_loc_dis_unit = multi_inventory.setting_loc_dis_unit;
    var listmode = multi_inventory.optiontype_loc;
    var sc_listmode = multi_inventory.scoptiontype_loc;
    var detailadd = multi_inventory.fulladd;
    var listformat = multi_inventory.viewformat;
    var wchideoosproduct = multi_inventory.wchideoosproduct;
    var NextClosestinStock = multi_inventory.NextClosestinStock;
    var isdefault = multi_inventory.isdefault;
    const { isClearCart } = multi_inventory;
    const { isLocationsGroup } = multi_inventory;
     $( ".next_closest_location_detail" ).html( "" );
     $( ".Wcmlim_accept_btn" ).remove();
    $( ".rselect_location" ).on( "change", function ()
    {
        $('input[class="nextAcceptLoc"]').each(function(){
        $( ".Wcmlim_accept_btn" ).remove();
      });

        const sLValue = $( "#select_location" ).find( "option:selected" ).val();
        const stockQt = $( "#select_location" ).find( "option:selected" ).attr( "data-lc-qty" );
        if ( $( '.variation_id' ).length )
        {
          var product_id = '';
          var variation_id = $( "input.variation_id" ).val();
        }
        else
        {
          var product_id = $( ".single_add_to_cart_button" ).val();
          var variation_id = '';
        }
      
        if (stockQt<= 0) {
            $.ajax( {
                url: ajaxurl,
                type: "post",
                data: {
                  product_id: product_id,
                  variation_id: variation_id,
                  selectedLocationId: sLValue,
                  action: "wcmlim_closest_instock_location",
                },
                dataType: "json",
                success ( response )
                {
                   
                  if ( $.trim( response.status ) == "true" )
                  {
                    if ( nextloc == "on" )
                      {
                        $( ".postcode-location-distance" ).show();
                          $( ".postcode-location-distance" ).html(
                            `<i class="fa fa-map-marker-alt"></i>` +
                            response.loc_dis_unit
                          );

                        $.each( [ "1", "2", "3","4","5" ], function(){
                            if ( $( ".nextAcceptLoc" ).length )
                        {
                            $( ".Wcmlim_accept_btn" ).remove();
                        
                        }
                          });
            
                        $( ".next_closest_location_detail" ).html( "" );
                        $( ".next_closest_location_detail" ).show();
                      
                        $( "#load" ).hide();
                        $(
                          `<button id="" class="Wcmlim_accept_btn"><i class="fa fa-check"></i>Accept</button><input type="hidden" class="nextAcceptLoc" value="${ response.secNearLocKey }" />`
                        ).appendTo( ".Wcmlim_nextloc_label" );
                        $(
                          `<strong>` + NextClosestinStock +
                          `: <br/> ` +response.loc_address + ` <span class="next_km">(` + response.loc_dis_unit + `)</span></strong>`
                        ).appendTo( ".next_closest_location_detail" );

                    
                      }
                }
             }
              });
        }

    } );


});
