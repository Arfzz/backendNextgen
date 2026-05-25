<?php

namespace App\Models;

use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;

class NewAccessToken implements Arrayable, Jsonable
{
    /**
     * Create a new access token result.
     *
     * @param  \App\Models\PersonalAccessToken  $accessToken
     * @param  string  $plainTextToken
     */
    public function __construct(public PersonalAccessToken $accessToken, public string $plainTextToken)
    {
    }

    /**
     * Get the instance as an array.
     */
    public function toArray(): array
    {
        return [
            'accessToken' => $this->accessToken,
            'plainTextToken' => $this->plainTextToken,
        ];
    }

    /**
     * Convert the object to its JSON representation.
     */
    public function toJson($options = 0): string
    {
        return json_encode($this->toArray(), $options);
    }
}
