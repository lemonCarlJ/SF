<?php
/**
 * lang.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class lang
{
    /**
     * @var string $language 当前使用语言
     */
    protected static $language = 'zh-cn';

    /**
     * @var array $lang 当前使用语言内容
     */
    protected static $content = array();

    /**
     * 语言模块初始化
     * @param string|null $language 当前语言
     */
    public function __construct( $language = null )
    {
        //设置默认语言包
        if( is_string( $language ) )
        {
            static::$language = $language;
        }
    }

    /**
     * 获取语言
     * @param string $name 获取的语言名
     * @param array $dynamic 动态语言 [ null ]
     * @return null|string 返回空或者语言
     */
    public static function get( $name, $dynamic = null )
    {
        if( isset( static::$content[ $name ] ) )
        {
            $lang = static::$content[ $name ];

            //替换动态字符
            if( is_array( $dynamic ) )
            {
                foreach ( $dynamic as $name => $value )
                {
                    $lang = str_replace( $name, $value, $lang);
                }
            }

            return $lang;
        }else{
            //向控制台输出
            \SF::report( 'Not Found Language [ ' . $name . ' ]' );

            return null;
        }
    }

    /**
     * 加载语言包
     * @param string $path 语言包存放目录
     * @return bool 返回是否载入成功
     */
    public static function loader( $path )
    {
        //语言包文件
        $lang_file = $path . static::$language . '.php';

        //载入语言包
        $content = loader::include_file( $lang_file );

        if( is_array( $content ) )
        {
            static::$content = array_merge( static::$content, $content );
            return true;
        }else{
            \SF::report( 'Not Found Language Pack [ ' . $lang_file . ' ]' );
        }

        return false;
    }

    /**
     * @return string 返回当前语言包
     */
    public static function language()
    {
        return static::$language;
    }

    /**
     * @return array 返回语言内容
     */
    public static function content()
    {
        return static::$content;
    }
}