jQuery(document).ready(function ($) {
    // Check if the map container element exists
    if ($('#loc-media-container').length > 0) {
        var map = new ol.Map({
            target: 'loc-media-container',
            layers: [
                new ol.layer.Tile({
                    source: new ol.source.OSM()
                })
            ],
            view: new ol.View({
                center: ol.proj.fromLonLat([0, 0]),
                zoom: 2
            })
        });
    }
});