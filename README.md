# BigBlueButton™ integration for Nextcloud

![Static analysis](https://github.com/sualko/cloud_bbb/workflows/Static%20analysis/badge.svg)
![PHP Tests](https://github.com/sualko/cloud_bbb/workflows/PHP%20Tests/badge.svg)
![Lint](https://github.com/sualko/cloud_bbb/workflows/Lint/badge.svg)
![Downloads](https://img.shields.io/github/downloads/sualko/cloud_bbb/total.svg)
![GitHub release](https://img.shields.io/github/release/sualko/cloud_bbb.svg)

[![ko-fi](https://www.ko-fi.com/img/githubbutton_sm.svg)](https://ko-fi.com/sualko)

This app allows to create meetings with an external installation of [BigBlueButton](https://bigbluebutton.org).

:clap: Developer wanted! If you have time it would be awesome if you could help to enhance this application.

__This app uses BigBlueButton and is not endorsed or certified by BigBlueButton Inc. BigBlueButton and the BigBlueButton Logo are trademarks of BigBlueButton Inc.__

![Screenshot configuration](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-configuration.png)

## :heart_eyes: Features
This integration provides the following features:

* **Room setup** Create multiple room configurations with name, welcome message, ...
* **Share guest link** Share the room link with all your guests
* **Share rooms** Share rooms with members, groups or circles
* **Custom presentation** Start a room with a selected presentation from your file browser
* **Manage recordings** View, share and delete recordings for your rooms
* **Restrictions** Restrict room creation to certain groups
* **Activities** Get an overview of your room activities

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

![Screenshot admin section](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-admin.png)

### Manual configuration (for experts)
If you prefer not to use the web interface for configuration, you will find all
used configuration keys in the list below. Please beware that there will be no
check if those values are correct. Therefore this is not the recommended way.
The syntax to set all settings is `occ config:app:set bbb KEY --value "VALUE"`.

Key                               | Description
--------------------------------- | ------------------------------------------------------------------------------------
`app.navigation`                  | Set to `true` to show navigation entry
`app.navigation.name`             | Defines the navigation label. Default "BigBlueButton".
`api.url`                         | URL to your BBB server. Should start with `https://`
`api.secret`                      | Secret of your BBB server
`app.shortener`                   | Value of your shortener service. Should start with `https://` and contain `{token}`.
`avatar.path`                     | Absolute path to an optional avatar cache directory.
`avatar.url`                      | URL which serves `avatar.path` to be used as avatar cache.
`api.meta_analytics-callback-url` | URL which gets called after meetings ends to generate statistics.

### Avatar cache (v2.2+)
The generation of avatars puts a high load on your Nextcloud instance, since the
number of requests increases squarely to the number of participants in a room.
To mitigate this situation, this app provides an optional avatar file cache. To
activate the cache `avatar.path` and `avatar.url` have to be configured.
`avatar.path` must provide an absolute path (e.g. `/srv/bbb-avatar-cache/`) to a
directory which is writable by the PHP user. `avatar.url` must contain the url
which serves all files from `avatar.path`. To bypass browser connection limits
we recommend to setup a dedicated host.

Example Apache configuration for a dedicated host with `avatar.path = /srv/bbb-avatar-cache/`
and `avatar.url = https://avatar-cache.your-nextcloud.com/`:

```
<VirtualHost *:443>
        ServerName avatar-cache.your-nextcloud.com

        Header always set Strict-Transport-Security "max-age=15768000;"

        DocumentRoot /srv/bbb-avatar-cache
        <Directory /srv/bbb-avatar-cache>
                Options -FollowSymLinks -Indexes
        </Directory>

        SSLEngine On
        # SSL config...
</VirtualHost>
```

For additional security, we recommend to disable directory listing, symlinks and
any language interpreter such as php for the cache directory.

Cached avatars are usually deleted as soon as the meeting ends. In cases the BBB
server shuts down unexpected, we provide the `bbb:clear-avatar-cache` occ
command (example use: `./occ bbb:clear-avatar-cache`).


## :bowtie: User guide

### Create your first room
Go to the BigBlueButton section inside your personal settings page and enter a
room name. That's it. You can now distribute the room url.

### Enter a room from files
Use the ... menu and select the desired BBB configuration to enter the room.
Beware that if the room is already running the presentation will **not** be
updated. Entering a room with a defined presentation works only if link shares
are enabled and do not require authentication. See [#1](https://github.com/sualko/cloud_bbb/issues/1)
for details.

![Screenshot file browser](https://github.com/sualko/cloud_bbb/raw/master/docs/screenshot-file-browser.png)

## :notebook: Notes
- By using the [Link Editor](https://apps.nextcloud.com/apps/files_linkeditor)
  you can share rooms as any other file

## :pick: Troubleshooting
- Before installing, make sure your BBB is running correctly
- If the room doesn't appear in the ... menu of files, a browser/cache reload
  might help
- To share an audio (mp3) or video (mp4) file with your audience follow these steps (more info in [#148](https://github.com/sualko/cloud_bbb/issues/148#issuecomment-827338650)):
  - create a public share of the exact file
  - copy the location of the share from the share information screen into your clipboard
  - open big blue button, press the plus icon in the lower left corner
  - click on add external video
  - paste the url from your clipboard and append the following string for audio `/download?.mp3` or `/download?.mp4` for video files
- To connect to a ScaleLite server, use the url like `https://yourscalelite.url/bigbluebutton/` without additional `api/` and as secret ScaleLite's `LOADBALANCER_SECRET`

## :heart: Sponsors
Writing such an application is a lot of work and therefore we are specially
thankful for people and organisations who are sponsoring features or bug fixes:

- [Medienwerkstatt Minden-Lübbecke e.V.](https://www.medienwerkstatt.org) manage recordings ([#19])
- [Deutscher Bundesjugendring](https://www.dbjr.de) version [0.4.0], version [0.5.0]
- [Graz University of Technology](https://www.tugraz.at) form action ([#47]), navigation entry ([#31]), restrictions ([#43], [#53]), circles ([#61])
- [Arawa](https://www.arawa.fr) UX audit
- [Niedersächsisches Landesinstitut für schulische Qualitätsentwicklung – Netzwerk Medienberatung](https://nlq.niedersachsen.de/) moderator url, UX improvements
- [Integrierte Gesamtschule Lengede](http://www.igs-lengede.de/) theme, join options

If you are looking for other ways to contribute to this project, you are welcome
to look at our [contributor guidelines]. Every contribution is valuable :tada:.

[contributor guidelines]: https://github.com/sualko/cloud_bbb/blob/master/.github/contributing.md
[#19]: https://github.com/sualko/cloud_bbb/issues/19
[#47]: https://github.com/sualko/cloud_bbb/issues/47
[#31]: https://github.com/sualko/cloud_bbb/issues/31
[#43]: https://github.com/sualko/cloud_bbb/issues/43
[#53]: https://github.com/sualko/cloud_bbb/issues/53
[#61]: https://github.com/sualko/cloud_bbb/issues/61
[0.4.0]: https://github.com/sualko/cloud_bbb/releases/tag/v0.4.0
[0.5.0]: https://github.com/sualko/cloud_bbb/releases/tag/v0.5.0
