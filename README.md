TinyIB
====

PHP image board

Installing
------------

 1. CD to the directory you wish to install TinyIB
 2. Run the following command:
    - `git clone git://github.com/tslocum/TinyIB.git ./`
 3. Copy settings.default.php to settings.php
 4. Configure settings.php
 5. CHMOD write permissions to the following directories:
    - /
    - src/
    - thumb/
    - res/
    - inc/flatfile/ (if you choose to use flat file)
 6. Navigate your browser to imgboard.php, which causes the following to take place:
    - Create database structure
    - Check necessary directories are writable
    - Write index.html containing the new image board

Updating
------------

`git pull`

Contributing
------------

 1. Read http://help.github.com/forking/
 2. Fork TinyIB
 3. Commit code changes to your forked repository
 4. Submit a pull request describing your modifications
