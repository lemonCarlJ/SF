<?php
/**
 * libaray.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class libaray
{
    /**
     * 基础库模块初始化
     * @param null|array $libaray 加载库列表 [ null ]
     */
    public function __construct( $libaray = null )
    {
        //载入库
        if( $libaray )
        {
            $this->loader( $libaray );
        }
    }

    /**
     * 基础库加载
     * 可以加载多个库文件,多个用逗号隔开或者数组,也可以使用命名空间
     * @param string|array $name 库名
     * @param null|string $path 库路径
     */
    public function loader( $name, $path = null )
    {
        //命名空间分隔符
        $namespace_delimit = '\\';
        
        //基础库路径
        if( is_null( $path ) )
        {
            $path = libaray\directory;
        }

        //加载库列表
        if( is_string( $name ) )
        {
            $name = explode( ',', $name );
        }

        foreach ( $name as $libaray )
        {
            //规范库名
            $libaray = trim( $libaray, $namespace_delimit );

            //基础库名
            $libaray_name = $libaray;

            //基础库路径
            $libaray_path = $path;

            if( $locate = strrpos( $libaray_name, $namespace_delimit ) )
            {
                //追加基础库路径
                $libaray_path .= substr( $libaray_name, 0, $locate ) . DIRECTORY_SEPARATOR;

                //重新定义基础库名
                $libaray_name = substr( $libaray_name, $locate + 1 );
            }

            //基础库文件
            $libaray_file = $libaray_path . $libaray_name . '.php';

            if( ! loader::include_file( $libaray_file ) )
            {
                \SF::report( 'Not Found Libaray File [ ' . $libaray_file . ' ]' );
            }
        }
    }

    /**
     * 库日志
     * @param string $name 库名
     * @param string $content 日志信息
     */
    public static function log( $name, $content )
    {
        log::write( 'libaray', array( $name => $content ) );
    }
}