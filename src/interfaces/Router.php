<?php

namespace alkemann\h2l\interfaces;

interface Router
{
	public static function match(string $url, string $method = Http::GET): ?Route;
	public static function getFallback(): ?Route;
	public static function getPageRoute(string $url): Route;
}