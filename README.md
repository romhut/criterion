Criterion
===

Criterion is a simple Continuous Integration tool build in PHP using MongoDB.

You can add GIT repos from any provider, and run any commands you wish against it.

An example criterion.yaml file:

```yml
commands:
    setup:
        - composer install --dev
    test:
        - phpunit
    fail:
        - sh log-fail.sh
    pass:
        - sh deploy.sh
```