## Setup

### Main

- run `compose.yaml`
- php bin/console doctrine:migrations:migrate

### Test database creation

- php bin/console --env=test doctrine:database:create
- php bin/console --env=test doctrine:migrations:migrate
- php bin/console --env=test doctrine:fixtures:load

### Test it out

- run `php bin/phpunit` . Uses `_test` database.
- or go to `tests/Product.http` for some manual tests on `dev` database.