<?php
/**
 * console.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class console
{
    /**
     * @var array 输入内容
     */
    protected $content = array();

    /**
     * @var string 命令
     */
    protected $commond = '';

    /**
     * @var array 参数
     */
    protected $parameter = array();

    /**
     * 控制台模块初始化
     * @param array $content 输入内容
     * @param string $commond_identify 命令标识符 [ r ]
     * @param string $parameter_identify 参数标识符 [ p ]
     */
    public function __construct( $content, $commond_identify = 'r', $parameter_identify = 'p' )
    {
        //命令内容
        $this->content = $content;

        //获取命令
        $this->commond = $this->commond( $commond_identify );

        //获取参数
        $this->parameter = $this->parameter( $parameter_identify );
    }

    /**
     * 获取命令
     * @param string $identify 命令标识符
     * @return string 返回获取命令
     */
    protected function commond( $identify )
    {
        //初始化命令
        $commond = '';

        //获取命令
        if( isset( $this->content[ $identify ] ) )
        {
            $commond = $this->content[ $identify ];

            //字符转义
            $commond = addslashes( $commond );

            //去除标签
            $commond = htmlspecialchars( $commond );
            $commond = strip_tags( $commond );

            //去除特殊字符
            $commond = trim( $commond );

            //去除空格
            $commond = preg_replace('/\s/', '', $commond );
        }

        return $commond;
    }

    protected function parameter( $identify )
    {
        //初始化参数
        $parameter = array();

        if( isset( $this->content[ $identify ] ) )
        {
            $parameter = trim( $this->content[ $identify ], '/' );

            if( is_string( $parameter ) && strpos( $parameter, '/' ) )
            {
                $parse = '';

                //分割参数并获取列表
                $lists = explode( '/', $parameter );

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

                parse_str( $parse, $parameter );
            }
        }

        return $parameter;
    }

    public function __get( $name )
    {
        return $this->$name;
    }
}