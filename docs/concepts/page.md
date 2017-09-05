# Concept : Page

A page is an automatic route added through the existence of a file found in a
matching sub-folder under `/contents/pages`.

So a call to `http://domain.com/places/cities.html` would work if there is a
file at `/content/pages/places/cities.html.php`. This file is both **view**
and **action** for this request. Anything unique to this url should go in that
file. Obviously logic could be delegated to other classes in an app namespace.

The Page response will try to wrap the **view template** in layout files; **head,
neck** and **footer**. Should they exist. By default it will look for the
`default` layout by looking for `head.html.php`, `neck.html.php` and
`foot.html.php` in `/content/layouts/default`. To use a different set of
layout files, the layout name (i.e. the folder name) can be specified at any
point in the view template:
```php
<?php $this->layout = 'cooler'; ?>
```

It is also possible to put the entire body (like HTML header etc) in the view
template and skip layouts all together. To do this set the layout to false:
```php
<?php $this->layout = false; ?>
```

