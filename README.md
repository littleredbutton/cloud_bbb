# BigBlueButton™ integration for Nextcloud

[![Build Status](https://travis-ci.org/sualko/cloud_bbb.svg?branch=master)](https://travis-ci.org/sualko/cloud_bbb)
![Downloads](https://img.shields.io/github/downloads/sualko/cloud_bbb/total.svg)
![GitHub release](https://img.shields.io/github/release/sualko/cloud_bbb.svg)

This app allows to create meetings with an external installation of [BigBlueButton](https://bigbluebutton.org).

:clap: Developer wanted! If you have time it would be awesome if you could help to enhance this application.

![Screenshot configuration](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-configuration.png)

__This app uses BigBlueButton and is not endorsed or certified by BigBlueButton Inc. BigBlueButton and the BigBlueButton Logo are trademarks of BigBlueButton Inc.__

## :heart_eyes: Features
This integration provides the following features:

* **Room setup** Create multiple room configurations with name, welcome message, ...
* **Share guest link** Share the room link with all your guests
* **Custom presentation** Start a room with a selected presentation from your file browser

## :rocket: Install it
The easiest way to install this app is by using the [Nextcloud app store](https://apps.nextcloud.com/apps/bbb).
If you like to build from source, please continue reading.

To install it change into your Nextcloud's apps directory:

    cd nextcloud/apps

Then run:

    git clone https://github.com/sualko/cloud_bbb.git bbb

Then install the dependencies using:

    make build


## :gear: Configure it
find out your BBB secrets by '''sudo bbb-conf --secret'''

enter these secrets in the BigBlueButton settings on the general configuration page of your NC

## Create your first room
Got to the BigBlueButton section inside your personal settings page and enter a
room name. That's it. You can now distribute the room url.

## Enter a room from files
Use the ... menu and select the desired BBB configuration to enter the room.
Beware that if the room is already running the presentation will **not** be
updated.

![Screenshot file browser](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-file-browser.png)

# Troubleshooting
- before installing, make sure your BBB is running correctly
- if no session opens with the ... menu of files, but a session opens in the
  general configuration page, look at your browser console. It will show a well
  hidden complaint that for sharing always a password is required. If this is
  the case, remove that requirement in sharing / enforce password.
- if the room doesn't appear in the ... menu of files, a browser/cache reload
  might help
