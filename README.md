!!! TinyIB has moved to GitLab !!!
====

Please visit https://gitlab.com/tslocum/tinyib

------------

TinyIB - A Lightweight and Efficient [Image Board](https://en.wikipedia.org/wiki/Imageboard) Script
====

**Got database? Get speed.**  Use [MySQL](https://mysql.com), [PostgreSQL](https://www.postgresql.org) or [SQLite](https://sqlite.org) for an efficient set-up able to handle high amounts of traffic.

**No database?  No problem.**  Store posts as text files for a portable set-up capable of running on virtually any PHP host.

**Not looking for an image board script?**  TinyIB is able to allow new threads without requiring an image, or even disallow images entirely.  See the [Text Board Mode](https://github.com/tslocum/TinyIB/wiki/Text-Board-Mode) wiki page for instructions.

For demos see the [TinyIB Installations](https://github.com/tslocum/TinyIB/wiki) page.

**Database structure was last modified on *15th Sep 2015*.** Are you unable to create new posts?  Run the SQL on [this page](https://github.com/tslocum/TinyIB/wiki/NewSQLStructure) to finish the upgrade process.

Features
------------
 - GIF, JPG, PNG, SWF and WebM upload.
 - YouTube, Vimeo and SoundCloud embedding.
 - CAPTCHA  (A simple implementation is included, reCAPTCHA is also supported)
 - Reference links >>###
 - Delete post via password.
 - Management panel:
   - Administrators and moderators use separate passwords.
     - Moderators are only able to sticky threads, delete posts, and approve posts when necessary.  (See ``TINYIB_REQMOD``)
   - Ban offensive/abusive posters across all boards.
   - Post using raw HTML.
   - Upgrade automatically when installed via git.  (Tested on Linux only)

Installing
------------

 1. Verify the following are installed:
    - [PHP 4.3+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - If you plan on disabling image uploads to use TinyIB as a text board only, this library is not required.
 2. CD to the directory you wish to install TinyIB.
 3. Run the command:
    - `git clone git://github.com/tslocum/TinyIB.git ./`
 4. Copy **settings.default.php** to **settings.php**
 5. Configure **settings.php**
    - To allow WebM upload:
      - Ensure your web host is running Linux.
      - Install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/).  On Ubuntu, run ``sudo apt-get install mediainfo ffmpegthumbnailer``.
    - To require moderation before displaying posts:
      - Ensure your ``TINYIB_DBMODE`` is set to ``mysql``, ``mysqli``, or ``pdo``.
      - Set ``TINYIB_REQMOD`` to ``files`` to require moderation for posts with files attached.
      - Set ``TINYIB_REQMOD`` to ``all`` to require moderation for all posts.
      - Moderate posts by visiting the management panel.
    - When setting ``TINYIB_DBMODE`` to ``pdo``, note that only the MySQL and PostgreSQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed.  If you use an alternative driver, please report back.
    - To use ImageMagick instead of GD when creating thumbnails:
      - Install ImageMagick and ensure that the ``convert`` command is available.
      - Set ``TINYIB_THUMBNAIL`` to ``imagemagick``.
      - **Note:** GIF files will have animated thumbnails, which will often have large file sizes.
    - To remove the play icon from .SWF and .WebM thumbnails, delete or rename **video_overlay.png** 
 6. [CHMOD](https://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - ./ (the directory containing TinyIB)
    - ./src/
    - ./thumb/
    - ./res/
    - ./inc/flatfile/ (only if you use the ``flatfile`` database mode)
 7. Navigate your browser to **imgboard.php** and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The file **index.html** will be created containing the new image board.

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
 3. Visit [GitHub](https://github.com/tslocum/TinyIB/wiki/NewSQLStructure) and check for new SQL queries which may be required to complete the update.

Migrating
------------

TinyIB includes a database migration tool, which currently only supports migrating from flat file to MySQL.  While the migration is in progress, visitors will not be able to create or delete posts.

 1. Edit **settings.php**
    - Ensure ``TINYIB_DBMODE`` is still set to ``flatfile``.
    - Set ``TINYIB_DBMIGRATE`` to ``true``.
    - Configure all MySQL-related settings.
 2. Open the management panel.
 3. Click **Migrate Database**
 4. Click **Start the migration**
 5. If the migration was successful:
    - Edit **settings.php**
      - Set ``TINYIB_DBMODE`` to ``mysqli``.
      - Set ``TINYIB_DBMIGRATE`` to ``false``.
    - Click **Rebuild All** and ensure the board still looks the way it should.

If there was a warning about AUTO_INCREMENT not being updated, you'll need to update it manually via a more privileged MySQL user.  Run the following query for one or both of the tables, dependant of the warnings you were issued:

``ALTER TABLE (table name) AUTO_INCREMENT = (value to be set)``

Support
------------

 1. Ensure you are running the latest version of TinyIB.
 2. Review the [open issues](https://github.com/tslocum/TinyIB/issues).
 3. Open a [new issue](https://github.com/tslocum/TinyIB/issues/new).

Contributing
------------

 1. Read the [GitHub Forking Guide](https://help.github.com/forking/).
 2. Fork TinyIB.
 3. Commit code changes to your forked repository.
 4. Submit a pull request describing your modifications.
