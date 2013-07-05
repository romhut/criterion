Criterion (/krīˈti(ə)rēən/)
===
"A principle or standard by which something may be judged or decided"

----

Criterion is a simple Continuous Integration tool build in PHP using MongoDB.

You can add GIT repos from any provider, and run any commands you wish against it.

### Dependencies
- PHP >=5.3.3
- MongoDB

### Example criterion.yaml
```yml
setup:
    - composer install --dev
test:
    - phpunit
fail:
    - sh log-fail.sh
pass:
    - sh deploy.sh
```


