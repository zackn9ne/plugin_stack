jQuery(document).ready(function($) {
	if (!google_maps_objects.notinclude_maps_api && (typeof google == 'undefined' || typeof google.maps == 'undefined')) {
		var script = document.createElement("script");
		script.type = "text/javascript";
		var key = '';
		if (google_maps_objects.google_api_key)
			key = "&key="+google_maps_objects.google_api_key;
		script.src = "//maps.google.com/maps/api/js?v=3.14&libraries=places&sensor=false"+key+"&callback="+google_maps_callback.callback;
		document.body.appendChild(script);
	} else
		window[google_maps_callback.callback]();
	
	equalColumnsHeight = function() {
		$('.w2dc-listings-block.w2dc-listings-grid .w2dc-listing-bottom, .w2dc-listings-block.w2dc-listings-grid .w2dc-listing-bottom-nologo').each(function() {
			$(this).css('position', 'relative');
		});

		var currentTallest = 0,
		currentRowStart = 0,
		rowDivs = new Array(), $el, topPosition = 0;
		$('.w2dc-listings-block.w2dc-listings-grid article.w2dc-listing').each(function() {
			$el = $(this);
			topPostion = $el.position().top;
			if (currentRowStart != topPostion) {
				for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
					rowDivs[currentDiv].height(currentTallest);
				}
				rowDivs.length = 0;
				currentRowStart = topPostion;
				currentTallest = $el.height();
				rowDivs.push($el);
			} else {
				rowDivs.push($el);
				currentTallest = (currentTallest < $el.height()) ? ($el.height()) : (currentTallest);
			}
			for (currentDiv = 0 ; currentDiv < rowDivs.length ; currentDiv++) {
				rowDivs[currentDiv].height(currentTallest);
			}
		});
		
		$('.w2dc-listings-block.w2dc-listings-grid .w2dc-listing-bottom, .w2dc-listings-block.w2dc-listings-grid .w2dc-listing-bottom-nologo').each(function() {
			$(this).css('position', 'absolute');
		});
	}
	
	ajax_listings_loader_show = function(controller_hash, scroll_to_anchor) {
		if (scroll_to_anchor)
			$('html,body').animate({scrollTop: scroll_to_anchor.offset().top}, 'slow');
		
		var listings_block = $('#w2dc-controller-'+controller_hash);

		listings_block.addClass("w2dc-ajax-block-loading");
		var wheel_width = 90;
		var wheel_height = 90;
		leftPosition = (listings_block.width() / 2) - (wheel_width / 2);
		topPosition = (listings_block.height() / 2) - (wheel_height / 2);
		if (topPosition > 300) topPosition = 300;
	   	listings_block.append('<img class="listings_block_loader_'+controller_hash+'" width="'+wheel_width+'" height="'+wheel_height+'" alt="Updating listings" src="'+js_objects.ajax_map_loader_url+'" style="position: absolute; left: '+leftPosition+'px; top: '+topPosition+'px;">');
	   	listings_block.find(".w2dc-listings-block").fadeTo("fast", 0.3);
	}
	
	ajax_listings_loader_hide = function(controller_hash) {
		var listings_block = $('#w2dc-controller-'+controller_hash);

		listings_block.removeClass("w2dc-ajax-block-loading");
		listings_block.find(".w2dc-listings-block").fadeTo("fast", 1);
		$(".listings_block_loader_"+controller_hash).remove();
	}
	
	get_controller_args_array = function(hash) {
		if (typeof controller_args_array != 'undefined' && Object.keys(controller_args_array))
			for (var controller_hash in controller_args_array)
				if (controller_hash == hash)
					return controller_args_array[controller_hash];
	}

	get_map_markers_attrs_array = function(hash) {
		if (typeof map_markers_attrs_array != 'undefined' && Object.keys(map_markers_attrs_array))
			for (var i=0; i<map_markers_attrs_array.length; i++)
				if (hash == map_markers_attrs_array[i].map_id)
					return map_markers_attrs_array[i];
	}

	get_original_map_markers_attrs_array = function(hash) {
		if (typeof _map_markers_attrs_array != 'undefined' && Object.keys(_map_markers_attrs_array))
			for (var i=0; i<_map_markers_attrs_array.length; i++)
				if (hash == _map_markers_attrs_array[i].map_id)
					return _map_markers_attrs_array[i];
	}
	
	process_listings_ajax_responce = function(response_from_the_action_function, do_replace, remove_shapes, do_replace_markers) {
		var responce_hash = response_from_the_action_function.hash;
		if (response_from_the_action_function) {
			var listings_block = $('#w2dc-controller-'+responce_hash);
			if (do_replace)
				listings_block.replaceWith(response_from_the_action_function.html);
			else
				listings_block.find(".w2dc-listings-block-content").append(response_from_the_action_function.html);
			ajax_listings_loader_hide(responce_hash);
			if (response_from_the_action_function.map_markers && typeof maps[responce_hash] != 'undefined') {
				if (do_replace)
					clearOverlays(responce_hash, remove_shapes);
				if (typeof infoWindows[responce_hash] != 'undefined')
					infoWindows[responce_hash].close();
				var markers_array = response_from_the_action_function.map_markers;
				
				var enable_radius_cycle = 0;
				var enable_clusters = 0;
				var show_summary_button = 1;
				var show_readmore_button = 1;
				if (attrs_array = get_map_markers_attrs_array(responce_hash)) {
					var enable_radius_cycle = attrs_array.enable_radius_cycle;
					var enable_clusters = attrs_array.enable_clusters;
					var show_summary_button = attrs_array.show_summary_button;
					var show_readmore_button = attrs_array.show_readmore_button;
					var map_attrs = attrs_array.map_attrs;

					if (do_replace_markers)
						attrs_array.markers_array = eval(response_from_the_action_function.map_markers);
				}

		    	for (var j=0; j<markers_array.length; j++) {
	    			map_coords_1 = markers_array[j][1];
			    	map_coords_2 = markers_array[j][2];
			    	if ($.isNumeric(map_coords_1) && $.isNumeric(map_coords_2)) {
		    			point = new google.maps.LatLng(map_coords_1, map_coords_2);

		    			var location_obj = new glocation(markers_array[j][0], point, 
		    				markers_array[j][3],
		    				markers_array[j][5],
		    				markers_array[j][6],
		    				markers_array[j][7],
		    				markers_array[j][8],
		    				markers_array[j][9],
		    				markers_array[j][10],
		    				show_summary_button,
		    				show_readmore_button,
		    				responce_hash,
		    				false
			    		);
			    		var marker = location_obj.placeMarker(responce_hash);
			    		global_markers_array[responce_hash].push(marker);
			    		global_locations_array[responce_hash].push(location_obj);
			    	}
	    		}
		    	if (global_markers_array[responce_hash].length) {
		    		var bounds = new google.maps.LatLngBounds();
		    		for (var j=0; j<global_markers_array[responce_hash].length; j++) {
		    			bounds.extend(global_markers_array[responce_hash][j].position);
		    		}
		    		maps[responce_hash].fitBounds(bounds);
		    	}
		    	ajax_map_loader_hide(responce_hash);
		    	
		    	setClusters(enable_clusters, responce_hash, global_markers_array[responce_hash]);
		    	
		    	/*if (typeof drawCircles[responce_hash] != 'undefined')
					drawCircles[responce_hash].setMap(null);*/

		    	if (enable_radius_cycle && typeof response_from_the_action_function.radius_params != 'undefined') {
		    		var radius_params = response_from_the_action_function.radius_params;
					map_radius = parseFloat(radius_params.radius_value);
					if (radius_params.dimension == 'miles')
						map_radius *= 1.609344;
					map_coords_1 = radius_params.map_coords_1;
					map_coords_2 = radius_params.map_coords_2;

					if ($.isNumeric(map_coords_1) && $.isNumeric(map_coords_2)) {
						map_radius *= 1000; // we need radius exactly in meters
						drawCircles[responce_hash] = new google.maps.Circle({
					    	center: new google.maps.LatLng(map_coords_1, map_coords_2),
					        radius: map_radius,
					        strokeColor: "#FF0000",
					        strokeOpacity: 0.25,
					        strokeWeight: 1,
					        fillColor: "#FF0000",
					        fillOpacity: 0.1,
					        map: maps[responce_hash]
					    });
					}
				}
			}
			//window.history.pushState("", "", href);
		}
		ajax_listings_loader_hide(responce_hash);
		ajax_map_loader_hide(responce_hash);
		equalColumnsHeight();
	}

	load_ajax_initial_elements = function() {
		// We have to wait while Google Maps API will be completely loaded
		if (js_objects.ajax_initial_load) {
			if (typeof controller_args_array != 'undefined' && Object.keys(controller_args_array)) {
				for (var controller_hash in controller_args_array) {
					var post_params = controller_args_array[controller_hash];
					var ajax_params = {'action': 'w2dc_controller_request', 'hash': controller_hash};
					for (var attrname in ajax_params) { post_params[attrname] = ajax_params[attrname]; }
	
					if ((typeof post_params['with_map'] != 'undefined' && post_params['with_map']) || $("#w2dc-maps-canvas-"+controller_hash).length) {
						if (map_attrs_array = get_map_markers_attrs_array(controller_hash))
							if (typeof map_attrs_array.map_attrs.ajax_loading != "undefined" && map_attrs_array.map_attrs.ajax_loading == 1)
								continue;
							
						post_params['with_map'] = 1;
				   		ajax_map_loader_show(controller_hash);
					}
	
				   	ajax_listings_loader_show(controller_hash, false);
					$.post(
						js_objects.ajaxurl,
						post_params,
						function(response_from_the_action_function) {
							process_listings_ajax_responce(response_from_the_action_function, true, true, true);
							var responce_hash = response_from_the_action_function.hash;
							if (response_from_the_action_function.map_markers && typeof maps[responce_hash] != 'undefined')
								if (typeof _map_markers_attrs_array != 'undefined' && _map_markers_attrs_array.length) {
									for (var i=0; i<_map_markers_attrs_array.length; i++)
										if (responce_hash == _map_markers_attrs_array[i].map_id)
											_map_markers_attrs_array[i].markers_array = eval(response_from_the_action_function.map_markers);
								}
						},
						'json'
					);
				}
			}
		}
	}
	
	$('body').on('click', '.w2dc-show-more-button', function(e) {
		e.preventDefault();
		
		var controller_hash = $(this).data('controller-hash');
		if (listings_args_array = get_controller_args_array(controller_hash)) {
			var button = $(this);
			button.attr('disabled', 'disabled').prepend('<img src="'+js_objects.ajax_iloader_url+'" class="w2dc-ajax-iloader" /> ');
					
			var post_params = jQuery.extend({}, listings_args_array);
			if (typeof post_params.paged != 'undefined')
				var paged = parseInt(post_params.paged)+1;
			else
				var paged = 2;
			listings_args_array.paged = paged;

			var ajax_params = {'action': 'w2dc_controller_request', 'do_append': 1, 'paged': paged, 'hash': controller_hash};
			for (var attrname in ajax_params) { post_params[attrname] = ajax_params[attrname]; }

			if ($("#w2dc-maps-canvas-"+controller_hash).length) {
				if (map_attrs_array = get_map_markers_attrs_array(controller_hash))
					if ((typeof map_attrs_array.map_attrs.ajax_loading == 'undefined' || map_attrs_array.map_attrs.ajax_loading == 0)
					&&
					(typeof map_attrs_array.map_attrs.drawing_state == 'undefined' || map_attrs_array.map_attrs.drawing_state == 0)) {
						ajax_map_loader_show(controller_hash);
						post_params.with_map= 1;
					}
			}
					
			if (typeof infoWindows[controller_hash] != 'undefined')
				infoWindows[controller_hash].marker.setAnimation(null);

			$.post(
				js_objects.ajaxurl,
				post_params,
				completeAJAXShowMore(button),
				'json'
			);
		}
	});
	var completeAJAXShowMore = function(button) {
		return function(response_from_the_action_function) {
			process_listings_ajax_responce(response_from_the_action_function, false, false, false);
			button.removeAttr('disabled').find(".w2dc-ajax-iloader").remove();
			if (response_from_the_action_function.hide_show_more_listings_button)
				button.hide();
		}
	}
	
	if (js_objects.ajax_load) {
		$('body').on('click', '.w2dc-search-form input[type="submit"]', function(e) {
			var search_inputs = $(this).parents('.w2dc-search-form').serializeArray();
			var post_params = {};
			for (var attr in search_inputs) {
				// checkboxes search form values
				if (search_inputs[attr]['name'].indexOf('[]') != -1) {
					if (typeof post_params[search_inputs[attr]['name']] == 'undefined')
						post_params[search_inputs[attr]['name']] = [];
					post_params[search_inputs[attr]['name']].push(search_inputs[attr]['value']);
				} else
					post_params[search_inputs[attr]['name']] = search_inputs[attr]['value'];
			}

			var controller_hash = false;
			if (typeof controller_args_array != 'undefined' && Object.keys(controller_args_array)) {
				for (var _controller_hash in controller_args_array) {
					if ($("#w2dc-controller-"+_controller_hash).data("custom-home") == 1) {
						controller_hash = _controller_hash;
						post_params["custom_home"] = 1;
						post_params["controller"] = 'directory_controller';
						break;
					}
				}
			}
			if (!controller_hash) {
				$(".w2dc-maps-canvas").each(function() {
					if ($(this).data("custom-home") == 1) {
						controller_hash = $(this).data("shortcode-hash");
						post_params["custom_home"] = 1;
						post_params["controller"] = 'directory_controller';
						return false;
					}
				});
			}
			if (!controller_hash) {
				if (typeof post_params['hash'] != 'undefined' && post_params['hash'])
					controller_hash = post_params['hash'];
			}

			if (controller_hash) {
				e.preventDefault();
				var search_button_obj = $(this);
				search_button_obj.val(js_objects.ajax_loader_text).attr('disabled', 'disabled');
				
				post_params['hash'] = controller_hash;

				var ajax_params = {'action': 'w2dc_controller_request'};
				// collect needed params from listing block
				if (listings_args_array = get_controller_args_array(controller_hash)) {
					ajax_params.hide_order = listings_args_array.hide_order;
					ajax_params.hide_count = listings_args_array.hide_count;
					ajax_params.hide_paginator = listings_args_array.hide_paginator;
					ajax_params.show_views_switcher = listings_args_array.show_views_switcher;
					ajax_params.listings_view_type = listings_args_array.listings_view_type;
					ajax_params.listings_view_grid_columns = listings_args_array.listings_view_grid_columns;
					ajax_params.listing_thumb_width = listings_args_array.listing_thumb_width;
					ajax_params.wrap_logo_list_view = listings_args_array.wrap_logo_list_view;
					ajax_params.logo_animation_effect = listings_args_array.logo_animation_effect;
					ajax_params.perpage = listings_args_array.perpage;
					ajax_params.onepage = listings_args_array.onepage;
				}
				// collect needed params from map attributes
				if (typeof ajax_params.perpage == 'undefined') {
					if (map_attrs_array = get_map_markers_attrs_array(controller_hash))
						if (typeof map_attrs_array.map_attrs.num != 'undefined')
							ajax_params.perpage = map_attrs_array.map_attrs.num;
				}
				if (map_attrs_array = get_map_markers_attrs_array(controller_hash)) {
					// repair ajax_loading after drawFreeHandPolygon
					if (typeof get_original_map_markers_attrs_array(controller_hash).map_attrs.ajax_loading != 'undefined' && get_original_map_markers_attrs_array(controller_hash).map_attrs.ajax_loading == 1) {
						map_attrs_array.map_attrs.ajax_loading = 1;
						google.maps.event.addListener(maps[controller_hash], 'idle', function() {
							setAjaxMarkers(maps[controller_hash], controller_hash);
						});
					}
					if (typeof map_attrs_array.map_attrs.ajax_loading != 'undefined' && map_attrs_array.map_attrs.ajax_loading == 1) {
						for (var attrname in post_params) { map_attrs_array.map_attrs[attrname] = post_params[attrname]; }
						delete map_attrs_array.map_attrs.locations;
						delete map_attrs_array.map_attrs.swLat;
						delete map_attrs_array.map_attrs.swLng;
						delete map_attrs_array.map_attrs.neLat;
						delete map_attrs_array.map_attrs.neLng;
						delete map_attrs_array.map_attrs.action;
						var enable_clusters = map_attrs_array.enable_clusters;
						var enable_radius_cycle = map_attrs_array.enable_radius_cycle;
						var show_summary_button = map_attrs_array.show_summary_button;
						var show_readmore_button = map_attrs_array.show_readmore_button;

						map_attrs_array.map_attrs.include_categories_children = 1;
						setAjaxMarkers(maps[controller_hash], controller_hash, search_button_obj);
						return false;
					}
				}

				for (var attrname in ajax_params) { post_params[attrname] = ajax_params[attrname]; }
				
				if ($("#w2dc-maps-canvas-"+controller_hash).length) {
			   		ajax_map_loader_show(controller_hash);
			   		post_params.with_map= 1;
			   	}

				if ($("#w2dc-controller-"+controller_hash).length)
					ajax_listings_loader_show(controller_hash, false);
			   	else
			   		post_params.without_listings= 1;
				
				$.post(
					js_objects.ajaxurl,
					post_params,
					completeAJAXSearch(search_button_obj),
					'json'
				);
			}
		});
		var completeAJAXSearch = function(search_button_obj) {
			return function(response_from_the_action_function) {
				process_listings_ajax_responce(response_from_the_action_function, true, true, true);
				search_button_obj.val(js_objects.search_button_text).removeAttr('disabled');
			}
		}
	
		$('body').on('click', '.w2dc-orderby-links a', function(e) {
			e.preventDefault();
	
			var controller_hash = $(this).data('controller-hash');
			if (listings_args_array = get_controller_args_array(controller_hash)) {
				var post_params = jQuery.extend({}, listings_args_array);
				var ajax_params = {'action': 'w2dc_controller_request', 'order_by': $(this).data('orderby'), 'order': $(this).data('order'), 'paged': 1, 'hash': controller_hash};
				for (var attrname in ajax_params) { post_params[attrname] = ajax_params[attrname]; }

				if ($("#w2dc-maps-canvas-"+controller_hash).length) {
					if (map_attrs_array = get_map_markers_attrs_array(controller_hash))
						if ((typeof map_attrs_array.map_attrs.ajax_loading == 'undefined' || map_attrs_array.map_attrs.ajax_loading == 0)
						&&
						(typeof map_attrs_array.map_attrs.drawing_state == 'undefined' || map_attrs_array.map_attrs.drawing_state == 0)) {
							ajax_map_loader_show(controller_hash);
						  	post_params.with_map= 1;
						}
				}

				ajax_listings_loader_show(controller_hash, false);
				$.post(
					js_objects.ajaxurl,
					post_params,
					function(response_from_the_action_function) {
						process_listings_ajax_responce(response_from_the_action_function, true, true, true);
					},
					'json'
				);
			}
		});

		$('body').on('click', '.w2dc-pagination li a', function(e) {
			e.preventDefault();
	
			var controller_hash = $(this).data('controller-hash');
			var paged = $(this).data('page');
			var href = $(this).attr('href');
			if (listings_args_array = get_controller_args_array(controller_hash)) {
				var post_params = jQuery.extend({}, listings_args_array);
				var ajax_params = {'action': 'w2dc_controller_request', 'paged': paged, 'hash': controller_hash};
				for (var attrname in ajax_params) { post_params[attrname] = ajax_params[attrname]; }
						
				if ($("#w2dc-maps-canvas-"+controller_hash).length) {
					if (map_attrs_array = get_map_markers_attrs_array(controller_hash))
						if ((typeof map_attrs_array.map_attrs.ajax_loading == 'undefined' || map_attrs_array.map_attrs.ajax_loading == 0)
						&&
						(typeof map_attrs_array.map_attrs.drawing_state == 'undefined' || map_attrs_array.map_attrs.drawing_state == 0)) {
							ajax_map_loader_show(controller_hash);
							post_params.with_map= 1;
						}
				}
				var anchor = $('#w2dc-controller-'+controller_hash);

				ajax_listings_loader_show(controller_hash, anchor);
				$.post(
					js_objects.ajaxurl,
					post_params,
					function(response_from_the_action_function) {
						process_listings_ajax_responce(response_from_the_action_function, true, false, true);
					},
					'json'
				);
			}
		});
	}
	
	$('<img/>')[0].src = js_objects.ajax_loader_url;

	if ("ontouchstart" in document.documentElement)
		$("body").addClass("w2dc-touch");
	else
		$("body").addClass("w2dc-no-touch");
	
	$(".w2dc-tokenizer").tokenize({ });

	equalColumnsHeight();
	window.addEventListener('orientationchange', equalColumnsHeight);

	$('body').on('click', '.w2dc-list-view-btn', function() {
		var button = $(this);
		var hash = button.data('shortcode-hash');
		var listings_block = $('#w2dc-controller-'+hash).find('.w2dc-listings-block');
		if (listings_block.hasClass('w2dc-listings-grid')) {
			listings_block.find('.w2dc-listings-block-content').fadeTo("fast", 0, function() {
				button.removeClass('w2dc-btn-default').addClass('w2dc-btn-primary');
				button.parents('.w2dc-views-links').find('.w2dc-grid-view-btn').removeClass('w2dc-btn-primary').addClass('w2dc-btn-default');
				listings_block.removeClass('w2dc-listings-grid w2dc-listings-grid-1 w2dc-listings-grid-2 w2dc-listings-grid-3 w2dc-listings-grid-4');
				listings_block.find('article.w2dc-listing').each(function() {
					$(this).css('height', 'auto');
				});
				$.cookie("w2dc_listings_view_"+hash, 'list', {expires: 365, path: "/"});
			});
			listings_block.find('.w2dc-listings-block-content').fadeTo("fast", 1);
		}
	});
	$('body').on('click', '.w2dc-grid-view-btn', function() {
		var button = $(this);
		var hash = button.data('shortcode-hash');
		var listings_block = $('#w2dc-controller-'+hash).find('.w2dc-listings-block');
		if (!listings_block.hasClass('w2dc-listings-grid')) {
			listings_block.find('.w2dc-listings-block-content').fadeTo("fast", 0, function() {
				button.removeClass('w2dc-btn-default').addClass('w2dc-btn-primary');
				button.parents('.w2dc-views-links').find('.w2dc-list-view-btn').removeClass('w2dc-btn-primary').addClass('w2dc-btn-default');
				listings_block.addClass('w2dc-listings-grid').addClass('w2dc-listings-grid-'+button.data('columns'));
				equalColumnsHeight();
				$.cookie("w2dc_listings_view_"+hash, 'grid', {expires: 365, path: "/"});
			});
			listings_block.find('.w2dc-listings-block-content').fadeTo("fast", 1);
		}
	});
	
	$('.w2dc-scrollable-block').each(function() {
		var hash = $(this).data('controller-hash');
		$(this).before('<div id="listings_scroller_anchor_'+hash+'"></div>');
		$('#w2dc-controller-'+hash).height(function(index, height) {
			return window.innerHeight - $('#listings_scroller_anchor_'+hash).outerHeight(true) - 10;
		});
		$(window).resize(function(){
			$('#w2dc-controller-'+hash).height(function(index, height) {
				return window.innerHeight - $('#listings_scroller_anchor_'+hash).outerHeight(true) - 10;
			});
		});
	});

	$("a.w2dc-hint-icon").w2dc_popover({ trigger: "hover" });

	// Place listings to/from favourites list
	if ($(".add_to_favourites").length) {
		$(".add_to_favourites").click(function() {
			var listing_id = $(this).attr("listingid");

			if ((str = $.cookie("favourites")) != null) {
				var favourites_array = str.split('*');
			} else {
				var favourites_array = new Array();
			}
			if (in_array(listing_id, favourites_array) === false) {
				favourites_array.push(listing_id);
				$(this).find('span.w2dc-glyphicon').removeClass(js_objects.not_in_favourites_icon).addClass(js_objects.in_favourites_icon);
				$(this).find('span.w2dc-bookmark-button').text(js_objects.not_in_favourites_msg);
			} else {
				for (var count=0; count<favourites_array.length; count++) {
					if (favourites_array[count] == listing_id) {
						delete favourites_array[count];
					}
				}
				$(this).find('span.w2dc-glyphicon').removeClass(js_objects.in_favourites_icon).addClass(js_objects.not_in_favourites_icon);
				$(this).find('span.w2dc-bookmark-button').text(js_objects.in_favourites_msg);
			}
			$.cookie("favourites", favourites_array.join('*'), {expires: 365, path: "/"});
			return false;
		});
	}
	
	$(".w2dc-remove-from-favourites-list").click(function() {
		var listing_id = $(this).attr("listingid");
		
		if ((str = $.cookie("favourites")) != null) {
			var favourites_array = str.split('*');
		} else {
			var favourites_array = new Array();
		}

		for (var count=0; count<favourites_array.length; count++) {
			if (favourites_array[count] == listing_id) {
				delete favourites_array[count];
			}
		}

		$(".w2dc-listing#post-"+listing_id).remove();
		
		$.cookie("favourites", favourites_array.join('*'), {expires: 365, path: "/"});
		return false;
	});

	$('.closed_cb').each(function() {
		check_is_week_day_closed($(this));
    });
	$('.closed_cb').click(function() {
		check_is_week_day_closed($(this));
    });
	
	function check_is_week_day_closed(cb) {
		if (cb.is(":checked"))
			cb.parent().find(".w2dc-week-day-input").attr('disabled', 'disabled');
    	else
    		cb.parent().find(".w2dc-week-day-input").removeAttr('disabled');
	}
	
	$('.w2dc-listing-tabs a').click(function (e) {		  
		  e.preventDefault();
		  w2dc_show_tab($(this));
	});
	var hash = window.location.hash.substring(1);
	if (hash == 'respond' || hash.indexOf('comment-', 0) >= 0) {
		w2dc_show_tab($('.w2dc-listing-tabs a[data-tab="#comments-tab"]'));
	} else if (hash) {
		w2dc_show_tab($('.w2dc-listing-tabs a[data-tab="#'+hash+'"]'));
	}
});



