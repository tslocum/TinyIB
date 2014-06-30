TinyIB - A Lightweight and Efficient [Image Board](http://en.wikipedia.org/wiki/Imageboard) Script
====

**Got database? Get speed.**  Use [MySQL](http://mysql.com) or [SQLite](http://sqlite.org) for an efficient set-up able to handle high amounts of traffic.

**No database?  No problem.**  Store posts as text files for a portable set-up capable of running on virtually any PHP host.

To allow new threads without requiring an image, see the [Text Board Mode](https://github.com/tslocum/TinyIB/wiki/Text-Board-Mode) page.

For demos see the [TinyIB Installations](https://github.com/tslocum/TinyIB/wiki) page.  [![githalytics.com alpha](https://cruel-carlota.pagodabox.com/5135372febbc40bacddbb13c1f0a8333 "githalytics.com")](http://githalytics.com/tslocum/TinyIB)

Features
------------
 - GIF, JPG, PNG and WebA/WebM upload.
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
 5. Edit **settings.php** with a text editor.
    - You should at least change TRIPSEED, ADMINPASS, MODPASS
    - DBMODE of "flatfile" is okay for single-user sites, consider using sqlite or mysql instead
    - if you use DBMODE "mysql", you must 
      - edit settings.php to set DBUSERNAME (ibuser), DBPASSWORD (arglebargle), DBNAME (tinyib).
      - don't actually use "ibuser" and "arglebargle" please make up your own username and password
      - `mysql -e 'create database tinyib;'`
      - `mysql -e 'create user "ibuser"@"localhost" identified by "arglebargle";'`
      - `mysql -e 'grant all on tinyib.* to "ibuser"@"localhost";'`
    - To allow WebA/WebM upload:
      - Ensure your web host is running Linux.
      - Install [mediainfo](http://mediaarea.net/en/MediaInfo).  On Ubuntu, run ``sudo apt-get install mediainfo``.
      - Set ``TINYIB_WEBM`` to ``true``.
      - To remove the play icon from thumbnails, delete or rename **video_overlay.png**.
 6. [CHMOD](http://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - `chmod a+w ./ ./src/ ./thumb/ ./res/`
    - if you use a flat file for the database, also `chmod a+w ./inc/flatfile/`
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

 1. Run the command:
    - `git pull`
 2. If TinyIB has been updated, note which files are modified.
    - If **settings.default.php** is updated, migrate the changes to **settings.php**
      - Take care to not change the value of **TINYIB_TRIPSEED**, as it would result in different secure tripcodes.
    - If other files are updated, and you have made changes yourself:
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
