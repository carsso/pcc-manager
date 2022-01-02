<?php

namespace App\Http\Controllers;

use Exception;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Http\RedirectResponse;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Support\Str;

class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

    /**
     * @param string $message
     * @param \Exception|null $e
     * @param string $sessionBag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function backWithError(string $message, ?Exception $e = null, $sessionBag = 'flash')
    {
        $withError = 'with' . Str::studly($sessionBag) . 'Error';
        $withErrorException = $withError . 'Exception';

        $response = back()->$withError($message)
                          ->withInput();
        if ($e) {
            $response = $response->$withErrorException($e->getMessage());
        }

        return $response;
    }

    /**
     * @param string $message
     * @param string $sessionBag
     * @param RedirectResponse $redirectResponse
     *
     * @return RedirectResponse
     */
    protected function backWithSuccess(string $message, $sessionBag = 'flash', RedirectResponse $redirectResponse = null)
    {
        $withSuccess = 'with' . Str::studly($sessionBag) . 'Success';

        $redirect = $redirectResponse ?? back();

        return $redirect->$withSuccess($message);
    }

    /**
     * @param string $message
     * @param \Illuminate\Http\RedirectResponse $redirectResponse
     * @param string $sessionBag
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    protected function redirectWithSuccess(string $message, RedirectResponse $redirectResponse, $sessionBag = 'flash')
    {
        $withSuccess = 'with' . Str::studly($sessionBag) . 'Success';

        return $redirectResponse->$withSuccess($message);
    }
}
