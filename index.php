<!DOCTYPE html>
<html lang="en">
<head>
    <title>Poznan Transit Monitor</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <!-- Vue -->
    <script src="https://unpkg.com/vue"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue/dist/vue.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/vue-resource@1.5.1"></script>
    <script src="https://cdn.jsdelivr.net/npm/http-vue-loader@1.4.2"></script>
    <script src="https://unpkg.com/vue-router/dist/vue-router.js"></script>
    <!-- Leaflet -->
    <link href="https://unpkg.com/leaflet@1.5.1/dist/leaflet.css" rel="stylesheet" integrity="sha512-xwE/Az9zrjBIphAcBb3F6JVqxf46+CDLwfLMHloNu6KEQCAWi6HcDUbeOfBIptF7tcCzusKFjFw2yuvEpDL9wQ==" crossorigin="" />
    <script src="https://unpkg.com/leaflet@1.5.1/dist/leaflet.js" integrity="sha512-GffPMF3RvMeYyc1LWMHtK8EbPv0iNZ8/oTtHPx9/cc2ILxQ+u905qIwdpULaqDkyBKgOaB57QTMg7ztg8Jm2Og==" crossorigin=""></script>
    <script src="https://unpkg.com/vue2-leaflet"></script>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <!-- <link href="https://fonts.googleapis.com/icon?family=Material+Icons+Two+Tone" rel="stylesheet"> -->
    <!-- <link href="https://fonts.googleapis.com/css?family=Material+Icons|Material+Icons+Outlined|Material+Icons+Two+Tone|Material+Icons+Round|Material+Icons+Sharp" rel="stylesheet"> -->
    <!-- Custom CSS -->
    <link href="http://51.178.29.39/transit-monitor/style.css" rel="stylesheet" />
    <!-- Detect React App -->
    <script>
    const REACT_APP = <?= ($_SERVER['HTTP_X_REQUESTED_WITH'] == "com.transmonitor") ? 'true' : 'false' ?>;
    </script>
</head>

<body>
    <div id="app">
        <selector :mode="mode"></selector>
        <searcher :stop_id.sync="stop_id" v-show="mode == 'monitor'"></searcher>
        <monitor :stop_id="stop_id" v-if="mode == 'monitor'"></monitor>
        <timetable v-show="mode == 'timetable'"></timetable>
        <leaflet-map v-if="map" v-show="mode == 'map'"></leaflet-map>
    </div>

    <script src="http://51.178.29.39/transit-monitor/app.js"></script>
</body>
</html>
