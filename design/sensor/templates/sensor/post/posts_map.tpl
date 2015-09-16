<link rel="stylesheet" href="{'stylesheets/leaflet.0.7.2.css'|ezdesign(no)}" />
<link rel="stylesheet" href="{'stylesheets/MarkerCluster.css'|ezdesign(no)}" />
<link rel="stylesheet" href="{'stylesheets/MarkerCluster.Default.css'|ezdesign(no)}" />
{ezscript_require(array('ezjsc::jquery'))}
<script src="{'javascript/leaflet.0.7.2.js'|ezdesign(no)}"></script>
<script src="{'javascript/leaflet.markercluster.js'|ezdesign(no)}"></script>
<script src="{'javascript/Leaflet.MakiMarkers.js'|ezdesign(no)}"></script>

<div class="hidden-xs">
<div class="full_page_photo"><div id="map"></div></div>
</div>


  <script type="text/javascript">
    var PointsOfInterest = {sensor_root_handler().areas.coords_json};
    var CenterMap = new L.latLng(PointsOfInterest[0].coords[0], PointsOfInterest[0].coords[1]);    
{literal}
		var tiles = L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {maxZoom: 18,attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'});
		var map = L.map('map').addLayer(tiles);
    map.scrollWheelZoom.disable();
		var markers = L.markerClusterGroup();     
		$.getJSON("{/literal}{'/sensor/data'|ezurl(no)}{literal}?contentType=geojson", function(data) {
      console.log(data);
      if (data !== null) {
        var geoJsonLayer = L.geoJson(data);
        markers.addLayer(geoJsonLayer);
        map.addLayer(markers);
        map.fitBounds(markers.getBounds());
      }else{
        map.setView(CenterMap, 13);
      }
    });    
    markers.on('click', function (a) {
      $.getJSON("{/literal}{'/sensor/data'|ezurl(no)}{literal}?contentType=marker&id="+a.layer.feature.id, function(data) {
        var popup = new L.Popup({maxHeight:360});
        popup.setLatLng(a.layer.getLatLng());
        popup.setContent(data.content);
        map.openPopup(popup); 
      });        
    });
{/literal}    
  </script>

