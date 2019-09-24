# Heroku and React builder template

## Requirements

- PHP 7.1
- Node.js
- Heroku

## Getting started

- `mkdir my-new-project && cd my-new-project` (Start with a fresh new folder)
- `composer require alkemann/h2l` composer require this library
- `vendor/bin/skeleton heroku` (copies all the files of `skeletons/heroku` into root of your project)
- `bin/install` Sets up Heroku
- Ready to go! See [Develop](#develop) or [Deploy](#deploy) parts for next steps

## Develop

- Run `bin/local [PORT NUMBER:8080]`, which will be running `php -S` (to
host a localhost server using PHP native)
- Edit your php api through `backend/Api` and `/configs/routes.php` or adding
"pages" to `/content/pages` for "automatic" routing

## Deploy

- Deployments are done through `git push`, so ensure that changes are commited
- Clean out your git state by commiting all or `git stash`
- Push to Heroku with `bin/deploy -b`
- `heroku open` to open the remote app in browser