// google_maps_view.js -------------------------------------------------------------------------------------------------------------------------------------------
/* Stack-based Douglas Peucker line simplification routine 
returned is a reduced GLatLng array 
After code by  Dr. Gary J. Robinson,
Environmental Systems Science Centre,
University of Reading, Reading, UK
*/
/*function GDouglasPeucker(source, kink) {
	var n_source, n_stack, n_dest, start, end, i, sig;    
	var dev_sqr, max_dev_sqr, band_sqr;
	var x12, y12, d12, x13, y13, d13, x23, y23, d23;
	var F = ((Math.PI / 180.0) * 0.5 );
	var index = new Array();
	var sig_start = new Array();
	var sig_end = new Array();	

	if ( source.length < 3 ) 
		return(source);

	n_source = source.length;
	band_sqr = kink * 360.0 / (2.0 * Math.PI * 6378137.0);
	band_sqr *= band_sqr;
	n_dest = 0;
	sig_start[0] = 0;
	sig_end[0] = n_source-1;
	n_stack = 1;

	while ( n_stack > 0 ) {
		start = sig_start[n_stack-1];
		end = sig_end[n_stack-1];
		n_stack--;

		if ( (end - start) > 1 ) {
			x12 = (source[end].lng() - source[start].lng());
			y12 = (source[end].lat() - source[start].lat());
			if (Math.abs(x12) > 180.0) 
				x12 = 360.0 - Math.abs(x12);
			x12 *= Math.cos(F * (source[end].lat() + source[start].lat()));
			d12 = (x12*x12) + (y12*y12);

			for ( i = start + 1, sig = start, max_dev_sqr = -1.0; i < end; i++ ) {                                    
				x13 = (source[i].lng() - source[start].lng());
				y13 = (source[i].lat() - source[start].lat());
				if (Math.abs(x13) > 180.0) 
					x13 = 360.0 - Math.abs(x13);
				x13 *= Math.cos (F * (source[i].lat() + source[start].lat()));
				d13 = (x13*x13) + (y13*y13);

				x23 = (source[i].lng() - source[end].lng());
				y23 = (source[i].lat() - source[end].lat());
				if (Math.abs(x23) > 180.0) 
					x23 = 360.0 - Math.abs(x23);
				x23 *= Math.cos(F * (source[i].lat() + source[end].lat()));
				d23 = (x23*x23) + (y23*y23);

				if ( d13 >= ( d12 + d23 ) )
					dev_sqr = d23;
				else if ( d23 >= ( d12 + d13 ) )
					dev_sqr = d13;
				else
					dev_sqr = (x13 * y12 - y13 * x12) * (x13 * y12 - y13 * x12) / d12;// solve triangle

				if ( dev_sqr > max_dev_sqr  ){
					sig = i;
					max_dev_sqr = dev_sqr;
				}
			}

			if ( max_dev_sqr < band_sqr ) {
				index[n_dest] = start;
				n_dest++;
			} else {
				n_stack++;
				sig_start[n_stack-1] = sig;
				sig_end[n_stack-1] = end;
				n_stack++;
				sig_start[n_stack-1] = start;
				sig_end[n_stack-1] = sig;
			}
		} else {
			index[n_dest] = start;
			n_dest++;
		}
	}
	index[n_dest] = n_source-1;
	n_dest++;

	var r = new Array();
	for(var i=0; i < n_dest; i++)
		r.push(source[index[i]]);

	return r;
}
function drawFreeHandPolygon(unique_map_id) {
	var poly = new google.maps.Polyline({
		map: maps[unique_map_id],
		clickable:false,
		strokeColor: '#AA2143',
		strokeWeight: 2,
		zIndex: 1000,
	});
	
	var move = google.maps.event.addListener(maps[unique_map_id], 'mousemove', function(e) {
		poly.getPath().push(e.latLng);
	});
	
	google.maps.event.addListenerOnce(maps[unique_map_id], 'mouseup', function(e) {
		google.maps.event.removeListener(move);
		var path = poly.getPath();
		poly.setMap(null);
	
		var theArrayofLatLng = path.j;
		var ArrayforPolygontoUse= GDouglasPeucker(theArrayofLatLng, 50); 
	
		var geo_poly = [];
		for (lat_lng in ArrayforPolygontoUse) { geo_poly.push({'lat': ArrayforPolygontoUse[lat_lng].A, 'lng': ArrayforPolygontoUse[lat_lng].F}); }
	
		if (geo_poly && (map_attrs_array = get_map_markers_attrs_array(unique_map_id))) {
			ajax_map_loader_show(unique_map_id);

			var ajax_params = {action: 'w2dc_search_by_poly', 'hash': unique_map_id, 'geo_poly': geo_poly};
			for (var attrname in map_attrs_array.map_attrs) { ajax_params[attrname] = map_attrs_array.map_attrs[attrname]; }

			if (listings_args_array = get_controller_args_array(unique_map_id)) {
				ajax_params.hide_order = listings_args_array.hide_order;
				ajax_params.hide_count = listings_args_array.hide_count;
				ajax_params.hide_paginator = listings_args_array.hide_paginator;
				ajax_params.show_views_switcher = listings_args_array.show_views_switcher;
				ajax_params.listings_view_type = listings_args_array.listings_view_type;
				ajax_params.listings_view_grid_columns = listings_args_array.listings_view_grid_columns;
				ajax_params.listing_thumb_width = listings_args_array.listing_thumb_width;
				ajax_params.wrap_logo_list_view = listings_args_array.wrap_logo_list_view;
				ajax_params.logo_animation_effect = listings_args_array.logo_animation_effect;
				ajax_params.perpage = listings_args_array.perpage;
				ajax_params.onepage = listings_args_array.onepage;
				ajax_params.order = listings_args_array.order;
				ajax_params.order_by = listings_args_array.order_by;

				ajax_listings_loader_show(unique_map_id);
			} else
				ajax_params.without_listings = 1;
		
			jQuery.post(
				js_objects.ajaxurl,
				ajax_params,
				function(response_from_the_action_function) {
					process_listings_ajax_responce(response_from_the_action_function, true, false, false);
				},
				'json'
			);
		 
			var polyOptions = {
				map: maps[unique_map_id],
				fillColor: '#0099FF',
				fillOpacity: 0.3,
				strokeColor: '#AA2143',
				strokeWeight: 1,
				clickable: false,
				zIndex: 1,
				path:ArrayforPolygontoUse,
				editable: false
			}

			polygons[unique_map_id] = new google.maps.Polygon(polyOptions);
		}

		var drawButton = jQuery(maps[unique_map_id].getDiv()).find('.w2dc-map-draw').get(0);
		drawButton.drawing_state = 0;
		jQuery('body').unbind('touchmove');
		enableDrawing(unique_map_id);
		jQuery(maps[unique_map_id].getDiv()).css('cursor', 'auto');
		jQuery(drawButton).removeClass('w2dc-btn-active');
		enableDrawing(unique_map_id);
		google.maps.event.clearListeners(maps[unique_map_id].getDiv(), 'mousedown');
		
		var editButton = jQuery(maps[unique_map_id].getDiv()).find('.w2dc-map-edit').get(0);
		jQuery(editButton).removeAttr('disabled');
	});
}
function disableDrawing(unique_map_id) {
	maps[unique_map_id].setOptions({
		draggable: false, 
		zoomControl: false, 
		scrollwheel: false
	});
}
function enableDrawing(unique_map_id) {
	maps[unique_map_id].setOptions({
		draggable: true, 
		zoomControl: true, 
		scrollwheel: true
	});
}*/

