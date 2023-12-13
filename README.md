# Foothills Forum new feed (ff)

## Setup

from survos/survos, run the services (meili, redis, rabbit)
```bash
docker compose up -d
```

Install the application, the default database is sqlite.

```bash
git clone git@github.com:survos-sites/foothills-forum.git ff && cd ff
composer install
bin/console doctrine:schema:update --force --complete
bin/console ff:scrape
bin/console grid:index
symfony server:start -d
symfony open:local 
```


Tools for KPA

Load the exists assets (youtube and songs) via

```bash
bin/console app:load-data
```
    
Database Tables

* Videos: from youtube now, eventually from Dropbox too.
* Photos: Eventually from Dropbox
* Schools: residencies
* Songs: 5K from the spreadsheet.  Talented Clementine and Best Friends _could_ be added.
* User: for permissions

## Other projects

// https://www.youtube.com/watch?v=NeRjdX06_n8&t=186s if it were a generalized video / transcript research site
