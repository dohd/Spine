#!/bin/sh
# compress core directories; app, routes and views
echo ========== compressing app ===========
zip -rq ~/Downloads/app ./app
echo ========== compressing routes ===========
zip -rq ~/Downloads/routes ./routes
echo ========== compressing views ===========
zip -rq ~/Downloads/views ./resources/views