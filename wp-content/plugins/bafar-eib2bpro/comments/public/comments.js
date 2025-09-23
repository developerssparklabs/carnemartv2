jQuery(document).ready(function () {
    "use strict";

    detectHash(eiB2BProGlobal.admin_url + "comment.php?action=editcomment&c=HASH");

    jQuery('body').on('click', ".eib2bpro-Replies", function () {
        jQuery(this).hide();
        jQuery('.eib2bpro-Reply_' + jQuery(this).attr('data-id')).show();
    });

    jQuery('body').on('click', '.eib2bpro-AjaxButton', function (e) {
        e.stopPropagation();
        var t = jQuery('#item_' + jQuery(this).attr('data-id'));
        var dataid = jQuery(this).attr('data-id');

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'comments',
            do: jQuery(this).attr('data-do'),
            id: dataid,
            state: jQuery(this).attr('data-state'),
        }, function (r) {
            if (1 === r.status) {
                if ('untrash' === r.state || 'unspam' === r.state) {
                    jQuery('#eib2bpro-AjaxResponse_' + r.id).remove();
                } else if ('approve' === r.state) {
                    jQuery(".eib2bpro-CommentStatus > span", t).removeClass('badge-danger').addClass('badge-success').text(eiComments.approved);
                    jQuery(".eib2bpro-CommentStatusButton", t).text(eiComments.unapprove).removeClass('eib2bpro-CommentStatusButton_Unapprove').addClass('eib2bpro-CommentStatusButton_Approve').attr('data-state', 'unapprove');
                } else if ('unapprove' === r.state) {
                    jQuery(".eib2bpro-CommentStatus > span", t).removeClass('badge-secondary').addClass('badge-danger').text(eiComments.unapproved);
                    jQuery(".eib2bpro-CommentStatusButton", t).text(eiComments.approve).removeClass('eib2bpro-CommentStatusButton_Unapprove').addClass('eib2bpro-CommentStatusButton_Approve').attr('data-state', 'approve');
                } else {
                    t.append('<div id="eib2bpro-AjaxResponse_' + r.id + '" class="eib2bpro-AjaxResponse eib2bpro-AjaxResponse_Comments d-flex justify-content-center align-items-center">' + r.message + '</div>');
                }

                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

    });


    jQuery('body').on('click', ".eib2bpro-Bulk_Do", function () {
        var sList = "";

        jQuery('.eib2bpro-Checkbox').each(function () {

            var sThisVal = jQuery(this).attr('data-id');

            if (this.checked) {
                sList += (sList === "" ? sThisVal : "," + sThisVal);
            }
        });

        eiB2BProAjax();

        jQuery.post(eiB2BProGlobal.ajax_url, {
            _wpnonce: jQuery('input[name=_wpnonce]').val(),
            _wp_http_referer: jQuery('input[name=_wp_http_referer]').val(),
            asnonce: eiB2BProGlobal.asnonce,
            action: "eib2bpro",
            app: 'comments',
            do: 'bulk',
            id: sList,
            state: jQuery(this).attr('data-do')
        }, function (r) {
            if (1 === r.status) {
                jQuery.each(r.id, function (i, item) {
                    var t = jQuery('#item_' + item.id);
                    if ('untrash' === item.state || 'unspam' === item.state) {
                        jQuery('#eib2bpro-AjaxResponse_' + item.id).remove();
                    } else if ('approve' === item.state) {
                        jQuery(".eib2bpro-CommentStatus > span", t).removeClass('badge-danger').addClass('badge-success').text(eiComments.approved);
                        jQuery(".eib2bpro-CommentStatusButton", t).text(eiComments.unapprove).attr('data-state', 'unapprove');
                    } else if ('unapprove' === item.state) {
                        jQuery(".eib2bpro-CommentStatus > span", t).removeClass('badge-success').addClass('badge-danger').text(eiComments.unapproved);
                        jQuery(".eib2bpro-CommentStatusButton", t).text(eiComments.approve).attr('data-state', 'approve');
                    } else if ('trash' === item.state) {
                        t.animate({opacity: 0.2}, 600, function () {
                            t.remove();
                        });
                    }
                });
                eiB2BProAjax('success', eiB2BProGlobal.i18n.done);
            } else {
                eiB2BProAjax('error', r.error);
            }
        }, 'json');

    });


    jQuery('body').on('click',".eib2bpro-Checkbox", function() {

        if ( 0 === jQuery(".eib2bpro-Checkbox:checked").length )  {
          jQuery(".eib2bpro-Bulk").hide();
        } else {
          jQuery(".eib2bpro-Bulk").show();
        }
        if (this.checked) {
          jQuery(this).parent().parent().addClass('eib2bpro-ItemChecked');
        } else {
          jQuery(this).parent().parent().removeClass('eib2bpro-ItemChecked');
        }
    
        if ( 0 < jQuery(".eib2bpro-Checkbox[data-state=s1]:checked").length ) {
          jQuery(".eib2bpro-Bulk_Unapprove").show();
        } else {
          jQuery(".eib2bpro-Bulk_Unapprove").hide();
        }
        if ( 0 < jQuery(".eib2bpro-Checkbox[data-state=s0]:checked").length ) {
          jQuery(".eib2bpro-Bulk_Approve").show();
        } else {
          jQuery(".eib2bpro-Bulk_Approve").hide();
        }
    
        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide');
      });
    
      jQuery(document).on('click', ".eib2bpro-CheckAll" , function() {
        if (this.checked) {
          jQuery(".eib2bpro-Standart").hide();
          jQuery(".eib2bpro-Bulk").show();
        } else {
          jQuery(".eib2bpro-Bulk").hide();
          jQuery(".eib2bpro-Standart").show();
        }
    
        jQuery(".eib2bpro-Checkbox").addClass('eib2bpro-NoHide').prop('checked', this.checked);
        jQuery(".eib2bpro-CheckAll").prop('checked', this.checked);
    
    
        if ( 0 < jQuery(".eib2bpro-Checkbox[data-state=publish]:checked").length ) {
          jQuery(".eib2bpro-Bulk_private").show();
        } else {
          jQuery(".eib2bpro-Bulk_private").hide();
        }
    
        if ( 0 < jQuery(".eib2bpro-Checkbox[data-state=private]:checked").length ) {
          jQuery(".eib2bpro-Bulk_publish").show();
        } else {
          jQuery(".eib2bpro-Bulk_publish").hide();
        }
      });
});
