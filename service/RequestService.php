<?php
/**
 * Created by PhpStorm.
 * @file   RequestService.php
 * @author 李锦 <jin.li@vhall.com>
 * @date   2021/4/29 下午2:45
 * @desc   RequestService.php
 */

namespace service;


class RequestService extends BaseService
{
    public $_data;
    private $_buffer;


    protected function __init($params)
    {
        $this->_buffer = $params;
    }

    /**
     * Get http raw head.
     *
     * @return string
     */
    public function rawHead()
    {
        if (!isset($this->_data['head'])) {
            $this->_data['head'] = \strstr($this->_buffer, "\r\n\r\n", true);
        }
        return $this->_data['head'];
    }

    public function parseHeaders()
    {
        $this->_data['headers'] = array();
        $raw_head = $this->rawHead();
        $end_line_position = \strpos($raw_head, "\r\n");
        if ($end_line_position === false) {
            return;
        }
        $head_buffer = \substr($raw_head, $end_line_position + 2);
        $head_data = \explode("\r\n", $head_buffer);
        foreach ($head_data as $content) {
            if (false !== \strpos($content, ':')) {
                list($key, $value) = \explode(':', $content, 2);
                $key = \strtolower($key);
                $value = \ltrim($value);
            } else {
                $key = \strtolower($content);
                $value = '';
            }
            if (isset($this->_data['headers'][$key])) {
                $this->_data['headers'][$key] = "{$this->_data['headers'][$key]},$value";
            } else {
                $this->_data['headers'][$key] = $value;
            }
        }
        $this->parseGet();
        $this->parsePost();
    }
    /**
     * $_GET.
     *
     * @param null $name
     * @param null $default
     * @return mixed|null
     */
    public function get($name = null, $default = null)
    {
        if (!isset($this->_data['get'])) {
            $this->parseGet();
        }
        if (null === $name) {
            return $this->_data['get'];
        }
        return isset($this->_data['get'][$name]) ? $this->_data['get'][$name] : $default;
    }

    /**
     * $_POST.
     *
     * @param $name
     * @param null $default
     * @return mixed|null
     */
    public function post($name = null, $default = null)
    {
        if (!isset($this->_data['post'])) {
            $this->parsePost();
        }
        if (null === $name) {
            return $this->_data['post'];
        }
        return isset($this->_data['post'][$name]) ? $this->_data['post'][$name] : $default;
    }


    /**
     * Parse head.
     *
     * @return void
     */
    protected function parseGet()
    {
        $query_string = $this->queryString();
        $this->_data['get'] = array();
        if ($query_string === '') {
            return;
        }
        \parse_str($query_string, $this->_data['get']);
    }


    /**
     * Get query string.
     *
     * @return mixed
     */
    public function queryString()
    {
        if (!isset($this->_data['query_string'])) {
            $this->_data['query_string'] = \parse_url($this->uri(), PHP_URL_QUERY);
        }
        return $this->_data['query_string'];
    }


    /**
     * Get uri.
     *
     * @return mixed
     */
    public function uri()
    {
        if (!isset($this->_data['uri'])) {
            $this->parseHeadFirstLine();
        }
        return $this->_data['uri'];
    }

    /**
     * Parse first line of http header buffer.
     *
     * @return void
     */
    protected function parseHeadFirstLine()
    {
        $first_line = \strstr($this->_buffer, "\r\n", true);
        $tmp = \explode(' ', $first_line, 3);
        $this->_data['method'] = $tmp[0];
        $this->_data['uri'] = isset($tmp[1]) ? $tmp[1] : '/';
    }


    /**
     * Parse post.
     *
     * @return void
     */
    protected function parsePost()
    {
        $body_buffer = $this->rawBody();
        $this->_data['post'] = $this->_data['files'] = array();
        if ($body_buffer === '') {
            return;
        }
        $content_type = $this->header('content-type', '');
        if (\preg_match('/boundary="?(\S+)"?/', $content_type, $match)) {
            $http_post_boundary = '--' . $match[1];
//            $this->parseUploadFiles($http_post_boundary);
            return;
        }
        if (\preg_match('/\bjson\b/i', $content_type)) {
            $this->_data['post'] = (array)json_decode($body_buffer, true);
        } else {
            \parse_str($body_buffer, $this->_data['post']);
        }
    }


    /**
     * Get header item by name.
     *
     * @param null $name
     * @param null $default
     * @return string|null
     */
    public function header($name = null, $default = null)
    {
        if (!isset($this->_data['headers'])) {
            $this->parseHeaders();
        }
        if (null === $name) {
            return $this->_data['headers'];
        }
        $name = \strtolower($name);
        return isset($this->_data['headers'][$name]) ? $this->_data['headers'][$name] : $default;
    }

    /**
     * Get http raw body.
     *
     * @return string
     */
    public function rawBody()
    {
        return \substr($this->_buffer, \strpos($this->_buffer, "\r\n\r\n") + 4);
    }


}
