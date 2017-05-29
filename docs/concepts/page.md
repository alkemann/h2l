# H2L - microframework

Welcome to H2L a micro framework for PHP 7.1+

## Concept : Page

A page is an automatic route added through the existance of a file found in a matching subfolder under `/contents/pages`.

So a call to `http://domain.com/places/cities.html` would work if there is a file at `/conents/pages/places/cities.html.php`.
