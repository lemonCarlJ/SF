<?php
/**
 * report.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class report
{
    /**
     * @var array $console 控制台
     */
    protected static $console = array();

    /**
     * @var string|null $page 报告页面
     */
    protected static $page = null;

    /**
     * @var string $status 响应报头
     */
    public static $status = 'HTTP/1.1 500 Internal Server Error';

    /**
     * 报告模块初始化
     * @param string|null $page 报告页面
     * @param string|null $status 响应报头
     */
    public function __construct( $page = null, $status = null )
    {
        //载入报告页面
        if( is_string( $page ) )
        {
            static::$page = report\directory . $page;
        }

        //定义报头
        if( is_string( $status ) )
        {
            static::$status = $status;
        }

        //注册用户报告处理
        set_error_handler( array( $this, 'user_handler' ) );

        //注册用户异常报告处理
        set_exception_handler( array( $this, 'user_exception' ) );

        //注册执行终止处理
        register_shutdown_function( array( $this, 'shutdown' ) );
    }

    /**
     * 用户报告处理
     * @param integer $code   报告等级
     * @param string $message 报告信息
     * @param string $file    报告文件
     * @param integer $line   报告行
     */
    public function user_handler( $code, $message, $file, $line )
    {
        //记录报告
        $report = array();

        $report[ 'code' ]    = $code;
        $report[ 'message' ] = $message;
        $report[ 'file' ]    = $file;
        $report[ 'line' ]    = $line;

        //致命错误输出报告
        if( $code == E_USER_ERROR )
        {
            $this->output( $report );
        }

        //向控制台记录报告
        static::$console[] = $report;
    }

    /**
     * 用户异常报告处理
     * @param object $exception 异常报告
     */
    public function user_exception( $exception )
    {
        //记录报告
        $report = array();

        $report[ 'code' ]    = $exception->getCode();
        $report[ 'message' ] = $exception->getMessage();
        $report[ 'file' ]    = $exception->getFile();
        $report[ 'line' ]    = $exception->getLine();

        //输出报告
        $this->output( $report );
    }

    /**
     * 执行终止处理
     */
    public function shutdown()
    {
        //如果产生错误报告
        if( $report = error_get_last() )
        {
            $report[ 'code' ] = $report[ 'type' ];

            //输出报告
            $this->output( $report );
        }
    }

    /**
     * 输出控制台报告信息
     * @return array 返回控制台报告信息
     */
    public static function console()
    {
        return static::$console;
    }

    /**
     * 输出报告信息
     * @param string $report 输出报告信息
     */
    protected function output( $report )
    {
        //输出信息
        if( file_exists( static::$page ) )
        {
            $content = include static::$page;
        }else{
            $content = " Code:" . $report[ 'code' ] .
                "\r\n Message:" . $report[ 'message' ] .
                "\r\n File:" . $report[ 'file' ] .
                "\r\n Line:" . $report[ 'line' ];
        }

        //执行输出
        \SF::export( $content, static::$status );
    }

    /**
     * 禁止操作
     */
    public function SF(){}
}
