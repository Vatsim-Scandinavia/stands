# Stands
A website which correlates aircraft on the VATSIM network with stands. Used for Scandinavian airports, but available to use on all airports with Open Street Map data available.
Created by [Daniel L.](https://github.com/blt950) (1352906), based on the [stand plugin from Alex Toff](https://github.com/atoff/vatsim-stand-status).

# Installing

## Docker Image
- Pull the `ghcr.io/vatsim-scandinavia/stands:latest` Docker image
- Add your [environment variables](#Environment-variables)
- Bind the `/public/data/` folder to a folder on your host machine to presist or modify the data
- Start the container and setup your reverse proxy
- Done!

*You may also use the docker-compose file in the repository to get started, just tweak it to your liking.*

## Manual
- To install the website, you require PHP 7.4 or greater.
- When the content is installed, use `composer install` to install the required dependencies.
- Make sure the `/vendor/cobaltgrid/vatsim-stand-status/storage/data` and `vendor/skymeyer/vatsimphp/app/cache` and `vendor/skymeyer/vatsimphp/app/logs` folders are writeable
- Done!

# Configuring

## Environment variables
- `APP_TRACKING_SCRIPT` - A script that will be inserted in the header of the page. This can be used to add your Google Analytics or similar scripts.

## Data

By default the service will feed off data from Open Street Map. However, there's a possiblity to enrich and verify the airport data with manual data source in json.

First of all, make sure the `data/index.json` is updated with the airports you want to display in the menu.
Then you can supply airport data with stands, coordinates and category of stands in `data/<icao>.json` for the airports of choice. See example airports that are already uploaded.

The stand category is shown in the tooltip when hovering mouse of a stand, but also to decide the size of the ring.

Tip: When dealing with overlapping stands like L/R-stands, the tooltip gets drawn in the same order as the file. You might want to draw the biggest stand first, then the overlaps so you can view them all.