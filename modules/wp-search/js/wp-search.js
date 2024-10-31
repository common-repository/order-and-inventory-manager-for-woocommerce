jQuery(function ($) {
    var selected_posttype = $("#wpsearchtype").val();
    var wpsearch = {
        init: function () {
            $("#wpsearchtype").on("change", this.change_post_type);
            $("#wpsearchfrm #wpsearchtext").on("keydown", function (event) {
                if (event.keyCode === $.ui.keyCode.TAB &&
                        $(this).autocomplete("instance").menu.active) {
                    event.preventDefault();
                }
            }).autocomplete({
                minLength: 1,
                source: function (name, response) {
                    $.ajax({
                        type: 'POST',
                        dataType: 'json',
                        url: search.ajaxurl,
                        data: 'action=get_searched_posts&post_type=' + selected_posttype + '&keyword=' + name.term,
                        success: function (data) {
                            response(data);
                            $("#wpsearchfrm #wpsearchtext").removeClass("ui-autocomplete-loading");
                        }
                    });
                },
                focus: function (event, ui) {
                    $(".ui-helper-hidden-accessible").hide();
                    event.preventDefault();
                },
                select: function (event, ui) {
                    console.log(ui);
                    $("#wpsearchfrm #wpsearchtext").val(ui.item.label);
                    window.location.href = ui.item.value;
                    return false;
                }
            });
        },
        change_post_type: function () {
            var post_type = $("#wpsearchtype").val();
            $("#wpsearchtype_hidden").val(post_type);
            var action = search.frm_action;
            $("#wpsearchfrm").attr('action', action + post_type);
            selected_posttype = post_type;
        }
    }
    wpsearch.init();
});