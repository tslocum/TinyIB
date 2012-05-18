TinyIB
====

PHP image board

Databases supported:

 * MySQL
 * SQLite
 * Flat file (Database entries are stored in text files)

Example installations available [here](https://github.com/tslocum/TinyIB/wiki)

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

 1. Read the [GitHub Guide on Forking](http://help.github.com/forking/)
 2. Fork TinyIB
 3. Commit code changes to your forked repository
 4. Submit a pull request describing your modifications
