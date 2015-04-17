var map;
var start_marker;
var stop_marker;

// Map options
var mapOptions = {
    zoom: 12,
    center: new google.maps.LatLng(14.06332, 100.617085)
  };

// Marker options
// start marker
var start_marker_opts = {
  draggable : true,
  visible : true,
  zIndex : 110,
  optimized : true,
  title : 'Start',
  position : new google.maps.LatLng(14.06332, 100.617085),
  icon : 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
};
// stop marker
var stop_marker_opts = {
  draggable : true,
  visible : true,
  zIndex : 110,
  optimized : true,
  title : 'Stop',
  position : new google.maps.LatLng(14.07332, 100.627085),
  icon : 'http://maps.google.com/mapfiles/ms/icons/red-dot.png'
};

// Map directions
var dirOpts = {
      travelMode : google.maps.TravelMode.DRIVING,
      optimizeWaypoints : true
    };
var directions = new google.maps.DirectionsService();
var renderer = new google.maps.DirectionsRenderer({suppressMarkers : true});

function re_routing() {
  dirOpts.origin = start_marker.getPosition();
    dirOpts.destination = stop_marker.getPosition();
    directions.route(dirOpts, function(response, status) {
      if (status == google.maps.DirectionsStatus.OK) {
        var legs = response.routes[0].legs[0];
        renderer.setDirections(response);
        start_marker.setPosition(legs.start_location);
        stop_marker.setPosition(legs.end_location);
        $('#route_distance').val(legs.distance.text);
        $('#route_distance').attr('data-distance', legs.distance.value)
      }
    });
}

function format_float(fnum) {
  return Math.round(fnum * 100000) / 100000;
}

function calculate() {
  var distance = Number($('#route_distance').attr('data-distance'));
  var fluel_rate = Number($('#usage_rate').val());
  var gas_price = Number($('#gas_prices').val());
  var gas_product = $('#gas_prices > option:selected').attr('data-gas-product');
  // input validation
  if(distance == 0) {
    return alert('You have to drag start and stop markers to find distance.');
  }
  if(fluel_rate == 0 || fluel_rate > 100) {
    return alert('You have to specific Average Gas Usage rate. This number can be 1-100.');
  }
  if(gas_price == 0) {
    return alert('invalid Gas price. Gas price service unavailable, try it later.');
  }
  var cal = ((distance / 1000) / fluel_rate) * gas_price;
  $('#rs-cost').html(Math.round(cal * 100) / 100);
  $('#rs-gas-product').html(gas_product);
  $('#rs-gas-price').html(gas_price);
  $('#rs-distance').html(Math.round(distance / 1000));
  $('#result-modal').modal('show');
}

$(function() {
  // load map
  map = new google.maps.Map(document.getElementById('map-canvas'), mapOptions);

  // initial marker
  start_marker = new google.maps.Marker(start_marker_opts);
  stop_marker = new google.maps.Marker(stop_marker_opts);
  start_marker.setMap(map);
  stop_marker.setMap(map);

  // set direction between start and stop points
  renderer.setMap(map);
  google.maps.event.addListener(start_marker, 'dragend', function() {
    var start_pos = this.getPosition();
    var stop_pos = stop_marker.getPosition();
    $('#start_pos').val(format_float(start_pos.lat()) + ',' + format_float(start_pos.lng()) );
    $('#stop_pos').val(format_float(stop_pos.lat()) + ',' + format_float(stop_pos.lng()) );

    // re-render route path
    re_routing();
  });
  google.maps.event.addListener(stop_marker, 'dragend', function() {
    var start_pos = start_marker.getPosition();
    var stop_pos = this.getPosition();
    $('#start_pos').val('Lat:' + format_float(start_pos.lat()) + ', Lng:' + format_float(start_pos.lng()) );
    $('#stop_pos').val('Lat:' + format_float(stop_pos.lat()) + ', Lng:' + format_float(stop_pos.lng()) );

    // re-render route path
    re_routing();
  });

  // calculate modal
  $('#cal_btn').on('click', function(e) {
    calculate();
  });

  // user help modal
  $('#help-btn').on('click', function(e) {
    $('#help-modal').modal('show');
  });
})
