<?php

namespace HeadlessLaravel\Formations\Exceptions;

use Exception;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PageExceededException extends Exception
{
    /**
     * The http status code.
     * @var int
     */
    protected $code = 302;

    /**
     * Render the exception into an HTTP response.
     *
     * @param  Request  $request
     * @return RedirectResponse
     */
    public function render($request)
    {
        return redirect()->to(
            request()->url().'?'.http_build_query(
                array_merge($request->query(), ['page' => 1])
            )
        );
    }
}
