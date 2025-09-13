jQuery(document).ready(($) =>
{
	jQuery("#per_page").on("change", function ()
	{
		var perPage = jQuery(this).val();
		if (perPage != "")
		{
			document.location.href = window.location.href+"&page=wcmlim-product-central" + perPage
		}
	});
	
	var counter = 0;
	jQuery(".SIMS_fields li.SIMS_options_li ").each(function ()
	{
		var status = jQuery(this).find('.switch input').attr('chech_status');
		if(status == 'Show'){
			counter = counter+1;
		}
	});
	if(counter == 0){
		jQuery(".tablenav.top").html('<h4 class="setting-disabled-message">Please go to the settings tab and enable the columns settings as per requirement.</h4>');
		
	}
	if (jQuery('.prodcentral_bulk_edit thead tr th').length > 0)
	{
		// $('.prodcentral_bulk_edit').DataTable({
		// 	"scrollY": true,
		// 	"scrollX": true,
		// 	//pagination
		// 	"paging": true,
		// 	"pagingType": "full_numbers",
		// 	"pageLength": 10,
		// 	stateSave: true,
		// 	// change show entry dropdown values
		// 	"lengthMenu": [
		// 		[5, 10, 25, 50, 75, 100, -1],
		// 		[5, 10, 25, 50, 75, 100, "All"]
		// 	],
		// 	// change label of show entries dropdown
		// 	"oLanguage": {
		// 		"sLengthMenu": "No. of items per page _MENU_ "
		// 	},
		// 	// default show entries to 10
		// 	"iDisplayLength": 10,
		// });
	}
	jQuery('ul.pctabs li').click(function ()
	{
		var tab_id = jQuery(this).attr('data-tabid');
		jQuery('ul.pctabs li').removeClass('current');
		jQuery('.tab-content').removeClass('current');
		jQuery(this).addClass('current');
		jQuery("#" + tab_id).addClass('current')
	});
	jQuery('.prodcentral_bulk_edit tbody').on('click', 'td.id .stckup_product_id', function ()
	{
		alertify.dismissAll();
		var pro_id = jQuery(this).attr('prdctID');
		document.location.href = "post.php?post=" + pro_id + "&action=edit"
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change', 'td.stock_status.column-stock_status .switch input', function ()
	{
		alertify.dismissAll();
		var id_check = jQuery(this).attr('id');
		if (jQuery(this).is(":checked"))
		{
			var stts = ' Yes';
			var stock = 'instock'
		}
		else
		{
			var stts = ' No';
			var stock = 'outofstock'
		}
		jQuery('.stckup_product_stock_status.' + id_check).html(stts);
		jQuery('td.stock_status.column-stock_status input.clickedit.' + id_check).val(stock);
		var inp_value = jQuery('td.stock_status.column-stock_status input.clickedit.' + id_check).val();
		var id = jQuery('td.stock_status.column-stock_status input.clickedit.' + id_check).attr("data-id");
		var pid = jQuery('td.stock_status.column-stock_status input.clickedit.' + id_check).attr("data-pid");
		var data_name = jQuery('td.stock_status.column-stock_status input.clickedit.' + id_check).attr("data-name");
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"data-pid": pid,
				"inp_value": inp_value,
				"data-name": data_name
			},
			success: function ()
			{
				alertify.success("Product Updated");
				location.reload();
			}
		});
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change', 'td.manage_stock.column-manage_stock .switch input', function ()
	{
		alertify.dismissAll();
		var id_check = jQuery(this).attr('id');
		if (jQuery(this).is(":checked"))
		{
			var stts = ' Yes';
			var stock = 1
		}
		else
		{
			var stts = ' No';
			var stock = null
		}
		jQuery('.product_stock_manage.' + id_check).html(stts);
		jQuery('td.manage_stock.column-manage_stock input.clickedit.' + id_check).val(stock);
		var inp_value = jQuery('td.manage_stock.column-manage_stock input.clickedit.' + id_check).val();
		var id = jQuery('td.manage_stock.column-manage_stock input.clickedit.' + id_check).attr("data-id");
		var data_name = jQuery('td.manage_stock.column-manage_stock input.clickedit.' + id_check).attr("data-name");
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": inp_value,
				"data-name": data_name
			},
			success: function ()
			{
				location.reload()
			}
		})
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change', 'td.status.column-status select.product_status', function ()
	{
		alertify.dismissAll();
		var Pid = jQuery(this).attr('Pid');
		var inp_value = this.value;
		var data_name = jQuery(this).attr("data-name");
		var id = jQuery('form#product-edit-table input.' + Pid + '[data-name="' + data_name + '"]').attr("data-id");
		jQuery('form#product-edit-table input.' + Pid + '[data-name="' + data_name + '"]').val(this.value);
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": inp_value,
				"data-name": data_name
			},
			success: function (result)
			{
				//console.log(result);
				alertify.success("Product Updated");
				jQuery("select.product_status").hide();
				jQuery(".stckup_product_status").show();
				location.reload()
			}
		})
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change', 'td.backorders.column-backorders select.bulk_product_backorder_edit', function ()
	{
		alertify.dismissAll();
		var Pid = jQuery(this).attr('Pid');
		var inp_value = this.value;
		var data_name = jQuery(this).attr("data-name");
		var id = jQuery('form#product-edit-table input.' + Pid + '[data-name="' + data_name + '"]').attr("data-id");
		jQuery('form#product-edit-table input.' + Pid + '[data-name="' + data_name + '"]').val(this.value);
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": inp_value,
				"data-name": data_name
			},
			success: function (result)
			{
				alertify.success("Product Updated ");
				jQuery("select.bulk_product_backorder_edit").hide();
				jQuery(".stckup_product_backorders").show();
				location.reload()
			}
		})
	});
	jQuery('.prodcentral_bulk_edit tbody').on('click', 'label.lbledit', function ()
	{
		alertify.dismissAll();
		jQuery(this).hide();
		jQuery(this).next().show().focus()
	});
	jQuery('.prodcentral_bulk_edit tbody').on('focusout', '.clickedit', function ()
	{
		var input = jQuery(this),
			label = input && input.prev();
		label.text(input.val() === "" ? label.text() : input.val());
		input.hide();
		label.show()
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change', '.clickedit', function ()
	{
		alertify.dismissAll();
		jQuery(".clickedit").hide().focusout(endEdit).keyup(function (e)
		{
			endEdit(e);
			return !1
		})
	});
	jQuery('.prodcentral_bulk_edit tbody').on('change focusout', 'td.column-stock_at_location input', function ()
	{
		var input = jQuery(this),
			label = input && input.prev();
		label.text(input.val() === "" ? label.text() : input.val());
		var inp_value = jQuery(this).val();
		var id = jQuery(this).attr("data-id");
		var data_name = jQuery(this).attr("data-name");
		var data_location = jQuery(this).attr("data-location");
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": inp_value,
				"data-name": data_name,
				"data-location": data_location
			},
			success: function (data, status, xhr)
			{
				input.hide();
				label.show();
				alertify.success("Product Updated ");
				location.reload()
			},
			error: function (jqXhr, textStatus, errorMessage)
			{
				input.hide();
				label.show();
				alertify.success("Data not updated");
				location.reload()
			}
		})
	});

	function endEdit(e)
	{
		alertify.dismissAll();
		var input = jQuery(e.target),
			label = input && input.prev();
		label.text(input.val() === "" ? label.text() : input.val());
		var inp_value = input.val();
		var id = jQuery(this).attr("data-id");
		var data_name = jQuery(this).attr("data-name");
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": inp_value,
				"data-name": data_name
			},
			success: function (response)
			{
				var data = JSON.parse(response);
				if(data == 'false'){
					alertify.error("Sale price should be less than regular price");
				}else{
					alertify.success("Product Updated");
				}
				input.hide();
				label.show();
				// alertify.success("Product Updated ");
				location.reload()
			}
		});
	}
	jQuery('#Submit_ProdCentral_Control').click(function ()
	{
		var arr = [];
		var i = 0;
		jQuery(".SIMS_fields li.SIMS_options_li ").each(function ()
		{
			var id = jQuery(this).find('.switch input').attr('id');
			var chech_status = jQuery(this).find('.switch input').attr('chech_status');
			arr[i++] = id + "=" + chech_status
		});
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_update_product_central",
				arr: arr,
			},
			success: function (result)
			{
				alertify.alert("<div id='pophead' style='text-align: center;'><span class='dashicons dashicons-yes-alt'></span><h2>Columns Settings!</h2><h4>Updated Control Successfully<h4><div>", function ()
				{
					location.reload()
				})
			}
		})
	});
	jQuery('#Enable_ProdCentral_Control').click(function ()
    {
			if (jQuery(this).is(":checked"))
			{
				jQuery(".SIMS_fields li.SIMS_options_li ").each(function ()
				{
				jQuery(this).find('.switch input').attr('chech_status', 'Show');
				jQuery(this).find('.switch input').prop('checked', true);
			   });
			}
			else
			{
				jQuery(".SIMS_fields li.SIMS_options_li ").each(function ()
				{
				jQuery(this).find('.switch input').attr('chech_status', 'Hide');
				jQuery(this).find('.switch input').prop('checked', false);
			 });
			}
    });
	jQuery('#Disable_ProdCentral_Control').click(function ()
    {
			if (jQuery(this).is(":checked"))
			{
				jQuery(".SIMS_fields li.SIMS_options_li ").each(function ()
				{
				jQuery(this).find('.switch input').attr('chech_status', 'Hide');
				jQuery(this).find('.switch input').prop('checked', false);
			   });
			}
    });
	jQuery('form#Product_Central_Setting li.SIMS_options_li .switch input').change(function ()
	{
		if (jQuery(this).is(":checked"))
		{
			jQuery(this).attr('chech_status', 'Show')
		}
		else
		{
			jQuery(this).attr('chech_status', 'Hide')
		}
	});
	jQuery(".ewc-filter-cat").on("change", function ()
	{
		var catFilter = jQuery(this).val();
		if (catFilter != "")
		{
			document.location.href = window.location.href+"&page=wcmlim-product-central" + catFilter
		}
	});
	jQuery(".ewc-filter-type").on("change", function ()
	{
		var typeFilter = jQuery(this).val();
		if (typeFilter != "")
		{
			document.location.href = window.location.href+"&page=wcmlim-product-central" + typeFilter
		}
	});
	//on enter press in search box 
	jQuery(".wcmlim_lst_search").keypress(function (e)
	{
		
		var searchFilter = jQuery(this).val();
		if (e.which == 13)
		{
			
			if (searchFilter != "")
			{
				
				document.location.href = window.location.href+"&page=wcmlim-product-central&s=" + searchFilter
				e.preventDefault();
			}
		}
	});
	
	
	jQuery(".ewc-filter-stock-status").on("change", function ()
	{
		var stockStatusFilter = jQuery(this).val();
		if (stockStatusFilter != "")
		{
			document.location.href = window.location.href+"&page=wcmlim-product-central" + stockStatusFilter
		}
	});

	jQuery('.multipleSelection .selectBox').click(function ()
	{
		var pro_id = jQuery(this).attr('prdctID');
		$(this).next('.multipleSelection .checkBoxes.check_bx_' + pro_id).slideToggle("slow")
	});
	jQuery('.prodcentral_bulk_edit td.column-categories label.stckup_product_category').click(function ()
	{
		var pro_id = jQuery(this).attr('prdctID');
		jQuery('.multipleSelection').hide();
		$(this).next('td.column-categories .multipleSelection.check_cat_' + pro_id).toggle()
	});
	jQuery('.prodcentral_bulk_edit td.column-tags label.stckup_product_tag').click(function ()
	{
		var pro_id = jQuery(this).attr('prdctID');
		jQuery('.multipleSelection').hide();
		$(this).next('td.column-tags .multipleSelection.check_cat_' + pro_id).toggle()
	});
	jQuery('.prodcentral_bulk_edit label').click(function ()
	{
		var lbl_cls = jQuery(this).attr('lbl_cls');
		if ((lbl_cls != 'category') && (lbl_cls != 'tag'))
		{
			jQuery('.multipleSelection').hide()
		}
	});
	jQuery('.multipleSelection .checkBoxes input').click(function ()
	{
		var inp_value = jQuery(this).attr("cat_id");
		var id = jQuery(this).attr("data-id");
		var inpt_typ = jQuery(this).attr("inpt_typ");
		var data_name = jQuery(this).attr("data-name");
		var cats = [];
		var i = 0;
		jQuery('.checkBoxes.check_bx_' + id + ' label[lbl_cls="' + inpt_typ + '"]').each(function ()
		{
			if (jQuery(this).find('input').is(":checked"))
			{
				var inp_vals = jQuery(this).find('input').attr("cat_nm");
				cats[i++] = inp_vals
			}
		});
		jQuery.ajax(
		{
			type: "POST",
			url: multi_inventory.ajaxurl,
			data:
			{
				action: "wcmlim_product_updated",
				"data-id": id,
				"inp_value": cats,
				"data-name": data_name,
			}
		});
		alertify.success("Product Updated");
		var cat_s = cats.toString();
		jQuery('.' + data_name + '[prdctid="' + id + '"]').html(cat_s);
	//	console.log('.checkBoxes.check_bx_' + id + ' label[lbl_cls="' + inpt_typ + '"]')
	})

	$('#wcmlim-rest').click(function() {
		$('#cat-filter, #ewc-filter-type , #ewc-filter-stock-status').val('');
		// Set the default value of "Show entries" to 10
		// var dataTable = $('.prodcentral_bulk_edit').DataTable();
		dataTable.page.len(10).draw();
	
		// Trigger the change event for any other event listeners
	});
})
