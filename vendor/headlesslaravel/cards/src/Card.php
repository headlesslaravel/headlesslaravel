<?php

namespace HeadlessLaravel\Cards;

use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Str;

class Card
{
    public $key;

    public $value;

    public $title;

    public $component;

    public $link;

    public $span;

    public $props;

    public $allowed = true;

    public function __construct($title, $key = null)
    {
        $this->title = $title;

        $this->key = is_null($key) ? Str::snake($title) : $key;
    }

    public static function make(string $title, $key = null): self
    {
        return new self($title, $key);
    }

    public function value(callable $value): self
    {
        $this->value = $value;

        return $this;
    }

    public function component($component): self
    {
        $this->component = $component;

        return $this;
    }

    public function link($link): self
    {
        $this->link = $link;

        return $this;
    }

    public function span($span): self
    {
        $this->span = $span;

        return $this;
    }

    public function props(array $props): self
    {
        $this->props = $props;

        return $this;
    }

    public function view($name): self
    {
        return $this->value(function () use ($name) {
            return View::make($name);
        });
    }

    public function http($url, $path = null): self
    {
        return $this->value(function () use ($url, $path) {
            return Http::get($url)->json($path);
        });
    }

    public function can($ability, $arguments = []): self
    {
        $this->allowed = Gate::allows($ability, $arguments);

        return $this;
    }

    public function resolve(array $parameters = []): array
    {
        $card = $this->toArray();

        $card = array_merge($card, [
            'endpoint' => $this->endpoint(),
            'value' => value($this->value),
        ]);

        return array_filter($card);
    }

    private function endpoint(): string
    {
        $slug = Str::slug($this->key);

        return app(Manager::class)->baseEndpoint() . '/' . $slug;
    }

    public function toArray(): array
    {
        return [
            'key' => $this->key,
            'value' => $this->value,
            'title' => $this->title,
            'props' => $this->props,
            'component' => $this->component,
            'span' => $this->span,
            'link' => $this->link,
            'allowed' => $this->allowed,
        ];
    }

    public function __destruct()
    {
        app(Manager::class)->appendResolving($this);
    }
}