var maps = [];
var maps_attrs = [];
var infoWindows = [];
var drawCircles = [];
var polygons = [];
var directions;
var address = '';
var global_markers_array = [];
var global_locations_array = [];
var markerClusters = [];
var glocation = (function(id, point, map_icon_file, listing_title, listing_logo, listing_url, content_fields, anchor, nofollow, show_summary_button, show_readmore_button, unique_map_id, is_ajax_markers) {
	this.id = id;
	this.point = point;
	this.map_icon_file = map_icon_file;
	this.listing_title = listing_title;
	this.listing_logo = listing_logo;
	this.listing_url = listing_url;
	this.content_fields = content_fields;
	this.anchor = anchor;
	this.nofollow = nofollow;
	this.show_summary_button = show_summary_button;
	this.show_readmore_button = show_readmore_button;
	this.placeMarker = function(unique_map_id) {
		this.marker = placeMarker(this, unique_map_id);
		return this.marker;
	};
	this.is_ajax_markers = is_ajax_markers;
});
var _map_markers_attrs_array;
(function($) {
	w2dc_load_maps_api = function() {
		// are there any markers?
		if (typeof map_markers_attrs_array != 'undefined' && map_markers_attrs_array.length) {
			_map_markers_attrs_array = JSON.parse(JSON.stringify(map_markers_attrs_array));
			google.maps.event.addDomListener(window, 'load', function() {
				for (var i=0; i<map_markers_attrs_array.length; i++)
					w2dc_load_map(i);

				geolocatePosition();
			});
		}
		
		if (google.maps && google.maps.places) {
			$(".w2dc-field-autocomplete").each( function() {
				var searchBox = new google.maps.places.Autocomplete(this);
			});
		}

		load_ajax_initial_elements();
		
		if ($(".direction_button").length) {
			unique_map_id = $(".direction_button").attr("id").replace("get_direction_button_", "");

			var directionsService = new google.maps.DirectionsService();
			var directionsDisplay = new google.maps.DirectionsRenderer({map: maps[unique_map_id]})
			directionsDisplay.setPanel(document.getElementById("route_"+unique_map_id));
		}
		
		$(".direction_button").click(function() {
			unique_map_id = $(".direction_button").attr("id").replace("get_direction_button_", "");
			// Retrieve the start and end locations and create
			// a DirectionsRequest using DRIVING directions.
			var start = $("#from_direction_"+unique_map_id).val();
			var end = $(".select_direction_"+unique_map_id+":checked").val();
			var request = {
				origin: start,
				destination: end,
				travelMode: google.maps.DirectionsTravelMode.DRIVING
			};

			directionsService.route(request, function(response, status) {
				if (status == google.maps.DirectionsStatus.OK) {
					directionsDisplay.setDirections(response);
				} else {
					handleDirectionsErrors(status);
				}
			});
		});
		
		$('body').on('click', '.w2dc-show-on-map', function() {
			var location_id = $(this).data("location-id");

			for (var unique_map_id in maps) {
				if (typeof global_locations_array[unique_map_id] != 'undefined') {
					for (i=0; i<global_locations_array[unique_map_id].length; i++) {
						if (location_id == global_locations_array[unique_map_id][i].id) {
							location_obj = global_locations_array[unique_map_id][i];
							showInfoWindow(location_obj, location_obj.marker, unique_map_id);
						}
					}
				}
			}
		});
	}

	function w2dc_load_map(i) {
		var unique_map_id = map_markers_attrs_array[i].map_id;
		var markers_array = map_markers_attrs_array[i].markers_array;
		var enable_radius_cycle = map_markers_attrs_array[i].enable_radius_cycle;
		var enable_clusters = map_markers_attrs_array[i].enable_clusters;
		var show_summary_button = map_markers_attrs_array[i].show_summary_button;
		var map_style_name = map_markers_attrs_array[i].map_style_name;
		var show_readmore_button = map_markers_attrs_array[i].show_readmore_button;
		var map_attrs = map_markers_attrs_array[i].map_attrs;
		if (document.getElementById("w2dc-maps-canvas-"+unique_map_id)) {
			var mapOptions = {
				zoom: 1,
				maxZoom: 17,
				draggable: true,
				scrollwheel: true,
				disableDoubleClickZoom: true,
				streetViewControl: true,
			    streetViewControlOptions: {
			        position: google.maps.ControlPosition.RIGHT_TOP
			    },
				mapTypeId: google.maps.MapTypeId.ROADMAP
			};
			if (map_style_name)
				mapOptions.styles = eval(google_maps_objects.map_styles[map_style_name]);
	
		    maps[unique_map_id] = new google.maps.Map(document.getElementById("w2dc-maps-canvas-"+unique_map_id), mapOptions);
		    maps_attrs[unique_map_id] = map_attrs;
	
		    /*var drawPanel = document.createElement('div');
		    $(drawPanel).addClass('w2dc-map-draw-panel');
		    maps[unique_map_id].controls[google.maps.ControlPosition.TOP_CENTER].push(drawPanel);
		    
		    var drawButton = document.createElement('button');
		    $(drawButton).addClass('w2dc-btn w2dc-btn-primary w2dc-map-draw').html('<span class="w2dc-glyphicon w2dc-glyphicon-pencil"></span> <span class="w2dc-map-draw-label">'+google_maps_objects.draw_area_button+'</span>');
		    drawPanel.appendChild(drawButton);
		    drawButton.map_id = unique_map_id;
			drawButton.drawing_state = 0;
			$(drawButton).click(function(e) {
				var unique_map_id = drawButton.map_id;
				if (this.drawing_state == 0) {
					this.drawing_state = 1;
					$('body').bind('touchmove', function(e){e.preventDefault()});
					clearOverlays(unique_map_id, true);

					disableDrawing(unique_map_id);
					
					var editButton = jQuery(maps[unique_map_id].getDiv()).find('.w2dc-map-edit').get(0);
					jQuery(editButton).attr('disabled', 'disabled');

					// remove ajax_loading and set drawing_state
					if (map_attrs_array = get_map_markers_attrs_array(unique_map_id)) {
						map_attrs_array.map_attrs.drawing_state = 1;
						google.maps.event.clearListeners(maps[unique_map_id], 'idle');
						delete map_attrs_array.map_attrs.ajax_loading;
					}
	
					$(maps[unique_map_id].getDiv()).css('cursor', 'crosshair');
					$(this).toggleClass('w2dc-btn-active');
					google.maps.event.clearListeners(maps[unique_map_id].getDiv(), 'mousedown');
					
					maps[unique_map_id].getDiv().map_id = unique_map_id;
					google.maps.event.addDomListener(maps[unique_map_id].getDiv(), 'mousedown', function(e) {
						var el = e.target;
	                    do {
	                        if ($(el).hasClass('w2dc-map-draw-panel')) {
	                            return;
	                        }
	                    } while (el = el.parentNode);
						drawFreeHandPolygon(this.map_id);
					});
				} else if (this.drawing_state == 1) {
					this.drawing_state = 0;
					$('body').unbind('touchmove');
					enableDrawing(unique_map_id);
					$(maps[unique_map_id].getDiv()).css('cursor', 'auto');
					$(this).toggleClass('w2dc-btn-active');
					google.maps.event.clearListeners(maps[unique_map_id].getDiv(), 'mousedown');
					
					// repair ajax_loading and set drawing_state
					if (map_attrs_array = get_map_markers_attrs_array(unique_map_id)) {
						map_attrs_array.map_attrs.drawing_state = 0;
						if (typeof get_original_map_markers_attrs_array(unique_map_id).map_attrs.ajax_loading != 'undefined' && get_original_map_markers_attrs_array(unique_map_id).map_attrs.ajax_loading == 1) {
							map_attrs_array.map_attrs.ajax_loading = 1;
							google.maps.event.addListener(maps[unique_map_id], 'idle', function() {
								setAjaxMarkers(maps[unique_map_id], unique_map_id);
							});
						}
					}
				}
			});
		    
		    var editButton = document.createElement('button');
		    $(editButton).addClass('w2dc-btn w2dc-btn-primary w2dc-map-edit').html('<span class="w2dc-glyphicon w2dc-glyphicon-edit"></span> <span class="w2dc-map-edit-label">'+google_maps_objects.edit_area_button+'</span>').attr('disabled', 'disabled');
		    drawPanel.appendChild(editButton);
		    editButton.map_id = unique_map_id;
		    editButton.editing_state = 0;
		    $(editButton).click(function(e) {
		    	var unique_map_id = editButton.map_id;
				if (this.editing_state == 0) {
					this.editing_state = 1;
					$(this).toggleClass('w2dc-btn-active');
					$(this).find('.w2dc-map-edit-label').text(google_maps_objects.apply_area_button);
					if (typeof polygons[unique_map_id] != 'undefined') {
						polygons[unique_map_id].setOptions({'editable': true});
					}
				} else if (this.editing_state == 1) {
					this.editing_state = 0;
					$(this).toggleClass('w2dc-btn-active');
					$(this).find('.w2dc-map-edit-label').text(google_maps_objects.edit_area_button);
					if (typeof polygons[unique_map_id] != 'undefined') {
						polygons[unique_map_id].setOptions({'editable': false});
						var path = polygons[unique_map_id].getPath();
						var theArrayofLatLng = path.j;
						var geo_poly = [];
						for (lat_lng in theArrayofLatLng) { geo_poly.push({'lat': theArrayofLatLng[lat_lng].A, 'lng': theArrayofLatLng[lat_lng].F}); }
					
						if (geo_poly && (map_attrs_array = get_map_markers_attrs_array(unique_map_id))) {
							ajax_map_loader_show(unique_map_id);
							
							var ajax_params = {action: 'w2dc_search_by_poly', 'hash': unique_map_id, 'geo_poly': geo_poly};
							for (var attrname in map_attrs_array.map_attrs) { ajax_params[attrname] = map_attrs_array.map_attrs[attrname]; }

							if (listings_args_array = get_controller_args_array(unique_map_id)) {
								ajax_params.hide_order = listings_args_array.hide_order;
								ajax_params.hide_count = listings_args_array.hide_count;
								ajax_params.hide_paginator = listings_args_array.hide_paginator;
								ajax_params.show_views_switcher = listings_args_array.show_views_switcher;
								ajax_params.listings_view_type = listings_args_array.listings_view_type;
								ajax_params.listings_view_grid_columns = listings_args_array.listings_view_grid_columns;
								ajax_params.listing_thumb_width = listings_args_array.listing_thumb_width;
								ajax_params.wrap_logo_list_view = listings_args_array.wrap_logo_list_view;
								ajax_params.logo_animation_effect = listings_args_array.logo_animation_effect;
								ajax_params.perpage = listings_args_array.perpage;
								ajax_params.onepage = listings_args_array.onepage;
								ajax_params.order = listings_args_array.order;
								ajax_params.order_by = listings_args_array.order_by;

								ajax_listings_loader_show(unique_map_id);
							} else
								ajax_params.without_listings = 1;
						
							jQuery.post(
								js_objects.ajaxurl,
								ajax_params,
								function(response_from_the_action_function) {
									process_listings_ajax_responce(response_from_the_action_function, true, false, false);
								},
								'json'
							);
						}
					}
				}
		    });
		    
		    var reloadButton = document.createElement('button');
		    $(reloadButton).addClass('w2dc-btn w2dc-btn-primary w2dc-map-reload').html('<span class="w2dc-glyphicon w2dc-glyphicon-refresh"></span> <span class="w2dc-map-reload-label">'+google_maps_objects.reload_map_button+'</span>');
		    drawPanel.appendChild(reloadButton);
		    reloadButton.map_id = unique_map_id;
		    $(reloadButton).click(function(e) {
				var unique_map_id = reloadButton.map_id;
				for (var i=0; i<map_markers_attrs_array.length; i++) {
					if (map_markers_attrs_array[i].map_id == unique_map_id) {
						map_markers_attrs_array[i] = JSON.parse(JSON.stringify(_map_markers_attrs_array[i]));
						
						$('body').unbind('touchmove');

						var editButton = jQuery(maps[unique_map_id].getDiv()).find('.w2dc-map-edit').get(0);
						$(editButton).removeClass('w2dc-btn-active');
						$(editButton).find('.w2dc-map-edit-label').text(google_maps_objects.edit_area_button);
						$(editButton).attr('disabled', 'disabled');

						w2dc_load_map(i);
						if (global_markers_array[unique_map_id].length) {
				    		var bounds = new google.maps.LatLngBounds();
				    		for (var j=0; j<global_markers_array[unique_map_id].length; j++) {
				    			bounds.extend(global_markers_array[unique_map_id][j].position);
				    		}
				    		maps[unique_map_id].fitBounds(bounds);
				    		if (typeof a == 'function') a();
				    	}
						break;
					}
				}
			});

			google.maps.event.addListenerOnce(maps[unique_map_id], 'idle', function(){
			    if ($(drawPanel).width() > 502) {
				    var locationButton = document.createElement('button');
				    $(locationButton).addClass('w2dc-btn w2dc-btn-primary w2dc-map-location').html('<span class="w2dc-glyphicon w2dc-glyphicon-screenshot"></span> <span class="w2dc-map-location-label">'+google_maps_objects.my_location_button+'</span>');
				    drawPanel.appendChild(locationButton);
				    
				    locationButton.map_id = unique_map_id;
				    $(locationButton).click(function(e) {
						var unique_map_id = locationButton.map_id;
						if (navigator.geolocation) {
					    	navigator.geolocation.getCurrentPosition( function(position) {
					    		var start_latitude = position.coords.latitude;
					    		var start_longitude = position.coords.longitude;
							    maps[unique_map_id].setCenter(new google.maps.LatLng(start_latitude, start_longitude));
					    	}, function() { });
						}
					});
			    }
			});*/

		    global_markers_array[unique_map_id] = [];
		    global_locations_array[unique_map_id] = [];

			var bounds = new google.maps.LatLngBounds();
		    if (markers_array.length) {
		    	if (typeof map_attrs.ajax_markers_loading != 'undefined' && map_attrs.ajax_markers_loading == 1)
					var is_ajax_markers = true;
				else
					var is_ajax_markers = false;
	
		    	var markers = [];
		    	for (var j=0; j<markers_array.length; j++) {
	    			map_coords_1 = markers_array[j][1];
			    	map_coords_2 = markers_array[j][2];
			    	if ($.isNumeric(map_coords_1) && $.isNumeric(map_coords_2)) {
		    			point = new google.maps.LatLng(map_coords_1, map_coords_2);
		    			bounds.extend(point);
	
		    			var location_obj = new glocation(
		    				markers_array[j][0],  // location ID
		    				point, 
		    				markers_array[j][3],  // map icon file
		    				markers_array[j][5],  // listing title
		    				markers_array[j][6],  // logo image
		    				markers_array[j][7],  // listing link
		    				markers_array[j][8],  // content fields output
		    				markers_array[j][9],  // listing link anchor
		    				markers_array[j][10], // is nofollow link
		    				show_summary_button,
		    				show_readmore_button,
		    				unique_map_id,
		    				is_ajax_markers
			    		);
			    		var marker = location_obj.placeMarker(unique_map_id);
			    		markers.push(marker);
	
			    		global_locations_array[unique_map_id].push(location_obj);
			    	}
	    		}
	
		    	setClusters(enable_clusters, unique_map_id, markers)
	
		    	if (enable_radius_cycle && typeof window['radius_params_'+unique_map_id] != 'undefined') {
		    		var radius_params = window['radius_params_'+unique_map_id];
					map_radius = parseFloat(radius_params.radius_value);
					if (radius_params.dimension == 'miles')
						map_radius *= 1.609344;
					map_coords_1 = radius_params.map_coords_1;
					map_coords_2 = radius_params.map_coords_2;
	
					if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
						map_radius *= 1000; // we need radius exactly in meters
						drawCircles[unique_map_id] = new google.maps.Circle({
					    	center: new google.maps.LatLng(map_coords_1, map_coords_2),
					        radius: map_radius,
					        strokeColor: "#FF0000",
					        strokeOpacity: 0.25,
					        strokeWeight: 1,
					        fillColor: "#FF0000",
					        fillOpacity: 0.1,
					        zIndex: 1,
					        map:  maps[unique_map_id]
					    });
					}
				}
		    } else
		    	var zoom_level = 2;
		    
		    
		    if (typeof map_attrs.start_zoom != 'undefined' && map_attrs.start_zoom)
	    		var zoom_level = map_attrs.start_zoom;
		    else if (markers_array.length == 1)
				var zoom_level = markers_array[0][4];
	    	else if (markers_array.length > 1)
				var zoom_level = 11;
	    	else
				var zoom_level = 2;
		    
		    google.maps.event.addListener(maps[unique_map_id], 'bounds_changed', function() {
				var zoom_level = maps[unique_map_id].getZoom();
				maps[unique_map_id].setZoom(parseInt(zoom_level));
			});

		    if (typeof map_attrs.start_latitude != 'undefined' && map_attrs.start_latitude && typeof map_attrs.start_longitude != 'undefined' && map_attrs.start_longitude) {
	    		var start_latitude = map_attrs.start_latitude;
	    		var start_longitude = map_attrs.start_longitude;
	    		var map_center = new google.maps.LatLng(start_latitude, start_longitude);
	    		maps[unique_map_id].setCenter(map_center);
			    maps[unique_map_id].setZoom(parseInt(zoom_level));
	    	} else if (typeof map_attrs.start_address != 'undefined' && map_attrs.start_address) {
	    		// use closures here
	    		geocodeStartAddress(map_attrs, maps[unique_map_id], zoom_level);
	    	} else if (markers_array.length) {
	    		if (markers_array.length == 1) {
	    			var map_center = bounds.getCenter();
	    			maps[unique_map_id].setCenter(map_center);
				    maps[unique_map_id].setZoom(parseInt(zoom_level));
	    		} else if (markers_array.length > 1)
	    			maps[unique_map_id].fitBounds(bounds);
	    	} else {
	    		var map_center = new google.maps.LatLng(34, 0);
	    		maps[unique_map_id].setCenter(map_center);
			    maps[unique_map_id].setZoom(parseInt(zoom_level));
	    	}
		    
		    if ((typeof map_attrs.ajax_loading != 'undefined' && map_attrs.ajax_loading == 1)
		    	&& ((typeof map_attrs.start_latitude != 'undefined' && map_attrs.start_latitude && typeof map_attrs.start_longitude != 'undefined' && map_attrs.start_longitude)
		    		||
		    		(typeof map_attrs.start_address != 'undefined' && map_attrs.start_address))
		    ) {
		    	delete map_attrs.swLat;
		    	delete map_attrs.swLng;
				delete map_attrs.neLat;
				delete map_attrs.neLng;
				delete map_attrs.action;
		    	// use closures here
		    	setMapAjaxListener(maps[unique_map_id], unique_map_id);
		    }
		}
	}
}(jQuery));
function setMapAjaxListener(map, unique_map_id, search_button_obj) {
	google.maps.event.addListener(map, 'idle', function() {
		setAjaxMarkers(map, unique_map_id, search_button_obj);
	});
}
function geocodeStartAddress(map_attrs, map, zoom_level) {
	function _geocodeStartAddress() {
		var geocoder = new google.maps.Geocoder();
		geocoder.geocode({'address': map_attrs.start_address}, function(results, status) {
		    if (status == google.maps.GeocoderStatus.OK) {
		    	var start_latitude = results[0].geometry.location.lat();
		    	var start_longitude = results[0].geometry.location.lng();
		    } else {
		    	var start_latitude = 34;
		    	var start_longitude = 0;
	    	}
		    map.setCenter(new google.maps.LatLng(start_latitude, start_longitude));
		    map.setZoom(parseInt(zoom_level));
		});
	}
	_geocodeStartAddress();
}
function geolocatePosition() {
	if (navigator.geolocation) {
		var geolocation_maps = [];
    	for (var unique_map_id in maps_attrs) {
    		if (typeof maps_attrs[unique_map_id].geolocation != 'undefined' && maps_attrs[unique_map_id].geolocation == 1) {
    			geolocation_maps.push(maps[unique_map_id]);
    		}
    	}
    	if (geolocation_maps.length) {
    		navigator.geolocation.getCurrentPosition( function(position) {
    			var start_latitude = position.coords.latitude;
    			var start_longitude = position.coords.longitude;
		    	for (var i in geolocation_maps) {
		    		geolocation_maps[i].setCenter(new google.maps.LatLng(start_latitude, start_longitude));
		    	}
    		}, function() { });
    	}
	}
}
function w2dc_project(latLng) {
	var TILE_SIZE = 256;
	var siny = Math.sin(latLng.lat() * Math.PI / 180);
	siny = Math.min(Math.max(siny, -0.9999), 0.9999);
	return new google.maps.Point(
	   TILE_SIZE * (0.5 + latLng.lng() / 360),
	   TILE_SIZE * (0.5 - Math.log((1 + siny) / (1 - siny)) / (4 * Math.PI)));
}
function setAjaxMarkers(map, unique_map_id, search_button_obj) {
	var attrs_array = get_map_markers_attrs_array(unique_map_id);
	var map_attrs = attrs_array.map_attrs;
	var enable_radius_cycle = attrs_array.enable_radius_cycle;
	var enable_clusters = attrs_array.enable_clusters;
	var show_summary_button = attrs_array.show_summary_button;
	var show_readmore_button = attrs_array.show_readmore_button;
	var search_button_obj = typeof search_button_obj !== 'undefined' ? search_button_obj : null;

	var bounds_new = map.getBounds();
	var south_west = bounds_new.getSouthWest();
	var north_east = bounds_new.getNorthEast();

	if (typeof map_attrs.swLat != 'undefined' && typeof map_attrs.swLng != 'undefined' && typeof map_attrs.neLat != 'undefined' && typeof map_attrs.neLng != 'undefined') {
		var bounds_old = new google.maps.LatLngBounds();
	    var sw_point = new google.maps.LatLng(map_attrs.swLat, map_attrs.swLng);
	    var ne_point = new google.maps.LatLng(map_attrs.neLat, map_attrs.neLng);
	    bounds_old.extend(sw_point);
	    bounds_old.extend(ne_point);

	    var scale = 1 << map.getZoom();
	    var worldCoordinate_new = w2dc_project(sw_point);
	    var worldCoordinate_old = w2dc_project(south_west);
	    if (
	    	(bounds_old.contains(south_west) && bounds_old.contains(north_east))
	    	||
		    	(140 > Math.abs(Math.floor(worldCoordinate_new.x*scale) - Math.floor(worldCoordinate_old.x*scale))
		    	&&
		    	140 > Math.abs(Math.floor(worldCoordinate_new.y*scale) - Math.floor(worldCoordinate_old.y*scale)))
	    )
	    	return false;
	}
	map_attrs.swLat = south_west.lat();
	map_attrs.swLng = south_west.lng();
	map_attrs.neLat = north_east.lat();
	map_attrs.neLng = north_east.lng();
	
	ajax_map_loader_show(unique_map_id);

	var ajax_params = { 'action': 'w2dc_get_map_markers', 'hash': unique_map_id };
	for (var attrname in map_attrs) { ajax_params[attrname] = map_attrs[attrname]; }

	if (listings_args_array = get_controller_args_array(unique_map_id)) {
		ajax_params.hide_order = listings_args_array.hide_order;
		ajax_params.hide_count = listings_args_array.hide_count;
		ajax_params.hide_paginator = listings_args_array.hide_paginator;
		ajax_params.show_views_switcher = listings_args_array.show_views_switcher;
		ajax_params.listings_view_type = listings_args_array.listings_view_type;
		ajax_params.listings_view_grid_columns = listings_args_array.listings_view_grid_columns;
		ajax_params.listing_thumb_width = listings_args_array.listing_thumb_width;
		ajax_params.wrap_logo_list_view = listings_args_array.wrap_logo_list_view;
		ajax_params.logo_animation_effect = listings_args_array.logo_animation_effect;
		ajax_params.perpage = listings_args_array.perpage;
		ajax_params.onepage = listings_args_array.onepage;
		ajax_params.order = listings_args_array.order;
		ajax_params.order_by = listings_args_array.order_by;

		ajax_listings_loader_show(unique_map_id);
	} else
		ajax_params.without_listings = 1;

	jQuery.ajax({
		type: "POST",
		url: js_objects.ajaxurl,
		//data: eval(map_attrs),
		data: ajax_params,
		dataType: 'json',
		success: function(response_from_the_action_function) {
			if (response_from_the_action_function) {
				var responce_hash = response_from_the_action_function.hash;

				if (response_from_the_action_function.html) {
					var listings_block = jQuery('#w2dc-controller-'+responce_hash);
					listings_block.replaceWith(response_from_the_action_function.html);
					ajax_listings_loader_hide(responce_hash);
				}

				clearOverlays(unique_map_id, true);
				// do not close opened info window when its marker is still in the viewpost
				/*if (typeof infoWindows[unique_map_id] != 'undefined') {
					var y1 = map_attrs.neLat;
					var y2 =  map_attrs.swLat;
					
					// when zoom level 2 - there may be problems with neLng and swLng of bounds
					if (map_attrs.neLat > map_attrs.swLng) {
						var x1 = map_attrs.neLng;
						var x2 = map_attrs.swLng;
					} else {
						var x1 = 180;
						var x2 = -180;
					}
	
					if (infoWindows[unique_map_id].marker.position.lat() < y2 || infoWindows[unique_map_id].marker.position.lat() > y1 || infoWindows[unique_map_id].marker.position.lng() < x2 || infoWindows[unique_map_id].marker.position.lng() > x1)
						infoWindows[unique_map_id].close();
				}*/
				
				if (typeof map_attrs.ajax_markers_loading != 'undefined' && map_attrs.ajax_markers_loading == 1)
					var is_ajax_markers = true;
				else
					var is_ajax_markers = false;
	
				var markers_array = response_from_the_action_function.map_markers;
				global_locations_array[unique_map_id] = [];
		    	for (var j=0; j<markers_array.length; j++) {
	    			map_coords_1 = markers_array[j][1];
			    	map_coords_2 = markers_array[j][2];
			    	if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
		    			point = new google.maps.LatLng(map_coords_1, map_coords_2);

		    			var location_obj = new glocation(markers_array[j][0], point, 
		    				markers_array[j][3],
		    				markers_array[j][5],
		    				markers_array[j][6],
		    				markers_array[j][7],
		    				markers_array[j][8],
		    				markers_array[j][9],
		    				markers_array[j][10],
		    				show_summary_button,
		    				show_readmore_button,
		    				unique_map_id,
		    				is_ajax_markers
			    		);
			    		var marker = location_obj.placeMarker(unique_map_id);

			    		global_locations_array[unique_map_id].push(location_obj);
			    	}
	    		}
		    	setClusters(enable_clusters, unique_map_id, global_markers_array[unique_map_id]);
		    	
		    	if (enable_radius_cycle && typeof response_from_the_action_function.radius_params != 'undefined') {
		    		var radius_params = response_from_the_action_function.radius_params;
					map_radius = parseFloat(radius_params.radius_value);
					if (radius_params.dimension == 'miles')
						map_radius *= 1.609344;
					map_coords_1 = radius_params.map_coords_1;
					map_coords_2 = radius_params.map_coords_2;

					if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
						map_radius *= 1000; // we need radius exactly in meters
						drawCircles[responce_hash] = new google.maps.Circle({
					    	center: new google.maps.LatLng(map_coords_1, map_coords_2),
					        radius: map_radius,
					        strokeColor: "#FF0000",
					        strokeOpacity: 0.25,
					        strokeWeight: 1,
					        fillColor: "#FF0000",
					        fillOpacity: 0.1,
					        map: maps[responce_hash]
					    });
					}
				}
			}
		},
		complete: completeAJAXSearchOnMap(unique_map_id, search_button_obj)
	});
}
var completeAJAXSearchOnMap = function(unique_map_id, search_button_obj) {
	return function() {
		ajax_listings_loader_hide(unique_map_id);
		ajax_map_loader_hide(unique_map_id);
		equalColumnsHeight();
		if (search_button_obj)
			search_button_obj.val(js_objects.search_button_text).removeAttr('disabled');
	}
}
function ajax_map_loader_show(unique_map_id) {
	jQuery("#w2dc-maps-canvas-"+unique_map_id).addClass("w2dc-ajax-block-loading");
	var wheel_width = 90;
	var wheel_height = 90;
	leftPosition = (jQuery("#w2dc-maps-canvas-"+unique_map_id).width() / 2) - (wheel_width / 2);
   	topPosition = (jQuery("#w2dc-maps-canvas-"+unique_map_id).height() / 2) - (wheel_height / 2);
	jQuery("#w2dc-maps-canvas-"+unique_map_id).append('<img class="ajax_map_loader_'+unique_map_id+'" width="'+wheel_width+'" height="'+wheel_height+'" alt="Updating the map" src="'+js_objects.ajax_map_loader_url+'" style="position: absolute; left: '+leftPosition+'px; top: '+topPosition+'px;">');
	jQuery("#w2dc-maps-canvas-"+unique_map_id).css("position", "relative");
	jQuery("#w2dc-maps-canvas-"+unique_map_id).fadeTo("fast", 0.5);
	if (typeof a == 'function') a();
}
function ajax_map_loader_hide(unique_map_id) {
	jQuery("#w2dc-maps-canvas-"+unique_map_id).removeClass("w2dc-ajax-block-loading");
	jQuery(".ajax_map_loader_"+unique_map_id).remove();
	jQuery("#w2dc-maps-canvas-"+unique_map_id).fadeTo("fast", 1);
}
function placeMarker(location, unique_map_id) {
	if (google_maps_objects.global_map_icons_path != '') {
		if (location.map_icon_file)
			var icon_file = google_maps_objects.global_map_icons_path+'icons/'+location.map_icon_file;
		else
			var icon_file = google_maps_objects.global_map_icons_path+"blank.png";

		var customIcon = {
			url: icon_file,
		    size: new google.maps.Size(parseInt(google_maps_objects.marker_image_width), parseInt(google_maps_objects.marker_image_height)),
		    origin: new google.maps.Point(0, 0),
		    anchor: new google.maps.Point(parseInt(google_maps_objects.marker_image_anchor_x), parseInt(google_maps_objects.marker_image_anchor_y))
		};

		var marker = new google.maps.Marker({
			position: location.point,
			map: maps[unique_map_id],
			icon: customIcon,
			animation: google.maps.Animation.DROP
		});
	} else 
		var marker = new google.maps.Marker({
			position: location.point,
			map: maps[unique_map_id],
			animation: google.maps.Animation.DROP
		});
	
	/*var marker = new RichMarker({
		position: location.point,
		map: maps[unique_map_id],
		flat: true,
		content: '<div class="my-marker"><img src="'+google_maps_objects.global_map_icons_path+'Airport.png'+'"></div>'
	});*/
	
	global_markers_array[unique_map_id].push(marker);

	google.maps.event.addListener(marker, 'click', function() {
		if (!location.is_ajax_markers)
			showInfoWindow(location, marker, unique_map_id);
		else {
			ajax_map_loader_show(unique_map_id);

			var post_data = {'location_id': location.id, 'action': 'w2dc_get_map_marker_info'};
			jQuery.ajax({
	    		type: "POST",
	    		url: js_objects.ajaxurl,
	    		data: eval(post_data),
	    		dataType: 'json',
	    		success: function(response_from_the_action_function) {
	    			var marker_array = response_from_the_action_function;
	    			map_coords_1 = marker_array[1];
			    	map_coords_2 = marker_array[2];
			    	if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
		    			point = new google.maps.LatLng(map_coords_1, map_coords_2);

		    			var new_location_obj = new glocation(marker_array[0], point, 
		    				marker_array[3],
		    				marker_array[5],
		    				marker_array[6],
		    				marker_array[7],
		    				marker_array[8],
		    				marker_array[9],
		    				marker_array[10],
		    				location.show_summary_button,
		    				location.show_readmore_button,
		    				unique_map_id,
		    				true
			    		);
		    			showInfoWindow(new_location_obj, marker, unique_map_id);
			    	}
	    		},
	    		complete: function() {
					ajax_map_loader_hide(unique_map_id);
				}
			});
		}
	});

	return marker;
}
// This function builds info Window and shows it hiding another
function showInfoWindow(glocation, marker, unique_map_id) {
	// infobox_packed.js -------------------------------------------------------------------------------------------------------------------------------------------
	if("undefined"!=typeof google) eval(function(p,a,c,k,e,r){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--)r[e(c)]=k[c]||e(c);k=[function(e){return r[e]}];e=function(){return'\\w+'};c=1};while(c--)if(k[c])p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c]);return p}('6 8(a){a=a||{};r.s.1P.2j(2,36);2.M=a.1y||"";2.1z=a.1q||J;2.U=a.1H||0;2.G=a.1B||1g r.s.1Y(0,0);2.E=a.Y||1g r.s.2B(0,0);2.X=a.V||t;2.1p=a.1s||"2g";2.1m=a.F||{};2.1G=a.1E||"39";2.N=a.1j||"34://31.r.2W/2Q/2O/2K/1u.2I";3(a.1j===""){2.N=""}2.19=a.1A||1g r.s.1Y(1,1);3(p a.A==="q"){3(p a.18==="q"){a.A=L}v{a.A=!a.18}}2.w=!a.A;2.17=a.1n||J;2.1I=a.2f||"2d";2.15=a.1l||J;2.4=t;2.z=t;2.14=t;2.T=t;2.B=t;2.R=t}8.9=1g r.s.1P();8.9.24=6(){5 i;5 f;5 a;5 d=2;5 c=6(e){e.20=L;3(e.1i){e.1i()}};5 b=6(e){e.2T=J;3(e.1Z){e.1Z()}3(!d.15){c(e)}};3(!2.4){2.4=16.2N("2M");2.1d();3(p 2.M.1v==="q"){2.4.Q=2.I()+2.M}v{2.4.Q=2.I();2.4.1e(2.M)}2.2F()[2.1I].1e(2.4);2.1t();3(2.4.7.C){2.R=L}v{3(2.U!==0&&2.4.Z>2.U){2.4.7.C=2.U;2.4.7.2A="2x";2.R=L}v{a=2.22();2.4.7.C=(2.4.Z-a.13-a.12)+"11";2.R=J}}2.1r(2.1z);3(!2.15){2.B=[];f=["2q","1N","2p","2o","1M","2n","2m","2l","2k"];1o(i=0;i<f.1L;i++){2.B.1K(r.s.u.1c(2.4,f[i],c))}2.B.1K(r.s.u.1c(2.4,"1N",6(e){2.7.1J="2i"}))}2.T=r.s.u.1c(2.4,"2h",b);r.s.u.S(2,"2e")}};8.9.I=6(){5 a="";3(2.N!==""){a="<2c";a+=" 2b=\'"+2.N+"\'";a+=" 2a=12";a+=" 7=\'";a+=" Y: 29;";a+=" 1J: 28;";a+=" 27: "+2.1G+";";a+="\'>"}K a};8.9.1t=6(){5 a;3(2.N!==""){a=2.4.3f;2.z=r.s.u.1c(a,"1M",2.26())}v{2.z=t}};8.9.26=6(){5 a=2;K 6(e){e.20=L;3(e.1i){e.1i()}r.s.u.S(a,"3e");a.1u()}};8.9.1r=6(d){5 m;5 n;5 e=0,H=0;3(!d){m=2.1F();3(m 3d r.s.3c){3(!m.25().3a(2.E)){m.38(2.E)}n=m.25();5 a=m.37();5 h=a.Z;5 f=a.23;5 k=2.G.C;5 l=2.G.1k;5 g=2.4.Z;5 b=2.4.23;5 i=2.19.C;5 j=2.19.1k;5 o=2.21().35(2.E);3(o.x<(-k+i)){e=o.x+k-i}v 3((o.x+g+k+i)>h){e=o.x+g+k+i-h}3(2.17){3(o.y<(-l+j+b)){H=o.y+l-j-b}v 3((o.y+l+j)>f){H=o.y+l+j-f}}v{3(o.y<(-l+j)){H=o.y+l-j}v 3((o.y+b+l+j)>f){H=o.y+b+l+j-f}}3(!(e===0&&H===0)){5 c=m.33();m.32(e,H)}}}};8.9.1d=6(){5 i,F;3(2.4){2.4.30=2.1p;2.4.7.2Z="";F=2.1m;1o(i 2Y F){3(F.2X(i)){2.4.7[i]=F[i]}}3(p 2.4.7.1f!=="q"&&2.4.7.1f!==""){2.4.7.2V="2S(1f="+(2.4.7.1f*2R)+")"}2.4.7.Y="2P";2.4.7.P=\'1b\';3(2.X!==t){2.4.7.V=2.X}}};8.9.22=6(){5 c;5 a={1a:0,1h:0,13:0,12:0};5 b=2.4;3(16.1w&&16.1w.1X){c=b.2L.1w.1X(b,"");3(c){a.1a=D(c.1W,10)||0;a.1h=D(c.1V,10)||0;a.13=D(c.1U,10)||0;a.12=D(c.1T,10)||0}}v 3(16.2J.O){3(b.O){a.1a=D(b.O.1W,10)||0;a.1h=D(b.O.1V,10)||0;a.13=D(b.O.1U,10)||0;a.12=D(b.O.1T,10)||0}}K a};8.9.2H=6(){3(2.4){2.4.2G.2U(2.4);2.4=t}};8.9.1x=6(){2.24();5 a=2.21().2E(2.E);2.4.7.13=(a.x+2.G.C)+"11";3(2.17){2.4.7.1h=-(a.y+2.G.1k)+"11"}v{2.4.7.1a=(a.y+2.G.1k)+"11"}3(2.w){2.4.7.P=\'1b\'}v{2.4.7.P="A"}};8.9.2D=6(a){3(p a.1s!=="q"){2.1p=a.1s;2.1d()}3(p a.F!=="q"){2.1m=a.F;2.1d()}3(p a.1y!=="q"){2.1S(a.1y)}3(p a.1q!=="q"){2.1z=a.1q}3(p a.1H!=="q"){2.U=a.1H}3(p a.1B!=="q"){2.G=a.1B}3(p a.1n!=="q"){2.17=a.1n}3(p a.Y!=="q"){2.1D(a.Y)}3(p a.V!=="q"){2.1R(a.V)}3(p a.1E!=="q"){2.1G=a.1E}3(p a.1j!=="q"){2.N=a.1j}3(p a.1A!=="q"){2.19=a.1A}3(p a.18!=="q"){2.w=a.18}3(p a.A!=="q"){2.w=!a.A}3(p a.1l!=="q"){2.15=a.1l}3(2.4){2.1x()}};8.9.1S=6(a){2.M=a;3(2.4){3(2.z){r.s.u.W(2.z);2.z=t}3(!2.R){2.4.7.C=""}3(p a.1v==="q"){2.4.Q=2.I()+a}v{2.4.Q=2.I();2.4.1e(a)}3(!2.R){2.4.7.C=2.4.Z+"11";3(p a.1v==="q"){2.4.Q=2.I()+a}v{2.4.Q=2.I();2.4.1e(a)}}2.1t()}r.s.u.S(2,"2C")};8.9.1D=6(a){2.E=a;3(2.4){2.1x()}r.s.u.S(2,"1Q")};8.9.1R=6(a){2.X=a;3(2.4){2.4.7.V=a}r.s.u.S(2,"2z")};8.9.2y=6(a){2.w=!a;3(2.4){2.4.7.P=(2.w?"1b":"A")}};8.9.2w=6(){K 2.M};8.9.1C=6(){K 2.E};8.9.2v=6(){K 2.X};8.9.2u=6(){5 a;3((p 2.1F()==="q")||(2.1F()===t)){a=J}v{a=!2.w}K a};8.9.2t=6(){2.w=J;3(2.4){2.4.7.P="A"}};8.9.3b=6(){2.w=L;3(2.4){2.4.7.P="1b"}};8.9.2s=6(c,b){5 a=2;3(b){2.E=b.1C();2.14=r.s.u.2r(b,"1Q",6(){a.1D(2.1C())})}2.1O(c);3(2.4){2.1r()}};8.9.1u=6(){5 i;3(2.z){r.s.u.W(2.z);2.z=t}3(2.B){1o(i=0;i<2.B.1L;i++){r.s.u.W(2.B[i])}2.B=t}3(2.14){r.s.u.W(2.14);2.14=t}3(2.T){r.s.u.W(2.T);2.T=t}2.1O(t)};',62,202,'||this|if|div_|var|function|style|InfoBox|prototype||||||||||||||||typeof|undefined|google|maps|null|event|else|isHidden_|||closeListener_|visible|eventListeners_|width|parseInt|position_|boxStyle|pixelOffset_|yOffset|getCloseBoxImg_|false|return|true|content_|closeBoxURL_|currentStyle|visibility|innerHTML|fixedWidthSet_|trigger|contextListener_|maxWidth_|zIndex|removeListener|zIndex_|position|offsetWidth||px|right|left|moveListener_|enableEventPropagation_|document|alignBottom_|isHidden|infoBoxClearance_|top|hidden|addDomListener|setBoxStyle_|appendChild|opacity|new|bottom|stopPropagation|closeBoxURL|height|enableEventPropagation|boxStyle_|alignBottom|for|boxClass_|disableAutoPan|panBox_|boxClass|addClickHandler_|close|nodeType|defaultView|draw|content|disableAutoPan_|infoBoxClearance|pixelOffset|getPosition|setPosition|closeBoxMargin|getMap|closeBoxMargin_|maxWidth|pane_|cursor|push|length|click|mouseover|setMap|OverlayView|position_changed|setZIndex|setContent|borderRightWidth|borderLeftWidth|borderBottomWidth|borderTopWidth|getComputedStyle|Size|preventDefault|cancelBubble|getProjection|getBoxWidths_|offsetHeight|createInfoBoxDiv_|getBounds|getCloseClickHandler_|margin|pointer|relative|align|src|img|floatPane|domready|pane|infoBox|contextmenu|default|apply|touchmove|touchend|touchstart|dblclick|mouseup|mouseout|mousedown|addListener|open|show|getVisible|getZIndex|getContent|auto|setVisible|zindex_changed|overflow|LatLng|content_changed|setOptions|fromLatLngToDivPixel|getPanes|parentNode|onRemove|gif|documentElement|mapfiles|ownerDocument|div|createElement|en_us|absolute|intl|100|alpha|returnValue|removeChild|filter|com|hasOwnProperty|in|cssText|className|www|panBy|getCenter|http|fromLatLngToContainerPixel|arguments|getDiv|setCenter|2px|contains|hide|Map|instanceof|closeclick|firstChild'.split('|'),0,{}))

	if (glocation.nofollow)
		var nofollow = 'rel="nofollow"';
	else
		var nofollow = '';

	var windowHtml = '<div class="w2dc-map-info-window">';
	windowHtml += '<div class="w2dc-map-info-window-title">';
	if (glocation.listing_url && glocation.show_readmore_button)
		windowHtml += '<a class="w2dc-map-info-window-title-link" href="' + glocation.listing_url + '" ' + nofollow + '>';
	windowHtml += glocation.listing_title;
	if (glocation.listing_url && glocation.show_readmore_button)
		windowHtml += '</a>';
	windowHtml += '</div>';

	if (glocation.listing_logo) {
		windowHtml += '<div class="w2dc-map-info-window-logo">';
		if (glocation.listing_url && glocation.show_readmore_button)
			windowHtml += '<a href="' + glocation.listing_url + '" ' + nofollow + '>';
		windowHtml += '<img width="' + google_maps_objects.infowindow_logo_width + 'px" src="' + glocation.listing_logo + '" />';
		if (glocation.listing_url && glocation.show_readmore_button)
			windowHtml += '</a>';
		windowHtml += '</div>';
	}

	windowHtml += '<div class="w2dc-map-info-window-content w2dc-clearfix">';
	if (glocation.content_fields) {
		for (var i=0; i<glocation.content_fields.length; i++) {
			if (glocation.content_fields[i]) {
				windowHtml += '<div class="w2dc-map-info-window_field">';
				if (google_maps_objects.w2dc_map_content_fields_icons[i])
					windowHtml += '<span class="w2dc-map-field-icon w2dc-fa ' + google_maps_objects.w2dc_map_content_fields_icons[i] + '"></span>';
				windowHtml += glocation.content_fields[i];
				windowHtml += '</div>';
			}
		}
	}
	windowHtml += '</div>';

	if ((glocation.show_summary_button && jQuery("#"+glocation.anchor).length) || (glocation.listing_url && glocation.show_readmore_button)) {
		if (!(glocation.show_summary_button && jQuery("#"+glocation.anchor).length) || !(glocation.listing_url && glocation.show_readmore_button))
			var button_class = 'w2dc-map-info-window-buttons-single';
		else
			var button_class = 'w2dc-map-info-window-buttons';

		windowHtml += '<div class="' + button_class + ' w2dc-clearfix">';
		if (glocation.show_summary_button && jQuery("#"+glocation.anchor).length)
			windowHtml += '<a href="javascript:void(0);" class="w2dc-btn w2dc-btn-primary w2dc-scroll-to-listing" onClick="scrollToListing(&quot;' + glocation.anchor + '&quot;);">' + google_maps_objects.w2dc_map_info_window_button_summary + '</a>';
		if (glocation.listing_url && glocation.show_readmore_button)
			windowHtml += '<a href="' +  glocation.listing_url + '" ' + nofollow + ' class="w2dc-btn w2dc-btn-primary">' + google_maps_objects.w2dc_map_info_window_button_readmore + '</a>';
		windowHtml += '</div>';
	}
	
	var tongue_pos = (parseInt(google_maps_objects.infowindow_width)/2);

	windowHtml += '<div style="position: absolute; left: '+(tongue_pos-10)+'px;"><div style="position: absolute; overflow: hidden; left: -6px; top: -1px; width: 16px; height: 30px;"><div class="w2dc-map-info-window-tongue" style="position: absolute; left: 6px; transform: skewX(22.6deg); transform-origin: 0px 0px 0px;  -webkit-transform: skewX(22.6deg); -webkit-transform-origin: 0px 0px 0px; height: 24px; width: 10px; box-shadow: 0px 1px 6px rgba(0, 0, 0, 0.6);"></div></div><div style="position: absolute; overflow: hidden; top: -1px; left: 10px; width: 16px; height: 30px;"><div class="w2dc-map-info-window-tongue" style="position: absolute; left: 0px; transform: skewX(-22.6deg); transform-origin: 10px 0px 0px; -webkit-transform: skewX(-22.6deg); -webkit-transform-origin: 10px 0px 0px; height: 24px; width: 10px; box-shadow: 0px 1px 6px rgba(0, 0, 0, 0.6);"></div></div></div>';
	windowHtml += '</div>';
            
    var myOptions = {
             content: windowHtml
            ,alignBottom: true
            ,disableAutoPan: false
            ,pixelOffset: new google.maps.Size(-tongue_pos, parseInt(google_maps_objects.infowindow_offset)-24)
            ,zIndex: null
            ,boxStyle: { 
              width: google_maps_objects.infowindow_width+"px"
             }
            ,closeBoxMargin: "10px 5px 2px 2px"
            ,closeBoxURL: "http://www.google.com/intl/en_us/mapfiles/close.gif"
            ,infoBoxClearance: new google.maps.Size(1, 1)
            ,isHidden: false
            ,pane: "floatPane"
            ,enableEventPropagation: false
    };

    // we use global infoWindow, not to close/open it - just to set new content (in order to prevent blinking)
    if (typeof infoWindows[unique_map_id] != 'undefined') {
    	infoWindows[unique_map_id].close();
    	infoWindows[unique_map_id].marker.setAnimation(null);
    }

    infoWindows[unique_map_id] = new InfoBox(myOptions);
    infoWindows[unique_map_id].open(maps[unique_map_id], marker);
    infoWindows[unique_map_id].marker = marker;
    infoWindows[unique_map_id].marker.setAnimation(google.maps.Animation.BOUNCE);
    google.maps.event.addListener(infoWindows[unique_map_id], 'closeclick', function(){
    	infoWindows[unique_map_id].marker.setAnimation(null);
    });
}
(function($) {
	scrollToListing = function(anchor) {
		var scroll_to_anchor = $("#"+anchor);
		if (!scroll_to_anchor.parent().parent().parent().hasClass('w2dc-scrollable-block')) {
			if (scroll_to_anchor.length)
				$('html,body').animate({scrollTop: scroll_to_anchor.offset().top}, 'fast');
		} else {
			if (scroll_to_anchor.length) {
				//scroll_to_anchor.scroll();
				//alert(scroll_to_anchor.position().top);
				scroll_to_anchor.parent().parent().animate({scrollTop: scroll_to_anchor.position().top}, 'slow');
			}
		}
	}
}(jQuery));
function handleDirectionsErrors(status){
   if (status == google.maps.DirectionsStatus.NOT_FOUND)
     alert("No corresponding geographic location could be found for one of the specified addresses. This may be due to the fact that the address is relatively new, or it may be incorrect.");
   else if (status == google.maps.DirectionsStatus.ZERO_RESULTS)
     alert("No route could be found between the origin and destination.");
   else if (status == google.maps.DirectionsStatus.UNKNOWN_ERROR)
     alert("A directions request could not be processed due to a server error. The request may succeed if you try again.");
   else if (status == google.maps.DirectionsStatus.REQUEST_DENIED)
     alert("The webpage is not allowed to use the directions service.");
   else if (status == google.maps.DirectionsStatus.INVALID_REQUEST)
     alert("The provided DirectionsRequest was invalid.");
   else if (status == google.maps.DirectionsStatus.OVER_QUERY_LIMIT)
     alert("The webpage has sent too many requests within the allowed time period.");
   else alert("An unknown error occurred.");
}
function setClusters(enable_clusters, unique_map_id, markers) {
	if (enable_clusters && typeof MarkerClusterer == 'function') {
		var clusterStyles = [];
		
		if (google_maps_objects.global_map_icons_path != '')
			var clusterStyles = [
				{
					url: google_maps_objects.global_map_icons_path + 'clusters/icon_cluster1.png',
					height: 64,
					width: 64
				},
				{
					url: google_maps_objects.global_map_icons_path + 'clusters/icon_cluster2.png',
					height: 74,
					width: 74
				},
				{
					url: google_maps_objects.global_map_icons_path + 'clusters/icon_cluster3.png',
					height: 84,
					width: 84
				},
				{
					url: google_maps_objects.global_map_icons_path + 'clusters/icon_cluster4.png',
					height: 94,
					width: 94
				},
				{
					url: google_maps_objects.global_map_icons_path + 'clusters/icon_cluster5.png',
					height: 104,
					width: 104
				}
			];
		var mcOptions = {
			gridSize: 40,
			styles: clusterStyles
		};
		markerClusters[unique_map_id] = new MarkerClusterer(maps[unique_map_id], markers, mcOptions);
	}
}
function clearOverlays(unique_map_id, remove_shapes) {
	if (typeof markerClusters[unique_map_id] != 'undefined')
		markerClusters[unique_map_id].clearMarkers();

	if (global_markers_array[unique_map_id]) {
		for(var i = 0; i<global_markers_array[unique_map_id].length; i++){
			global_markers_array[unique_map_id][i].setMap(null);
		}
	}
	global_markers_array[unique_map_id] = [];
	global_locations_array[unique_map_id] = [];
	
	if (typeof infoWindows[unique_map_id] != 'undefined')
		infoWindows[unique_map_id].close();

	if (remove_shapes) {
		if (typeof drawCircles[unique_map_id] != 'undefined')
			drawCircles[unique_map_id].setMap(null);
	
		if (typeof polygons[unique_map_id] != 'undefined')
			polygons[unique_map_id].setMap(null);
	}
}
function setZoomCenter(map) {
	var zoom = map.getZoom();
	var center = map.getCenter();
	google.maps.event.trigger(map, 'resize');
	map.setZoom(zoom);
	map.setCenter(center);
}


