<!doctype html>
<html>
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <?php if (alkemann\h2l\Environment::current() === 'LOCAL') : ?>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
    tailwind.config = {
      theme: {
        extend: {}
      }
    }
  </script>
  <?php else: ?>
    <link href="/css/tailwind.css" rel="stylesheet">
  <?php endif; ?>
  <script src="//unpkg.com/alpinejs" defer></script>
</head>
<body>
