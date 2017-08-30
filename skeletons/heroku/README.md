# Heroku and React builder template

## Requirements

- PHP 7.1
- Node.js
- Heroku

## Getting started

- `alkemann/h2l:*` composer require this library
- `vendor/bin/skeleton heroku` (copies all the files of `skeletons/heroku` into root of your project)
- `bin/install` Sets up Heroku and Node dependencies
- Ready to go! See `develop` or `deploy` parts for next steps

## Develop

- Run `bin/local [PORT NUMBER:8080]`, which will be running the `npm run build` (that
keeps building javascripts to `webroot/js/bundle.js`) and `php -S` (to
host a localhost server using PHP native)
- Edit your javascript under `frontend`
- Edit your php api through `backend/Api` and `/configs/routes.php` or adding
"pages" to `/content/pages` for "automatic" routing

## Deploy

- Deployments are done through `git push`, so ensure that changes are commited
- Clean out your git state by commiting all or `git stash`
- Push to Heroku with `bin/deploy -b` (You may skipp the `-b` if you are building as you go with `npm run build`)
- `heroku open` to open the remote app in browser