function w2dc_show_tab(tab) {
	jQuery('.w2dc-listing-tabs li').removeClass('w2dc-active');
	tab.parent().addClass('w2dc-active');
	jQuery('.w2dc-tab-content .w2dc-tab-pane').removeClass('w2dc-in w2dc-active');
	jQuery('.w2dc-tab-content '+tab.data('tab')).addClass('w2dc-in w2dc-active');
	if (tab.data('tab') == '#addresses-tab')
		 for (var key in maps)
			if (typeof maps[key] != 'undefined') {
				setZoomCenter(maps[key]);
			}
}

function geocodeField(field, error_message) {
	if (navigator.geolocation) {
		navigator.geolocation.getCurrentPosition( function(position) {
			var latlng = new google.maps.LatLng(position.coords.latitude, position.coords.longitude);
			var geocoder = new google.maps.Geocoder();
			geocoder.geocode({'latLng': latlng}, function(results, status) {
				if (status == google.maps.GeocoderStatus.OK) {
					if (results[0]) {
						field.val(results[0].formatted_address);
					}
				}
			});
	    }, function() {}, {enableHighAccuracy: true, timeout: 5000, maximumAge: 0});
	} else
		alert(error_message);
}

function ajax_loader_show(msg) {
	(function($) {
		if (!$('#ajax_loader').length > 0) {
			$("body").append('<div id="ajax_loader"><img src="'+js_objects.ajax_loader_url+'"></div>');
		
			if (msg == null)
				msg = 'Loading...';
			$("#ajax_loader").dialog({
				title: msg,
				resizable: false,
				draggable: false,
				autoOpen: false,
				modal: true,
				closeOnEscape: false,
				width: 250,
				minHeight: 30,
				open: function(event, ui) {$(".ui-dialog-titlebar-close").hide();}
			}).dialog('open');
		}
	}(jQuery));
}

function ajax_loader_hide() {
	(function($) {
		$("#ajax_loader").dialog('close').remove();
		$(".ui-dialog-titlebar-close").show();
	}(jQuery));
}

