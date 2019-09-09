# Example: Use Tidy to ensure proper XHTML response

While it is possible to create a dedicated Reponse implementation, a simple middleware that overrides the
created response, renders it to string, then parses and cleans it with Tidy before recreating a Response
that gets the same message, with new "tidy" body and sending it out.

## Example:

```php

Environment::addMiddle(function(Request $r, Chain $c): ?Response
{
	return new class($c->next($r, $c)) extends Response {
	    public function __construct(Response $response) {
	        $tidy = new tidy();
	        $tidy->parseString($response->render(), ['indent' => true, 'clean' => true], 'utf8');
	        $tidy->cleanRepair();
	        $this->message = $response->message()->withBody("$tidy");
	    }
	    public function render(): string {
	        $this->setHeaders();
	        return $this->message->body();
	    }
	};
});
```

Ofcourse this example assumes all responses are to be HTML, so checks on that  could be done, for example:
```php
$result = $c->next($r, $c);
if ($result instanceof alkemann\response\Html) { 
	// create tidy
```

Note Middleware should be last in middle queue for most reliable result. 