<?php

namespace QT\Seewo;

use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\Psr7\Utils;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client as GuzzleClient;

/**
 * 希沃Api基类
 * 
 * @package QT\Seewo
 */
abstract class BaseClient
{
    /**
     * 希沃服务app key
     * 
     * @var string
     */
    protected $appid;

    /**
     * 希沃服务密钥
     * 
     * @var string
     */
    protected $secret;

    /**
     * 可选项
     * 
     * @var array
     */
    protected $options;

    /**
     * HTTP Client
     * 
     * @var ClientInterface
     */
    protected $httpClient;

    /**
     * 当前请求使用的host
     * 
     * @var string|null
     */
    protected $host = null;

    /**
     * 希沃服务器域名
     * 
     * @var string
     */
    protected $defaultHost = 'openapi.seewo.com';

    /**
     * 希沃沙盒服务器域名
     * 
     * @var string
     */
    protected $sandBoxHost = 'openapi.test.seewo.com';

    /**
     * @param string $appid
     * @param string $secret
     * @param array $options
     */
    public function __construct(string $appid, string $secret, array $options = [])
    {
        $this->appid   = $appid;
        $this->secret  = $secret;
        $this->options = $options;

        if (!empty($options['host'])) {
            $this->host = $options['host'];
        }

        if ($this->host === null) {
            $this->host = isset($options['is_sandbox']) && $options['is_sandbox'] === true
                ? $this->sandBoxHost
                : $this->defaultHost;
        }
    }

    /**
     * 发起一个get请求
     * 
     * @param string $uri
     * @param array $query
     * @param array $headers
     * @return Response
     */
    public function get(string $uri, array $query = [], array $headers = []): Response
    {
        $request = $this->createRequest('GET', Uri::withQueryValues(new Uri($uri), $query), $headers);

        return $this->send($request);
    }

    /**
     * 发起一个post请求
     * 
     * @param string $uri
     * @param array $body
     * @param array $headers
     * @return Response
     */
    public function post(string $uri, array $body = [], array $headers = []): Response
    {
        $request = $this->createRequest('POST', new Uri($uri), $headers, $body);

        if (!empty($body)) {
            $content = \GuzzleHttp\json_encode($body);
            $request = $request
                ->withHeader('x-sw-content-md5', mb_strtoupper(md5($content)))
                ->withHeader('Content-Type', 'application/json')
                ->withBody(Utils::streamFor($content));
        }

        return $this->send($request);
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function send(Request $request): Response
    {
        // 防止外部误传入签名,先把该header清空
        $request = $request->withoutHeader('x-sw-sign');

        // 生成签名后请求希沃服务器
        $response = $this->getHttpClient()->send(
            $request->withHeader('x-sw-sign', $this->makeSign($request))
        );

        $response->getBody()->rewind();

        return $response;
    }

    /**
     * 生成签名
     *
     * @param Request $request
     * @return string
     */
    protected function makeSign(Request $request): string
    {
        $payload = [];

        // 强制匹配x-sw开头的header头
        foreach ($request->getHeaders() as $key => $val) {
            if (mb_strpos($key, 'x-sw') === false) {
                continue;
            }

            $payload[$key] = $val[0];
        }

        $extendHeaders = $request->getHeader('x-sw-sign-headers');
        // 检查是否有扩展的header信息
        if (!empty($extendHeaders)) {
            foreach (explode(',', $extendHeaders[0]) as $key) {
                $val = $request->getHeader($key);

                if (!empty($val)) {
                    $payload[$key] = $val[0];
                }
            }
        }

        // 把query也作为签名字符串的一部分
        parse_str($request->getUri()->getQuery(), $query);

        foreach ($query as $key => $val) {
            $payload[$key] = $val;
        }

        ksort($payload);

        // 过滤为空的值
        $result = '';
        foreach (array_filter($payload) as $key => $val) {
            $result .= "{$key}{$val}";
        }

        return mb_strtoupper(hash_hmac('md5', $result, $this->secret));
    }

    /**
     * 生成request对象
     * 
     * @param string $method
     * @param Uri $uri
     * @param array $headers
     * @return Request
     */
    protected function createRequest(string $method, Uri $uri, array $headers): Request
    {
        if ($uri->getHost() === '') {
            $uri = $uri->withHost($this->getSeewoHost());
        }

        return new Request($method, $uri, array_merge($headers, [
            'x-sw-app-id'    => $this->appid,
            'x-sw-timestamp' => floor(microtime(true) * 1000),
            'x-sw-req-path'  => $uri->getPath(),
            'x-sw-sign-type' => 'hmac',
            'x-sw-version'   => '2',
        ]));
    }

    /**
     * 设置希沃服务器host
     * 
     * @param string $host
     */
    public function setSeewoHost(string $host)
    {
        $this->host = $host;
    }

    /**
     * 获取希沃服务器host
     * 
     * @return string
     */
    public function getSeewoHost(): string
    {
        return $this->host;
    }

    /**
     * Set GuzzleHttp\Client.
     *
     * @return $this
     */
    public function setHttpClient(ClientInterface $httpClient)
    {
        $this->httpClient = $httpClient;

        return $this;
    }

    /**
     * Return GuzzleHttp\ClientInterface instance.
     *
     * @return ClientInterface
     */
    public function getHttpClient(): ClientInterface
    {
        if (!$this->httpClient instanceof ClientInterface) {
            $this->httpClient = new GuzzleClient([
                'handler' => HandlerStack::create($this->getGuzzleHandler()),
            ]);
        }

        return $this->httpClient;
    }

    /**
     * Get guzzle handler.
     *
     * @return callable
     */
    protected function getGuzzleHandler()
    {
        if (isset($this->options['guzzle_handler'])) {
            $handler = $this->options['guzzle_handler'];

            return is_string($handler) ? new $handler() : $handler;
        }

        return \GuzzleHttp\choose_handler();
    }
}
