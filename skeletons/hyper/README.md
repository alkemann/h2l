# Hyper Skeleton for H2L

Everything is about the HTML view files. Comes with HTMX, Apline.js and Tailwind to make it all selfcontained

## Libraries

 - [HTMX](htmx.org)
 - [TailwindCSS](https://tailwindcss.com/)
 - [Apline.js](https://alpinejs.dev/)

## What does this skeleton come with

 - Environments (config/environments.php)
 - Middlewares (config/middlewares.php)
   - A middleware to set up HTMX
   - A request logging middleware
 - A default layout that includes the CSS and JS, and an empty layout used for boosted HTMX requests
 - Resource folder set up to contain logs, cache and locales
 - A default error handler (config/error_handlers.php)
 - No routes config since you are expected to use pages

## Install

 - `composer require alkemann/h2l`
 - `./vendor/bin/skeleton hyper`
 - Optionally to add local server with `./vendor/bin/skeleton local`
 - OR add a Heroku set of commands with local as well with `./vendor/bin/skeleton heroku`


