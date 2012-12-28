TinyIB
====

Lightweight [image board](http://en.wikipedia.org/wiki/Imageboard) script.  See [example installations](https://github.com/tslocum/TinyIB/wiki) for a demo.

**Database not required.**  Store posts as text files for a portable set-up capable of running on virtually any PHP host.

**Got database?**  Use [MySQL](http://mysql.com) or [SQLite](http://sqlite.org) for an efficient set-up able to handle high amounts of traffic.
Features
------------
 - Reference links >>###
 - Delete post via password
 - Management panel
   - Administrators and moderators use separate passwords
     - Moderators are only able to delete posts
   - Ban offensive/abusive posters across all boards
   - Post using raw HTML
   
Installing
------------

 1. CD to the directory you wish to install TinyIB
 2. Run the command:
    - `git clone git://github.com/tslocum/TinyIB.git ./`
 3. Copy settings.default.php to settings.php
 4. Configure settings.php
 5. CHMOD write permissions to these directories:
    - ./
    - ./src/
    - ./thumb/
    - ./res/
    - ./inc/flatfile/ (only if you use flat file for the database)
 6. Navigate your browser to imgboard.php and the following will take place:
    - The database structure will be created
    - Directories will be verified to be writable
    - The file index.html will be created containing the new image board

Updating
------------

`git pull`

Support
------------

Contact tslocum@gmail.com

Contributing
------------

 1. Read the [GitHub Forking Guide](http://help.github.com/forking/)
 2. Fork TinyIB
 3. Commit code changes to your forked repository
 4. Submit a pull request describing your modifications
