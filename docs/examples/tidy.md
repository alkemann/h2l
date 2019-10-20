# Example: Use Tidy to ensure proper XHTML response

While it is possible to create a dedicated Reponse implementation, a simple
middleware that overrides the created response, renders it to string, then
parses and cleans it with Tidy before recreating a Response that gets the
same message, with new "tidy" body and sending it out.

## Example:

```php
use alkemann\h2l\{ Environment, Request, Response, util\Chain, Message, Http };

// Tidy up the rendered HTML using `tidy`
$tidy_middle = function(Request $request, Chain $chain): Response {
    /** @var Response $response */
    $response = $chain->next($request, $chain);
    if ($response->contentType() == Http::CONTENT_HTML) {
        /** @var Message $message */
        $message = $response->message();
        $tidy = new tidy();
        $tidy->parseString(
            $message->body(),
            ['indent' => true, 'clean' => true],
            'utf8'
        );
        $tidy->cleanRepair();
        $response = $response->withMessage($message->withBody("$tidy"));
    }
    return $response;
};

Environment::addMiddle($tidy_middle, Environment::ALL);
```

Note Middleware should be last in middle chain for most reliable result.