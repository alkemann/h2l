# Contributing

Project follows coding standards PSR1 and PSR2. It also aims for 100% test coverage.

It also aims to be lightweight, so suggestions for added features may well be rejected as "bloat".

## Git Hook

Add the following content to new file `/.git/hooks/pre-push`
```bash
#!/bin/sh

if ! vendor/bin/phpstan.phar analyse -c phpstan.neon --no-interaction --no-progress;
then
    echo " "
    echo " == FAILURES: GIT PUSH BLOCKED == "
    echo " "
    exit 1
fi

if ! bin/runtests --coverage-clover tests/clover.xml;
then
    echo " "
    echo " == FAILURES: GIT PUSH BLOCKED == "
    echo " "
    exit 1
fi

if ! php bin/coverage tests/clover.xml 100;
then
    echo " "
    echo " == FAILURES: GIT PUSH BLOCKED == "
    echo " "
    exit 1
fi

exit 0
```

If PSalm is also installed, it should be added as a hook after PHPStan, like so:
```bash
if ! psalm --show-info=false --no-progress --no-cache;
then
        echo " "
        echo " == FAILURES: GIT PUSH BLOCKED == "
        echo " "
        exit 1
fi
```
