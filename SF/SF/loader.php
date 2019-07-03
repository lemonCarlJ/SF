<?php
/**
 * loader.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class loader
{
    /**
     * @var array $fileset 加载文件集
     */
    protected static $fileset = array();

    /**
     * @var array $namespace 加载命名空间
     */
    protected static $namespaces = array();

    /**
     * @var array $map 加载文件映射
     */
    protected static $maps = array();

    /**
     * 装载器模块初始化
     * @param bool $autoload 是否开启自动加载 [ false ]
     */
    public function __construct( $autoload = false )
    {
        //注册系统命名空间
        static::add_namespace( 'SF', \SF\directory );

        //开启自动加载
        if( $autoload )
        {
            static::register();
        }
    }

    /**
     * 注册自动加载
     */
    public static function register()
    {
        spl_autoload_register( '\SF\loader::autoload' );
    }

    /**
     * 注销自动加载
     */
    public static function un_register()
    {
        spl_autoload_unregister( '\SF\loader::autoload' );
    }

    /**
     * 自动加载
     * @param $class 加载类
     * @return bool|mixed 返回加载内容或者是否成功
     */
    protected static function autoload( $class )
    {
        //相对路径
        if( strpos( $class, '\\' ) === false && strpos( $class, '/' ) === false )
        {
            //查找映射
            if( isset( static::$map[ $class ] ) )
            {
                return static::include_file( static::$map[ $class ] );
            }
        }

        //查找文件
        if( $file_path = static::search( $class ) )
        {
            return static::include_file( $file_path );
        }

        return false;
    }

    /**
     * 搜索文件路径
     * @param string $path 搜索路径
     * @return bool|string 返回未搜索到或者搜索到的文件路径
     */
    protected static function search( $path )
    {
        //按顺序搜索位置信息
        foreach( static::$namespaces as $name => $locate )
        {
            if( strpos( $path, $name . '\\' ) === 0 || strpos( $path, $name . '/' ) === 0 )
            {
                //相对路径
                $relative_path = trim( trim( str_replace( $name, '', $path ), '\\' ). '/' );

                if( strtolower( substr( $relative_path, -4, 4 ) ) != '.php' )
                {
                    $relative_path .= '.php';
                }

                //绝对路径
                $absolute_path = $locate . $relative_path;

                if( file_exists( $absolute_path ) )
                {
                    return $absolute_path;
                }
            }
        }

        return false;
    }

    /**
     * 设置命名空间
     * @param string $name 空间名
     * @param string $path 真实路径
     */
    public function add_namespace( $name, $path )
    {
        static::$namespaces[ $name ] = trim( trim( $path, '\\' ), '/' );
    }

    /**
     * 设置映射
     * @param array $maps 映射集合
     */
    public function add_maps( $maps )
    {
        static::$maps = array_merge( static::$maps, $maps );
    }

    /**
     * 高级加载
     * @param $path 路径信息
     * @return bool|mixed 返回加载状态或者加载文件内容
     */
    public static function import( $path )
    {
        //优先使用自动加载
        if( strpos( $path, '/' ) === false || strpos( $path, '\\' ) === 0 )
        {
            return static::autoload( $path );
        }

        return static::include_file( $path );
    }

    /**
     * require 加载文件
     * @param $file_path 文件路径
     * @return mixed|boolean 返回文件内容或者加载状态
     */
    public static function require_file( $file_path )
    {
        //记录加载文件
        static::$fileset[] = $file_path;
        return require $file_path;
    }

    /**
     * include 加载文件
     * @param $file_path 文件路径
     * @return mixed|boolean 返回文件内容或者加载状态
     */
    public static function include_file( $file_path )
    {
        //记录加载文件
        static::$fileset[] = $file_path;
        return file_exists( $file_path ) ? include $file_path : false;
    }

    /**
     * 直接获取文件内容
     * @param $file_path 文件路径
     * @return false|string 返回读取内容或者读取失败
     */
    public static function content( $file_path )
    {
        //记录加载文件
        static::$fileset[] = $file_path;
        return file_exists( $file_path ) ? file_get_contents( $file_path ) : null;
    }

    /**
     * @return array 返回文件集
     */
    public static function fileset()
    {
        return static::$fileset;
    }

    /**
     * @return array 返回命名空间
     */
    public static function namespaces()
    {
        return static::$namespaces;
    }

    /**
     * @return array 返回映射
     */
    public static function maps()
    {
        return static::$maps;
    }

    /**
     * 返回私有变量
     * @param $name
     * @return mixed
     */
    public function __get( $name )
    {
        return $this->$name;
    }
}