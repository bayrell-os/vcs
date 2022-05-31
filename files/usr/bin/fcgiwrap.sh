#!/bin/bash

chown fcgiwrap:www /var/run/fcgiwrap
yes | rm -f /var/run/fcgiwrap/fcgiwrap.sock

for i in $(seq 1 30); do
    sleep $i && chmod 660 /var/run/fcgiwrap/fcgiwrap.sock &
done

sudo -u www /usr/bin/fcgiwrap -s unix:/var/run/fcgiwrap/fcgiwrap.sock
