# Foothills Forum new feed (ff)

## Setup

from the __survos__ repo, run the services (meili, redis, rabbit)
```bash
docker compose up -d
```

Install the application, the default database is sqlite.

```bash
git clone git@github.com:survos-sites/foothills-forum.git ff && cd ff
composer install
bin/console d:d:drop --force
bin/console d:d:create
bin/console doctrine:schema:update --force --complete
bin/console ff:scrape
bin/console grid:index --reset
symfony server:start -d
symfony open:local 
```


## Photos and Events

See https://rappahannockcountyhs.rschoolteams.com/ for school events 

