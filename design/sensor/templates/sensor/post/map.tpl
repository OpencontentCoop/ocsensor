{if $post.geoLocation}
<link rel="stylesheet" href="{'stylesheets/leaflet.0.7.2.css'|ezdesign(no)}" />
<script src="{'javascript/leaflet.0.7.2.js'|ezdesign(no)}"></script>
<script src="{'javascript/Leaflet.MakiMarkers.js'|ezdesign(no)}"></script>

<div id="map" style="width: 100%; height: 200px;"></div>

{if $post.geoLocation.address}
	<small><i class="fa fa-map-marker"></i> {$post.geoLocation.address|wash()}</small>
{elseif $post.areas|count()}
	<small><i class="fa fa-map-marker"></i> {foreach $post.areas as $area}{$area.name|wash()}{/foreach}</small>
{/if}


{literal}
    <script type="text/javascript">
        var latlng={/literal}[{$post.geoLocation.latitude},{$post.geoLocation.longitude}]{literal};
        var map = new L.Map('map');
        map.scrollWheelZoom.disable();
        var customIcon = L.MakiMarkers.icon({icon: "star", color: "#f00", size: "l"});
        var postMarker = new L.marker(latlng,{icon:customIcon});
        postMarker.addTo(map);
        map.setView(latlng, 18);
        L.tileLayer('//{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="http://osm.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
    </script>
{/literal}
{/if}
