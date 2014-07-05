TinyIB - A Lightweight and Efficient [Image Board](http://en.wikipedia.org/wiki/Imageboard) Script
====

**Got database? Get speed.**  Use [MySQL](http://mysql.com) or [SQLite](http://sqlite.org) for an efficient set-up able to handle high amounts of traffic.

**No database?  No problem.**  Store posts as text files for a portable set-up capable of running on virtually any PHP host.

To allow new threads without requiring an image, see the [Text Board Mode](https://github.com/tslocum/TinyIB/wiki/Text-Board-Mode) page.

For demos see the [TinyIB Installations](https://github.com/tslocum/TinyIB/wiki) page.  [![githalytics.com alpha](https://cruel-carlota.pagodabox.com/5135372febbc40bacddbb13c1f0a8333 "githalytics.com")](http://githalytics.com/tslocum/TinyIB)

Features
------------
 - GIF, JPG, PNG, SWF and WebA/WebM upload.
 - Reference links >>###
 - Delete post via password.
 - Management panel:
   - Administrators and moderators use separate passwords.
     - Moderators are only able to delete posts.
   - Ban offensive/abusive posters across all boards.
   - Post using raw HTML.
   - Upgrade automatically when installed via git.  (Tested on Linux only)

Installing
------------

 1. Verify the following requirements are met:
    - [PHP](http://php.net) 4 or higher is installed.
    - [GD Image Processing Library](http://php.net/gd) is installed.
      - This library is installed by default on most hosts.
 2. CD to the directory you wish to install TinyIB.
 3. Run the command:
    - `git clone git://github.com/tslocum/TinyIB.git ./`
 4. Copy **settings.default.php** to **settings.php**
 5. Configure **settings.php**
    - To remove the play icon from .SWF/.WebM thumbnails, delete or rename **video_overlay.png**.
    - To allow WebA/WebM upload:
      - Ensure your web host is running Linux.
      - Install [mediainfo](http://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/).  On Ubuntu, run ``sudo apt-get install mediainfo ffmpegthumbnailer``.
      - Set ``TINYIB_WEBM`` to ``true``.
 6. [CHMOD](http://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - ./ (the directory containing TinyIB)
    - ./src/
    - ./thumb/
    - ./res/
    - ./inc/flatfile/ (only if you use flat file for the database)
 7. Navigate your browser to **imgboard.php** and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The file index.html will be created containing the new image board.

Moderating
------------

 1. If you are not logged in already, log in to the management panel by clicking **[Manage]**.
 2. On the board, tick the checkbox next to the offending post.
 3. Scroll to the bottom of the page.
 4. Click **Delete** with the password field blank.
    - From this page you are able to delete the post and/or ban the author.

Updating
------------

 1. Obtain the latest release.
 	- If you installed via Git, run the following command in TinyIB's directory:
 	  - `git pull`
 	- Otherwise, [download](https://github.com/tslocum/TinyIB/archive/master.zip) and extract a zipped archive.
 2. Note which files were modified.
    - If **settings.default.php** was updated, migrate the changes to **settings.php**
      - Take care to not change the value of **TINYIB_TRIPSEED**, as it would result in different secure tripcodes.
    - If other files were updated, and you have made changes yourself:
      - Visit [GitHub](https://github.com/tslocum/TinyIB) and review the changes made in the update.
      - Ensure the update does not interfere with your changes.

Support
------------

Contact tslocum@gmail.com

Contributing
------------

 1. Read the [GitHub Forking Guide](http://help.github.com/forking/).
 2. Fork TinyIB.
 3. Commit code changes to your forked repository.
 4. Submit a pull request describing your modifications.
