/* Author: 

*/// Allows you to use the $ shortcut.  Put all your code  inside this wrapper.
jQuery(document).ready(function(e){e("li.page_item_has_children").addClass("dropdown");e("li.page_item_has_children > a").addClass("dropdown-toggle");e("li.page_item_has_children > a").attr("data-toggle","dropdown");e("a.dropdown-toggle").append('<b class="caret"></b>');e("ul.children").addClass("dropdown-menu");e("ul.page-numbers").addClass("pagination");e("#jumbotron-standard .item:first-child, #jumbotron-cinematic .item:first-child").addClass("active");e("#jumbotron-standard li, #jumbotron-cinematic li").addClass("active");e(".nav-tabs > li:first-child, .tab-content .tab-pane:first-child").addClass("active");e("#layaway-info").popover("hide");e(".widget_product_categories select").addClass("form-control champagn-border product-filter-dropdown");e(".product-filter-dropdown").change(function(){location.href="?product_cat="+e(this).val()});e(".entry-content").on("change","#add_insurance",function(){checked=e(this).is(":checked");console.log("lasdfasdfasdf");location.href="?insure="+(checked?"1":"0")})});