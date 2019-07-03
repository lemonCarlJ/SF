<?php
/**
 * application.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class application
{
    /**
     * @var string $name 应用名
     */
    protected $name;

    /**
     * @var string $path 应用路径
     */
    protected $path = application\directory;

    /**
     * @var string $version 应用版本
     */
    protected $version = '1.0.0';

    /**
     * @var string|null $author 应用作者
     */
    protected $author = null;

    /**
     * @var string|null $email 应用联系邮箱
     */
    protected $email = null;

    /**
     * @var 框架接口
     */
    private $SF;

    /**
     * 应用模块初始化
     * @param string|null $name 应用名
     * @param string|null $version 应用版本
     * @param string|null $author 应用作者
     * @param string|null $email 应用邮箱
     */
    public function __construct( $name = null, $version = null, $author = null, $email = null )
    {
        //注入接口
        $this->SF = \SF::instance();

        //定义应用名
        if( is_null( $name ) )
        {
            $name = basename( $_SERVER[ 'SCRIPT_FILENAME' ], '.php' );
        }
        $this->name = $name;

        //定义应用版本
        if( ! is_null( $version ) )
        {
            $this->version = $version;
        }

        //定义应用作者
        if( ! is_null( $author ) )
        {
            $this->author = $author;
        }

        //定义应用邮箱
        if( ! is_null( $email ) )
        {
            $this->email = $email;
        }

        //模块列表
        $modules = $this->SF->SF;

        //应用下每个模块所属路径
        foreach( $modules as $module )
        {
            $module_dir = 'SF\application\\' . $module . '\directory';

            if( ! defined( $module_dir ) )
            {
                define( $module_dir, application\directory . $module . DIRECTORY_SEPARATOR );
            }
        }

        //注册加载器命名空间路径
        $this->SF->loader->add_namespace( 'SF\application', application\directory );
    }


    /**
     * 初始化应用
     * @param null|string $config 配置文件名
     */
    public function initialize( $config = null )
    {
        //载入应用配置
        if( is_string( $config ) )
        {
            config::loader( application\config\directory . $config . '.php' );
        }

        //载入用户配置
        if( $config = config::get( 'application.config' ) )
        {
            foreach ( $config as $config_file )
            {
                config::loader( $config_file );
            }
        }

        //载入请求过滤
        if( $request = config::get( 'application.request' ) )
        {
            $this->SF->request->filter( $request );
        }

        //载入语言包
        if( $lang = config::get( 'application.lang' ) )
        {
            foreach ( $lang as $lang_path )
            {
                lang::loader( $lang_path );
            }
        }

        //绑定驱动
        if( $driver = config::get( 'application.driver' ) )
        {
            foreach( $driver as $drive_name => $drive_handler )
            {
                $this->SF->driver->bind( $drive_name, $drive_handler );
            }
        }

        //载入公共函数
        if( $function = config::get( 'application.function' ) )
        {
            foreach( $function as $function_file )
            {
                loader::include_file( application\directory . $function_file );
            }
        }

        //载入项目库
        if( $libaray = config::get( 'application.libaray' ) )
        {
            foreach( $libaray as $libaray_name => $libaray_file )
            {
                $this->SF->libaray->loader( $libaray_name, $libaray_file );
            }
        }

        //载入视图引擎
        if( $view = config::get( 'application.view' ) )
        {
            foreach ( $view as $view_name => $view_conf )
            {
                //array_unshift( $view_conf, array( 'name' => $view_name ) );
                call_user_func_array( array( $this->SF->view, 'engine' ), $view_conf );
            }
        }

        //载入控制器
        if( $controller = config::get( 'application.controller' ) )
        {
            foreach( $controller as $controller_name )
            {
                $this->SF->controller->loader( $controller_name );
            }
        }
    }

    /**
     * 执行应用
     * @param string $commond 命令 [ '' ]
     * @param array $parameter 参数 [ array ]
     * @param array $controller 依赖控制器 [ null ]
     * @return boolean|object|mixed 返回加载失败或者执行应用或者应用方法
     * @throws \ReflectionException
     */
    public function execute( $commond = '', $parameter = array(), $controller = null )
    {
        //控制权限交给控制台
        if( empty( $commond ) )
        {
            //控制台命令
            $commond = $this->SF->console->commond;

            //空命令废弃此次执行
            if( $commond == '' )
            {
                return '';
            }

            //控制台参数
            $parameter = $this->SF->console->parameter;
        }

        //规范命令
        $commond = trim( trim( $commond, '/' ), '\\' );

        //载入基础控制器
        if( is_string( $controller ) )
        {
            $controller = explode( ',', $controller );
        }

        if( is_array( $controller ) )
        {
            foreach( $controller as $controller_name )
            {
                $this->SF->controller->loader( $controller_name );
            }
        }

        //获取执行文件路径
        if( ( $execute_route = dirname( $commond ) ) != '.' )
        {
            //执行模块
            $execute_module = basename( $commond );

            //执行文件
            $execute_file = application\directory . 'application' . DIRECTORY_SEPARATOR . str_replace( '\\', '/', $execute_route ) . '.php';

            //开始执行
            if( loader::include_file( $execute_file ) )
            {
                $execute_class = 'SF\application\\' . str_replace( '/', '\\', $execute_route );

                if( method_exists( $execute_class, $execute_module ) )
                {
                    $reflection_class = new \ReflectionClass( $execute_class );

                    /**
                     * 初始化执行应用
                     */
                    if( $construct = $reflection_class->getConstructor() )
                    {
                        //初始化注入实参
                        $construct_arguments = array();

                        //获取被注入形参
                        $construct_parameters = $construct->getParameters();

                        foreach( $construct_parameters as $locate => $construct_parameter )
                        {
                            //如果有默认值则不进行注入
                            if( ! $construct_parameter->isDefaultValueAvailable() )
                            {
                                if( $depend = $construct_parameter->getClass() )
                                {
                                    //注入模块名
                                    $depend_module = $depend->getName();

                                    //开始注入参数
                                    if( $depend_module == 'SF\SF' )
                                    {
                                        $construct_arguments[ $locate ] = $this->SF;
                                    }else{
                                        $depend_module_name = basename( $depend_module );
                                        $construct_arguments[ $locate ] = $this->SF->$depend_module_name;
                                    }
                                }else{
                                    throw new \Exception( 'Application Execution Initialization Missing Parameters [ ' . $construct_parameter->getName() . ' ]', E_USER_ERROR );
                                }
                            }else{
                                $construct_arguments[ $locate ] = $construct_parameter->getDefaultValue();
                            }
                        }

                        $initialize = $reflection_class->newInstanceArgs( $construct_arguments );
                    }else{
                        $initialize = $reflection_class->newInstance();
                    }

                    /**
                     * 执行应用模块
                     */
                    if( $module = $reflection_class->getMethod( $execute_module ) )
                    {
                        //初始化注入实参
                        $module_arguments = array();

                        //获取被注入形参
                        $module_parameters = $module->getParameters();

                        foreach( $module_parameters as $locate => $module_parameter )
                        {
                            //模块参数名
                            $module_parameter_name = $module_parameter->getName();

                            //如果已经赋值则不进行注入
                            if( ! isset( $parameter[ $module_parameter_name ] ) )
                            {
                                //如果有默认值则不进行注入
                                if( ! $module_parameter->isDefaultValueAvailable() )
                                {
                                    if( $depend = $module_parameter->getClass() )
                                    {
                                        //注入模块名
                                        $depend_module = $depend->getName();

                                        //开始注入参数
                                        if( $depend_module == 'SF\SF' )
                                        {
                                            $module_arguments[ $module_parameter_name ] = $this->SF;
                                        }else{
                                            $depend_module_name = basename( $depend_module );
                                            $module_arguments[ $module_parameter_name ] = $this->SF->$depend_module_name;
                                        }
                                    }else{
                                        \SF::report( 'Module ' . $execute_module . ' Is Missing Parameter [ ' . $module_parameter_name . ' ]' );
                                    }
                                }else{
                                    $module_arguments[ $module_parameter_name ] = $module_parameter->getDefaultValue();
                                }
                            }else{
                                $module_arguments[ $module_parameter_name ] = $parameter[ $module_parameter_name ];
                            }
                        }

                        //参数对等
                        if( count( $module_arguments ) == count( $module_parameters ) )
                        {
                            //执行模块
                            return $module->invokeArgs( $initialize, $module_arguments );
                        }
                    }
                }else{
                    \SF::report( 'Application Execution Failed [ ' . $execute_route . '/' . $execute_module . ' ]' );
                }
            }else{
                \SF::report( 'Not Found The Executable File [ ' . $execute_file . ' ]' );
            }
        }

        return false;
    }

    /**
     * 对比当前应用版本
     * @param string $compare 对比版本
     * @return mixed 返回对比结果
     */
    public function version( $compare )
    {
        return version_compare( $this->version, $compare );
    }

    /**
     * 获取关键字
     * @param $name
     * @return mixed|null
     */
    public function __get( $name )
    {
        if( isset( $this->$name ) )
        {
            return $this->$name;
        }

        return null;
    }
}