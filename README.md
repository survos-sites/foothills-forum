# Foothills Forum new feed (ff)

## Setup

from the __survos__ repo, run the services (meili, redis, rabbit)
```bash
docker compose up -d
```

Although the default is postgres, it runs with sqlite as well, 
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

# rSchoolToday

Activity Scheduler: 

Representative in Virginia for rSchoolToday:  Samantha Townsend, 804-678-8410 samantha.townsend@rschooltoday.com

## Photos and Events

See https://rappahannockcountyhs.rschoolteams.com/ for school events
https://www.bullrundistrictva.org/public/schoolactivities/genie/359/school/6/

aws s3 ls s3://foothills-forum/

https://rappnews.com/tncms/webservice/#operation/editorial_asset_options
OpenAPI Schema: https://www.rappnews.com/tncms/webservice/resources/

## Subscriptions

Users can subcribe to submissions to a entity, e.g a Team or a Sport.  When a photo is added to one of those, an email is sent.

In the case of an event, the photos are sent in batch X hours after then event.

@todo: use ngrok or pinggy or localtunnel to debug google
