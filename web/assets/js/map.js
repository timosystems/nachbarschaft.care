var map = null;
var plzIcons = null;
var polygon = null;
var selectedPlzCenter = null;
var selectedPlzPolygon = null;

$( document ).ready(function() {
    plzIcons = JSON.parse($('#plz-icons').attr('data-js'));
    selectedPlzCenter = JSON.parse($('#plz-selected-center').attr('data-js'));
    selectedPlzPolygon = JSON.parse($('#plz-selected-polygon').attr('data-js'));

    map = L.map('map').setView([51.133481,10.018343], 6);

    L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 18,
        minZoom: 6,
    }).addTo(map);

    var markers = L.markerClusterGroup({
        iconCreateFunction: function(cluster) {
            var children = cluster.getAllChildMarkers();
            var counter = 0;
            for (i = 0; i < children.length; i++) {
                counter = counter + children[i]['options']['icon']['options']['dataCount'];
            }
            return L.divIcon({ 
                html: '<div class="map-cluster-group-icon"><div class="map-icon-content">' + counter + '<i class="fas fa-users ml-2"></i></div></div>',
            });
        },
        showCoverageOnHover: false,
    });

    for (i = 0; i < plzIcons.length; i++) {
        markers.addLayer(L.marker([plzIcons[i][2][0], plzIcons[i][2][1]], {icon: new L.DivIcon({
            html: '<a class="maplink" href="/?plz=' + plzIcons[i][0] + '"><div class="map-icon"><div class="map-icon-header">' + plzIcons[i][0] + '</div><div class="map-icon-content">' + plzIcons[i][1] + '<i class="fas fa-user ml-2"></i></div></div></a>',
            iconSize: L.point(54, 42),
            dataCount: plzIcons[i][1]
        })}));        
    }

    map.addLayer(markers);

    if(selectedPlzCenter != null){
        addPLZInfo();
    }
});

function addPLZInfo(){
    polygon = L.polygon(selectedPlzPolygon)
    polygon.addTo(map);
    zoomPLZInfo();
}

function zoomPLZInfo(){
    map.setView(selectedPlzCenter, 12);
    map.invalidateSize();
}

function removePolygon(){
    polygon.remove(map);
}