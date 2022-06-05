<?php
/*
 * This is an integrated example of how to use StandStatus's OSM data feature to create a StandStatus instance.
 * The target airport here is London Heathrow (EGLL).
 */

use CobaltGrid\VatsimStandStatus\StandStatus;
require_once '../vendor/autoload.php';

$indexes = json_decode(file_get_contents("data/index.json"));
$airport = false;
$airportCords = [];

$alertChooseAirport = false;
$alertNotFound = false;
$alertNoData = false;
$alertNotVerified = true;

// Define airport from GET
$searchInput = substr(filter_input(INPUT_GET, 'icao', FILTER_SANITIZE_EMAIL), 0, 4);
if(isset($searchInput) && !empty($searchInput)){
    $airport = strtolower($searchInput);
} else {
    $alertChooseAirport = true;
}

// Do we have local data for that airport? If yes, load our quality ensured .json, otherwise load from OSM
if(isset($airport) && $airport && file_exists('data/'.$airport.'.json')){

    $airportData = json_decode(file_get_contents('data/'.$airport.'.json'), true);
    $airportCords = [$airportData["airport"]["latitude"], $airportData["airport"]["longitude"], $airportData["stands"]];

    $StandStatus = new StandStatus($airportCords[0], $airportCords[1], StandStatus::COORD_FORMAT_DECIMAL);
    $StandStatus->setMaxDistanceFromAirport(4)->loadStandDataFromArray($airportData["stands"])->parseData();

} else {

    // Load airports database
    $allAirports = json_decode(file_get_contents("data/airports.json"), true);

    // Find the airport's data
    foreach($allAirports as $data){
        if($data["ident"] == strtoupper($airport)){
            $airportCords = [$data["latitude_deg"], $data["longitude_deg"]];
            break;
        }
    }

    // If we didn't find airport or not set, load data from OSM if any
    if(!empty($airportCords)){
        try {
            $StandStatus = new StandStatus($airportCords[0], $airportCords[1], StandStatus::COORD_FORMAT_DECIMAL);
            $StandStatus->setMaxDistanceFromAirport(4)->fetchAndLoadStandDataFromOSM($airport)->parseData();
        } catch(CobaltGrid\VatsimStandStatus\Exceptions\NoStandDataException $e) {
            $alertNoData = true;
        } catch(CobaltGrid\VatsimStandStatus\Exceptions\InvalidStandException $e) {
            // Continue and skip, this is unverified data source anyway
        } catch(CobaltGrid\VatsimStandStatus\Exceptions\InvalidICAOCodeException $e){
            // Airport doesn't exist
            $airport = false;
            $alertNotFound = true;
        }
    } else {
        // Airport doesn't exist
        $airport = false;
        $alertNotFound = true;
    }
    
}

