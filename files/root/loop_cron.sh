#!/bin/bash

while true
do
    sleep 120
    /var/www/html/console.php users:update --quiet
done