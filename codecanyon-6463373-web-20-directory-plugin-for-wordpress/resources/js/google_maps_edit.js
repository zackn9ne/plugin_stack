	var map = null;
	var allow_map_zoom = false; // allow/disallow map zoom in listener, this option needs because map.setZoom() also calls this listener
    var geocoder = null;
    var infoWindow = null;
    var markersArray = [];
    var address = '';
    var glocation_backend = (function(index, point, location, address_line_1, address_line_2, zip_or_postal_index, map_icon_file) {
    	this.index = index;
    	this.point = point;
    	this.location = location;
    	this.address_line_1 = address_line_1;
    	this.address_line_2 = address_line_2;
    	this.zip_or_postal_index = zip_or_postal_index;
    	this.map_icon_file = map_icon_file;
    	this.placeMarker = function() {
    		return placeMarker_backend(this);
    	};
    	this.compileAddress = function() {
    		address = this.address_line_1;
    		if (this.address_line_2)
    			address += ", "+this.address_line_2;
    		if (this.location) {
    			if (address)
    				address += " ";
    			address += this.location;
    		}
    		if (google_maps_objects.default_geocoding_location) {
    			if (address)
    				address += " ";
    			address += google_maps_objects.default_geocoding_location;
    		}
    		if (this.zip_or_postal_index) {
    			if (address)
    				address += " ";
    			address += this.zip_or_postal_index;
    		}
    		return address;
    	};
    	this.compileHtmlAddress = function() {
    		address = this.address_line_1;
    		if (this.address_line_2)
    			address += ", "+this.address_line_2;
    		if (this.location) {
    			if (this.address_line_1 || this.address_line_2)
    				address += "<br />";
    			address += this.location;
    		}
    		if (this.zip_or_postal_index)
    			address += " "+this.zip_or_postal_index;
    		return address;
    	};
    	this.setPoint = function(point) {
    		this.point = point;
    	};
    });

    (function($) {
    	w2dc_load_maps_api_backend = function() {
    		google.maps.event.addDomListener(window, 'load', function() {
				if (document.getElementById("w2dc-maps-canvas")) {
				    var mapOptions = {
						zoom: 1,
						scrollwheel: false,
						disableDoubleClickZoom: true,
						mapTypeId: google.maps.MapTypeId.ROADMAP
					};
				    if (google_maps_objects.map_style_name != 'default' && google_maps_objects.map_styles)
						mapOptions.styles = eval(google_maps_objects.map_styles[google_maps_objects.map_style_name]);
				    map = new google.maps.Map(document.getElementById("w2dc-maps-canvas"), mapOptions);
		
				    geocoder = new google.maps.Geocoder();
					    
				    var coords_array_1 = new Array();
		   			var coords_array_2 = new Array();
		
		   			if (isAnyLocation_backend())
				    	generateMap_backend();
				    else
				    	map.setCenter(new google.maps.LatLng(34, 0));
		
					google.maps.event.addListener(map, 'zoom_changed', function() {
						if (allow_map_zoom)
							jQuery(".w2dc-map-zoom").val(map.getZoom());
					});
				}
    		});
    	}
	}(jQuery));
	
	function setMapCenter_backend(coords_array_1, coords_array_2) {
		var count = 0;
		var bounds = new google.maps.LatLngBounds();
		for (count == 0; count<coords_array_1.length; count++)  {
			bounds.extend(new google.maps.LatLng(coords_array_1[count], coords_array_2[count]));
		}
		if (count == 1) {
			if (jQuery(".w2dc-map-zoom").val() == '' || jQuery(".w2dc-map-zoom").val() == 0)
				var zoom_level = 1;
			else
				var zoom_level = parseInt(jQuery(".w2dc-map-zoom").val());
		} else {
			map.fitBounds(bounds);
			var zoom_level = map.getZoom();
		}
		map.setCenter(bounds.getCenter());
		
		// allow/disallow map zoom in listener, this option needs because map.setZoom() also calls this listener
		allow_map_zoom = false;
		map.setZoom(zoom_level);
		allow_map_zoom = false;
	}

	var coords_array_1 = new Array();
	var coords_array_2 = new Array();
	var attempts = 0;
	function generateMap_backend() {
		ajax_loader_show("Locations targeting...");
		coords_array_1 = new Array();
		coords_array_2 = new Array();
		attempts = 0;
		clearOverlays_backend();
		geocodeAddress_backend(0);
	}
	
	function geocodeAddress_backend(i) {
		if  (jQuery(".w2dc-location-in-metabox:eq("+i+")").length) {
			var locations_drop_boxes = [];
			jQuery(".w2dc-location-in-metabox:eq("+i+")").find("select").each(function(j, val) {
				if (jQuery(this).val())
					locations_drop_boxes.push(jQuery(this).children(":selected").text());
			});

			var location_string = locations_drop_boxes.reverse().join(', ');

			if (jQuery(".w2dc-manual-coords:eq("+i+")").is(":checked") && jQuery(".w2dc-map-coords-1:eq("+i+")").val()!='' && jQuery(".w2dc-map-coords-2:eq("+i+")").val()!='' && (jQuery(".w2dc-map-coords-1:eq("+i+")").val()!=0 || jQuery(".w2dc-map-coords-2:eq("+i+")").val()!=0)) {
				map_coords_1 = jQuery(".w2dc-map-coords-1:eq("+i+")").val();
				map_coords_2 = jQuery(".w2dc-map-coords-2:eq("+i+")").val();
				if (jQuery.isNumeric(map_coords_1) && jQuery.isNumeric(map_coords_2)) {
					point = new google.maps.LatLng(map_coords_1, map_coords_2);
					coords_array_1.push(map_coords_1);
					coords_array_2.push(map_coords_2);
	
					var location_obj = new glocation_backend(i, point, 
						location_string,
						jQuery(".w2dc-address-line-1:eq("+i+")").val(),
						jQuery(".w2dc-address-line-2:eq("+i+")").val(),
						jQuery(".w2dc-zip-or-postal-index:eq("+i+")").val(),
						jQuery(".w2dc-map-icon-file:eq("+i+")").val()
					);
					location_obj.placeMarker();
				}
				geocodeAddress_backend(i+1);
				if ((i+1) == jQuery(".w2dc-location-in-metabox").length) {
					setMapCenter_backend(coords_array_1, coords_array_2);
					ajax_loader_hide();
				}
			} else if (location_string || jQuery(".w2dc-address-line-1:eq("+i+")").val() || jQuery(".w2dc-address-line-2:eq("+i+")").val() || jQuery(".w2dc-zip-or-postal-index:eq("+i+")").val()) {
				var location_obj = new glocation_backend(i, null, 
					location_string,
					jQuery(".w2dc-address-line-1:eq("+i+")").val(),
					jQuery(".w2dc-address-line-2:eq("+i+")").val(),
					jQuery(".w2dc-zip-or-postal-index:eq("+i+")").val(),
					jQuery(".w2dc-map-icon-file:eq("+i+")").val()
				);

				// Geocode by address
				geocoder.geocode( { 'address': location_obj.compileAddress()}, function(results, status) {
					if (status != google.maps.GeocoderStatus.OK) {
						if (status == 'OVER_QUERY_LIMIT' && attempts < 5) {
							attempts++;
							setTimeout('geocodeAddress_backend('+i+')', 2000);
						} else {
							alert("Sorry, we were unable to geocode that address (address #"+(i)+") for the following reason: " + status);
							ajax_loader_hide();
						}
					} else {
						point = results[0].geometry.location;
						jQuery(".w2dc-map-coords-1:eq("+i+")").val(point.lat());
						jQuery(".w2dc-map-coords-2:eq("+i+")").val(point.lng());
						map_coords_1 = point.lat();
						map_coords_2 = point.lng();
						coords_array_1.push(map_coords_1);
						coords_array_2.push(map_coords_2);
						location_obj.setPoint(point);
						location_obj.placeMarker();
						geocodeAddress_backend(i+1);
					}
					if ((i+1) == jQuery(".w2dc-location-in-metabox").length) {
						setMapCenter_backend(coords_array_1, coords_array_2);
						ajax_loader_hide();
					}
				});
			} else
				ajax_loader_hide();
		} else
			attempts = 0;
	}

	function placeMarker_backend(glocation) {
		if (google_maps_objects.global_map_icons_path != '') {
			if (glocation.map_icon_file)
				var icon_file = google_maps_objects.global_map_icons_path+'icons/'+glocation.map_icon_file;
			else
				var icon_file = google_maps_objects.global_map_icons_path+"blank.png";

			var customIcon = {
				url: icon_file,
			    size: new google.maps.Size(parseInt(google_maps_objects.marker_image_width), parseInt(google_maps_objects.marker_image_height)),
			    origin: new google.maps.Point(0, 0),
			    anchor: new google.maps.Point(parseInt(google_maps_objects.marker_image_anchor_x), parseInt(google_maps_objects.marker_image_anchor_y))
			};

			var marker = new google.maps.Marker({
				position: glocation.point,
				map: map,
				icon: customIcon,
				draggable: false
			});
		} else 
			var marker = new google.maps.Marker({
				position: glocation.point,
				map: map,
				draggable: false
			});

		markersArray.push(marker);
		google.maps.event.addListener(marker, 'click', function() {
			showInfoWindow_backend(glocation, marker);
		});
		
		google.maps.event.addListener(marker, 'dragend', function(event) {
			var point = marker.getPosition();
			if (point !== undefined) {
				var selected_location_num = glocation.index;
				jQuery(".w2dc-manual-coords:eq("+glocation.index+")").attr("checked", true);
				jQuery(".w2dc-manual-coords:eq("+glocation.index+")").parents(".w2dc-manual-coords-wrapper").find(".w2dc-manual-coords-block").show(200);

				jQuery(".w2dc-map-coords-1:eq("+glocation.index+")").val(point.lat());
				jQuery(".w2dc-map-coords-2:eq("+glocation.index+")").val(point.lng());
			}
		});
	}
	
	// This function builds info Window and shows it hiding another
	function showInfoWindow_backend(glocation, marker) {
		address = glocation.compileHtmlAddress();
		index = glocation.index;

		// we use global infoWindow, not to close/open it - just to set new content (in order to prevent blinking)
		if (!infoWindow)
			infoWindow = new google.maps.InfoWindow();

		infoWindow.setContent(address);
		infoWindow.open(map, marker);
	}
	
	function clearOverlays_backend() {
		if (markersArray) {
			for(var i = 0; i<markersArray.length; i++){
				markersArray[i].setMap(null);
			}
		}
	}
	
	function isAnyLocation_backend() {
		/*if (jQuery(".map_coords_1[value!=''][value!='0.000000'][value!='0']").length != 0 || jQuery(".map_coords_2[value!=''][value!='0.000000'][value!='0']").length != 0)
			return true;*/

		var is_location = false;
		jQuery(".w2dc-location-in-metabox").each(function(i, val) {
			var locations_drop_boxes = [];
			jQuery(this).find("select").each(function(j, val) {
				if (jQuery(this).val()) {
					is_location = true;
					return false;
				}
			});
			
			if (jQuery(".w2dc-manual-coords:eq("+i+")").is(":checked") && jQuery(".w2dc-map-coords-1:eq("+i+")").val()!='' && jQuery(".w2dc-map-coords-2:eq("+i+")").val()!='' && (jQuery(".w2dc-map-coords-1:eq("+i+")").val()!=0 || jQuery(".w2dc-map-coords-2:eq("+i+")").val()!=0)) {
				is_location = true;
				return false;
			}
		});
		if (is_location)
			return true;

		if (jQuery(".w2dc-address-line-1[value!='']").length != 0)
			return true;

		if (jQuery(".w2dc-address-line-2[value!='']").length != 0)
			return true;

		if (jQuery(".w2dc-zip-or-postal-index[value!='']").length != 0)
			return true;
	}
