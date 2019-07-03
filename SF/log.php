<?php
/**
 * log.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class log
{
    /**
     * @var array $record 日志记录
     */
    protected static $record = array();

    /**
     * @var array $trace 调试记录
     */
    protected static $trace = array();

    /**
     * 日志信息等级
     * D:DEBUG 调试
     * I:INFO 信息
     * W:WARNING 注意
     * N:NOTICE 警告
     * E:ERROR 错误
     */
    const grade = array(
        'D' => 'DEBUG',
        'I' => 'INFO',
        'W' => 'WARNING',
        'N' => 'NOTICE',
        'E' => 'ERROR'
    );

    /**
     * 读取模块日志
     * @param string $module 读取日志的模块名
     * @return bool|mixed 返回日志集或者失败
     */
    public static function read( $module )
    {
        if( isset( static::$record[ $module ] ) )
        {
            return static::$record[ $module ];
        }

        return false;
    }

    /**
     * 写入模块日志
     * 日志唯一，不能被覆盖
     * @param $module 日志所属模块
     * @param $log 日志内容
     * @param string $grade 日志等级[ I ]
     * @return bool 返回写入是否成功
     */
    public static function write( $module, $log, $grade = 'I' )
    {
        //初始化模块日志
        if( ! isset( static::$record[ $module ] ) )
        {
            static::$record[ $module ] = array();
        }

        if( is_string( $log ) )
        {
            $log = array( '[ ' . static::grade[ $grade ] . ' ] ' . $log );
        }

        static::$record[ $module ] = array_merge_recursive( $log, static::$record[ $module ] );

        return true;
    }

    /**
     * 调试信息操作
     * @param bool $clear 清除调试信息
     * @return array|void 返回调试信息或者无返回
     */
    public static function trace( $clear = false )
    {
        if( $clear )
        {
            static::$trace = array();
        }else{
            return static::$trace;
        }
    }

    /**
     * 向控制台记录一条调试内容
     * @param mixed $content 调试内容
     */
    public static function console( $content )
    {
        static::$trace[] = $content;
    }
}
