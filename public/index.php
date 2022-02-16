<?php
/*
 * This is an integrated example of how to use StandStatus's OSM data feature to create a StandStatus instance.
 * The target airport here is London Heathrow (EGLL).
 */

use CobaltGrid\VatsimStandStatus\StandStatus;

require_once '../vendor/autoload.php';

$StandStatus = new StandStatus(60.28909078470454, 5.227381717245824, StandStatus::COORD_FORMAT_DECIMAL);
$StandStatus->setMaxDistanceFromAirport(2)->fetchAndLoadStandDataFromOSM("ENBR")->parseData();

?>

<html lang="en">
    <head>
        <title>Stands | VATSIM Scandinavia</title> 
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link href="style.css" rel="stylesheet">
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,100;0,300;0,400;0,500;0,700;1,100;1,300;1,400;1,500;1,700&display=swap" rel="stylesheet">

        <!-- Leaflet -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

        <!-- GMaps & Labels -->
        <script src="https://maps.googleapis.com/maps/api/js?key="></script>
        <script src="js/maplabel-min.js"></script>        
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">

                <a class="navbar-brand" href="/">
                    <img src="img/division-square.png" alt="" height="25" class="d-inline-block align-text-top">
                    Stands
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Denmark
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            <li><a class="dropdown-item" href="?icao=ekch">EKCH - Copenhagen</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Finland
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            <li><a class="dropdown-item" href="?icao=efhk">EFHK - Helsinki</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Iceland
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            <li><a class="dropdown-item" href="?icao=bikf">BIKF - Keflavik</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Norway
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            <li><a class="dropdown-item" href="?icao=engm">ENGM - Oslo</a></li>
                            <li><a class="dropdown-item" href="?icao=enbr">ENBR - Bergen</a></li>
                            <li><a class="dropdown-item" href="?icao=enva">ENVA - Trondheim</a></li>
                            <li><a class="dropdown-item" href="?icao=enzv">ENZV - Stavanger</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            Sweden
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            <li><a class="dropdown-item" href="?icao=essa">ESSA - Stockholm</a></li>
                        </ul>
                    </li>
                </ul>
                <form class="d-flex">
                    <input id="search" name="icao" class="form-control form-control-sm" type="search" placeholder="Search for ICAO" aria-label="Search">
                </form>
                </div>
            </div>
        </nav>

        <!-- Map -->
        <div id="map"></div>

        <!-- Footer -->
        <footer>
            <a href="https://vatsim-scandinavia.org" target="_blank">
                <img src="img/vatsca-logo-negative.svg" height="75">
            </a>
        </footer>

        <!-- Map Script -->
        <script>
            var map = L.map('map').setView([60.28909078470454, 5.227381717245824], 16);
            L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 17,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: 'Data &copy; VATSIM Scandinavia &copy; OpenStreetMap Contributors'
            }).addTo(map);

            <?php
                foreach($StandStatus->stands() as $stand){
                    echo '
                    L.circle(['.$stand->latitude.', '.$stand->longitude.'], {
                        color: '.($stand->isOccupied() ? '"#f31e23"' : '"#35ee34"').',
                        fillColor: '.($stand->isOccupied() ? '"#f31e23"' : '"#35ee34"').',
                        fillOpacity: 0.25,
                        radius: 15
                    })
                    .addTo(map)
                    .bindPopup("<table class=\"table\"><tr><th class=\"fw-normal\">Stand</th><th>'.$stand->getName().'</th></tr><tr><th class=\"fw-normal\">Category</th><th>C</th></tr><tr><th class=\"fw-normal\">Status</th><th>'.($stand->isOccupied() ? "<span class='text-danger'>Occupied (".$stand->occupier->callsign.")</span>" : "<span class='text-success'>Available</span>").'</th></tr></table>")
                    .on("mouseover", function (e) { this.openPopup(); })
                    .on("mouseout", function (e) { this.closePopup(); });
                    ';
                }
            ?>

        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>