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
     * @param Closure|callable $command_analysis 执行命令分析
     * @param Closure|callable $arguments_analysis 执行参数分析
     */
    public function __construct( $content, $command_analysis, $arguments_analysis )
    {
        //控制台内容
        $this->content = $content;

        //解析执行命令
        $this->command = $command_analysis( $content );

        //解析执行参数
        $this->arguments = $arguments_analysis( $content );
    }

    public function __get( $name )
    {
        return $this->$name;
    }
}
