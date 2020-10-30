# TinyIB - Lightweight and efficient [imageboard](https://en.wikipedia.org/wiki/Imageboard)
[![Translate](https://hosted.weblate.org/widgets/tinyib/-/tinyib/svg-badge.svg)](https://hosted.weblate.org/projects/tinyib/tinyib/)
[![Donate](https://img.shields.io/liberapay/receives/rocketnine.space.svg?logo=liberapay)](https://liberapay.com/rocketnine.space)

See [TinyIB Installations](https://gitlab.com/tslocum/tinyib/wikis/Home) for demos.

## Features

**Got database? Get speed.**  Use [MySQL](https://mysql.com), [PostgreSQL](https://www.postgresql.org) or [SQLite](https://sqlite.org) for an efficient set-up able to handle high amounts of traffic.

**No database?  No problem.**  Store posts as text files for a portable set-up capable of running on virtually any PHP host.

**Not looking for an image board script?**  TinyIB is able to allow new threads without requiring an image, or even disallow images entirely.  See the [Text Board Mode](https://gitlab.com/tslocum/tinyib/wikis/Text-Board-Mode) wiki page for instructions.

 - GIF, JPG, PNG, SWF, MP4 and WebM upload.
 - YouTube, Vimeo and SoundCloud embedding.
 - CAPTCHA
   - A simple, self-hosted implementation is included
   - [ReCAPTCHA](https://www.google.com/recaptcha/about/) is supported but [not recommended](https://nearcyan.com/you-probably-dont-need-recaptcha/)
 - Reference links >>###
 - Delete post via password.
 - Management panel:
   - Administrators and moderators use separate passwords.
     - Moderators are only able to sticky threads, lock threads, delete posts, and approve posts when necessary.  (See ``TINYIB_REQMOD``)
   - Ban offensive/abusive posters across all boards.
   - Post using raw HTML.
   - Upgrade automatically when installed via git.  (Tested on Linux only)
 - [Translations](https://hosted.weblate.org/projects/tinyib/tinyib/):
   - Chinese
   - Dutch
   - French
   - German
   - Italian
   - Korean
   - Norwegian
   - Spanish (Mexico)

## Donate

Please consider supporting the continued development of TinyIB.

- [LiberaPay](https://liberapay.com/rocketnine.space)
- [PayPal](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=TEP9HT98XK7QA)

## Install

 1. Verify the following are installed:
    - [PHP 5.3+](https://php.net)
    - [GD Image Processing Library](https://php.net/gd)
      - This library is usually installed by default.
      - If you plan on disabling image uploads to use TinyIB as a text board only, this library is not required.
 2. CD to the directory you wish to install TinyIB.
 3. Run the command:
    - `git clone https://gitlab.com/tslocum/tinyib.git ./`
 4. Copy **settings.default.php** to **settings.php**
 5. Configure **settings.php**
    - When setting ``TINYIB_DBMODE`` to ``flatfile``, note that all post and ban data are exposed as the database is composed of standard text files.  Access to ./inc/database/flatfile/ should be denied.
    - When setting ``TINYIB_DBMODE`` to ``pdo``, note that only the MySQL and PostgreSQL databases drivers have been tested. Theoretically it will work with any applicable driver, but this is not guaranteed.  If you use an alternative driver, please report back.
    - To require moderation before displaying posts:
      - Set ``TINYIB_REQMOD`` to ``files`` to require moderation for posts with files attached.
      - Set ``TINYIB_REQMOD`` to ``all`` to require moderation for all posts.
      - Moderate posts by visiting the management panel.
    - To allow video uploads:
      - Ensure your web host is running Linux.
      - Install [mediainfo](https://mediaarea.net/en/MediaInfo) and [ffmpegthumbnailer](https://code.google.com/p/ffmpegthumbnailer/).  On Ubuntu, run ``sudo apt-get install mediainfo ffmpegthumbnailer``.
      - Add desired video file types to ``$tinyib_uploads``.
    - To remove the play icon from .SWF and .WebM thumbnails, delete or rename **video_overlay.png**
    - To use ImageMagick instead of GD when creating thumbnails:
      - Install ImageMagick and ensure that the ``convert`` command is available.
      - Set ``TINYIB_THUMBNAIL`` to ``imagemagick``.
      - **Note:** GIF files will have animated thumbnails, which will often have large file sizes.
    - To use TINYIB in another language:
      - Set ``TINYIB_LOCALE`` to a language code found in `locale/`.
      - Ensure you are running PHP 5.4+.
 6. [CHMOD](https://en.wikipedia.org/wiki/Chmod) write permissions to these directories:
    - ./ (the directory containing TinyIB)
    - ./src/
    - ./thumb/
    - ./res/
    - ./inc/database/flatfile/ (only if you use the ``flatfile`` database mode)
 7. Navigate your browser to **imgboard.php** and the following will take place:
    - The database structure will be created.
    - Directories will be verified to be writable.
    - The board index will be written to ``TINYIB_INDEX``.

## Moderate

 1. If you are not logged in already, log in to the management panel by clicking **[Manage]**.
 2. On the board, tick the checkbox next to the offending post.
 3. Scroll to the bottom of the page.
 4. Click **Delete** with the password field blank.
    - From this page you are able to delete the post and/or ban the author.

## Update

 1. Obtain the latest release.
    - If you installed via Git, run the following command in TinyIB's directory:
      - `git pull`
    - Otherwise, [download](https://gitlab.com/tslocum/tinyib/-/archive/master/tinyib-master.zip) and extract a zipped archive.
 2. Note which files were modified.
    - If **settings.default.php** was updated, migrate the changes to **settings.php**
      - Take care to not change the value of **TINYIB_TRIPSEED**, as it would result in different secure tripcodes.
    - If other files were updated, and you have made changes yourself:
      - Visit [GitLab](https://gitlab.com/tslocum/tinyib) and review the changes made in the update.
      - Ensure the update does not interfere with your changes.

## Migrate

TinyIB includes a database migration tool.

While the migration is in progress, visitors will not be able to create or delete posts.

 1. Edit **settings.php**
    - Set ``TINYIB_DBMIGRATE`` to the desired ``TINYIB_DBMODE`` after the migration.
    - Configure all settings related to the desired ``TINYIB_DBMODE``.
 2. Open the management panel.
 3. Click **Migrate Database**
 4. Click **Start the migration**
 5. If the migration was successful:
    - Edit **settings.php**
      - Set ``TINYIB_DBMODE`` to the mode previously specified as ``TINYIB_DBMIGRATE``.
      - Set ``TINYIB_DBMIGRATE`` to a blank string (``''``).
    - Click **Rebuild All** and ensure the board still looks the way it should.

## Support

 1. Ensure you are running the latest version of TinyIB.
 2. Review the [open issues](https://gitlab.com/tslocum/tinyib/issues).
 3. Open a [new issue](https://gitlab.com/tslocum/tinyib/issues/new).

## Translate

Translation is handled [online](https://hosted.weblate.org/projects/tinyib/tinyib/).

## Contribute

**Note:** Please do not submit translations as pull requests.  See above.

 1. Read the [GitLab Forking Workflow](https://docs.gitlab.com/ce/workflow/forking_workflow.html).
 2. Fork TinyIB.
 3. Commit code changes to your forked repository.
 4. Submit a pull request describing your modifications.