function make_slug(name) {
	name = name.toLowerCase();
	
	var defaultDiacriticsRemovalMap = [
	                                   {'base':'A', 'letters':/[\u0041\u24B6\uFF21\u00C0\u00C1\u00C2\u1EA6\u1EA4\u1EAA\u1EA8\u00C3\u0100\u0102\u1EB0\u1EAE\u1EB4\u1EB2\u0226\u01E0\u00C4\u01DE\u1EA2\u00C5\u01FA\u01CD\u0200\u0202\u1EA0\u1EAC\u1EB6\u1E00\u0104\u023A\u2C6F]/g},
	                                   {'base':'AA','letters':/[\uA732]/g},
	                                   {'base':'AE','letters':/[\u00C6\u01FC\u01E2]/g},
	                                   {'base':'AO','letters':/[\uA734]/g},
	                                   {'base':'AU','letters':/[\uA736]/g},
	                                   {'base':'AV','letters':/[\uA738\uA73A]/g},
	                                   {'base':'AY','letters':/[\uA73C]/g},
	                                   {'base':'B', 'letters':/[\u0042\u24B7\uFF22\u1E02\u1E04\u1E06\u0243\u0182\u0181]/g},
	                                   {'base':'C', 'letters':/[\u0043\u24B8\uFF23\u0106\u0108\u010A\u010C\u00C7\u1E08\u0187\u023B\uA73E]/g},
	                                   {'base':'D', 'letters':/[\u0044\u24B9\uFF24\u1E0A\u010E\u1E0C\u1E10\u1E12\u1E0E\u0110\u018B\u018A\u0189\uA779]/g},
	                                   {'base':'DZ','letters':/[\u01F1\u01C4]/g},
	                                   {'base':'Dz','letters':/[\u01F2\u01C5]/g},
	                                   {'base':'E', 'letters':/[\u0045\u24BA\uFF25\u00C8\u00C9\u00CA\u1EC0\u1EBE\u1EC4\u1EC2\u1EBC\u0112\u1E14\u1E16\u0114\u0116\u00CB\u1EBA\u011A\u0204\u0206\u1EB8\u1EC6\u0228\u1E1C\u0118\u1E18\u1E1A\u0190\u018E]/g},
	                                   {'base':'F', 'letters':/[\u0046\u24BB\uFF26\u1E1E\u0191\uA77B]/g},
	                                   {'base':'G', 'letters':/[\u0047\u24BC\uFF27\u01F4\u011C\u1E20\u011E\u0120\u01E6\u0122\u01E4\u0193\uA7A0\uA77D\uA77E]/g},
	                                   {'base':'H', 'letters':/[\u0048\u24BD\uFF28\u0124\u1E22\u1E26\u021E\u1E24\u1E28\u1E2A\u0126\u2C67\u2C75\uA78D]/g},
	                                   {'base':'I', 'letters':/[\u0049\u24BE\uFF29\u00CC\u00CD\u00CE\u0128\u012A\u012C\u0130\u00CF\u1E2E\u1EC8\u01CF\u0208\u020A\u1ECA\u012E\u1E2C\u0197]/g},
	                                   {'base':'J', 'letters':/[\u004A\u24BF\uFF2A\u0134\u0248]/g},
	                                   {'base':'K', 'letters':/[\u004B\u24C0\uFF2B\u1E30\u01E8\u1E32\u0136\u1E34\u0198\u2C69\uA740\uA742\uA744\uA7A2]/g},
	                                   {'base':'L', 'letters':/[\u004C\u24C1\uFF2C\u013F\u0139\u013D\u1E36\u1E38\u013B\u1E3C\u1E3A\u0141\u023D\u2C62\u2C60\uA748\uA746\uA780]/g},
	                                   {'base':'LJ','letters':/[\u01C7]/g},
	                                   {'base':'Lj','letters':/[\u01C8]/g},
	                                   {'base':'M', 'letters':/[\u004D\u24C2\uFF2D\u1E3E\u1E40\u1E42\u2C6E\u019C]/g},
	                                   {'base':'N', 'letters':/[\u004E\u24C3\uFF2E\u01F8\u0143\u00D1\u1E44\u0147\u1E46\u0145\u1E4A\u1E48\u0220\u019D\uA790\uA7A4]/g},
	                                   {'base':'NJ','letters':/[\u01CA]/g},
	                                   {'base':'Nj','letters':/[\u01CB]/g},
	                                   {'base':'O', 'letters':/[\u004F\u24C4\uFF2F\u00D2\u00D3\u00D4\u1ED2\u1ED0\u1ED6\u1ED4\u00D5\u1E4C\u022C\u1E4E\u014C\u1E50\u1E52\u014E\u022E\u0230\u00D6\u022A\u1ECE\u0150\u01D1\u020C\u020E\u01A0\u1EDC\u1EDA\u1EE0\u1EDE\u1EE2\u1ECC\u1ED8\u01EA\u01EC\u00D8\u01FE\u0186\u019F\uA74A\uA74C]/g},
	                                   {'base':'OI','letters':/[\u01A2]/g},
	                                   {'base':'OO','letters':/[\uA74E]/g},
	                                   {'base':'OU','letters':/[\u0222]/g},
	                                   {'base':'P', 'letters':/[\u0050\u24C5\uFF30\u1E54\u1E56\u01A4\u2C63\uA750\uA752\uA754]/g},
	                                   {'base':'Q', 'letters':/[\u0051\u24C6\uFF31\uA756\uA758\u024A]/g},
	                                   {'base':'R', 'letters':/[\u0052\u24C7\uFF32\u0154\u1E58\u0158\u0210\u0212\u1E5A\u1E5C\u0156\u1E5E\u024C\u2C64\uA75A\uA7A6\uA782]/g},
	                                   {'base':'S', 'letters':/[\u0053\u24C8\uFF33\u1E9E\u015A\u1E64\u015C\u1E60\u0160\u1E66\u1E62\u1E68\u0218\u015E\u2C7E\uA7A8\uA784]/g},
	                                   {'base':'T', 'letters':/[\u0054\u24C9\uFF34\u1E6A\u0164\u1E6C\u021A\u0162\u1E70\u1E6E\u0166\u01AC\u01AE\u023E\uA786]/g},
	                                   {'base':'TZ','letters':/[\uA728]/g},
	                                   {'base':'U', 'letters':/[\u0055\u24CA\uFF35\u00D9\u00DA\u00DB\u0168\u1E78\u016A\u1E7A\u016C\u00DC\u01DB\u01D7\u01D5\u01D9\u1EE6\u016E\u0170\u01D3\u0214\u0216\u01AF\u1EEA\u1EE8\u1EEE\u1EEC\u1EF0\u1EE4\u1E72\u0172\u1E76\u1E74\u0244]/g},
	                                   {'base':'V', 'letters':/[\u0056\u24CB\uFF36\u1E7C\u1E7E\u01B2\uA75E\u0245]/g},
	                                   {'base':'VY','letters':/[\uA760]/g},
	                                   {'base':'W', 'letters':/[\u0057\u24CC\uFF37\u1E80\u1E82\u0174\u1E86\u1E84\u1E88\u2C72]/g},
	                                   {'base':'X', 'letters':/[\u0058\u24CD\uFF38\u1E8A\u1E8C]/g},
	                                   {'base':'Y', 'letters':/[\u0059\u24CE\uFF39\u1EF2\u00DD\u0176\u1EF8\u0232\u1E8E\u0178\u1EF6\u1EF4\u01B3\u024E\u1EFE]/g},
	                                   {'base':'Z', 'letters':/[\u005A\u24CF\uFF3A\u0179\u1E90\u017B\u017D\u1E92\u1E94\u01B5\u0224\u2C7F\u2C6B\uA762]/g},
	                                   {'base':'a', 'letters':/[\u0061\u24D0\uFF41\u1E9A\u00E0\u00E1\u00E2\u1EA7\u1EA5\u1EAB\u1EA9\u00E3\u0101\u0103\u1EB1\u1EAF\u1EB5\u1EB3\u0227\u01E1\u00E4\u01DF\u1EA3\u00E5\u01FB\u01CE\u0201\u0203\u1EA1\u1EAD\u1EB7\u1E01\u0105\u2C65\u0250]/g},
	                                   {'base':'aa','letters':/[\uA733]/g},
	                                   {'base':'ae','letters':/[\u00E6\u01FD\u01E3]/g},
	                                   {'base':'ao','letters':/[\uA735]/g},
	                                   {'base':'au','letters':/[\uA737]/g},
	                                   {'base':'av','letters':/[\uA739\uA73B]/g},
	                                   {'base':'ay','letters':/[\uA73D]/g},
	                                   {'base':'b', 'letters':/[\u0062\u24D1\uFF42\u1E03\u1E05\u1E07\u0180\u0183\u0253]/g},
	                                   {'base':'c', 'letters':/[\u0063\u24D2\uFF43\u0107\u0109\u010B\u010D\u00E7\u1E09\u0188\u023C\uA73F\u2184]/g},
	                                   {'base':'d', 'letters':/[\u0064\u24D3\uFF44\u1E0B\u010F\u1E0D\u1E11\u1E13\u1E0F\u0111\u018C\u0256\u0257\uA77A]/g},
	                                   {'base':'dz','letters':/[\u01F3\u01C6]/g},
	                                   {'base':'e', 'letters':/[\u0065\u24D4\uFF45\u00E8\u00E9\u00EA\u1EC1\u1EBF\u1EC5\u1EC3\u1EBD\u0113\u1E15\u1E17\u0115\u0117\u00EB\u1EBB\u011B\u0205\u0207\u1EB9\u1EC7\u0229\u1E1D\u0119\u1E19\u1E1B\u0247\u025B\u01DD]/g},
	                                   {'base':'f', 'letters':/[\u0066\u24D5\uFF46\u1E1F\u0192\uA77C]/g},
	                                   {'base':'g', 'letters':/[\u0067\u24D6\uFF47\u01F5\u011D\u1E21\u011F\u0121\u01E7\u0123\u01E5\u0260\uA7A1\u1D79\uA77F]/g},
	                                   {'base':'h', 'letters':/[\u0068\u24D7\uFF48\u0125\u1E23\u1E27\u021F\u1E25\u1E29\u1E2B\u1E96\u0127\u2C68\u2C76\u0265]/g},
	                                   {'base':'hv','letters':/[\u0195]/g},
	                                   {'base':'i', 'letters':/[\u0069\u24D8\uFF49\u00EC\u00ED\u00EE\u0129\u012B\u012D\u00EF\u1E2F\u1EC9\u01D0\u0209\u020B\u1ECB\u012F\u1E2D\u0268\u0131]/g},
	                                   {'base':'j', 'letters':/[\u006A\u24D9\uFF4A\u0135\u01F0\u0249]/g},
	                                   {'base':'k', 'letters':/[\u006B\u24DA\uFF4B\u1E31\u01E9\u1E33\u0137\u1E35\u0199\u2C6A\uA741\uA743\uA745\uA7A3]/g},
	                                   {'base':'l', 'letters':/[\u006C\u24DB\uFF4C\u0140\u013A\u013E\u1E37\u1E39\u013C\u1E3D\u1E3B\u017F\u0142\u019A\u026B\u2C61\uA749\uA781\uA747]/g},
	                                   {'base':'lj','letters':/[\u01C9]/g},
	                                   {'base':'m', 'letters':/[\u006D\u24DC\uFF4D\u1E3F\u1E41\u1E43\u0271\u026F]/g},
	                                   {'base':'n', 'letters':/[\u006E\u24DD\uFF4E\u01F9\u0144\u00F1\u1E45\u0148\u1E47\u0146\u1E4B\u1E49\u019E\u0272\u0149\uA791\uA7A5]/g},
	                                   {'base':'nj','letters':/[\u01CC]/g},
	                                   {'base':'o', 'letters':/[\u006F\u24DE\uFF4F\u00F2\u00F3\u00F4\u1ED3\u1ED1\u1ED7\u1ED5\u00F5\u1E4D\u022D\u1E4F\u014D\u1E51\u1E53\u014F\u022F\u0231\u00F6\u022B\u1ECF\u0151\u01D2\u020D\u020F\u01A1\u1EDD\u1EDB\u1EE1\u1EDF\u1EE3\u1ECD\u1ED9\u01EB\u01ED\u00F8\u01FF\u0254\uA74B\uA74D\u0275]/g},
	                                   {'base':'oi','letters':/[\u01A3]/g},
	                                   {'base':'ou','letters':/[\u0223]/g},
	                                   {'base':'oo','letters':/[\uA74F]/g},
	                                   {'base':'p','letters':/[\u0070\u24DF\uFF50\u1E55\u1E57\u01A5\u1D7D\uA751\uA753\uA755]/g},
	                                   {'base':'q','letters':/[\u0071\u24E0\uFF51\u024B\uA757\uA759]/g},
	                                   {'base':'r','letters':/[\u0072\u24E1\uFF52\u0155\u1E59\u0159\u0211\u0213\u1E5B\u1E5D\u0157\u1E5F\u024D\u027D\uA75B\uA7A7\uA783]/g},
	                                   {'base':'s','letters':/[\u0073\u24E2\uFF53\u00DF\u015B\u1E65\u015D\u1E61\u0161\u1E67\u1E63\u1E69\u0219\u015F\u023F\uA7A9\uA785\u1E9B]/g},
	                                   {'base':'t','letters':/[\u0074\u24E3\uFF54\u1E6B\u1E97\u0165\u1E6D\u021B\u0163\u1E71\u1E6F\u0167\u01AD\u0288\u2C66\uA787]/g},
	                                   {'base':'tz','letters':/[\uA729]/g},
	                                   {'base':'u','letters':/[\u0075\u24E4\uFF55\u00F9\u00FA\u00FB\u0169\u1E79\u016B\u1E7B\u016D\u00FC\u01DC\u01D8\u01D6\u01DA\u1EE7\u016F\u0171\u01D4\u0215\u0217\u01B0\u1EEB\u1EE9\u1EEF\u1EED\u1EF1\u1EE5\u1E73\u0173\u1E77\u1E75\u0289]/g},
	                                   {'base':'v','letters':/[\u0076\u24E5\uFF56\u1E7D\u1E7F\u028B\uA75F\u028C]/g},
	                                   {'base':'vy','letters':/[\uA761]/g},
	                                   {'base':'w','letters':/[\u0077\u24E6\uFF57\u1E81\u1E83\u0175\u1E87\u1E85\u1E98\u1E89\u2C73]/g},
	                                   {'base':'x','letters':/[\u0078\u24E7\uFF58\u1E8B\u1E8D]/g},
	                                   {'base':'y','letters':/[\u0079\u24E8\uFF59\u1EF3\u00FD\u0177\u1EF9\u0233\u1E8F\u00FF\u1EF7\u1E99\u1EF5\u01B4\u024F\u1EFF]/g},
	                                   {'base':'z','letters':/[\u007A\u24E9\uFF5A\u017A\u1E91\u017C\u017E\u1E93\u1E95\u01B6\u0225\u0240\u2C6C\uA763]/g}
	                               ];
	for(var i=0; i<defaultDiacriticsRemovalMap.length; i++)
		name = name.replace(defaultDiacriticsRemovalMap[i].letters, defaultDiacriticsRemovalMap[i].base);

	//change spaces and other characters by '_'
	name = name.replace(/\W/gi, "_");
	// remove double '_'
	name = name.replace(/(\_)\1+/gi, "_");
	
	return name;
}

function in_array(val, arr) 
{
	for (var i = 0; i < arr.length; i++) {
		if (arr[i] == val)
			return i;
	}
	return false;
}



/* HTML5 Placeholder jQuery Plugin - v2.1.3 -------------------------------------------------------------------------------------------------------------------------------------------
 * Copyright (c)2015 Mathias Bynens
 * 2015-09-23
 */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):a("object"==typeof module&&module.exports?require("jquery"):jQuery)}(function(a){function b(b){var c={},d=/^jQuery\d+$/;return a.each(b.attributes,function(a,b){b.specified&&!d.test(b.name)&&(c[b.name]=b.value)}),c}function c(b,c){var d=this,f=a(d);if(d.value===f.attr("placeholder")&&f.hasClass(m.customClass))if(d.value="",f.removeClass(m.customClass),f.data("placeholder-password")){if(f=f.hide().nextAll('input[type="password"]:first').show().attr("id",f.removeAttr("id").data("placeholder-id")),b===!0)return f[0].value=c,c;f.focus()}else d==e()&&d.select()}function d(d){var e,f=this,g=a(f),h=f.id;if(d&&"blur"===d.type){if(g.hasClass(m.customClass))return;if("password"===f.type&&(e=g.prevAll('input[type="text"]:first'),e.length>0&&e.is(":visible")))return}if(""===f.value){if("password"===f.type){if(!g.data("placeholder-textinput")){try{e=g.clone().prop({type:"text"})}catch(i){e=a("<input>").attr(a.extend(b(this),{type:"text"}))}e.removeAttr("name").data({"placeholder-enabled":!0,"placeholder-password":g,"placeholder-id":h}).bind("focus.placeholder",c),g.data({"placeholder-textinput":e,"placeholder-id":h}).before(e)}f.value="",g=g.removeAttr("id").hide().prevAll('input[type="text"]:first').attr("id",g.data("placeholder-id")).show()}else{var j=g.data("placeholder-password");j&&(j[0].value="",g.attr("id",g.data("placeholder-id")).show().nextAll('input[type="password"]:last').hide().removeAttr("id"))}g.addClass(m.customClass),g[0].value=g.attr("placeholder")}else g.removeClass(m.customClass)}function e(){try{return document.activeElement}catch(a){}}var f,g,h="[object OperaMini]"===Object.prototype.toString.call(window.operamini),i="placeholder"in document.createElement("input")&&!h,j="placeholder"in document.createElement("textarea")&&!h,k=a.valHooks,l=a.propHooks,m={};i&&j?(g=a.fn.placeholder=function(){return this},g.input=!0,g.textarea=!0):(g=a.fn.placeholder=function(b){var e={customClass:"placeholder"};return m=a.extend({},e,b),this.filter((i?"textarea":":input")+"[placeholder]").not("."+m.customClass).bind({"focus.placeholder":c,"blur.placeholder":d}).data("placeholder-enabled",!0).trigger("blur.placeholder")},g.input=i,g.textarea=j,f={get:function(b){var c=a(b),d=c.data("placeholder-password");return d?d[0].value:c.data("placeholder-enabled")&&c.hasClass(m.customClass)?"":b.value},set:function(b,f){var g,h,i=a(b);return""!==f&&(g=i.data("placeholder-textinput"),h=i.data("placeholder-password"),g?(c.call(g[0],!0,f)||(b.value=f),g[0].value=f):h&&(c.call(b,!0,f)||(h[0].value=f),b.value=f)),i.data("placeholder-enabled")?(""===f?(b.value=f,b!=e()&&d.call(b)):(i.hasClass(m.customClass)&&c.call(b),b.value=f),i):(b.value=f,i)}},i||(k.input=f,l.value=f),j||(k.textarea=f,l.value=f),a(function(){a(document).delegate("form","submit.placeholder",function(){var b=a("."+m.customClass,this).each(function(){c.call(this,!0,"")});setTimeout(function(){b.each(d)},10)})}),a(window).bind("beforeunload.placeholder",function(){a("."+m.customClass).each(function(){this.value=""})}))});

jQuery(function() {
	jQuery('input, textarea').placeholder();
});



/*!
 * jQuery Mousewheel 3.1.13 -------------------------------------------------------------------------------------------------------------------------------------------
 *
 * Copyright 2015 jQuery Foundation and other contributors
 * Released under the MIT license.
 * http://jquery.org/license
 */
!function(a){"function"==typeof define&&define.amd?define(["jquery"],a):"object"==typeof exports?module.exports=a:a(jQuery)}(function(a){function b(b){var g=b||window.event,h=i.call(arguments,1),j=0,l=0,m=0,n=0,o=0,p=0;if(b=a.event.fix(g),b.type="mousewheel","detail"in g&&(m=-1*g.detail),"wheelDelta"in g&&(m=g.wheelDelta),"wheelDeltaY"in g&&(m=g.wheelDeltaY),"wheelDeltaX"in g&&(l=-1*g.wheelDeltaX),"axis"in g&&g.axis===g.HORIZONTAL_AXIS&&(l=-1*m,m=0),j=0===m?l:m,"deltaY"in g&&(m=-1*g.deltaY,j=m),"deltaX"in g&&(l=g.deltaX,0===m&&(j=-1*l)),0!==m||0!==l){if(1===g.deltaMode){var q=a.data(this,"mousewheel-line-height");j*=q,m*=q,l*=q}else if(2===g.deltaMode){var r=a.data(this,"mousewheel-page-height");j*=r,m*=r,l*=r}if(n=Math.max(Math.abs(m),Math.abs(l)),(!f||f>n)&&(f=n,d(g,n)&&(f/=40)),d(g,n)&&(j/=40,l/=40,m/=40),j=Math[j>=1?"floor":"ceil"](j/f),l=Math[l>=1?"floor":"ceil"](l/f),m=Math[m>=1?"floor":"ceil"](m/f),k.settings.normalizeOffset&&this.getBoundingClientRect){var s=this.getBoundingClientRect();o=b.clientX-s.left,p=b.clientY-s.top}return b.deltaX=l,b.deltaY=m,b.deltaFactor=f,b.offsetX=o,b.offsetY=p,b.deltaMode=0,h.unshift(b,j,l,m),e&&clearTimeout(e),e=setTimeout(c,200),(a.event.dispatch||a.event.handle).apply(this,h)}}function c(){f=null}function d(a,b){return k.settings.adjustOldDeltas&&"mousewheel"===a.type&&b%120===0}var e,f,g=["wheel","mousewheel","DOMMouseScroll","MozMousePixelScroll"],h="onwheel"in document||document.documentMode>=9?["wheel"]:["mousewheel","DomMouseScroll","MozMousePixelScroll"],i=Array.prototype.slice;if(a.event.fixHooks)for(var j=g.length;j;)a.event.fixHooks[g[--j]]=a.event.mouseHooks;var k=a.event.special.mousewheel={version:"3.1.12",setup:function(){if(this.addEventListener)for(var c=h.length;c;)this.addEventListener(h[--c],b,!1);else this.onmousewheel=b;a.data(this,"mousewheel-line-height",k.getLineHeight(this)),a.data(this,"mousewheel-page-height",k.getPageHeight(this))},teardown:function(){if(this.removeEventListener)for(var c=h.length;c;)this.removeEventListener(h[--c],b,!1);else this.onmousewheel=null;a.removeData(this,"mousewheel-line-height"),a.removeData(this,"mousewheel-page-height")},getLineHeight:function(b){var c=a(b),d=c["offsetParent"in a.fn?"offsetParent":"parent"]();return d.length||(d=a("body")),parseInt(d.css("fontSize"),10)||parseInt(c.css("fontSize"),10)||16},getPageHeight:function(b){return a(b).height()},settings:{adjustOldDeltas:!0,normalizeOffset:!0}};a.fn.extend({mousewheel:function(a){return a?this.bind("mousewheel",a):this.trigger("mousewheel")},unmousewheel:function(a){return this.unbind("mousewheel",a)}})});


// tax_dropdowns.js -------------------------------------------------------------------------------------------------------------------------------------------
(function($) {
	$(document).on('change', '.w2dc-tax-dropdowns-wrap select', function() {
		var select_box = $(this).attr('id').split('_');
		var parent = $(this).val();
		var current_level = select_box[1];
		var uID = select_box[2];

		var divclass = jQuery(this).parents('.w2dc-tax-dropdowns-wrap').attr('class').split(' ');
		var tax = divclass[0];
		var count = divclass[1];

		update_tax(parent, tax, current_level, count, uID);
	});

	function update_tax(parent, tax, current_level, count, uID){
		var current_level = parseInt(current_level);
		var next_level = current_level + 1;
		var prev_level = current_level - 1;
		var selects_length = $('#w2dc-tax-dropdowns-wrap-'+uID+' select').length;
		
		if (parent)
			$('#selected_tax\\['+uID+'\\]').val(parent).trigger('change');
		else if (current_level > 1)
			$('#selected_tax\\['+uID+'\\]').val($('#chainlist_'+prev_level+'_'+uID).val()).trigger('change');
		else
			$('#selected_tax\\['+uID+'\\]').val(0).trigger('change');

		for (var i=next_level; i<=selects_length; i++)
			$('#wrap_chainlist_'+i+'_'+uID).remove();
		
		if (parent) {
			var labels_source = js_objects['tax_dropdowns_'+uID][uID];

			if (labels_source.labels[current_level] != undefined)
				var label = labels_source.labels[current_level];
			else
				var label = '';
			if (labels_source.titles[current_level] != undefined)
				var title = labels_source.titles[current_level];
			else
				var title = '';

			$('#chainlist_'+current_level+'_'+uID).addClass('w2dc-ajax-loading').attr('disabled', 'disabled');
			$.post(
				js_objects.ajaxurl,
				{'action': 'tax_dropdowns_hook', 'parentid': parent, 'next_level': next_level, 'tax': tax, 'count': count, 'label': label, 'title': title, 'uID': uID},
				function(response_from_the_action_function){
					if (response_from_the_action_function != 0)
						$('#w2dc-tax-dropdowns-wrap-'+uID).append(response_from_the_action_function);

					$('#chainlist_'+current_level+'_'+uID).removeClass('w2dc-ajax-loading').removeAttr('disabled');
				}
			);
		}
	}
	
	function first(p){for(var i in p)return p[i];}
}(jQuery));



// jquery.coo_kie.js -------------------------------------------------------------------------------------------------------------------------------------------
jQuery.cookie=function(e,i,o){if("undefined"==typeof i){var n=null;if(document.cookie&&""!=document.cookie)for(var r=document.cookie.split(";"),t=0;t<r.length;t++){var p=jQuery.trim(r[t]);if(p.substring(0,e.length+1)==e+"="){n=decodeURIComponent(p.substring(e.length+1));break}}return n}o=o||{},null===i&&(i="",o.expires=-1);var u="";if(o.expires&&("number"==typeof o.expires||o.expires.toUTCString)){var s;"number"==typeof o.expires?(s=new Date,s.setTime(s.getTime()+24*o.expires*60*60*1e3)):s=o.expires,u="; expires="+s.toUTCString()}var a=o.path?"; path="+o.path:"",c=o.domain?"; domain="+o.domain:"",m=o.secure?"; secure":"";document.cookie=[e,"=",encodeURIComponent(i),u,a,c,m].join("")};



