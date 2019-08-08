<?php
/**
 * console.php
 * @Copyright lemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class console
{
    /**
     * @var string|mixed $content 控制台内容
     */
    protected $content = '';

    /**
     * @var mixed $command 执行命令
     */
    protected $command;

    /**
     * @var mixed $arguments 执行参数
     */
    protected $arguments;

    /**
     * 控制台模块初始化
     * @param string $content 输入控制台内容
     * @param string|callable $command_resolve 执行命令分析 [ null ]
     * @param string|callable $arguments_resolve 执行参数分析 [ null ]
     */
    public function __construct( $content, $command_resolve = 'r', $arguments_resolve = 'p' )
    {
        //控制台内容
        $this->content = $content;

        //解析执行命令
        if( is_callable( $command_resolve ) )
        {
            $this->command = call_user_func( $command_resolve, $content );
        }else{
            $this->command = $this->command( $command_resolve );
        }

        //解析执行参数
        if( is_callable( $arguments_resolve ) )
        {
            $this->arguments = call_user_func( $arguments_resolve, $content );
        }else{
            $this->arguments = $this->arguments( $arguments_resolve );
        }
    }

    /**
     * 获取命令
     * @param string $identify 命令标识符
     * @return string 返回获取命令
     */
    protected function command( $identify )
    {
        //初始化命令
        $command = '';

        //解析控制台内容
        parse_str( $this->content, $content );

        //获取命令
        if( isset( $content[ $identify ] ) )
        {
            $command = $content[ $identify ];

            //字符转义
            $command = addslashes( $command );

            //去除标签
            $command = htmlspecialchars( $command );
            $command = strip_tags( $command );

            //去除特殊字符
            $command = trim( $command );

            //去除空格
            $command = preg_replace('/\s/', '', $command );

            //修正系统地址常量
            $command = str_replace( '\\', '/', $command );
        }

        return $command;
    }

    protected function arguments( $identify )
    {
        //初始化参数
        $arguments = array();

        //解析控制台内容
        parse_str( $this->content, $content );

        if( isset( $content[ $identify ] ) )
        {
            $arguments = trim( $content[ $identify ], '/' );

            if( is_string( $arguments ) && strpos( $arguments, '/' ) )
            {
                $parse = '';

                //分割参数并获取列表
                $lists = explode( '/', $arguments );

                //过滤操作
                $lists = array_filter( $lists );

                //重新排列
                $lists = array_values( $lists );

                for( $i = 0; $i < count( $lists ); $i ++ )
                {
                    if( $i % 2 == 0 )
                    {
                        $parse .= '&' . $lists[ $i ] . '=';
                    }else{
                        $parse .= $lists[ $i ];
                    }
                }

                parse_str( $parse, $arguments );
            }
        }

        return $arguments;
    }

    public function __get( $name )
    {
        return $this->$name;
    }
}
