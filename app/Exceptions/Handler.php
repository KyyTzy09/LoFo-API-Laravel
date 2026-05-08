protected function unauthenticated($request, \Illuminate\Auth\AuthenticationException $exception)
{
    return response()->json([
        'status' => 401,
        'message' => 'Unauthenticated'
    ], 401);
}
