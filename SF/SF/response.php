<?php
/**
 * response.php
 * @Copyright lemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class response
{
    /**
     * 预设发送报头
     * @var array
     */
    public $header = array();

    /**
     * 默认状态码,建议追加状态从大一点的数组开始设置，防止系统默认状态更新
     * @var array
     */
    protected $status_code = array(
        0 => 'ok', //ok
        1 => 'unauthorized', //需要授权
        2 => 'no permission', //无权限
    );

    /**
     * 输出报头
     */
    public function header()
    {
        $header = $this->header;

        foreach ( $header as $name => $value )
        {
            $header_string = is_numeric( $name ) ? $value : $name . ':' . $value;
            header( $header_string ); //设置报头
        }
    }

    /**
     * 输出文本内容
     * @param string $data 输出数据
     */
    public function content( $data )
    {
        ob_clean();

        //去除json格式报头
        if( isset( $this->header[ 'Content-Type' ] ) )
        {
            unset( $this->header[ 'Content-Type' ] );
        }

        //发送报头
        $this->header();

        //输出数据
        echo $data;
    }

    /**
     * 输出json数据
     * @param array $data 输出数据
     */
    public function data( $data )
    {
        ob_clean();

        //输出json格式报头
        $this->header[ 'Content-Type' ] = 'application/json';

        //发送报头
        $this->header();

        //输出数据
        echo json_encode( $data );
    }

    /**
     * 输出标准化的数据
     * @param number $code 接口状态
     * @param number $status 数据状态
     * @param $message  数据信息
     * @param array $data 返回数据集 [ array ]
     */
    public function api( $code, $status, $message, $data = array() )
    {
        //输出数据
        $this->data( array(
            'code' => $code,
            'message' => $this->status_code[ $code ],
            'data' => array(
                'status' => $status,
                'message' => $message,
                'data' => $data
            )
        ) );
    }

    public function __get( $name )
    {
        return $this->$name;
    }

    public function __set( $name, $value )
    {
        $this->$name = array_merge( $value, $this->$name );
    }
}
