<?php
/**
 * debug.php
 * @Copyright lemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class debug
{
    /**
     * @var array $message 调试信息
     */
    protected $message = array();

    /**
     * 调试模块初始化
     * @param int $start_time 开始执行时间戳 [ 0 ]
     */
    public function __construct( $start_time = 0 )
    {
        //记录开始执行时间
        if( ! $start_time )
        {
            $start_time = microtime( true );
        }
        $this->message[ 'start_time' ] = $start_time;

        //初始化运行时间
        $this->message[ 'runtime' ] = array();
    }

    /**
     * 统计运行时间
     * @param string $name 位置名称
     */
    public function runtime( $name )
    {
        //运行结束时间
        $end_time = microtime( true );

        if( ! $start_time = array_pop( $this->message[ 'runtime' ] ) )
        {
            $start_time = $this->message[ 'start_time' ];
        }

        $this->message[ 'runtime' ][ $name ] = number_format( $end_time - $start_time, 10 );
    }

    /**
     * 载入控制台
     * @param null|string $page 控制台页面 [ null ]
     */
    public function console( $page = null )
    {
        //收集调试信息
        $this->collect();

        //获取调试信息
        $message = $this->message;

        //默认调试模板
        if( is_null( $page ) )
        {
            $page = 'debug.php';
        }
        $page_path = debug\directory . $page;

        //载入调试模板
        if( file_exists( $page_path ) )
        {
            include $page_path;
        }else{
            //换行符
            $break = PHP_EOL . '<br/>';
            //空格符
            $space = '&nbsp;&nbsp;&nbsp;';

            echo '<small>';

            //处理并输出调试信息
            foreach ( $message as $name => $record )
            {
                //输出系统报告
                if( $name == 'report' )
                {
                    echo 'Report : ' . $break;
                    foreach( $record as $report )
                    {
                        echo $space . $report . $break;
                    }
                    echo $break;
                }

                //输出执行列队
                if( $name == 'execute' )
                {
                    echo 'Execute : ' . $break;
                    foreach( $record as $execute_locate => $execute )
                    {
                        foreach ( $execute as $execute_module => $execute_method )
                        {
                            echo $space . 'Locate:' . $execute_locate . $space . ' Module:' . $execute_module . $space . ' Method:' . $execute_method . $break;
                        }
                    }
                    echo $break;
                }

                //输出模块日志信息
                if( $name == 'SF' )
                {
                    echo 'Module : ' . $break;
                    foreach ( $record as $module => $module_record )
                    {
                        if( ! empty( $module_record ) )
                        {
                            echo $space . $module . $break;

                            foreach( $module_record as $report )
                            {
                                switch ( $module )
                                {
                                    //报告模块处理
                                    case 'report' :
                                        echo  $space . $space . "Code:". $report[ 'code' ] . "&nbsp;Message:" . $report[ 'message' ] . "&nbsp;File:" . $report[ 'file' ] . "&nbsp;Line:" . $report[ 'line' ] . $break;
                                        break;

                                    default :
                                        echo $space . $space . $report . $break;
                                }
                            }

                            echo $break;
                        }
                    }
                }

                //输出运行时间
                if( $name == 'runtime' )
                {
                    echo 'Runtime : ' . $break;

                    foreach ( $record as $name => $runtime )
                    {
                        echo $space . $name . ' Runtime Is ' . $runtime . ' Second ' . $break;
                    }

                    echo $break;
                }
            }

            //输出执行时间
            echo $break . "Execution Time : <b>" . number_format( microtime( true ) - $message[ 'start_time' ], 10 ) . "</b> Second";

            echo '</small>';
        }
    }

    /**
     * 收集调试信息
     */
    protected function collect()
    {
        //初始化调试信息
        $message = $this->message;

        //获取模块列表
        $modules = \SF::instance()->SF;

        //收集框架核心报告
        $message[ 'report' ] = \SF::report();

        //收集框架执行任务
        $message[ 'execute' ] = \SF::execute();

        //收集模块信息
        $SF = array();
        foreach ( $modules as $module )
        {
            switch ( $module )
            {
                //日志模块处理
                case 'log' :
                    $message[ 'SF' ][ 'log' ] = log::trace();
                    break;

                //报告模块处理
                case 'report' :
                    $message[ 'SF' ][ 'report' ] = report::console();
                    break;

                //装载器模块处理
                case 'loader' :
                    $message[ 'SF' ][ 'loader' ] = loader::fileset();
                    break;

                //默认模块处理
                default :
                    $message[ 'SF' ][ $module ] = log::read( $module );
            }
        }

        $this->message = $message;
    }
}
