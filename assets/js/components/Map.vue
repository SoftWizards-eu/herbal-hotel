<template>
    <div class="map">
    </div>
</template>
<script> 
    let maps = []
    let api_loaded = false
    import point from '../../images/point.png'
    window.initMap = function() {
        maps.forEach(m => m.print());
    }
    export default {
        name :'map-module',
        props : {
            lat : {
                type: Number
            },
            lng : {
                type: Number
            },
            api : { 
                type: String
            },
            content : {
                type: String
            }
        },
        mounted() {
            maps.push(this)
            this.loadApi()
        },
        
        data : function() {
            return {
                map : null
            }
        },
        methods: {
            loadApi() {
                if (api_loaded) {
                    return
                }
                api_loaded = true
                var s = document.createElement('script');
                s.async = 'async';
                s.defer = 'defer';
                s.type = 'text/javascript';
                s.src = 'https://maps.googleapis.com/maps/api/js?key=' + this.api + '&callback=initMap';
                document.body.appendChild(s);
            },
            print() {
                var m = this.$el;
                var address = {
                        lat : parseFloat(this.lat),
                        lng : parseFloat(this.lng)
                };
                if (address.lat == 0 || address.lng == 0) {
                    return;
                }
    
                var myOptions = {
                    zoom: 12,
                    center: address,
                    mapTypeId: google.maps.MapTypeId.ROADMAP,
                    scrollwheel: false,
                    styles: this.styles
                }
                let map = this.map = new google.maps.Map(m, myOptions);
                var infoWindow = new google.maps.InfoWindow({
                    content : '<div class="text">'+this.content + '</div>'
                });
                var marker = new google.maps.Marker({
                    map: map,
                    icon : point,
                    position: address
                });
                marker.addListener('click', function() {
                    infoWindow.open(map, marker);
                });
            }
        },
        computed: {
            styles() {
                console.log(point)
                return [
                        {
                            "featureType": "administrative",
                            "elementType": "labels.text.fill",
                            "stylers": [
                                {
                                    "color": "#444444"
                                }
                            ]
                        },
                        {
                            "featureType": "landscape",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "color": "#f2f2f2"
                                }
                            ]
                        },
                        {
                            "featureType": "poi",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "visibility": "off"
                                }
                            ]
                        },
                        {
                            "featureType": "road",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "saturation": -100
                                },
                                {
                                    "lightness": 45
                                }
                            ]
                        },
                        {
                            "featureType": "road.highway",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "visibility": "simplified"
                                }
                            ]
                        },
                        {
                            "featureType": "road.arterial",
                            "elementType": "labels.icon",
                            "stylers": [
                                {
                                    "visibility": "off"
                                }
                            ]
                        },
                        {
                            "featureType": "transit",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "visibility": "off"
                                }
                            ]
                        },
                        {
                            "featureType": "water",
                            "elementType": "all",
                            "stylers": [
                                {
                                    "color": "#0088f6"
                                },
                                {
                                    "visibility": "on"
                                }
                            ]
                        }
                    ];


            }
        }
    }
</script>
<style>

</style>