// Check if the current airport is verified data, otherwise show warning
if($airport){
    foreach($indexes as $index){
        foreach($index as $a){
            if($a->icao == strtoupper($airport)){
                if($a->verified == true){
                    $alertNotVerified = false;
                    break;
                }
            }
        }        
    }
}

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

        <!-- Global site tag (gtag.js) - Google Analytics -->
        <script async src="https://www.googletagmanager.com/gtag/js?id=G-FLBQS8NJPN"></script>
        <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());

        gtag('config', 'G-FLBQS8NJPN');
        </script>
    </head>
    <body>
        <!-- Navigation -->
        <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
            <div class="container">

                <a class="navbar-brand" href="/">
                    <img src="img/square-logo.png" alt="" height="25" class="d-inline-block align-text-top">
                    Stands
                </a>

                <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarScroll" aria-controls="navbarScroll" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
                </button>
                <div class="collapse navbar-collapse" id="navbarScroll">
                <ul class="navbar-nav me-auto my-2 my-lg-0 navbar-nav-scroll">

                    <?php
                        foreach($indexes as $country => $index){
                            echo '
                            <li class="nav-item dropdown">
                            <a class="nav-link dropdown-toggle" href="#" id="navbarScrollingDropdown" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                                '.$country.'
                            </a>
                            <ul class="dropdown-menu" aria-labelledby="navbarScrollingDropdown">
                            ';

                            foreach($index as $a){
                                echo '
                                    <li><a class="dropdown-item" href="?icao='.$a->icao.'">'.strtoupper($a->icao).' - '.$a->city.'</a></li>
                                ';
                            }

                            echo '
                            </ul>
                            </li>
                            ';
                        }
                    ?>

                </ul>
                <form class="d-flex">
                    <input id="search" name="icao" class="form-control form-control-sm" type="search" placeholder="Search for any ICAO" maxlength="4" aria-label="Search">
                </form>
                </div>
            </div>
        </nav>

        <?php

            if($alertChooseAirport){
                echo '
                <div class="alert alert-info" role="alert">
                    Choose or search for airport
                </div>
                ';
            } elseif($alertNotFound){
                echo '
                <div class="alert alert-danger" role="alert">
                    Airport not found
                </div>
                ';
            } elseif($alertNoData){
                echo '
                <div class="alert alert-danger" role="alert">
                    No stand data is available for this airport.
                </div>
                ';
            } elseif($alertNotVerified){
                echo '
                <div class="alert alert-warning" role="alert">
                    Warning: This airport\'s data source is not quality ensured.
                </div>
                ';
            }

        ?>

        <!-- Map -->
        <div id="map"></div>

        <!-- Footer -->
        <footer>
            <a href="https://vatsim-scandinavia.org" target="_blank">
                <img src="img/negative-logo.svg" height="75">
            </a>
        </footer>

        <!-- Map Script -->
        <script>
            <?php 

                if($airport){
                    // Create map based on airport coords
                    echo 'var map = L.map("map").setView(['.$airportCords[0].', '.$airportCords[1].'], 15);';
                } else {
                    // Show map over Scandinavia
                    echo 'var map = L.map("map").setView([61.269332358502595, 11.51592413253783], 5);';
                }

            ?>
            L.tileLayer('https://{s}.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
                maxZoom: 18,
                subdomains:['mt0','mt1','mt2','mt3'],
                attribution: 'Data &copy; VATSCA, OpenStreetMap/OurAirports Contributors'
            }).addTo(map);

            <?php
                if($airport){
                    foreach($StandStatus->stands() as $stand){

                        $category = null;
                        $wingspan = null;
                        $radius = 24;

                        // If airport stands data are supplied
                        if(isset($airportCords[2])){

                            // Set category and wingspan tag based on supplied data
                            foreach($airportCords[2] as $sd){
                                if($sd[0] == $stand->id){
                                    $category = $sd[3];
                                    $wingspan = $sd[4];
                                    break;
                                }
                            }

                            // Set radius based on stand category
                            switch($category){
                                case "A": $radius = 7; break;
                                case "B": $radius = 12; break;
                                case "C": $radius = 18; break;
                                case "D": $radius = 26; break;
                                case "E": $radius = 32; break;
                                case "F": $radius = 40; break;
                                case "H": $radius = 15; break;
                            }

                            // Set wing radius to wingspan variable if exists
                            if($wingspan){ $radius = $wingspan/2; }

                        }

                        echo '
                        L.circle(['.$stand->latitude.', '.$stand->longitude.'], {
                            color: '.($stand->isOccupied() ? '"#f31e23"' : '"#35ee34"').',
                            fillColor: '.($stand->isOccupied() ? '"#f31e23"' : '"#35ee34"').',
                            fillOpacity: 0.25,
                            radius: '.$radius.'
                        })
                        .addTo(map)
                        ';

                        if($category){
                            echo '
                            .bindPopup("<table class=\"table\"><tr><th class=\"fw-normal\">Stand</th><th>'.$stand->getName().'</th></tr><tr><th class=\"fw-normal\">Code</th><th>'.$category.'</th></tr><tr><th class=\"fw-normal\">Status</th><th>'.($stand->isOccupied() ? "<span class='text-danger'>Occupied (".$stand->occupier->callsign.")</span>" : "<span class='text-success'>Available</span>").'</th></tr></table>")
                            ';
                        } elseif($wingspan){
                            echo '
                            .bindPopup("<table class=\"table\"><tr><th class=\"fw-normal\">Stand</th><th>'.$stand->getName().'</th></tr><tr><th class=\"fw-normal\">Wingspan</th><th>max '.round($wingspan).'m</th></tr><tr><th class=\"fw-normal\">Status</th><th>'.($stand->isOccupied() ? "<span class='text-danger'>Occupied (".$stand->occupier->callsign.")</span>" : "<span class='text-success'>Available</span>").'</th></tr></table>")
                            ';
                        } else {
                            echo '
                            .bindPopup("<table class=\"table\"><tr><th class=\"fw-normal\">Stand</th><th>'.$stand->getName().'</th></tr><tr><th class=\"fw-normal\">Status</th><th>'.($stand->isOccupied() ? "<span class='text-danger'>Occupied (".$stand->occupier->callsign.")</span>" : "<span class='text-success'>Available</span>").'</th></tr></table>")
                            ';
                        }
                        

                        echo '
                        .on("mouseover", function (e) { this.openPopup(); })
                        .on("mouseout", function (e) { this.closePopup(); });
                        ';
                    }
                }
                
            ?>

        </script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" integrity="sha384-ka7Sk0Gln4gmtz2MlQnikT1wXgYsOg+OMhuP+IlRH9sENBO0LRn5q+8nbTov4+1p" crossorigin="anonymous"></script>
    </body>
</html>