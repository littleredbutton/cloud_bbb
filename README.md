# BigBlueButton™ integration for Nextcloud

[![Build Status](https://travis-ci.org/sualko/cloud_bbb.svg?branch=master)](https://travis-ci.org/sualko/cloud_bbb)
![Downloads](https://img.shields.io/github/downloads/sualko/cloud_bbb/total.svg)
![GitHub release](https://img.shields.io/github/release/sualko/cloud_bbb.svg)

This app allows to create meetings with an external installation of [BigBlueButton](https://bigbluebutton.org).

:clap: Developer wanted! If you have time it would be awesome if you could help to enhance this application.

__This app uses BigBlueButton and is not endorsed or certified by BigBlueButton Inc. BigBlueButton and the BigBlueButton Logo are trademarks of BigBlueButton Inc.__

![Screenshot configuration](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-configuration.png)

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
Get your BBB API url and secret by executing `sudo bbb-conf --secret` on your
BBB server.

```
$ sudo bbb-conf --secret

    URL: https://bbb.your.domain/bigbluebutton/
    Secret: abcdefghijklmnopqrstuvwxyz012345679

    Link to the API-Mate:
    https://mconf.github.io/api-mate/#server=https://...
```

Enter these values in the additional settings section on the admin
configuration page of your Nextcloud instance.

## Create your first room
Go to the BigBlueButton section inside your personal settings page and enter a
room name. That's it. You can now distribute the room url.

## Enter a room from files
Use the ... menu and select the desired BBB configuration to enter the room.
Beware that if the room is already running the presentation will **not** be
updated. Entering a room with a defined presentation works only if link shares
are enabled and do not require authentication. See [#1](https://github.com/sualko/cloud_bbb/issues/1)
for details.

![Screenshot file browser](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-file-browser.png)

# :notebook: Notes
- By using the [Link Editor](https://apps.nextcloud.com/apps/files_linkeditor)
  you can share rooms as any other file

# :pick: Troubleshooting
- Before installing, make sure your BBB is running correctly
- If no session opens with the ... menu of files, but a session opens in the
  general configuration page, look at your browser console. It will show a well
  hidden complaint that for sharing always a password is required. If this is
  the case, remove that requirement in sharing / enforce password.
- If the room doesn't appear in the ... menu of files, a browser/cache reload
  might help

# :heart: Sponsors
Writing such an application is a lot of work and therefore we are specially
thankful for people and organisations who are sponsoring features or bug fixes:

- [Medienwerkstatt Minden-Lübbecke e.V.](https://www.medienwerkstatt.org) manage recordings ([#19])

If you are looking for other ways to contribute to this project, you are welcome
to look at our [contributor guidelines]. Every contribution is valuable :tada:.

[contributor guidelines]: https://github.com/sualko/cloud_bbb/blob/master/.github/contributing.md
[#19]: https://github.com/sualko/cloud_bbb/issues/19