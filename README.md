# BigBlueButton™ integration for Nextcloud

:arrow_right: __This app uses BigBlueButton and is not endorsed or certified by BigBlueButton Inc. BigBlueButton and the BigBlueButton Logo are trademarks of BigBlueButton Inc.__

## Install it
To install it change into your Nextcloud's apps directory:

    cd nextcloud/apps

Then run:

    git clone https://github.com/sualko/cloud_bbb.git bbb

Then install the dependencies using:

    make build
    

## configure it
find out your BBB secrets by '''sudo bbb-conf --secret'''

enter these secrets in the BigBlueButton settings on the general configuration page of your NC

## create your first room
go to your personal settings page

find the BigBlueButton section

enter a room name

enter the room by pointing to the arrow button

## enter a room from files

(to be edited as soon as it works for me)
