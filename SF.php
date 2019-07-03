<?php
/**
 * SF.php
 * @Copyright lemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace {

    //欢迎使用SF框架
    define( 'SF', true );

    //定义框架核心目录
    define( 'SF\directory', dirname( __FILE__ ) . DIRECTORY_SEPARATOR );

    /**
     * 模块访问接口
     * @return \SF\SF
     */
    function SF()
    {
        return SF::instance();
    }

    /**
     * 框架核心类
     * @package SF
     */
    class SF
    {
        /**
         * @var \SF\SF $SF 模块实例化存储器
         */
        private static $SF;

        /**
         * 框架版本
         */
        const version = '1.0.0';

        /**
         * 框架核心初始化
         */
        public function __construct()
        {
            static::$SF = new SF\SF();
        }

        /**
         * @return \SF\SF 模块访问接口
         */
        public static function instance()
        {
            return static::$SF;
        }

        /**
         * 载入框架核心模块
         * @param string $name 模块名称
         * @return object 返回载入模块实例
         * @throws ReflectionException
         */
        public function module( $name )
        {
            //模块类名
            $module = 'SF\\' . $name;

            //载入模块文件
            if( ! class_exists( $module, false ) )
            {
                if( ! include_once( SF\directory . 'SF' . DIRECTORY_SEPARATOR . $name . '.php' ) )
                {
                    static::export( 'Not Found ' . $name . ' System Module' );
                }
            }

            //定义框架核心模块所属目录
            $directory = $module . '\directory';
            if( ! defined( $directory ) )
            {
                define( $directory, SF\directory . $name . DIRECTORY_SEPARATOR );
            }

            //模块初始化前操作
            static::execute( 'SF.module.' . $name . '.before' );

            //获取注入实参
            $arguments = func_get_args();

            //去除模块name参数
            array_shift( $arguments );

            //初始化反射
            $reflection = new ReflectionClass( $module );

            //实例化模块
            if( $construct = $reflection->getConstructor() )
            {
                //是否定义模块接口
                if( method_exists( $module, 'SF' ) )
                {
                    $initialize = $reflection->newInstanceArgs( $arguments );

                    //只暴露模块接口
                    static::$SF->$name = call_user_func_array( array( $initialize, 'SF' ), $arguments );
                }else{
                    static::$SF->$name = $reflection->newInstanceArgs( $arguments );
                }
            }else{
                static::$SF->$name = $reflection->newInstance();
            }

            //模块实例化后操作
            static::execute( 'SF.module.' . $name . '.after' );

            return static::$SF->$name;
        }

        /**
         * 注册执行列队
         * @param string|null $locate 执行位置 [ null ]
         * @param string|null $module 执行模块 [ null ]
         * @param string|null $method 执行方法 [ null ]
         * @return array|void 返回任务列队
         */
        public static function execute( $locate = null, $module = null, $method = null )
        {
            //任务列队
            static $queue = array();

            //读取列队
            if( is_null( $locate ) )
            {
                return $queue;
            }

            //执行任务列队
            if( is_null( $module ) )
            {
                if( ! empty( $queue[ $locate ] ) )
                {
                    foreach( $queue[ $locate ] as $module => $method )
                    {
                        static::$SF->$module->$method();
                    }
                }

                return ;
            }

            //初始化位置
            if( ! isset( $queue[ $locate ] ) )
            {
                $queue[ $locate ] = array();
            }

            //移除任务列队
            if( is_null( $method ) )
            {
                if( isset( $queue[ $locate ][ $module ] ) )
                {
                    unset( $queue[ $locate ][ $module ] );
                }

                return ;
            }

            //更新任务列队
            $queue[ $locate ][ $module ] = $method;
        }

        /**
         * 记录系统报告
         * @param string|null $message 报告信息 [ null ]
         * @return array|void 返回报告记录
         */
        public static function report( $message = null )
        {
            //报告记录
            static $record = array();

            if( is_null( $message ) )
            {
                //返回记录
                return $record;
            }else{
                //写入报告
                array_push( $record, $message );
            }
        }

        /**
         * 输出系统信息
         * @param $message 系统信息
         * @param string $http_status 发送http状态 [ HTTP/1.1 500 Internal Server Error ]
         */
        public static function export( $message, $http_status = 'HTTP/1.1 500 Internal Server Error' )
        {
            //清除缓冲区域
            ob_clean();

            //发送http状态
            header( $http_status );

            //输出报告
            echo $message;

            exit;
        }

        /**
         * 框架核心注销
         */
        public function __destruct()
        {
            //执行框架注销任务列队
            static::execute( 'SF.__destruct' );

            //销毁模块
            static::$SF = null;
        }
    }
}

namespace SF
{
    /**
     * 框架核心模块存储类
     * @package SF\SF
     */
    class SF
    {
        /**
         * @var array $SF 模块列表
         */
        private $SF = array();

        /**
         * 模块获取器
         * @param string $module 模块名称
         * @return object|null 返回模块或者null
         */
        public function __get( $module )
        {
            //检测模块是否存在
            if( ! isset( $this->$module ) )
            {
                \SF::export( 'Failed To Get ' . $module . ' System Module' );
            }

            return $this->$module;
        }

        /**
         * 模块设置器
         * @param string $module 模块名称
         * @param object $instance 模块实例
         */
        public function __set( $module, $instance )
        {
            //检测模块是否存在
            if( isset( $this->$module ) )
            {
                \SF::export( $module . ' System Module Has Been Loaded' );
            }

            //存储模块
            $this->$module = $instance;

            //记录载入模块
            array_push( $this->SF, $module );
        }
    }
}
