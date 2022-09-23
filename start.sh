#!/bin/sh
# start php server listening on port 8000
gnome-terminal -- sh -c "php -S localhost:8000 -t public; bash"
