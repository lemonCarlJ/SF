<?php
/**
 * driver.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class driver
{
    /**
     * @var array $storage 驱动存储列表
     */
    protected $storage = array();

    /**
     * @var array $handler 已装载处理程序列表
     */
    protected $handler = array();

    /**
     * @var array $record 驱动器记录列表
     */
    protected $record = array();

    /**
     * @var array 执行队列
     */
    protected $execute = array();

    /**
     * 驱动器模块初始化
     * 传入格式 array( '驱动' => '模式' )
     * @param null|array drives 需要加载的驱动器以及模式 [ null ]
     */
    public function __construct( $drives = null )
    {
        //注册任务执行
        \SF::execute( 'SF.__destruct', 'driver', 'execute' );

        if( is_array( $drives ) )
        {
            foreach ( $drives as $drive => $mode )
            {
                $this->loader( $drive, $mode );
            }
        }
    }

    /**
     * 载入驱动器
     * @param string $drive 驱动器
     * @param $mode 模式
     */
    protected function loader( $drive, $mode )
    {
        if( loader::include_file( driver\directory . $drive . '.php' ) && class_exists( 'SF\driver\\' . $drive ) )
        {
            $this->record[ $drive ] = $mode;
        }else{
            \SF::report( 'Driver Failed To Load [ ' . $drive . ' ]' );
        }
    }

    /**
     * 绑定驱动器程序
     * @param string $drive 驱动器名
     * @param string $handler 程序名
     * @return bool 返回绑定状态
     */
    public function bind( $drive, $handler )
    {
        //查询驱动列表
        if( isset( $this->record[ $drive ] ) )
        {
            //驱动模式
            $mode = $this->record[ $drive ];

            //驱动器实例
            $drive_name = 'SF\driver\\' . $drive;

            switch ( $mode )
            {
                //被动模式
                case 0 :
                    //驱动器是否已经绑定
                    if( isset( $this->handler[ $drive ] ) )
                    {
                        \SF::report( 'Driver Has Been Bound Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //是否发现处理程序
                    if( ! class_exists( $handler ) && ( is_subclass_of( $drive, $handler ) ) )
                    {
                        \SF::report( 'Driver Not Found Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //初始化驱动器
                    $this->storage[ $drive ] = new $handler();

                    //记录驱动器初始化
                    $this->handler[ $drive ] = $handler;

                    return true;

                    break;

                //主动模式
                case 1 :
                    //驱动器是否已经绑定
                    if( isset( $this->handler[ $drive ] ) )
                    {
                        \SF::report( 'Driver Has Been Bound Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //是否发现处理程序
                    if( ! class_exists( $handler ) )
                    {
                        \SF::report( 'Driver Not Found Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //初始化驱动器
                    $this->storage[ $drive ] = new $drive_name( $handler );

                    //记录驱动器初始化
                    $this->handler[ $drive ] = $handler;

                    return true;

                    break;

                //主动多处理模式
                case 2 :
                    //驱动器是否已经绑定
                    if( isset( $this->handler[ $drive ] ) )
                    {
                        \SF::report( 'Driver Has Been Bound Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //初始化驱动器
                    $this->storage[ $drive ] = new $drive_name( $handler );

                    //记录驱动器初始化
                    $this->handler[ $drive ] = $handler;

                    return true;

                    break;

                //被动多处理模式
                case 3 :
                    //是否发现处理程序
                    if( ! class_exists( $handler ) )
                    {
                        \SF::report( 'Driver Not Found Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //实例化驱动器
                    if( ! isset( $this->storage[ $drive ] ) )
                    {
                        $this->storage[ $drive ] = new $drive_name();

                        //初始化记录
                        $this->handler[ $drive ] = array();
                    }

                    //实例化程序
                    $handler_name = basename( str_replace( '\\', '/', $handler ) );
                    $this->storage[ $drive ]->$handler_name = new $handler();

                    //记录驱动器初始化
                    $this->handler[ $drive ][] = $handler;

                    return true;

                    break;

                //虚拟驱动
                case 4 :
                    //驱动器是否已经绑定
                    if( isset( $this->handler[ $drive ] ) )
                    {
                        \SF::report( 'Driver Has Been Bound Handler [ ' . $drive . '/' . $handler . ' ]' );
                        return false;
                    }

                    //实例化程序
                    $this->storage[ $drive ] = new $drive_name();

                    //记录驱动器初始化
                    $this->handler[ $drive ] = $drive_name;

                    return true;

                    break;
            }
        }else{
            \SF::report( 'Not Found Driver [ ' . $drive . ' ]' );
        }

        return false;
    }

    /**
     * @return array 返回已装载处理程序列表
     */
    public function handler()
    {
        return $this->handler;
    }

    /**
     * @return array 返回驱动器记录列表
     */
    public function record()
    {
        return $this->record;
    }

    /**
     * 任务队列
     * @param string $name 驱动名
     * @param string $module 执行模块
     */
    public function queue( $name, $module )
    {
        $this->execute[ $name ] = $module;
    }

    /**
     * SF框架注销执行任务
     */
    public function execute()
    {
        if( ! empty( $this->execute ) )
        {
            foreach( $this->execute as $name => $module )
            {
                //执行任务
                $this->storage[ $name ]->$module();
            }
        }
    }

    /**
     * 驱动日志
     * @param string $name 驱动名
     * @param string $content 日志信息
     */
    public static function log( $name, $content )
    {
        log::write( 'driver', array( $name => $content ) );
    }

    /**
     * 返回驱动器
     * @param $name 驱动名
     * @return mixed|null 返回驱动器或者null
     */
    public function __get( $name )
    {
        return isset( $this->storage[ $name ] ) ? $this->storage[ $name ] : null;
    }
}
