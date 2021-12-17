<?php

namespace HeadlessLaravel\Formations\Exceptions;

use Exception;
use function redirect;
use function route;

class UnauthorizedException extends Exception
{
    /**
     * The http status code.
     * @var int
     */
    protected $code = 401;

    /**
     * Render the exception into an HTTP response.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function render($request)
    {
        return redirect()->to(route('login'));
    }
}
