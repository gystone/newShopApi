<?php

if (! function_exists('respond')) {
    /**
     * 封装响应
     * @param string $message
     * @param int $code 200 OK - [GET]：服务器成功返回用户请求的数据，该操作是幂等的（Idempotent）。
     *                  201 CREATED - [POST/PUT/PATCH]：用户新建或修改数据成功。
     *                  202 Accepted - [*]：表示一个请求已经进入后台排队（异步任务）
     *                  204 NO CONTENT - [DELETE]：用户删除数据成功。
     *                  400 INVALID REQUEST - [POST/PUT/PATCH]：用户发出的请求有错误，服务器没有进行新建或修改数据的操作，该操作是幂等的。
     *                  401 Unauthorized - [*]：表示用户没有权限（令牌、用户名、密码错误）。
     *                  403 Forbidden - [*] 表示用户得到授权（与401错误相对），但是访问是被禁止的。
     *                  404 NOT FOUND - [*]：用户发出的请求针对的是不存在的记录，服务器没有进行操作，该操作是幂等的。
     *                  406 Not Acceptable - [GET]：用户请求的格式不可得（比如用户请求JSON格式，但是只有XML格式）。
     *                  410 Gone -[GET]：用户请求的资源被永久删除，且不会再得到的。
     *                  422 Unprocesable entity - [POST/PUT/PATCH] 当创建一个对象时，发生一个验证错误。
     *                  500 INTERNAL SERVER ERROR - [*]：服务器发生错误，用户将无法判断发出的请求是否成功。
     * @param array|object $data
     * @param array $header
     * @return \Illuminate\Http\JsonResponse
     */
    function respond(string $message, int $code = 200, $data = null, array $header = [])
    {
        if (is_object($data) || is_array($data) || $data === null) {
            $res = $data === null ?
                array(
                    'code' => $code,
                    'message' => $message
                ) :
                array(
                    'code' => $code,
                    'message' => $message,
                    'data' => $data
                );

            return response()->json($res, $code, $header);
        }
    }
}

if (! function_exists('respond_token')) {
    /**
     * Token 封装
     * @param $token
     * @return \Illuminate\Http\JsonResponse
     */
    function respond_token($token, $guard = 'api')
    {
        return response()->json([
            'code' => 201,
            'message' => '成功生成Token',
            'data' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => auth($guard)->factory()->getTTL() * 60
            ]
        ], 201);
    }
}

if (! function_exists('modify_env')) {
    /**
     * 修改env文件
     * @param array $data
     */
    function modify_env(array $data)
    {
        $envPath = base_path() . DIRECTORY_SEPARATOR . '.env';
        $contentArray = collect(file($envPath, FILE_IGNORE_NEW_LINES));
        $contentArray->transform(function ($item) use ($data){
            foreach ($data as $key => $value){
                if(str_contains($item, $key)){
                    return $key . '=' . $value;
                }
            }
            return $item;
        });
        $content = implode($contentArray->toArray(), "\n");
        return \File::put($envPath, $content);
    }
}