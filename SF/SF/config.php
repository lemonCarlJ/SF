<?php
/**
 * config.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class config
{
    /**
     * @var array $config 配置信息
     */
    protected static $config = array();

    /**
     * 配置模块初始化
     * @param string|null $config 配置文件名 [ null ]
     */
    public function __construct( $config = null )
    {
        if( is_string( $config ) )
        {
            //配置文件
            $config_file = \SF\config\directory . $config . '.php';

            //载入配置文件
            $this->loader( $config_file );
        }
    }

    /**
     * 配置操作
     * 必须存在配置才能操作配置
     * @param string $name 配置名
     * @param string $value 配置值 [ null ]
     * @param bool $operate 是否操作 [ false ]
     * @return boolean|mixed 返回配置
     */
    protected static function _config( $name, $value = null, $operate = false)
    {
        $config = &static::$config;
        if( isset( $config[ $name ] ) )
        {
            if( $operate )
            {
                $config[ $name ] = $value;
                return true;
            }else{
                return $config[ $name ];
            }
        }else{
            //如果使用空间定位
            if( strpos( $name, '.' ) )
            {
                //配置组
                $ini = explode( '.', $name );

                //处理参数深度
                $count = count( $ini );

                for( $i = 0; $i < $count; $i++ )
                {
                    if( isset( $config[ $ini[ $i ] ] ) )
                    {
                        $config = &$config[ $ini[ $i ] ];

                        if( $i === ( $count - 1 ) )
                        {
                            if( $operate )
                            {
                                $config = $value;
                                return true;
                            }else{
                                return $config;
                            }
                        }
                    }
                }
            }
        }

        return $operate ? false : null;
    }

    /**
     * 设置配置
     * 新的配置只能载入
     * @param string $name 配置名
     * @param mixed $value 配置值
     * @return boolean|mixed 返回配置或者修改状态
     */
    public static function set( $name, $value )
    {
        return static::_config( $name, $value, true );
    }

    /**
     * 获取配置
     * @param string $name 配置名 [ null ]
     * @return mixed 返回配置
     */
    public static function get( $name = null )
    {
        if( $name )
        {
            return self::_config( $name );
        }

        return static::$config;
    }

    /**
     * 载入配置文件
     * @param $config_file 配置文件
     * @return bool 返回是否载入成功
     */
    public static function loader( $config_file )
    {
        //载入配置文件
        if( is_array( $config = loader::include_file( $config_file ) ) )
        {
            static::$config = array_merge_recursive( static::$config, $config );
            return true;
        }

        \SF::report( 'Load Configuration Failed [ ' . $config_file . ' ]' );

        return false;
    }
}