# Heroku and React builder template

## Getting started

- Composer require `alkemann/h2l`
- Ensure your `composer.json` includes `"autoload": {"psr-4": {"backend\\": "backend/"}}`
- Copy all the files of `skeletons/heroku` into root of your project
- Initiate git and add both composer.json and composer.lock files
- Create Heroku app with `heroku apps:create`
- Scale your app with `heroku ps:scale web=1`
- Ready to go! See `develop` or `deploy` parts for next steps

## Develop

- Run `bin/local.sh`, which will be running the `npm run build` (that
keeps building javascripts to `webroot/js/bundle.js`) and `php -S` (to
host a localhost server using PHP native)
- Edit your javascript under `frontend`
- Edit your php api through `backend/Api` and `/configs/routes.php` or adding
"pages" to `/content/pages` for "automatic" routing

## Deploy

- Deployments are done through `git push`, so ensure that changes are commited
- Clean out your git state by commiting all or `git stash`
- Push to Heroku with `bin/deploy.sh`
