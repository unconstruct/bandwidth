$(function() {
    new FastClick(document.body);

    var map,
        geolocate;

    //Build the map with zoom bounds, and a set view on DC
    map = L.mapbox.map('litmap', 'wamu.map-nhi22yr3,wamu.literacy-map', {
      maxZoom: 9,
      minZoom: 3,
      detectRetina: true,
      retinaVersion: 'wamu.map-jseql4x8,wamu.literacy-map'
    }).setView([38.91,-77],7);

    //Movetip
    map.gridControl.options.follow = true;

    //Add the legend
    map.legendControl.addLegend(document.getElementById('legend-content').innerHTML);

    geolocate = document.getElementById('geolocate');

    // This uses the HTML5 geolocation API, which is available on
    // most mobile browsers and modern browsers, but not in Internet Explorer
    if (!navigator.geolocation) {
        alert('Geolocation is not available. Try again on another device');
    } else {
        geolocate.onclick = function (e) {
            e.preventDefault();
            e.stopPropagation();
            map.locate();
        };
    }

    // Once we've got a position, zoom and center the map
    // on it, and add a single marker.
    map.on('locationfound', function(e) {
        map.fitBounds(e.bounds);

        map.markerLayer.setGeoJSON({
            type: "Feature",
            geometry: {
                type: "Point",
                coordinates: [e.latlng.lng, e.latlng.lat]
            },
            properties: {
                'marker-color': '#000',
            }
        });

        // And hide the geolocation button
        geolocate.parentNode.removeChild(geolocate);
    });

    // If the user chooses not to allow their location
    // to be shared, display an error message.
    map.on('locationerror', function() {
        alert('Position could not be found.');
    });
});