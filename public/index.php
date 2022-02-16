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

        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-1BmE4kWBq78iYhFldvKuhfTAU6auU8tT94WrHftjDbrCEXSU1oBoqyl2QvZ6jIW3" crossorigin="anonymous">
        <link href="style.css" rel="stylesheet">

        <!-- Leaflet -->
        <link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" integrity="sha512-xodZBNTC5n17Xt2atTPuE1HxjVMSvLVW9ocqUKLsCC5CXdbqCmblAshOMAS6/keqq/sMZMZ19scR4PsZChSR7A==" crossorigin=""/>
        <script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js" integrity="sha512-XQoYMqMTK8LvdxXYG3nZ448hOEQiglfqkJs1NOQV44cWnUrBc8PkAOcXy20w0vlaXaVUearIOBhiXZ5V3ynxwA==" crossorigin=""></script>

        <!-- GMaps & Labels -->
        <script src="https://maps.googleapis.com/maps/api/js?key="></script>
        <script src="js/maplabel-min.js"></script>        
    </head>
    <body>
        <nav class="navbar navbar-expand-lg navbar-light bg-light">
            <div class="container">
                <a class="navbar-brand" href="/">Stands</a>
                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll" style="--bs-scroll-height: 100px;">
                    <li class="nav-item">
                        <a class="nav-link active" aria-current="page" href="/">Overview</a>
                    </li>
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
                    <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
                    <button class="btn btn-outline-success" type="submit">Search</button>
                </form>
                </div>
            </div>
        </nav>

        <div class="container">
            <div class="row">
                <div class="col">
                    <div id="map" style="height: 500px;width: 100%;"></div>
                </div>
            </div>
            <div class="row">
                <div class="col d-flex flex-column justify-content-center">
                    <h5>All Stands</h5>
                    <i>Stand Data &copy; OpenStreetMap Contributors</i>
                    <table id="standsTable" class="table table-responsive table-sm text-center align-self-center">
                        <thead>
                        <tr>
                            <th>Stand</th>
                            <th>Latitude</th>
                            <th>Longitude</th>
                            <th>Occupier</th>
                        </tr>
                        </thead>
                        <tbody>
                            <?php
                                foreach ($StandStatus->stands() as $stand) {
                                    echo '
                                    <tr>
                                        <td>'.$stand->getName().'</td>
                                        <td>'.$stand->latitude.'</td>
                                        <td>'.$stand->longitude.'</td>
                                        <td>'.($stand->isOccupied() ? $stand->occupier->callsign : null).'</td>
                                    </tr>
                                    ';
                                }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <footer>
            hei
        </footer>

        <!-- Map Script -->
        <script>
            var center = {lat:60.28909078470454, lng: 5.227381717245824};
            map = new google.maps.Map(document.getElementById('map'), {
                zoom: 14,
                mapTypeId: 'satellite',
                center: center,
                disableDefaultUI: true

            });
            
            <?php
                foreach($StandStatus->stands() as $stand){
                    echo '
                    new google.maps.Circle({
                        strokeColor: '.($stand->isOccupied() ? '"#FF0000"' : '"#00FF00"').',
                        strokeOpacity: 0.8,
                        strokeWeight: 2,
                        fillColor: '.($stand->isOccupied() ? '"#FF0000"' : '"#00FF00"').',
                        fillOpacity: 0.35,
                        map: map,
                        center: {
                            lat: '.$stand->latitude.',
                            lng: '.$stand->longitude.'
                        },
                        radius: 40
                    });';
                }

                if($stand->isOccupied()){

                    echo '
                    new MapLabel({
                        text: "'.$stand->occupier->callsign.'",
                        position: new google.maps.LatLng(
                            '.$stand->occupier->latitude.',
                            '.$stand->occupier->longitude.'
                        ),
                        map: map,
                        fontSize: 12,
                        strokeWeight: 2
                    });
                    ';

                }

            ?>

        </script>

        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>