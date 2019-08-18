<?php
/**
 * request.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class request
{
    /**
     * @var array $header 报头信息
     */
    protected $header = array();

    /**
     * @var array $data 响应数据
     */
    protected $data = array();

    /**
     * @var array $filter 请求过滤
     */
    public $filter = array();

    /**
     * @var string $method 请求方式
     */
    protected $method;

    /**
     * 初始化请求模块
     */
    public function __construct()
    {
        //获取报头
        $this->header = $this->header();

        //获取请求方式
        $this->method = $_SERVER[ 'REQUEST_METHOD' ];

        //获取get
        $this->data[ 'get' ] = $_GET;

        //获取post
        $this->data[ 'post' ] = $_POST;

        //获取其他请求
        if( $this->method != 'POST' && $this->method != 'GET' )
        {
            parse_str( file_get_contents( 'php://input' ), $this->data[ strtolower( $this->method ) ] );
        }

        //获取cookie
        $this->data[ 'cookie' ] = $_COOKIE;

        //获取files
        $this->data[ 'files' ] = $_FILES;
    }

    /**
     * 获取get请求
     * @param null|string $name 请求名 [ null ]
     * @param array $filter 过滤函数 [ array ]
     * @return mixed|null
     */
    public function get( $name = null, $filter = array() )
    {
        if( ! is_null( $name ) )
        {
            if( isset( $this->data[ 'get' ][ $name ] ) )
            {
                return $this->filter( $this->data[ 'get' ][ $name ], $filter );
            }
        }else{
            return $this->filter( $this->data[ 'get' ], $filter );
        }

        return null;
    }

    /**
     * 获取post请求
     * @param null|string $name 请求名 [ null ]
     * @param array $filter 过滤函数 [ array ]
     * @return mixed|null
     */
    public function post( $name = null, $filter = array() )
    {
        if( ! is_null( $name ) )
        {
            if( isset( $this->data[ 'post' ][ $name ] ) )
            {
                return $this->filter( $this->data[ 'post' ][ $name ], $filter );
            }
        }else{
            return $this->filter( $this->data[ 'post' ], $filter );
        }

        return null;
    }

    /**
     * 返回cookie
     * @param null|string $name 名称 [ null ]
     * @return mixed|null
     */
    public function cookie( $name = null )
    {
        if( is_null( $name ) )
        {
            return $this->data[ 'cookie' ];
        }

        return isset( $this->data[ 'cookie' ][ $name ] ) ? $this->data[ 'cookie' ][ $name ] : null;
    }

    /**
     * 返回文件集
     * @param null|string $name 名称 [ null ]
     * @return mixed|null
     */
    public function files( $name = null )
    {
        if( is_null( $name ) )
        {
            return $this->data[ 'files' ];
        }
        return isset( $this->data[ 'files' ][ $name ] ) ? $this->data[ 'files' ][ $name ] : null;
    }

    /**
     * 过滤参数
     * 自定义过滤会覆盖过滤参数
     * @param mixed $data 过滤数据
     * @param array $filter 自定义过滤 [ array ]
     * @return mixed 返回过滤后的参数
     */
    protected function filter( $data, $filter = array() )
    {
        //返回数据
        $result = array();

        //过滤方法
        $methods = array_merge( $this->filter, $filter );

        if( ! empty( $filter ) )
        {
            if( is_array( $data ) )
            {
                //循环过滤
                foreach ( $data as $key => $value )
                {
                    if( is_array( $value ) )
                    {
                        $result[ $key ] = $this->filter( $value );
                    }else{
                        $result[ $key ] = $value;
                    }
                }
            }else{
                //执行过滤
                foreach( $methods as $method )
                {
                    $result = $method( $data );
                }
            }
        }else{
            $result = $data;
        }

        return $result;
    }

    /**
     * 获取报头
     * @return array
     */
    protected function header()
    {
        //报头
        $header = array();

        foreach ( $_SERVER as $name => $value )
        {
            if( substr( $name, 0, 5 ) == 'HTTP_' )
            {
                //取小写header名
                $name = strtolower( substr( $name, 5 ) );
                $header[ $name ] = $value;
            }
        }

        return $header;
    }

    /**
     * 只读变量
     * @param $name
     * @return mixed
     */
    public function __get( $name )
    {
        return $this->$name;
    }

    /**
     * 获取其他请求
     * @param $name 请求名
     * @param $arguments 请求参数
     * @return mixed|null
     */
    public function __call( $name, $arguments )
    {
        if( ! is_null( $name ) )
        {
            if( isset( $this->data[ $name ][ $arguments[ 0 ] ] ) )
            {
                return $this->filter( $this->data[ $name ][ $arguments[ 0 ] ], $arguments[ 1 ] );
            }
        }else{
            return $this->filter( $this->data[ $name ], $arguments[ 1 ] );
        }

        return null;
    }
}
