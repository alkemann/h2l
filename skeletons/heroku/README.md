# Heroku and React builder template

## Requirements

- PHP 7.1
- Node.js
- Heroku

## Getting started

- Composer require `alkemann/h2l:*`
- Ensure your `composer.json` includes `"autoload": {"psr-4": {"backend\\": "backend/"}}`
- `composer install` (installs php dependency)
- `vendor/bin/skeleton heroku` (copies all the files of `skeletons/heroku` into root of your project)
- `npm install` (installs node.js dependencies)
- `git init .` Add files (`git add .`). Ensure both composer.json and composer.lock files is included
- Make an initial commit
- `git branch release` (makes a release branch that will be used to push to Heroku)
- Create Heroku app with `heroku apps:create NAME --region eu`
- Scale your app with `heroku ps:scale web=1`
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
- Push to Heroku with `bin/deploy`
- Go to https://<NAME>.herokuapp.com/say/hello_world