// google_maps_clasterer.js -------------------------------------------------------------------------------------------------------------------------------------------
(function(){var d=null;function e(a){return function(b){this[a]=b}}function h(a){return function(){return this[a]}}var j;
function k(a,b,c){this.extend(k,google.maps.OverlayView);this.c=a;this.a=[];this.f=[];this.ca=[53,56,66,78,90];this.j=[];this.A=!1;c=c||{};this.g=c.gridSize||60;this.l=c.minimumClusterSize||2;this.J=c.maxZoom||d;this.j=c.styles||[];this.X=c.imagePath||this.Q;this.W=c.imageExtension||this.P;this.O=!0;if(c.zoomOnClick!=void 0)this.O=c.zoomOnClick;this.r=!1;if(c.averageCenter!=void 0)this.r=c.averageCenter;l(this);this.setMap(a);this.K=this.c.getZoom();var f=this;google.maps.event.addListener(this.c,
"zoom_changed",function(){var a=f.c.getZoom();if(f.K!=a)f.K=a,f.m()});google.maps.event.addListener(this.c,"idle",function(){f.i()});b&&b.length&&this.C(b,!1)}j=k.prototype;j.Q="http://google-maps-utility-library-v3.googlecode.com/svn/trunk/markerclusterer/images/m";j.P="png";j.extend=function(a,b){return function(a){for(var b in a.prototype)this.prototype[b]=a.prototype[b];return this}.apply(a,[b])};j.onAdd=function(){if(!this.A)this.A=!0,n(this)};j.draw=function(){};
function l(a){if(!a.j.length)for(var b=0,c;c=a.ca[b];b++)a.j.push({url:a.X+(b+1)+"."+a.W,height:c,width:c})}j.S=function(){for(var a=this.o(),b=new google.maps.LatLngBounds,c=0,f;f=a[c];c++)b.extend(f.getPosition());this.c.fitBounds(b)};j.z=h("j");j.o=h("a");j.V=function(){return this.a.length};j.ba=e("J");j.I=h("J");j.G=function(a,b){for(var c=0,f=a.length,g=f;g!==0;)g=parseInt(g/10,10),c++;c=Math.min(c,b);return{text:f,index:c}};j.$=e("G");j.H=h("G");
j.C=function(a,b){for(var c=0,f;f=a[c];c++)q(this,f);b||this.i()};function q(a,b){b.s=!1;b.draggable&&google.maps.event.addListener(b,"dragend",function(){b.s=!1;a.L()});a.a.push(b)}j.q=function(a,b){q(this,a);b||this.i()};function r(a,b){var c=-1;if(a.a.indexOf)c=a.a.indexOf(b);else for(var f=0,g;g=a.a[f];f++)if(g==b){c=f;break}if(c==-1)return!1;b.setMap(d);a.a.splice(c,1);return!0}j.Y=function(a,b){var c=r(this,a);return!b&&c?(this.m(),this.i(),!0):!1};
j.Z=function(a,b){for(var c=!1,f=0,g;g=a[f];f++)g=r(this,g),c=c||g;if(!b&&c)return this.m(),this.i(),!0};j.U=function(){return this.f.length};j.getMap=h("c");j.setMap=e("c");j.w=h("g");j.aa=e("g");
j.v=function(a){var b=this.getProjection(),c=new google.maps.LatLng(a.getNorthEast().lat(),a.getNorthEast().lng()),f=new google.maps.LatLng(a.getSouthWest().lat(),a.getSouthWest().lng()),c=b.fromLatLngToDivPixel(c);c.x+=this.g;c.y-=this.g;f=b.fromLatLngToDivPixel(f);f.x-=this.g;f.y+=this.g;c=b.fromDivPixelToLatLng(c);b=b.fromDivPixelToLatLng(f);a.extend(c);a.extend(b);return a};j.R=function(){this.m(!0);this.a=[]};
j.m=function(a){for(var b=0,c;c=this.f[b];b++)c.remove();for(b=0;c=this.a[b];b++)c.s=!1,a&&c.setMap(d);this.f=[]};j.L=function(){var a=this.f.slice();this.f.length=0;this.m();this.i();window.setTimeout(function(){for(var b=0,c;c=a[b];b++)c.remove()},0)};j.i=function(){n(this)};
function n(a){if(a.A)for(var b=a.v(new google.maps.LatLngBounds(a.c.getBounds().getSouthWest(),a.c.getBounds().getNorthEast())),c=0,f;f=a.a[c];c++)if(!f.s&&b.contains(f.getPosition())){for(var g=a,u=4E4,o=d,v=0,m=void 0;m=g.f[v];v++){var i=m.getCenter();if(i){var p=f.getPosition();if(!i||!p)i=0;else var w=(p.lat()-i.lat())*Math.PI/180,x=(p.lng()-i.lng())*Math.PI/180,i=Math.sin(w/2)*Math.sin(w/2)+Math.cos(i.lat()*Math.PI/180)*Math.cos(p.lat()*Math.PI/180)*Math.sin(x/2)*Math.sin(x/2),i=6371*2*Math.atan2(Math.sqrt(i),
Math.sqrt(1-i));i<u&&(u=i,o=m)}}o&&o.F.contains(f.getPosition())?o.q(f):(m=new s(g),m.q(f),g.f.push(m))}}function s(a){this.k=a;this.c=a.getMap();this.g=a.w();this.l=a.l;this.r=a.r;this.d=d;this.a=[];this.F=d;this.n=new t(this,a.z(),a.w())}j=s.prototype;
j.q=function(a){var b;a:if(this.a.indexOf)b=this.a.indexOf(a)!=-1;else{b=0;for(var c;c=this.a[b];b++)if(c==a){b=!0;break a}b=!1}if(b)return!1;if(this.d){if(this.r)c=this.a.length+1,b=(this.d.lat()*(c-1)+a.getPosition().lat())/c,c=(this.d.lng()*(c-1)+a.getPosition().lng())/c,this.d=new google.maps.LatLng(b,c),y(this)}else this.d=a.getPosition(),y(this);a.s=!0;this.a.push(a);b=this.a.length;b<this.l&&a.getMap()!=this.c&&a.setMap(this.c);if(b==this.l)for(c=0;c<b;c++)this.a[c].setMap(d);b>=this.l&&a.setMap(d);
a=this.c.getZoom();if((b=this.k.I())&&a>b)for(a=0;b=this.a[a];a++)b.setMap(this.c);else if(this.a.length<this.l)z(this.n);else{b=this.k.H()(this.a,this.k.z().length);this.n.setCenter(this.d);a=this.n;a.B=b;a.ga=b.text;a.ea=b.index;if(a.b)a.b.innerHTML=b.text;b=Math.max(0,a.B.index-1);b=Math.min(a.j.length-1,b);b=a.j[b];a.da=b.url;a.h=b.height;a.p=b.width;a.M=b.textColor;a.e=b.anchor;a.N=b.textSize;a.D=b.backgroundPosition;this.n.show()}return!0};
j.getBounds=function(){for(var a=new google.maps.LatLngBounds(this.d,this.d),b=this.o(),c=0,f;f=b[c];c++)a.extend(f.getPosition());return a};j.remove=function(){this.n.remove();this.a.length=0;delete this.a};j.T=function(){return this.a.length};j.o=h("a");j.getCenter=h("d");function y(a){a.F=a.k.v(new google.maps.LatLngBounds(a.d,a.d))}j.getMap=h("c");
function t(a,b,c){a.k.extend(t,google.maps.OverlayView);this.j=b;this.fa=c||0;this.u=a;this.d=d;this.c=a.getMap();this.B=this.b=d;this.t=!1;this.setMap(this.c)}j=t.prototype;
j.onAdd=function(){this.b=document.createElement("DIV");if(this.t)this.b.style.cssText=A(this,B(this,this.d)),this.b.innerHTML=this.B.text;this.getPanes().overlayMouseTarget.appendChild(this.b);var a=this;google.maps.event.addDomListener(this.b,"click",function(){var b=a.u.k;google.maps.event.trigger(b,"clusterclick",a.u);b.O&&a.c.fitBounds(a.u.getBounds())})};function B(a,b){var c=a.getProjection().fromLatLngToDivPixel(b);c.x-=parseInt(a.p/2,10);c.y-=parseInt(a.h/2,10);return c}
j.draw=function(){if(this.t){var a=B(this,this.d);this.b.style.top=a.y+"px";this.b.style.left=a.x+"px"}};function z(a){if(a.b)a.b.style.display="none";a.t=!1}j.show=function(){if(this.b)this.b.style.cssText=A(this,B(this,this.d)),this.b.style.display="";this.t=!0};j.remove=function(){this.setMap(d)};j.onRemove=function(){if(this.b&&this.b.parentNode)z(this),this.b.parentNode.removeChild(this.b),this.b=d};j.setCenter=e("d");
function A(a,b){var c=[];c.push("background-image:url("+a.da+");");c.push("background-position:"+(a.D?a.D:"0 0")+";");typeof a.e==="object"?(typeof a.e[0]==="number"&&a.e[0]>0&&a.e[0]<a.h?c.push("height:"+(a.h-a.e[0])+"px; padding-top:"+a.e[0]+"px;"):c.push("height:"+a.h+"px; line-height:"+a.h+"px;"),typeof a.e[1]==="number"&&a.e[1]>0&&a.e[1]<a.p?c.push("width:"+(a.p-a.e[1])+"px; padding-left:"+a.e[1]+"px;"):c.push("width:"+a.p+"px; text-align:center;")):c.push("height:"+a.h+"px; line-height:"+a.h+
"px; width:"+a.p+"px; text-align:center;");c.push("cursor:pointer; top:"+b.y+"px; left:"+b.x+"px; color:"+(a.M?a.M:"black")+"; position:absolute; font-size:"+(a.N?a.N:11)+"px; font-family:Arial,sans-serif; font-weight:bold");return c.join("")}window.MarkerClusterer=k;k.prototype.addMarker=k.prototype.q;k.prototype.addMarkers=k.prototype.C;k.prototype.clearMarkers=k.prototype.R;k.prototype.fitMapToMarkers=k.prototype.S;k.prototype.getCalculator=k.prototype.H;k.prototype.getGridSize=k.prototype.w;
k.prototype.getExtendedBounds=k.prototype.v;k.prototype.getMap=k.prototype.getMap;k.prototype.getMarkers=k.prototype.o;k.prototype.getMaxZoom=k.prototype.I;k.prototype.getStyles=k.prototype.z;k.prototype.getTotalClusters=k.prototype.U;k.prototype.getTotalMarkers=k.prototype.V;k.prototype.redraw=k.prototype.i;k.prototype.removeMarker=k.prototype.Y;k.prototype.removeMarkers=k.prototype.Z;k.prototype.resetViewport=k.prototype.m;k.prototype.repaint=k.prototype.L;k.prototype.setCalculator=k.prototype.$;
k.prototype.setGridSize=k.prototype.aa;k.prototype.setMaxZoom=k.prototype.ba;k.prototype.onAdd=k.prototype.onAdd;k.prototype.draw=k.prototype.draw;s.prototype.getCenter=s.prototype.getCenter;s.prototype.getSize=s.prototype.T;s.prototype.getMarkers=s.prototype.o;t.prototype.onAdd=t.prototype.onAdd;t.prototype.draw=t.prototype.draw;t.prototype.onRemove=t.prototype.onRemove;
})();


// jquery.bxslider.min.js -------------------------------------------------------------------------------------------------------------------------------------------
/**
 * BxSlider v4.1.2 - Fully loaded, responsive content slider
 * http://bxslider.com
 *
 * Copyright 2014, Steven Wanderski - http://stevenwanderski.com - http://bxcreative.com
 * Written while drinking Belgian ales and listening to jazz
 *
 * Released under the MIT license - http://opensource.org/licenses/MIT
 */
