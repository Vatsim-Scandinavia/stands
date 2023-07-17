<?php

    // Script to cleanup the airport raw file only outputing relevant information for the service.
    // Run this if you download a new airport file from https://ourairports.com/data/

    $output = [];
    $data = json_decode(file_get_contents('data/airports.json'));
    $types = [];

    foreach($data as $d){

        if($d->type != "closed" && $d->type != "seaplane_base" && $d->type != "balloonport" && $d->type != "heliport" ){
            array_push($output, [
                "ident" => $d->ident,
                "latitude_deg" => $d->latitude_deg,
                "longitude_deg" => $d->longitude_deg,
            ]);
        }

        $types[$d->type] = true;
    }

    var_dump($types);

    file_put_contents('data/airports-new.json', json_encode($output));


?>