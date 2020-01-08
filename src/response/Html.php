<?php

namespace alkemann\h2l\response;

use alkemann\h2l\Message;
use alkemann\h2l\Response;
use alkemann\h2l\util\Http;

/**
 * Class Html
 *
 * Content should be either a rendered HTML string or an object that when cast to string renders the HTML.
 * Also a slim templateing system is provided. You can use this by sending an array as content, but the
 * array must follow some strict rules. The first value (no key), should be the template string. Any key/value
 * pair added to this array will be applied as replacements in the template in the following manner:
 *
 *  - The content key must be a unique string name, like `city`
 *  - The place in the template must be prefixed with `{:` and end with `}`, i.e. `{:city}
 *
 * Example:
 *
 * ```php
 *  Router::add('|city/(?<city>\w+)|', function(Request $request): Html {
 *      $template = <<<TEM
 *  <html>
 *      <head>
 *          <title>{:name}</title>
 *      </head>
 *      <body>
 *          <h1>City {:name}</h1>
 *          <p>On {:street}</p>
 *      </body>
 *  </html>
 *  TEM;
 *
 *  $data = [
 *      $template,
 *      'name' => $r->param('city'),
 *      'street' => 'Mainstreet',
 *  ];
 *  return new Html($data, 200);
 *
 *  }
 * ```
 *
 * Going to localhost/city/Oslo, will render as:
 *
 * ```
 *  <html>
 *      <head>
 *          <title>Oslo</title>
 *      </head>
 *      <body>
 *          <h1>City Oslo</h1>
 *          <p>On Mainstreet</p>
 *      </body>
 *  </html>
 * ```
 *
 * @package alkemann\h2l
 */
class Html extends Response
{
    /**
     * @param string|mixed $content HTML. Objects will be cast, arrays will render template
     * @param int $code HTTP code to respond with, defaults to `200`
     * @param array<string, mixed> $config inject config/overrides like `header_func`
     */
    public function __construct($content = null, int $code = Http::CODE_OK, array $config = [])
    {
        if (is_string($content) === false) {
            $content = $this->convertToString($content, $config);
        }
        $this->config = $config;
        $this->message = (new Message())
            ->withCode($code)
            ->withHeaders(['Content-Type' => Http::CONTENT_HTML])
            ->withBody($content ? "$content" : '')
        ;
    }

    /**
     * @param mixed $content
     * @param array<string, mixed> $config
     * @return string
     */
    private function convertToString($content, array $config = []): string
    {
        if (is_array($content) === false) {
            return (string) $content;
        }
        reset($content);
        $template = array_shift($content);
        $replace = [];
        foreach ($content as $name => $value) {
            $replace["{:$name}"] = $value;
        }
        return strtr($template, $replace);
    }

    /**
     * Set header and return a string rendered and ready to be echo'ed as response
     *
     * Header 'Content-type:' will be set using `header` or an injeced 'header_func' through constructor
     *
     * @return string
     */
    public function render(): string
    {
        $this->setHeaders();
        return $this->message->body();
    }
}
