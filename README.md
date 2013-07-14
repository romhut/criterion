Criterion
===
"A principle or standard by which something may be judged or decided"

----

Criterion is a Continuous Integration app built in PHP using MongoDB. Criterion is very easy to setup, and you can add GIT repositories from any provider, and run any commands you wish against it.

Criterion will auto detect test environments if no .criterion.yml file is provided. This means you can setup CI with no changes to your code at all. For example, below we are testing a few libraries that have never even heard of Criterion, yet they still run.

![Criterion](http://f.cl.ly/items/2k3M0b1c1H353C2w3q06/Screen%20Shot%202013-07-14%20at%2014.54.28.png)

Please note: This only works for phpunit testing environments right now, but we will be adding more in the future.

### Dependencies

- PHP >=5.3.3
- PHP Mongo Driver >= 1.3.0
- MongoDB >= 2.2.0
- Git >= 1.7.0

### Installation

```shell
bin/cli install
```

### Running Workers

```shell
bin/cli worker:start --debug --interval=20
```

### Example criterion.yml
```yml
setup:
    - composer install --dev
script:
    - phpunit
fail:
    - sh log-fail.sh
pass:
    - sh deploy.sh
```

### Github Service Hook
You can add a Github WebHook to automatically run the tests on your project when a commit is pushed. For example:

```
http://hook:password@criterion.example.com/hook/github
```
You can even add this hook to your Github repositories, and Criterion will automatically create a project if you have not already done so.

### Authors
- [Scott Robertson](http://github.com/scottymeuk)
- [Marc Qualie](http://github.com/marcqualie)
