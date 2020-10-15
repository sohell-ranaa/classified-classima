(function ($) {
  // Map loading
  $(function () {
    // render map claster
    var map_view = $('.rtcl-map-view');

    if (map_view.length) {
      map_view.each(function () {
        render_map_view($(this));
      });
    } // render single map


    $('.rtcl-map').each(function () {
      render_map($(this));
    });
  });

  function get_generated_address() {
    var address = [],
        items = {},
        address_order = ['address', 'sub_sub_location', 'sub_location', 'location', 'zipcode'];
    $('.rtcl-map-field').map(function () {
      if ($(this).is(":visible")) {
        var type = $(this).getType();

        if ((type === 'text' || type === 'textarea') && $(this).val()) {
          items[$(this).attr('name')] = $(this).val();
        } else if (type === 'select' && $(this).find('option:selected').val()) {
          items[$(this).attr('name')] = $(this).find('option:selected').text();
        }
      }
    });
    address_order.map(function (value) {
      if (items[value] !== undefined) {
        address.push(items[value]);
      }
    });
    address = address.filter(function (v) {
      return v !== '';
    });
    address = address.join();
    return address;
  }

  function render_map($el) {
    var $markers = $el.find('.marker'),
        args = {
      zoom: parseInt(rtcl.zoom_level) || 16,
      center: new google.maps.LatLng(0, 0),
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      zoomControl: true,
      scrollwheel: false
    },
        map = new google.maps.Map($el[0], args);
    map.markers = [];
    map.type = $el.data('type'); // Add marker

    $markers.each(function (listener) {
      var $marker = $(this),
          latitude = $marker.data('latitude') || '',
          longitude = $marker.data('longitude') || '',
          address = $marker.data('address') || '',
          latlng,
          marker;

      if (latitude && longitude) {
        latlng = new google.maps.LatLng(latitude, longitude);
      } else if (address) {
        var geoCoder = new google.maps.Geocoder();
        geoCoder.geocode({
          'address': address
        }, function (results, status) {
          if (status === google.maps.GeocoderStatus.OK) {
            var marker = new google.maps.Marker({
              map: map,
              position: results[0].geometry.location,
              draggable: map.type === 'input'
            });
            map.markers.push(marker);

            if ($marker.html()) {
              // create info window
              var infowindow = new google.maps.InfoWindow({
                content: $marker.html()
              }); // show info window when marker is clicked

              google.maps.event.addListener(marker, 'click', function () {
                infowindow.open(map, marker);
              });
            }

            if (map.type === 'input') {
              google.maps.event.addListener(marker, "dragend", function () {
                var point = marker.getPosition();
                map.panTo(point);
                update_latlng(point);
              });
            }

            center_map(map);
          }
        });
      } else if (map.type === 'input') {
        var _marker = new google.maps.Marker({
          map: map,
          position: new google.maps.LatLng(0, 0),
          draggable: true
        });

        google.maps.event.addListener(_marker, "dragend", function () {
          var point = _marker.getPosition();

          map.panTo(point);
          update_latlng(point);
        });
        map.markers.push(_marker);
        re_render_map_by_address_change(map);
      }

      if (latlng) {
        // create marker
        marker = new google.maps.Marker({
          position: latlng,
          map: map,
          draggable: map.type === 'input'
        });
        map.markers.push(marker);

        if ($marker.html()) {
          // create info window
          var infowindow = new google.maps.InfoWindow({
            content: $marker.html()
          }); // show info window when marker is clicked

          google.maps.event.addListener(marker, 'click', function () {
            infowindow.open(map, marker);
          });
        } // update latitude and longitude values in the form when marker is moved


        if (map.type === 'input') {
          google.maps.event.addListener(marker, "dragend", function () {
            var point = marker.getPosition();
            map.panTo(point);
            update_latlng(point);
          });
        }
      }

      if (map.type === 'input') {
        $('.rtcl-map-field').on('blur change keyup', function () {
          re_render_map_by_address_change(map);
        });
      }
    });
    center_map(map);
  }

  function re_render_map_by_address_change(map) {
    var geoCoder = new google.maps.Geocoder(),
        address = get_generated_address();
    geoCoder.geocode({
      'address': address
    }, function (results, status) {
      if (status === google.maps.GeocoderStatus.OK) {
        var point = results[0].geometry.location,
            marker = map.markers[0];
        marker.setPosition(point);
        center_map(map);
        update_latlng(point);
        google.maps.event.addListener(marker, "dragend", function () {
          var point = marker.getPosition();
          map.panTo(point);
          update_latlng(point);
        });
      }
    });
  }

  function update_latlng(point) {
    $('#rtcl-latitude').val(point.lat());
    $('#rtcl-longitude').val(point.lng());
  }

  function center_map(map) {
    var bounds = new google.maps.LatLngBounds(); // loop through all markers and create bounds

    $.each(map.markers, function (i, marker) {
      var latlng = new google.maps.LatLng(marker.position.lat(), marker.position.lng());
      bounds.extend(latlng);
    }); // only 1 marker?

    if (map.markers.length === 1) {
      // set center of map
      map.setCenter(bounds.getCenter());
      map.setZoom(parseInt(rtcl.zoom_level));
    } else {
      // fit to bounds
      map.fitBounds(bounds);
    }
  }

  function render_map_view(view) {
    var bounds = new google.maps.LatLngBounds(),
        mapOptions = {
      center: new google.maps.LatLng(0, 0),
      zoom: 3,
      mapTypeId: google.maps.MapTypeId.ROADMAP,
      styles: ''
    },
        infoBox = new InfoBox({
      enableEventPropagation: true,
      maxWidth: 350,
      infoBoxClearance: new google.maps.Size(50, 50),
      alignBottom: true,
      pixelOffset: new google.maps.Size(-47, -75)
    }),
        markers = [],
        addedIDs = [],
        markerCluster,
        map,
        mapType = view.data('map-type') || '',
        itemData = view.data('map-data') || [];

    if (mapType === 'search') {
      itemData = getMarketData();
    }

    map = new google.maps.Map(view[0], mapOptions);
    $.each(itemData, function (index, _item) {
      var item = Object.assign({
        id: 0,
        latitude: 0,
        longitude: 0,
        icon: '',
        content: ''
      }, _item);
      var latlong = new google.maps.LatLng(item.latitude, item.longitude);
      var latLongKey = item.latitude + '-' + item.latitude;

      if (addedIDs.indexOf(item.id) === -1) {
        addedIDs.push(item.id);
        bounds.extend(latlong);
        var marker = new google.maps.Marker({
          position: latlong,
          icon: item.icon,
          content: item.content,
          map: map
        });
        markers.push(marker);
        marker.addListener('click', function () {
          infoBox.close();
          infoBox.setContent(marker.content);
          infoBox.setOptions({
            pixelOffset: new google.maps.Size(-47, -75)
          });
          infoBox.open(map, marker);
        });
      }
    });
    markerCluster = new MarkerClusterer(map, markers, {
      imagePath: rtcl.plugin_url + '/assets/images/map/m'
    });
    google.maps.event.addListener(markerCluster, 'click', function (cluster) {
      infoBox.close();
      var markers = cluster.getMarkers(),
          pos,
          samePosition = true;

      for (var i = 0; i < markers.length; i++) {
        if (!pos) {
          pos = markers[i].position;
        } else if (!pos.equals(markers[i].position)) {
          samePosition = false;
        }
      }

      if (samePosition) {
        var content = '<ul class="list-unstyled info-box-markers-list">',
            markers = cluster.getMarkers(),
            addedMarkers = [];
        $.each(markers, function (index, marker) {
          content += '<li>' + marker.content + '</li>';
        });
        content += '</ul>';
        infoBox.setContent(content);
        infoBox.setOptions({
          pixelOffset: new google.maps.Size(-45, -50)
        });
        infoBox.open(map, markers[markers.length - 1]);
        setTimeout(function () {
          $('.info-box-markers-list').scrollbar();
        }, 50);
        markerCluster.setZoomOnClick(false);
      }
    });
    map.fitBounds(bounds);
  }

  function getMarketData() {
    var data = [];
    $('.rtcl-search-map-lat-long').each(function () {
      var $this = $(this),
          $parent = $this.parents('.rtcl-listing-item'),
          $contentString = $('<div />');
      $contentString.append($parent.find('.rtcl-media').clone());
      $contentString.append($('<h5 class="rtcl-map-item-title" />').append($parent.find('.listing-title a').clone()));
      $contentString.find('h5').wrap('<div class="flex-right"></div>');
      $contentString.find('.flex-right').append('<div class="bottom-rtcl-meta flex-wrap"></div>');
      $contentString.find('.bottom-rtcl-meta').append($parent.find('.rtcl-price-amount').clone());
      $contentString.find('a').addClass('text-overflow').attr('target', '_blank');
      data.push({
        latitude: $this.data('latitude'),
        longitude: $this.data('longitude'),
        id: $this.data('id'),
        icon: $this.data('icon'),
        content: $contentString.html(),
        parent: $parent
      });
    });
    return data;
  }
})(jQuery);
