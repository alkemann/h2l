# Route

The Route object is a container for the

### Table of Contents

 - [Class specification](#class-specification)


## Class specification
```php
/**
 * The class is immutable so the constructor is only chance to set values
 *
 * @param string $url
 * @param Closure $cb
 * @param array $parameters
 */
public function __construct(string $url, ?Closure $cb, array $parameters = [])

/**
 *  Returns the url that the route was created for/with
 *
 * @return string
 */
public function url(): string

/**
 * Returns all the parameters that the route was created with
 *
 * @return array
 */
public function parameters(): array;

/**
 * Converts the Route to a Response that can be rendered for the final output
 *
 * @param Request $request
 * @return Response|null
 * @throws InvalidCallback if callback did not return Response|null
 */
public function __invoke(Request $request): ?Response

/**
 * Returns the URL (after domain) of the route
 *
 * @return string
 */
public function __toString(): string
```