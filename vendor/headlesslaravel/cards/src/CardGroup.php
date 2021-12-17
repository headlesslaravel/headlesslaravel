<?php

namespace HeadlessLaravel\Cards;

use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\Validator;

class CardGroup
{
    public $wrap = 'data';

    protected $manager;

    public function __construct(Manager $manager)
    {
        $this->manager = $manager;
    }

    public function cards()
    {
        //
    }

    public function rules(): array
    {
        return [];
    }

    public function authorize(): bool
    {
        return true;
    }

    public function validate()
    {
        if (! $this->authorize()) {
            throw new AuthorizationException();
        }

        return Validator::make(
            Request::all(),
            $this->rules()
        )->validate();
    }

    public function all()
    {
        if ($resolved = $this->manager->byObject($this)) {
            return $resolved;
        }

        $this->manager->start($this);

        $this->cards();

        return $this->manager->finish();
    }

    public function get($key): array
    {
        $key = str_replace('-', '_', $key);

        foreach ($this->all() as $card) {
            if ($card->key === $key) {
                if (! $card->allowed) {
                    throw new AuthorizationException();
                }

                return $card->resolve();
            }
        }

        return [];
    }

    public function response($key = null): array
    {
        $this->validate();

        if ($key) {
            return $this->get($key);
        }

        $cards = [];

        foreach ($this->all() as $index => $card) {
            if ($card->allowed) {
                $cards[$index] = $card->resolve();
            }
        }

        $cards = array_values($cards);

        if ($this->wrap) {
            return [ $this->wrap => $cards ];
        }

        return $cards;
    }
}
