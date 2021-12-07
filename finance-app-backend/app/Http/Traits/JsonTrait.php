<?php

namespace App\Http\Traits;

/**
 * JsonTrait
 */
trait JsonTrait
{
  public function jsonResponse($data, $message = '', $code=200) {
    return response()->json([
      'status' => ($code != 200) ? false : true,
      'code' => $code,
      'data' => $data,
      'message' => $message
    ], $code);
  }
}