!function(t){var e={},s={mode:"horizontal",slideSelector:"",infiniteLoop:!0,hideControlOnEnd:!1,speed:500,easing:null,slideMargin:0,startSlide:0,randomStart:!1,captions:!1,ticker:!1,tickerHover:!1,adaptiveHeight:!1,adaptiveHeightSpeed:500,video:!1,useCSS:!0,preloadImages:"visible",responsive:!0,slideZIndex:50,touchEnabled:!0,swipeThreshold:50,oneToOneTouch:!0,preventDefaultSwipeX:!0,preventDefaultSwipeY:!1,pager:!0,pagerType:"full",pagerShortSeparator:" / ",pagerSelector:null,buildPager:null,pagerCustom:null,controls:!0,nextText:"Next",prevText:"Prev",nextSelector:null,prevSelector:null,autoControls:!1,startText:"Start",stopText:"Stop",autoControlsCombine:!1,autoControlsSelector:null,auto:!1,pause:4e3,autoStart:!0,autoDirection:"next",autoHover:!1,autoDelay:0,minSlides:1,maxSlides:1,moveSlides:0,slideWidth:0,onSliderLoad:function(){},onSlideBefore:function(){},onSlideAfter:function(){},onSlideNext:function(){},onSlidePrev:function(){},onSliderResize:function(){}};t.fn.bxSlider=function(n){if(0==this.length)return this;if(this.length>1)return this.each(function(){t(this).bxSlider(n)}),this;var o={},r=this;e.el=this;var a=t(window).width(),l=t(window).height(),d=function(){o.settings=t.extend({},s,n),o.settings.slideWidth=parseInt(o.settings.slideWidth),o.children=r.children(o.settings.slideSelector),o.children.length<o.settings.minSlides&&(o.settings.minSlides=o.children.length),o.children.length<o.settings.maxSlides&&(o.settings.maxSlides=o.children.length),o.settings.randomStart&&(o.settings.startSlide=Math.floor(Math.random()*o.children.length)),o.active={index:o.settings.startSlide},o.carousel=o.settings.minSlides>1||o.settings.maxSlides>1,o.carousel&&(o.settings.preloadImages="all"),o.minThreshold=o.settings.minSlides*o.settings.slideWidth+(o.settings.minSlides-1)*o.settings.slideMargin,o.maxThreshold=o.settings.maxSlides*o.settings.slideWidth+(o.settings.maxSlides-1)*o.settings.slideMargin,o.working=!1,o.controls={},o.interval=null,o.animProp="vertical"==o.settings.mode?"top":"left",o.usingCSS=o.settings.useCSS&&"fade"!=o.settings.mode&&function(){var t=document.createElement("div"),e=["WebkitPerspective","MozPerspective","OPerspective","msPerspective"];for(var i in e)if(void 0!==t.style[e[i]])return o.cssPrefix=e[i].replace("Perspective","").toLowerCase(),o.animProp="-"+o.cssPrefix+"-transform",!0;return!1}(),"vertical"==o.settings.mode&&(o.settings.maxSlides=o.settings.minSlides),r.data("origStyle",r.attr("style")),r.children(o.settings.slideSelector).each(function(){t(this).data("origStyle",t(this).attr("style"))}),c()},c=function(){r.wrap('<div class="bx-wrapper"><div class="bx-viewport"></div></div>'),o.viewport=r.parent(),o.loader=t('<div class="bx-loading" />'),o.viewport.prepend(o.loader),r.css({width:"horizontal"==o.settings.mode?100*o.children.length+215+"%":"auto",position:"relative"}),o.usingCSS&&o.settings.easing?r.css("-"+o.cssPrefix+"-transition-timing-function",o.settings.easing):o.settings.easing||(o.settings.easing="swing"),f(),o.viewport.css({width:"100%",overflow:"hidden",position:"relative"}),o.viewport.parent().css({maxWidth:p()}),o.settings.pager||o.viewport.parent().css({margin:"0 auto 0px"}),o.children.css({"float":"horizontal"==o.settings.mode?"left":"none",listStyle:"none",position:"relative"}),o.children.css("width",u()),"horizontal"==o.settings.mode&&o.settings.slideMargin>0&&o.children.css("marginRight",o.settings.slideMargin),"vertical"==o.settings.mode&&o.settings.slideMargin>0&&o.children.css("marginBottom",o.settings.slideMargin),"fade"==o.settings.mode&&(o.children.css({position:"absolute",zIndex:0,display:"none"}),o.children.eq(o.settings.startSlide).css({zIndex:o.settings.slideZIndex,display:"block"})),o.controls.el=t('<div class="bx-controls" />'),o.settings.captions&&P(),o.active.last=o.settings.startSlide==x()-1,o.settings.video&&r.fitVids();var e=o.children.eq(o.settings.startSlide);"all"==o.settings.preloadImages&&(e=o.children),o.settings.ticker?o.settings.pager=!1:(o.settings.pager&&T(),o.settings.controls&&C(),o.settings.auto&&o.settings.autoControls&&E(),(o.settings.controls||o.settings.autoControls||o.settings.pager)&&o.viewport.after(o.controls.el)),g(e,h)},g=function(e,i){var s=e.find("img, iframe").length;if(0==s)return i(),void 0;var n=0;e.find("img, iframe").each(function(){t(this).one("load",function(){++n==s&&i()}).each(function(){this.complete&&t(this).load()})})},h=function(){if(o.settings.infiniteLoop&&"fade"!=o.settings.mode&&!o.settings.ticker){var e="vertical"==o.settings.mode?o.settings.minSlides:o.settings.maxSlides,i=o.children.slice(0,e).clone().addClass("bx-clone"),s=o.children.slice(-e).clone().addClass("bx-clone");r.append(i).prepend(s)}o.loader.remove(),S(),"vertical"==o.settings.mode&&(o.settings.adaptiveHeight=!0),o.viewport.height(v()),r.redrawSlider(),o.settings.onSliderLoad(o.active.index),o.initialized=!0,o.settings.responsive&&t(window).bind("resize",Z),o.settings.auto&&o.settings.autoStart&&H(),o.settings.ticker&&L(),o.settings.pager&&q(o.settings.startSlide),o.settings.controls&&W(),o.settings.touchEnabled&&!o.settings.ticker&&O()},v=function(){var e=0,s=t();if("vertical"==o.settings.mode||o.settings.adaptiveHeight)if(o.carousel){var n=1==o.settings.moveSlides?o.active.index:o.active.index*m();for(s=o.children.eq(n),i=1;i<=o.settings.maxSlides-1;i++)s=n+i>=o.children.length?s.add(o.children.eq(i-1)):s.add(o.children.eq(n+i))}else s=o.children.eq(o.active.index);else s=o.children;return"vertical"==o.settings.mode?(s.each(function(){e+=t(this).outerHeight()}),o.settings.slideMargin>0&&(e+=o.settings.slideMargin*(o.settings.minSlides-1))):e=Math.max.apply(Math,s.map(function(){return t(this).outerHeight(!1)}).get()),e},p=function(){var t="100%";return o.settings.slideWidth>0&&(t="horizontal"==o.settings.mode?o.settings.maxSlides*o.settings.slideWidth+(o.settings.maxSlides-1)*o.settings.slideMargin:o.settings.slideWidth),t},u=function(){var t=o.settings.slideWidth,e=o.viewport.width();return 0==o.settings.slideWidth||o.settings.slideWidth>e&&!o.carousel||"vertical"==o.settings.mode?t=e:o.settings.maxSlides>1&&"horizontal"==o.settings.mode&&(e>o.maxThreshold||e<o.minThreshold&&(t=(e-o.settings.slideMargin*(o.settings.minSlides-1))/o.settings.minSlides)),t},f=function(){var t=1;if("horizontal"==o.settings.mode&&o.settings.slideWidth>0)if(o.viewport.width()<o.minThreshold)t=o.settings.minSlides;else if(o.viewport.width()>o.maxThreshold)t=o.settings.maxSlides;else{var e=o.children.first().width();t=Math.floor(o.viewport.width()/e)}else"vertical"==o.settings.mode&&(t=o.settings.minSlides);return t},x=function(){var t=0;if(o.settings.moveSlides>0)if(o.settings.infiniteLoop)t=o.children.length/m();else for(var e=0,i=0;e<o.children.length;)++t,e=i+f(),i+=o.settings.moveSlides<=f()?o.settings.moveSlides:f();else t=Math.ceil(o.children.length/f());return t},m=function(){return o.settings.moveSlides>0&&o.settings.moveSlides<=f()?o.settings.moveSlides:f()},S=function(){if(o.children.length>o.settings.maxSlides&&o.active.last&&!o.settings.infiniteLoop){if("horizontal"==o.settings.mode){var t=o.children.last(),e=t.position();b(-(e.left-(o.viewport.width()-t.width())),"reset",0)}else if("vertical"==o.settings.mode){var i=o.children.length-o.settings.minSlides,e=o.children.eq(i).position();b(-e.top,"reset",0)}}else{var e=o.children.eq(o.active.index*m()).position();o.active.index==x()-1&&(o.active.last=!0),void 0!=e&&("horizontal"==o.settings.mode?b(-e.left,"reset",0):"vertical"==o.settings.mode&&b(-e.top,"reset",0))}},b=function(t,e,i,s){if(o.usingCSS){var n="vertical"==o.settings.mode?"translate3d(0, "+t+"px, 0)":"translate3d("+t+"px, 0, 0)";r.css("-"+o.cssPrefix+"-transition-duration",i/1e3+"s"),"slide"==e?(r.css(o.animProp,n),r.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd",function(){r.unbind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd"),D()})):"reset"==e?r.css(o.animProp,n):"ticker"==e&&(r.css("-"+o.cssPrefix+"-transition-timing-function","linear"),r.css(o.animProp,n),r.bind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd",function(){r.unbind("transitionend webkitTransitionEnd oTransitionEnd MSTransitionEnd"),b(s.resetValue,"reset",0),N()}))}else{var a={};a[o.animProp]=t,"slide"==e?r.animate(a,i,o.settings.easing,function(){D()}):"reset"==e?r.css(o.animProp,t):"ticker"==e&&r.animate(a,speed,"linear",function(){b(s.resetValue,"reset",0),N()})}},w=function(){for(var e="",i=x(),s=0;i>s;s++){var n="";o.settings.buildPager&&t.isFunction(o.settings.buildPager)?(n=o.settings.buildPager(s),o.pagerEl.addClass("bx-custom-pager")):(n=s+1,o.pagerEl.addClass("bx-default-pager")),e+='<div class="bx-pager-item"><a href="" data-slide-index="'+s+'" class="bx-pager-link">'+n+"</a></div>"}o.pagerEl.html(e)},T=function(){o.settings.pagerCustom?o.pagerEl=t(o.settings.pagerCustom):(o.pagerEl=t('<div class="bx-pager" />'),o.settings.pagerSelector?t(o.settings.pagerSelector).html(o.pagerEl):o.controls.el.addClass("bx-has-pager").append(o.pagerEl),w()),o.pagerEl.on("click","a",I)},C=function(){o.controls.next=t('<a class="bx-next" href="">'+o.settings.nextText+"</a>"),o.controls.prev=t('<a class="bx-prev" href="">'+o.settings.prevText+"</a>"),o.controls.next.bind("click",y),o.controls.prev.bind("click",z),o.settings.nextSelector&&t(o.settings.nextSelector).append(o.controls.next),o.settings.prevSelector&&t(o.settings.prevSelector).append(o.controls.prev),o.settings.nextSelector||o.settings.prevSelector||(o.controls.directionEl=t('<div class="bx-controls-direction" />'),o.controls.directionEl.append(o.controls.prev).append(o.controls.next),o.controls.el.addClass("bx-has-controls-direction").append(o.controls.directionEl))},E=function(){o.controls.start=t('<div class="bx-controls-auto-item"><a class="bx-start" href="">'+o.settings.startText+"</a></div>"),o.controls.stop=t('<div class="bx-controls-auto-item"><a class="bx-stop" href="">'+o.settings.stopText+"</a></div>"),o.controls.autoEl=t('<div class="bx-controls-auto" />'),o.controls.autoEl.on("click",".bx-start",k),o.controls.autoEl.on("click",".bx-stop",M),o.settings.autoControlsCombine?o.controls.autoEl.append(o.controls.start):o.controls.autoEl.append(o.controls.start).append(o.controls.stop),o.settings.autoControlsSelector?t(o.settings.autoControlsSelector).html(o.controls.autoEl):o.controls.el.addClass("bx-has-controls-auto").append(o.controls.autoEl),A(o.settings.autoStart?"stop":"start")},P=function(){o.children.each(function(){var e=t(this).find("img:first").attr("title");void 0!=e&&(""+e).length&&t(this).append('<div class="bx-caption"><span>'+e+"</span></div>")})},y=function(t){o.settings.auto&&r.stopAuto(),r.goToNextSlide(),t.preventDefault()},z=function(t){o.settings.auto&&r.stopAuto(),r.goToPrevSlide(),t.preventDefault()},k=function(t){r.startAuto(),t.preventDefault()},M=function(t){r.stopAuto(),t.preventDefault()},I=function(e){o.settings.auto&&r.stopAuto();var i=t(e.currentTarget),s=parseInt(i.attr("data-slide-index"));s!=o.active.index&&r.goToSlide(s),e.preventDefault()},q=function(e){var i=o.children.length;return"short"==o.settings.pagerType?(o.settings.maxSlides>1&&(i=Math.ceil(o.children.length/o.settings.maxSlides)),o.pagerEl.html(e+1+o.settings.pagerShortSeparator+i),void 0):(o.pagerEl.find("a").removeClass("active"),o.pagerEl.each(function(i,s){t(s).find("a").eq(e).addClass("active")}),void 0)},D=function(){if(o.settings.infiniteLoop){var t="";0==o.active.index?t=o.children.eq(0).position():o.active.index==x()-1&&o.carousel?t=o.children.eq((x()-1)*m()).position():o.active.index==o.children.length-1&&(t=o.children.eq(o.children.length-1).position()),t&&("horizontal"==o.settings.mode?b(-t.left,"reset",0):"vertical"==o.settings.mode&&b(-t.top,"reset",0))}o.working=!1,o.settings.onSlideAfter(o.children.eq(o.active.index),o.oldIndex,o.active.index)},A=function(t){o.settings.autoControlsCombine?o.controls.autoEl.html(o.controls[t]):(o.controls.autoEl.find("a").removeClass("active"),o.controls.autoEl.find("a:not(.bx-"+t+")").addClass("active"))},W=function(){1==x()?(o.controls.prev.addClass("disabled"),o.controls.next.addClass("disabled")):!o.settings.infiniteLoop&&o.settings.hideControlOnEnd&&(0==o.active.index?(o.controls.prev.addClass("disabled"),o.controls.next.removeClass("disabled")):o.active.index==x()-1?(o.controls.next.addClass("disabled"),o.controls.prev.removeClass("disabled")):(o.controls.prev.removeClass("disabled"),o.controls.next.removeClass("disabled")))},H=function(){o.settings.autoDelay>0?setTimeout(r.startAuto,o.settings.autoDelay):r.startAuto(),o.settings.autoHover&&r.hover(function(){o.interval&&(r.stopAuto(!0),o.autoPaused=!0)},function(){o.autoPaused&&(r.startAuto(!0),o.autoPaused=null)})},L=function(){var e=0;if("next"==o.settings.autoDirection)r.append(o.children.clone().addClass("bx-clone"));else{r.prepend(o.children.clone().addClass("bx-clone"));var i=o.children.first().position();e="horizontal"==o.settings.mode?-i.left:-i.top}b(e,"reset",0),o.settings.pager=!1,o.settings.controls=!1,o.settings.autoControls=!1,o.settings.tickerHover&&!o.usingCSS&&o.viewport.hover(function(){r.stop()},function(){var e=0;o.children.each(function(){e+="horizontal"==o.settings.mode?t(this).outerWidth(!0):t(this).outerHeight(!0)});var i=o.settings.speed/e,s="horizontal"==o.settings.mode?"left":"top",n=i*(e-Math.abs(parseInt(r.css(s))));N(n)}),N()},N=function(t){speed=t?t:o.settings.speed;var e={left:0,top:0},i={left:0,top:0};"next"==o.settings.autoDirection?e=r.find(".bx-clone").first().position():i=o.children.first().position();var s="horizontal"==o.settings.mode?-e.left:-e.top,n="horizontal"==o.settings.mode?-i.left:-i.top,a={resetValue:n};b(s,"ticker",speed,a)},O=function(){o.touch={start:{x:0,y:0},end:{x:0,y:0}},o.viewport.bind("touchstart",X)},X=function(t){if(o.working)t.preventDefault();else{o.touch.originalPos=r.position();var e=t.originalEvent;o.touch.start.x=e.changedTouches[0].pageX,o.touch.start.y=e.changedTouches[0].pageY,o.viewport.bind("touchmove",Y),o.viewport.bind("touchend",V)}},Y=function(t){var e=t.originalEvent,i=Math.abs(e.changedTouches[0].pageX-o.touch.start.x),s=Math.abs(e.changedTouches[0].pageY-o.touch.start.y);if(3*i>s&&o.settings.preventDefaultSwipeX?t.preventDefault():3*s>i&&o.settings.preventDefaultSwipeY&&t.preventDefault(),"fade"!=o.settings.mode&&o.settings.oneToOneTouch){var n=0;if("horizontal"==o.settings.mode){var r=e.changedTouches[0].pageX-o.touch.start.x;n=o.touch.originalPos.left+r}else{var r=e.changedTouches[0].pageY-o.touch.start.y;n=o.touch.originalPos.top+r}b(n,"reset",0)}},V=function(t){o.viewport.unbind("touchmove",Y);var e=t.originalEvent,i=0;if(o.touch.end.x=e.changedTouches[0].pageX,o.touch.end.y=e.changedTouches[0].pageY,"fade"==o.settings.mode){var s=Math.abs(o.touch.start.x-o.touch.end.x);s>=o.settings.swipeThreshold&&(o.touch.start.x>o.touch.end.x?r.goToNextSlide():r.goToPrevSlide(),r.stopAuto())}else{var s=0;"horizontal"==o.settings.mode?(s=o.touch.end.x-o.touch.start.x,i=o.touch.originalPos.left):(s=o.touch.end.y-o.touch.start.y,i=o.touch.originalPos.top),!o.settings.infiniteLoop&&(0==o.active.index&&s>0||o.active.last&&0>s)?b(i,"reset",200):Math.abs(s)>=o.settings.swipeThreshold?(0>s?r.goToNextSlide():r.goToPrevSlide(),r.stopAuto()):b(i,"reset",200)}o.viewport.unbind("touchend",V)},Z=function(){var e=t(window).width(),i=t(window).height();(a!=e||l!=i)&&(a=e,l=i,r.redrawSlider(),o.settings.onSliderResize.call(r,o.active.index))};return r.goToSlide=function(e,i){if(!o.working&&o.active.index!=e)if(o.working=!0,o.oldIndex=o.active.index,o.active.index=0>e?x()-1:e>=x()?0:e,o.settings.onSlideBefore(o.children.eq(o.active.index),o.oldIndex,o.active.index),"next"==i?o.settings.onSlideNext(o.children.eq(o.active.index),o.oldIndex,o.active.index):"prev"==i&&o.settings.onSlidePrev(o.children.eq(o.active.index),o.oldIndex,o.active.index),o.active.last=o.active.index>=x()-1,o.settings.pager&&q(o.active.index),o.settings.controls&&W(),"fade"==o.settings.mode)o.settings.adaptiveHeight&&o.viewport.height()!=v()&&o.viewport.animate({height:v()},o.settings.adaptiveHeightSpeed),o.children.filter(":visible").fadeOut(o.settings.speed).css({zIndex:0}),o.children.eq(o.active.index).css("zIndex",o.settings.slideZIndex+1).fadeIn(o.settings.speed,function(){t(this).css("zIndex",o.settings.slideZIndex),D()});else{o.settings.adaptiveHeight&&o.viewport.height()!=v()&&o.viewport.animate({height:v()},o.settings.adaptiveHeightSpeed);var s=0,n={left:0,top:0};if(!o.settings.infiniteLoop&&o.carousel&&o.active.last)if("horizontal"==o.settings.mode){var a=o.children.eq(o.children.length-1);n=a.position(),s=o.viewport.width()-a.outerWidth()}else{var l=o.children.length-o.settings.minSlides;n=o.children.eq(l).position()}else if(o.carousel&&o.active.last&&"prev"==i){var d=1==o.settings.moveSlides?o.settings.maxSlides-m():(x()-1)*m()-(o.children.length-o.settings.maxSlides),a=r.children(".bx-clone").eq(d);n=a.position()}else if("next"==i&&0==o.active.index)n=r.find("> .bx-clone").eq(o.settings.maxSlides).position(),o.active.last=!1;else if(e>=0){var c=e*m();n=o.children.eq(c).position()}if("undefined"!=typeof n){var g="horizontal"==o.settings.mode?-(n.left-s):-n.top;b(g,"slide",o.settings.speed)}}},r.goToNextSlide=function(){if(o.settings.infiniteLoop||!o.active.last){var t=parseInt(o.active.index)+1;r.goToSlide(t,"next")}},r.goToPrevSlide=function(){if(o.settings.infiniteLoop||0!=o.active.index){var t=parseInt(o.active.index)-1;r.goToSlide(t,"prev")}},r.startAuto=function(t){o.interval||(o.interval=setInterval(function(){"next"==o.settings.autoDirection?r.goToNextSlide():r.goToPrevSlide()},o.settings.pause),o.settings.autoControls&&1!=t&&A("stop"))},r.stopAuto=function(t){o.interval&&(clearInterval(o.interval),o.interval=null,o.settings.autoControls&&1!=t&&A("start"))},r.getCurrentSlide=function(){return o.active.index},r.getCurrentSlideElement=function(){return o.children.eq(o.active.index)},r.getSlideCount=function(){return o.children.length},r.redrawSlider=function(){o.children.add(r.find(".bx-clone")).outerWidth(u()),o.viewport.css("height",v()),o.settings.ticker||S(),o.active.last&&(o.active.index=x()-1),o.active.index>=x()&&(o.active.last=!0),o.settings.pager&&!o.settings.pagerCustom&&(w(),q(o.active.index))},r.destroySlider=function(){o.initialized&&(o.initialized=!1,t(".bx-clone",this).remove(),o.children.each(function(){void 0!=t(this).data("origStyle")?t(this).attr("style",t(this).data("origStyle")):t(this).removeAttr("style")}),void 0!=t(this).data("origStyle")?this.attr("style",t(this).data("origStyle")):t(this).removeAttr("style"),t(this).unwrap().unwrap(),o.controls.el&&o.controls.el.remove(),o.controls.next&&o.controls.next.remove(),o.controls.prev&&o.controls.prev.remove(),o.pagerEl&&o.settings.controls&&o.pagerEl.remove(),t(".bx-caption",this).remove(),o.controls.autoEl&&o.controls.autoEl.remove(),clearInterval(o.interval),o.settings.responsive&&t(window).unbind("resize",Z))},r.reloadSlider=function(t){void 0!=t&&(n=t),r.destroySlider(),d()},d(),this}}(jQuery);



// bootstrap.min.js -------------------------------------------------------------------------------------------------------------------------------------------
/*!
 * Bootstrap v3.3.5 (http://getbootstrap.com)
 * Copyright 2011-2015 Twitter, Inc.
 * Licensed under MIT (https://github.com/twbs/bootstrap/blob/master/LICENSE)
 */
if("undefined"==typeof jQuery)throw new Error("Bootstrap's JavaScript requires jQuery");+function(t){"use strict";var e=t.fn.jquery.split(" ")[0].split(".");if(e[0]<2&&e[1]<9||1==e[0]&&9==e[1]&&e[2]<1)throw new Error("Bootstrap's JavaScript requires jQuery version 1.9.1 or higher")}(jQuery),+function(t){"use strict";function e(e){return this.each(function(){var o=t(this),n=o.data("bs.w2dc-tooltip"),s="object"==typeof e&&e;(n||!/destroy|hide/.test(e))&&(n||o.data("bs.w2dc-tooltip",n=new i(this,s)),"string"==typeof e&&n[e]())})}var i=function(t,e){this.type=null,this.options=null,this.enabled=null,this.timeout=null,this.hoverState=null,this.$element=null,this.inState=null,this.init("w2dc_tooltip",t,e)};i.VERSION="3.3.5",i.TRANSITION_DURATION=150,i.DEFAULTS={animation:!0,placement:"top",selector:!1,template:'<div class="w2dc-tooltip" role="w2dc-tooltip"><div class="w2dc-tooltip-arrow"></div><div class="w2dc-tooltip-inner"></div></div>',trigger:"hover focus",title:"",delay:0,html:!1,container:!1,viewport:{selector:"body",padding:0}},i.prototype.init=function(e,i,o){if(this.enabled=!0,this.type=e,this.$element=t(i),this.options=this.getOptions(o),this.$viewport=this.options.viewport&&t(t.isFunction(this.options.viewport)?this.options.viewport.call(this,this.$element):this.options.viewport.selector||this.options.viewport),this.inState={click:!1,hover:!1,focus:!1},this.$element[0]instanceof document.constructor&&!this.options.selector)throw new Error("`selector` option must be specified when initializing "+this.type+" on the window.document object!");for(var n=this.options.trigger.split(" "),s=n.length;s--;){var r=n[s];if("click"==r)this.$element.on("click."+this.type,this.options.selector,t.proxy(this.toggle,this));else if("manual"!=r){var a="hover"==r?"mouseenter":"focusin",l="hover"==r?"mouseleave":"focusout";this.$element.on(a+"."+this.type,this.options.selector,t.proxy(this.enter,this)),this.$element.on(l+"."+this.type,this.options.selector,t.proxy(this.leave,this))}}this.options.selector?this._options=t.extend({},this.options,{trigger:"manual",selector:""}):this.fixTitle()},i.prototype.getDefaults=function(){return i.DEFAULTS},i.prototype.getOptions=function(e){return e=t.extend({},this.getDefaults(),this.$element.data(),e),e.delay&&"number"==typeof e.delay&&(e.delay={show:e.delay,hide:e.delay}),e},i.prototype.getDelegateOptions=function(){var e={},i=this.getDefaults();return this._options&&t.each(this._options,function(t,o){i[t]!=o&&(e[t]=o)}),e},i.prototype.enter=function(e){var i=e instanceof this.constructor?e:t(e.currentTarget).data("bs."+this.type);return i||(i=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data("bs."+this.type,i)),e instanceof t.Event&&(i.inState["focusin"==e.type?"focus":"hover"]=!0),i.tip().hasClass("w2dc-in")||"in"==i.hoverState?void(i.hoverState="in"):(clearTimeout(i.timeout),i.hoverState="in",i.options.delay&&i.options.delay.show?void(i.timeout=setTimeout(function(){"in"==i.hoverState&&i.show()},i.options.delay.show)):i.show())},i.prototype.isInStateTrue=function(){for(var t in this.inState)if(this.inState[t])return!0;return!1},i.prototype.leave=function(e){var i=e instanceof this.constructor?e:t(e.currentTarget).data("bs."+this.type);return i||(i=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data("bs."+this.type,i)),e instanceof t.Event&&(i.inState["focusout"==e.type?"focus":"hover"]=!1),i.isInStateTrue()?void 0:(clearTimeout(i.timeout),i.hoverState="out",i.options.delay&&i.options.delay.hide?void(i.timeout=setTimeout(function(){"out"==i.hoverState&&i.hide()},i.options.delay.hide)):i.hide())},i.prototype.show=function(){var e=t.Event("show.bs."+this.type);if(this.hasContent()&&this.enabled){this.$element.trigger(e);var o=t.contains(this.$element[0].ownerDocument.documentElement,this.$element[0]);if(e.isDefaultPrevented()||!o)return;var n=this,s=this.tip(),r=this.getUID(this.type);this.setContent(),s.attr("id",r),this.$element.attr("aria-describedby",r),this.options.animation&&s.addClass("w2dc-fade");var a="function"==typeof this.options.placement?this.options.placement.call(this,s[0],this.$element[0]):this.options.placement,l=/\s?auto?\s?/i,h=l.test(a);h&&(a=a.replace(l,"")||"top"),s.detach().css({top:0,left:0,display:"block"}).addClass("w2dc-"+a).data("bs."+this.type,this),this.options.container?s.appendTo(this.options.container):s.insertAfter(this.$element),this.$element.trigger("inserted.bs."+this.type);var p=this.getPosition(),f=s[0].offsetWidth,c=s[0].offsetHeight;if(h){var d=a,u=this.getPosition(this.$viewport);a="bottom"==a&&p.bottom+c>u.bottom?"top":"top"==a&&p.top-c<u.top?"bottom":"right"==a&&p.right+f>u.width?"left":"left"==a&&p.left-f<u.left?"right":a,s.removeClass(d).addClass(a)}var g=this.getCalculatedOffset(a,p,f,c);this.applyPlacement(g,a);var v=function(){var t=n.hoverState;n.$element.trigger("shown.bs."+n.type),n.hoverState=null,"out"==t&&n.leave(n)};t.support.transition&&this.$tip.hasClass("w2dc-fade")?s.one("bsTransitionEnd",v).emulateTransitionEnd(i.TRANSITION_DURATION):v()}},i.prototype.applyPlacement=function(e,i){var o=this.tip(),n=o[0].offsetWidth,s=o[0].offsetHeight,r=parseInt(o.css("margin-top"),10),a=parseInt(o.css("margin-left"),10);isNaN(r)&&(r=0),isNaN(a)&&(a=0),e.top+=r,e.left+=a,t.offset.setOffset(o[0],t.extend({using:function(t){o.css({top:Math.round(t.top),left:Math.round(t.left)})}},e),0),o.addClass("w2dc-in");var l=o[0].offsetWidth,h=o[0].offsetHeight;"top"==i&&h!=s&&(e.top=e.top+s-h);var p=this.getViewportAdjustedDelta(i,e,l,h);p.left?e.left+=p.left:e.top+=p.top;var f=/top|bottom/.test(i),c=f?2*p.left-n+l:2*p.top-s+h,d=f?"offsetWidth":"offsetHeight";o.offset(e),this.replaceArrow(c,o[0][d],f)},i.prototype.replaceArrow=function(t,e,i){this.arrow().css(i?"left":"top",50*(1-t/e)+"%").css(i?"top":"left","")},i.prototype.setContent=function(){var t=this.tip(),e=this.getTitle();t.find(".w2dc-tooltip-inner")[this.options.html?"html":"text"](e),t.removeClass("w2dc-fade w2dc-in w2dc-top w2dc-bottom w2dc-left w2dc-right")},i.prototype.hide=function(e){function o(){"in"!=n.hoverState&&s.detach(),n.$element.removeAttr("aria-describedby").trigger("hidden.bs."+n.type),e&&e()}var n=this,s=t(this.$tip),r=t.Event("hide.bs."+this.type);return this.$element.trigger(r),r.isDefaultPrevented()?void 0:(s.removeClass("w2dc-in"),t.support.transition&&s.hasClass("w2dc-fade")?s.one("bsTransitionEnd",o).emulateTransitionEnd(i.TRANSITION_DURATION):o(),this.hoverState=null,this)},i.prototype.fixTitle=function(){var t=this.$element;(t.attr("title")||"string"!=typeof t.attr("data-original-title"))&&t.attr("data-original-title",t.attr("title")||"").attr("title","")},i.prototype.hasContent=function(){return this.getTitle()},i.prototype.getPosition=function(e){e=e||this.$element;var i=e[0],o="BODY"==i.tagName,n=i.getBoundingClientRect();null==n.width&&(n=t.extend({},n,{width:n.right-n.left,height:n.bottom-n.top}));var s=o?{top:0,left:0}:e.offset(),r={scroll:o?document.documentElement.scrollTop||document.body.scrollTop:e.scrollTop()},a=o?{width:t(window).width(),height:t(window).height()}:null;return t.extend({},n,r,a,s)},i.prototype.getCalculatedOffset=function(t,e,i,o){return"bottom"==t?{top:e.top+e.height,left:e.left+e.width/2-i/2}:"top"==t?{top:e.top-o,left:e.left+e.width/2-i/2}:"left"==t?{top:e.top+e.height/2-o/2,left:e.left-i}:{top:e.top+e.height/2-o/2,left:e.left+e.width}},i.prototype.getViewportAdjustedDelta=function(t,e,i,o){var n={top:0,left:0};if(!this.$viewport)return n;var s=this.options.viewport&&this.options.viewport.padding||0,r=this.getPosition(this.$viewport);if(/right|left/.test(t)){var a=e.top-s-r.scroll,l=e.top+s-r.scroll+o;a<r.top?n.top=r.top-a:l>r.top+r.height&&(n.top=r.top+r.height-l)}else{var h=e.left-s,p=e.left+s+i;h<r.left?n.left=r.left-h:p>r.right&&(n.left=r.left+r.width-p)}return n},i.prototype.getTitle=function(){var t,e=this.$element,i=this.options;return t=e.attr("data-original-title")||("function"==typeof i.title?i.title.call(e[0]):i.title)},i.prototype.getUID=function(t){do t+=~~(1e6*Math.random());while(document.getElementById(t));return t},i.prototype.tip=function(){if(!this.$tip&&(this.$tip=t(this.options.template),1!=this.$tip.length))throw new Error(this.type+" `template` option must consist of exactly 1 top-level element!");return this.$tip},i.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".w2dc-tooltip-arrow")},i.prototype.enable=function(){this.enabled=!0},i.prototype.disable=function(){this.enabled=!1},i.prototype.toggleEnabled=function(){this.enabled=!this.enabled},i.prototype.toggle=function(e){var i=this;e&&(i=t(e.currentTarget).data("bs."+this.type),i||(i=new this.constructor(e.currentTarget,this.getDelegateOptions()),t(e.currentTarget).data("bs."+this.type,i))),e?(i.inState.click=!i.inState.click,i.isInStateTrue()?i.enter(i):i.leave(i)):i.tip().hasClass("w2dc-in")?i.leave(i):i.enter(i)},i.prototype.destroy=function(){var t=this;clearTimeout(this.timeout),this.hide(function(){t.$element.off("."+t.type).removeData("bs."+t.type),t.$tip&&t.$tip.detach(),t.$tip=null,t.$arrow=null,t.$viewport=null})};var o=t.fn.w2dc_tooltip;t.fn.w2dc_tooltip=e,t.fn.w2dc_tooltip.Constructor=i,t.fn.w2dc_tooltip.noConflict=function(){return t.fn.w2dc_tooltip=o,this}}(jQuery),+function(t){"use strict";function e(e){return this.each(function(){var o=t(this),n=o.data("bs.w2dc-popover"),s="object"==typeof e&&e;(n||!/destroy|hide/.test(e))&&(n||o.data("bs.w2dc-popover",n=new i(this,s)),"string"==typeof e&&n[e]())})}var i=function(t,e){this.init("w2dc-popover",t,e)};if(!t.fn.w2dc_tooltip)throw new Error("w2dc-popover requires w2dc-tooltip.js");i.VERSION="3.3.5",i.DEFAULTS=t.extend({},t.fn.w2dc_tooltip.Constructor.DEFAULTS,{placement:"right",trigger:"click",content:"",template:'<div class="w2dc-popover" role="w2dc-tooltip"><div class="arrow"></div><h3 class="w2dc-popover-title"></h3><div class="w2dc-popover-content"></div></div>'}),i.prototype=t.extend({},t.fn.w2dc_tooltip.Constructor.prototype),i.prototype.constructor=i,i.prototype.getDefaults=function(){return i.DEFAULTS},i.prototype.setContent=function(){var t=this.tip(),e=this.getTitle(),i=this.getContent();t.find(".w2dc-popover-title")[this.options.html?"html":"text"](e),t.find(".w2dc-popover-content").children().detach().end()[this.options.html?"string"==typeof i?"html":"append":"text"](i),t.removeClass("w2dc-fade w2dc-top w2dc-bottom w2dc-left w2dc-right w2dc-in"),t.find(".w2dc-popover-title").html()||t.find(".w2dc-popover-title").hide()},i.prototype.hasContent=function(){return this.getTitle()||this.getContent()},i.prototype.getContent=function(){var t=this.$element,e=this.options;return t.attr("data-content")||("function"==typeof e.content?e.content.call(t[0]):e.content)},i.prototype.arrow=function(){return this.$arrow=this.$arrow||this.tip().find(".arrow")};var o=t.fn.w2dc_popover;t.fn.w2dc_popover=e,t.fn.w2dc_popover.Constructor=i,t.fn.w2dc_popover.noConflict=function(){return t.fn.w2dc_popover=o,this}}(jQuery),+function(t){"use strict";function e(e){return this.each(function(){var o=t(this),n=o.data("bs.w2dc-affix"),s="object"==typeof e&&e;n||o.data("bs.w2dc-affix",n=new i(this,s)),"string"==typeof e&&n[e]()})}var i=function(e,o){this.options=t.extend({},i.DEFAULTS,o),this.$target=t(this.options.target).on("scroll.bs.affix.data-api",t.proxy(this.checkPosition,this)).on("click.bs.affix.data-api",t.proxy(this.checkPositionWithEventLoop,this)),this.$element=t(e),this.affixed=null,this.unpin=null,this.pinnedOffset=null,this.checkPosition()};i.VERSION="3.3.5",i.RESET="affix affix-top affix-bottom",i.DEFAULTS={offset:0,target:window},i.prototype.getState=function(t,e,i,o){var n=this.$target.scrollTop(),s=this.$element.offset(),r=this.$target.height();if(null!=i&&"top"==this.affixed)return i>n?"top":!1;if("bottom"==this.affixed)return null!=i?n+this.unpin<=s.top?!1:"bottom":t-o>=n+r?!1:"bottom";var a=null==this.affixed,l=a?n:s.top,h=a?r:e;return null!=i&&i>=n?"top":null!=o&&l+h>=t-o?"bottom":!1},i.prototype.getPinnedOffset=function(){if(this.pinnedOffset)return this.pinnedOffset;this.$element.removeClass(i.RESET).addClass("w2dc-affix");var t=this.$target.scrollTop(),e=this.$element.offset();return this.pinnedOffset=e.top-t},i.prototype.checkPositionWithEventLoop=function(){setTimeout(t.proxy(this.checkPosition,this),1)},i.prototype.checkPosition=function(){if(this.$element.is(":visible")){var e=this.$element.height(),o=this.options.offset,n=o.top,s=o.bottom,r=Math.max(t(document).height(),t(document.body).height());"object"!=typeof o&&(s=n=o),"function"==typeof n&&(n=o.top(this.$element)),"function"==typeof s&&(s=o.bottom(this.$element));var a=this.getState(r,e,n,s);if(this.affixed!=a){null!=this.unpin&&this.$element.css("top","");var l="affix"+(a?"-"+a:""),h=t.Event(l+".bs.affix");if(this.$element.trigger(h),h.isDefaultPrevented())return;this.affixed=a,this.unpin="bottom"==a?this.getPinnedOffset():null,this.$element.removeClass(i.RESET).addClass(l).trigger(l.replace("affix","affixed")+".bs.affix")}"bottom"==a&&this.$element.offset({top:r-e-s})}};var o=t.fn.affix;t.fn.affix=e,t.fn.affix.Constructor=i,t.fn.affix.noConflict=function(){return t.fn.affix=o,this},t(window).on("load",function(){t('[data-spy="affix"]').each(function(){var i=t(this),o=i.data();o.offset=o.offset||{},null!=o.offsetBottom&&(o.offset.bottom=o.offsetBottom),null!=o.offsetTop&&(o.offset.top=o.offsetTop),e.call(i,o)})})}(jQuery),+function(t){"use strict";function e(e){var i,o=e.attr("data-target")||(i=e.attr("href"))&&i.replace(/.*(?=#[^\s]+$)/,"");return t(o)}function i(e){return this.each(function(){var i=t(this),n=i.data("bs.w2dc-collapse"),s=t.extend({},o.DEFAULTS,i.data(),"object"==typeof e&&e);!n&&s.toggle&&/show|hide/.test(e)&&(s.toggle=!1),n||i.data("bs.w2dc-collapse",n=new o(this,s)),"string"==typeof e&&n[e]()})}var o=function(e,i){this.$element=t(e),this.options=t.extend({},o.DEFAULTS,i),this.$trigger=t('[data-toggle="w2dc-collapse"][href="#'+e.id+'"],[data-toggle="w2dc-collapse"][data-target="#'+e.id+'"]'),this.transitioning=null,this.options.parent?this.$parent=this.getParent():this.addAriaAndCollapsedClass(this.$element,this.$trigger),this.options.toggle&&this.toggle()};o.VERSION="3.3.5",o.TRANSITION_DURATION=350,o.DEFAULTS={toggle:!0},o.prototype.dimension=function(){var t=this.$element.hasClass("w2dc-width");return t?"width":"height"},o.prototype.show=function(){if(!this.transitioning&&!this.$element.hasClass("w2dc-in")){var e,n=this.$parent&&this.$parent.children(".panel").children(".in, .collapsing");if(!(n&&n.length&&(e=n.data("bs.w2dc-collapse"),e&&e.transitioning))){var s=t.Event("show.bs.collapse");if(this.$element.trigger(s),!s.isDefaultPrevented()){n&&n.length&&(i.call(n,"hide"),e||n.data("bs.w2dc-collapse",null));var r=this.dimension();this.$element.removeClass("w2dc-collapse").addClass("w2dc-collapsing")[r](0).attr("aria-expanded",!0),this.$trigger.removeClass("w2dc-collapsed").attr("aria-expanded",!0),this.transitioning=1;var a=function(){this.$element.removeClass("w2dc-collapsing").addClass("w2dc-collapse w2dc-in")[r](""),this.transitioning=0,this.$element.trigger("shown.bs.collapse")};if(!t.support.transition)return a.call(this);var l=t.camelCase(["scroll",r].join("-"));this.$element.one("bsTransitionEnd",t.proxy(a,this)).emulateTransitionEnd(o.TRANSITION_DURATION)[r](this.$element[0][l])}}}},o.prototype.hide=function(){if(!this.transitioning&&this.$element.hasClass("w2dc-in")){var e=t.Event("hide.bs.w2dc-collapse");if(this.$element.trigger(e),!e.isDefaultPrevented()){var i=this.dimension();this.$element[i](this.$element[i]())[0].offsetHeight,this.$element.addClass("w2dc-collapsing").removeClass("w2dc-collapse w2dc-in").attr("aria-expanded",!1),this.$trigger.addClass("w2dc-collapsed").attr("aria-expanded",!1),this.transitioning=1;var n=function(){this.transitioning=0,this.$element.removeClass("w2dc-collapsing").addClass("w2dc-collapse").trigger("hidden.bs.w2dc-collapse")};return t.support.transition?void this.$element[i](0).one("bsTransitionEnd",t.proxy(n,this)).emulateTransitionEnd(o.TRANSITION_DURATION):n.call(this)}}},o.prototype.toggle=function(){this[this.$element.hasClass("w2dc-in")?"hide":"show"]()},o.prototype.getParent=function(){return t(this.options.parent).find('[data-toggle="w2dc-collapse"][data-parent="'+this.options.parent+'"]').each(t.proxy(function(i,o){var n=t(o);this.addAriaAndCollapsedClass(e(n),n)},this)).end()},o.prototype.addAriaAndCollapsedClass=function(t,e){var i=t.hasClass("w2dc-in");t.attr("aria-expanded",i),e.toggleClass("collapsed",!i).attr("aria-expanded",i)};var n=t.fn.collapse;t.fn.collapse=i,t.fn.collapse.Constructor=o,t.fn.collapse.noConflict=function(){return t.fn.collapse=n,this},t(document).on("click.bs.w2dc-collapse.data-api",'[data-toggle="w2dc-collapse"]',function(o){var n=t(this);n.attr("data-target")||o.preventDefault();var s=e(n),r=s.data("bs.w2dc-collapse"),a=r?"toggle":n.data();i.call(s,a)})}(jQuery),+function(t){"use strict";function e(i,o){this.$body=t(document.body),this.$scrollElement=t(t(i).is(document.body)?window:i),this.options=t.extend({},e.DEFAULTS,o),this.selector=(this.options.target||"")+" .w2dc-nav li > a",this.offsets=[],this.targets=[],this.activeTarget=null,this.scrollHeight=0,this.$scrollElement.on("scroll.bs.w2dc-scrollspy",t.proxy(this.process,this)),this.refresh(),this.process()}function i(i){return this.each(function(){var o=t(this),n=o.data("bs.w2dc-scrollspy"),s="object"==typeof i&&i;n||o.data("bs.w2dc-scrollspy",n=new e(this,s)),"string"==typeof i&&n[i]()})}e.VERSION="3.3.5",e.DEFAULTS={offset:10},e.prototype.getScrollHeight=function(){return this.$scrollElement[0].scrollHeight||Math.max(this.$body[0].scrollHeight,document.documentElement.scrollHeight)},e.prototype.refresh=function(){var e=this,i="offset",o=0;this.offsets=[],this.targets=[],this.scrollHeight=this.getScrollHeight(),t.isWindow(this.$scrollElement[0])||(i="position",o=this.$scrollElement.scrollTop()),this.$body.find(this.selector).map(function(){var e=t(this),n=e.data("target")||e.attr("href"),s=/^#./.test(n)&&t(n);return s&&s.length&&s.is(":visible")&&[[s[i]().top+o,n]]||null}).sort(function(t,e){return t[0]-e[0]}).each(function(){e.offsets.push(this[0]),e.targets.push(this[1])})},e.prototype.process=function(){var t,e=this.$scrollElement.scrollTop()+this.options.offset,i=this.getScrollHeight(),o=this.options.offset+i-this.$scrollElement.height(),n=this.offsets,s=this.targets,r=this.activeTarget;if(this.scrollHeight!=i&&this.refresh(),e>=o)return r!=(t=s[s.length-1])&&this.activate(t);if(r&&e<n[0])return this.activeTarget=null,this.clear();for(t=n.length;t--;)r!=s[t]&&e>=n[t]&&(void 0===n[t+1]||e<n[t+1])&&this.activate(s[t])},e.prototype.activate=function(e){this.activeTarget=e,this.clear();var i=this.selector+'[data-target="'+e+'"],'+this.selector+'[href="'+e+'"]',o=t(i).parents("li").addClass("w2dc-active");o.parent(".w2dc-dropdown-menu").length&&(o=o.closest("li.dropdown").addClass("w2dc-active")),o.trigger("activate.bs.w2dc-scrollspy")},e.prototype.clear=function(){t(this.selector).parentsUntil(this.options.target,".w2dc-active").removeClass("w2dc-active")};var o=t.fn.scrollspy;t.fn.scrollspy=i,t.fn.scrollspy.Constructor=e,t.fn.scrollspy.noConflict=function(){return t.fn.scrollspy=o,this},t(window).on("load.bs.w2dc-scrollspy.data-api",function(){t('[data-spy="w2dc-scroll"]').each(function(){var e=t(this);i.call(e,e.data())})})}(jQuery),+function(t){"use strict";function e(){var t=document.createElement("bootstrap"),e={WebkitTransition:"webkitTransitionEnd",MozTransition:"transitionend",OTransition:"oTransitionEnd otransitionend",transition:"transitionend"};for(var i in e)if(void 0!==t.style[i])return{end:e[i]};return!1}t.fn.emulateTransitionEnd=function(e){var i=!1,o=this;t(this).one("bsTransitionEnd",function(){i=!0});var n=function(){i||t(o).trigger(t.support.transition.end)};return setTimeout(n,e),this},t(function(){t.support.transition=e(),t.support.transition&&(t.event.special.bsTransitionEnd={bindType:t.support.transition.end,delegateType:t.support.transition.end,handle:function(e){return t(e.target).is(this)?e.handleObj.handler.apply(this,arguments):void 0}})})}(jQuery);


// jquery.tokenize.js -------------------------------------------------------------------------------------------------------------------------------------------
!function(e){var t={BACKSPACE:8,TAB:9,ENTER:13,ESCAPE:27,ARROW_UP:38,ARROW_DOWN:40},n=null,o="tokenize",s=function(t,n){if(!n.data(o)){var s=new e.tokenize(e.extend({},e.fn.tokenize.defaults,t));n.data(o,s),s.init(n)}return n.data(o)};e.tokenize=function(t){void 0==t&&(t=e.fn.tokenize.defaults),this.options=t},e.extend(e.tokenize.prototype,{init:function(t){var n=this;this.select=t.attr("multiple","multiple").css({margin:0,padding:0,border:0}).hide(),this.container=e("<div />").attr("class",this.select.attr("class")).addClass("Tokenize"),1==this.options.maxElements&&this.container.addClass("OnlyOne"),this.dropdown=e("<ul />").addClass("Dropdown"),this.tokensContainer=e("<ul />").addClass("TokensContainer"),this.options.autosize&&this.tokensContainer.addClass("Autosize"),this.searchToken=e("<li />").addClass("TokenSearch").appendTo(this.tokensContainer),this.searchInput=e("<input />").appendTo(this.searchToken),this.options.searchMaxLength>0&&this.searchInput.attr("maxlength",this.options.searchMaxLength),this.select.prop("disabled")&&this.disable(),this.options.sortable&&("undefined"!=typeof e.ui?this.tokensContainer.sortable({items:"li.Token",cursor:"move",placeholder:"Token MovingShadow",forcePlaceholderSize:!0,update:function(){n.updateOrder()},start:function(){n.searchToken.hide()},stop:function(){n.searchToken.show()}}).disableSelection():(this.options.sortable=!1,console.log("jQuery UI is not loaded, sortable option has been disabled"))),this.container.append(this.tokensContainer).append(this.dropdown).insertAfter(this.select),this.tokensContainer.on("click",function(e){e.stopImmediatePropagation(),n.searchInput.get(0).focus(),n.updatePlaceholder(),n.dropdown.is(":hidden")&&""!=n.searchInput.val()&&n.search()}),this.searchInput.on("blur",function(){n.tokensContainer.removeClass("Focused")}),this.searchInput.on("focus click",function(){n.tokensContainer.addClass("Focused"),n.options.displayDropdownOnFocus&&"select"==n.options.datas&&n.search()}),this.searchInput.on("keydown",function(e){n.resizeSearchInput(),n.keydown(e)}),this.searchInput.on("keyup",function(e){n.keyup(e)}),this.searchInput.on("keypress",function(e){n.keypress(e)}),this.searchInput.on("paste",function(){setTimeout(function(){n.resizeSearchInput()},10),setTimeout(function(){var t=n.searchInput.val().split(",");t.length>1&&e.each(t,function(e,t){n.tokenAdd(t.trim(),"")})},20)}),e(document).on("click",function(){n.dropdownHide(),1==n.options.maxElements&&n.searchInput.val()&&n.tokenAdd(n.searchInput.val(),"")}),this.resizeSearchInput(),this.remap(!0),this.updatePlaceholder()},updateOrder:function(){if(this.options.sortable){var t,n,o=this;e.each(this.tokensContainer.sortable("toArray",{attribute:"data-value"}),function(s,i){n=e('option[value="'+i+'"]',o.select),void 0==t?n.prependTo(o.select):t.after(n),t=n}),this.options.onReorder(this)}},updatePlaceholder:function(){0!=this.options.placeholder&&(void 0==this.placeholder&&(this.placeholder=e("<li />").addClass("Placeholder").html(this.options.placeholder),this.placeholder.insertBefore(e("li:first-child",this.tokensContainer))),0==this.searchInput.val().length&&0==e("li.Token",this.tokensContainer).length?this.placeholder.show():this.placeholder.hide())},dropdownShow:function(){this.dropdown.show()},dropdownPrev:function(){e("li.Hover",this.dropdown).length>0?e("li.Hover",this.dropdown).is("li:first-child")?(e("li.Hover",this.dropdown).removeClass("Hover"),e("li:last-child",this.dropdown).addClass("Hover")):e("li.Hover",this.dropdown).removeClass("Hover").prev().addClass("Hover"):e("li:first",this.dropdown).addClass("Hover")},dropdownNext:function(){e("li.Hover",this.dropdown).length>0?e("li.Hover",this.dropdown).is("li:last-child")?(e("li.Hover",this.dropdown).removeClass("Hover"),e("li:first-child",this.dropdown).addClass("Hover")):e("li.Hover",this.dropdown).removeClass("Hover").next().addClass("Hover"):e("li:first",this.dropdown).addClass("Hover")},dropdownAddItem:function(t,n,o){if(void 0==o&&(o=n),!e('li[data-value="'+t+'"]',this.tokensContainer).length){var s=this,i=e("<li />").attr("data-value",t).attr("data-text",n).html(o).on("click",function(t){t.stopImmediatePropagation(),s.tokenAdd(e(this).attr("data-value"),e(this).attr("data-text"))}).on("mouseover",function(){e(this).addClass("Hover")}).on("mouseout",function(){e("li",s.dropdown).removeClass("Hover")});this.dropdown.append(i),this.options.onDropdownAddItem(t,n,o,this)}return this},dropdownHide:function(){this.dropdownReset(),this.dropdown.hide()},dropdownReset:function(){this.dropdown.html("")},resizeSearchInput:function(){this.searchInput.attr("size",Number(this.searchInput.val().length)+5),this.updatePlaceholder()},resetSearchInput:function(){this.searchInput.val(""),this.resizeSearchInput()},resetPendingTokens:function(){e("li.PendingDelete",this.tokensContainer).removeClass("PendingDelete")},keypress:function(e){String.fromCharCode(e.which)==this.options.delimiter&&(e.preventDefault(),this.tokenAdd(this.searchInput.val(),""))},keydown:function(n){switch(n.keyCode){case t.BACKSPACE:0==this.searchInput.val().length&&(n.preventDefault(),e("li.Token.PendingDelete",this.tokensContainer).length?this.tokenRemove(e("li.Token.PendingDelete").attr("data-value")):e("li.Token:last",this.tokensContainer).addClass("PendingDelete"),this.dropdownHide());break;case t.TAB:case t.ENTER:if(e("li.Hover",this.dropdown).length){var o=e("li.Hover",this.dropdown);n.preventDefault(),this.tokenAdd(o.attr("data-value"),o.attr("data-text"))}else this.searchInput.val()&&(n.preventDefault(),this.tokenAdd(this.searchInput.val(),""));this.resetPendingTokens();break;case t.ESCAPE:this.resetSearchInput(),this.dropdownHide(),this.resetPendingTokens();break;case t.ARROW_UP:n.preventDefault(),this.dropdownPrev();break;case t.ARROW_DOWN:n.preventDefault(),this.dropdownNext();break;default:this.resetPendingTokens()}},keyup:function(e){switch(this.updatePlaceholder(),e.keyCode){case t.TAB:case t.ENTER:case t.ESCAPE:case t.ARROW_UP:case t.ARROW_DOWN:break;case t.BACKSPACE:this.searchInput.val()?this.search():this.dropdownHide();break;default:this.searchInput.val()&&this.search()}},search:function(){var t=this,n=1;if(this.options.maxElements>0&&e("li.Token",this.tokensContainer).length>=this.options.maxElements)return!1;if("select"==this.options.datas){var o=!1,s=new RegExp(this.searchInput.val().replace(/[-[\]{}()*+?.,\\^$|#\s]/g,"\\$&"),"i");this.dropdownReset(),e("option",this.select).not(":selected, :disabled").each(function(){return n<=t.options.nbDropdownElements?void(s.test(e(this).html())&&(t.dropdownAddItem(e(this).attr("value"),e(this).html()),o=!0,n++)):!1}),o?(e("li:first",this.dropdown).addClass("Hover"),this.dropdownShow()):this.dropdownHide()}else this.debounce(function(){e.ajax({url:t.options.datas,data:t.options.searchParam+"="+t.searchInput.val(),dataType:t.options.dataType,success:function(o){return o&&(t.dropdownReset(),e.each(o,function(e,o){if(!(n<=t.options.nbDropdownElements))return!1;var s=void 0;o[t.options.htmlField]&&(s=o[t.options.htmlField]),t.dropdownAddItem(o[t.options.valueField],o[t.options.textField],s),n++}),e("li",t.dropdown).length)?(e("li:first",t.dropdown).addClass("Hover"),t.dropdownShow(),!0):void t.dropdownHide()},error:function(e,t){console.log("Error : "+t)}})},this.options.debounce)},debounce:function(e,t){var o=this,s=arguments,i=function(){e.apply(o,s),n=null};n&&clearTimeout(n),n=setTimeout(i,t||this.options.debounce)},tokenAdd:function(t,n,o){if(t=this.escape(t),void 0==t||""==t)return this;if((void 0==n||""==n)&&(n=t),void 0==o&&(o=!1),this.options.maxElements>0&&e("li.Token",this.tokensContainer).length>=this.options.maxElements)return this.resetSearchInput(),this;var s=this,i=e("<a />").addClass("Close").html("&#215;").on("click",function(e){e.stopImmediatePropagation(),s.tokenRemove(t)});if(e('option[value="'+t+'"]',this.select).length)e('option[value="'+t+'"]',this.select).attr("selected",!0).prop("selected",!0);else{if(!(this.options.newElements||!this.options.newElements&&e('li[data-value="'+t+'"]',this.dropdown).length>0))return this.resetSearchInput(),this;var a=e("<option />").attr("selected",!0).attr("value",t).attr("data-type","custom").prop("selected",!0).html(n);this.select.append(a)}return e('li.Token[data-value="'+t+'"]',this.tokensContainer).length>0?this:(e("<li />").addClass("Token").attr("data-value",t).append("<span>"+n+"</span>").prepend(i).insertBefore(this.searchToken),o||this.options.onAddToken(t,n,this),this.resetSearchInput(),this.dropdownHide(),this.updateOrder(),this)},tokenRemove:function(t){var n=e('option[value="'+t+'"]',this.select);return"custom"==n.attr("data-type")?n.remove():n.removeAttr("selected").prop("selected",!1),e('li.Token[data-value="'+t+'"]',this.tokensContainer).remove(),this.options.onRemoveToken(t,this),this.resizeSearchInput(),this.dropdownHide(),this.updateOrder(),this},clear:function(){var t=this;return e("li.Token",this.tokensContainer).each(function(){t.tokenRemove(e(this).attr("data-value"))}),this.options.onClear(this),this.dropdownHide(),this},disable:function(){return this.select.prop("disabled",!0),this.searchInput.prop("disabled",!0),this.container.addClass("Disabled"),this.options.sortable&&this.tokensContainer.sortable("disable"),this},enable:function(){return this.select.prop("disabled",!1),this.searchInput.prop("disabled",!1),this.container.removeClass("Disabled"),this.options.sortable&&this.tokensContainer.sortable("enable"),this},remap:function(t){var n=this,o=e("option:selected",this.select);return void 0==t&&(t=!1),this.clear(),o.each(function(){n.tokenAdd(e(this).val(),e(this).html(),t)}),this},toArray:function(){var t=[];return e("option:selected",this.select).each(function(){t.push(e(this).val())}),t},escape:function(e){return String(e).replace(/["]/g,function(){return""})}}),e.fn.tokenize=function(t){void 0==t&&(t={});var n=this.filter("select");return n.length>1?(n.each(function(){s(t,e(this))}),n):s(t,e(this))},e.fn.tokenize.defaults={datas:"select",placeholder:!1,searchParam:"search",searchMaxLength:0,debounce:0,delimiter:",",newElements:!0,autosize:!1,nbDropdownElements:10,displayDropdownOnFocus:!1,maxElements:0,sortable:!1,dataType:"json",valueField:"value",textField:"text",htmlField:"html",onAddToken:function(){},onRemoveToken:function(){},onClear:function(){},onReorder:function(){},onDropdownAddItem:function(){}}}(jQuery,"tokenize");