jQuery(document).ready(function() {

    jQuery("#wpgmza_mlist_selection_1").click(function() {
        jQuery("#rb_wpgmza_mlist_selection_1").attr('checked', true);
        jQuery("#rb_wpgmza_mlist_selection_2").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_3").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_4").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_5").attr('checked', false);
        jQuery("#wpgmza_mlist_selection_1").addClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_2").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_3").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_4").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_5").removeClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_mlist_sel_text").text(wpgmaps_localize_strings["wpgm_mlist_sel_2"]);

    });

    jQuery("#wpgmza_mlist_selection_2").click(function() {
        jQuery("#rb_wpgmza_mlist_selection_1").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_2").attr('checked', true);
        jQuery("#rb_wpgmza_mlist_selection_3").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_4").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_5").attr('checked', false);
        jQuery("#wpgmza_mlist_selection_1").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_2").addClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_3").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_4").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_5").removeClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_mlist_sel_text").text(wpgmaps_localize_strings["wpgm_mlist_sel_4"]);

    });

    jQuery("#wpgmza_mlist_selection_3").click(function() {
        jQuery("#rb_wpgmza_mlist_selection_1").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_2").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_3").attr('checked', true);
        jQuery("#rb_wpgmza_mlist_selection_4").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_5").attr('checked', false);
        jQuery("#wpgmza_mlist_selection_1").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_2").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_3").addClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_4").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_5").removeClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_mlist_sel_text").text(wpgmaps_localize_strings["wpgm_mlist_sel_3"]);

    });


    jQuery("#wpgmza_mlist_selection_4").click(function() {
        jQuery("#rb_wpgmza_mlist_selection_1").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_2").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_3").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_4").attr('checked', true);
        jQuery("#rb_wpgmza_mlist_selection_5").attr('checked', false);
        jQuery("#wpgmza_mlist_selection_1").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_2").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_3").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_4").addClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_5").removeClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_mlist_sel_text").text(wpgmaps_localize_strings["wpgm_mlist_sel_5"]);

    });


    jQuery("#wpgmza_mlist_selection_5").click(function() {
        jQuery("#rb_wpgmza_mlist_selection_1").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_2").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_3").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_4").attr('checked', false);
        jQuery("#rb_wpgmza_mlist_selection_5").attr('checked', true);
        jQuery("#wpgmza_mlist_selection_1").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_2").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_3").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_4").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_mlist_selection_5").addClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_mlist_sel_text").text(wpgmaps_localize_strings["wpgm_mlist_sel_1"]);
    });


    jQuery("#wpgmza_iw_selection_1").click(function() {
        jQuery("#rb_wpgmza_iw_selection_1").attr('checked', true);
        jQuery("#rb_wpgmza_iw_selection_2").attr('checked', false);
        jQuery("#wpgmza_iw_selection_1").addClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_iw_selection_2").removeClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_iw_sel_text").text(wpgmaps_localize_strings["wpgm_iw_sel_1"]);
    });

    jQuery("#wpgmza_iw_selection_2").click(function() {
        jQuery("#rb_wpgmza_iw_selection_1").attr('checked', false);
        jQuery("#rb_wpgmza_iw_selection_2").attr('checked', true);
        jQuery("#wpgmza_iw_selection_1").removeClass("wpgmza_mlist_selection_activate");
        jQuery("#wpgmza_iw_selection_2").addClass("wpgmza_mlist_selection_activate");
        jQuery(".wpgmza_iw_sel_text").text(wpgmaps_localize_strings["wpgm_iw_sel_2"]);
    });


});