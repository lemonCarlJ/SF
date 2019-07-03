<?php
/**
 * view.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF
{
    class view
    {
        /**
         * @var view\SF 引擎集合
         */
        protected $engine;

        /**
         * 视图模块初始化
         */
        public function __construct()
        {
            $this->engine = new view\SF();
        }

        /**
         * 载入视图引擎
         * 视图引擎不可以被重载
         * @param string $name 视图引擎名
         * @param string $entrance 视图入口文件
         * @param string $instance 视图接口名
         * @param string $path 视图路径 [ null ]
         * @return boolean 返回视图是否加载
         */
        public function engine( $name, $entrance, $instance, $path = null )
        {
            if( isset( $this->engine->$name ) )
            {
                \SF::report( 'View Engine Has Been Loaded [ ' . $name . ' ]' );
                return true;
            }

            //视图路径
            if( ! $path )
            {
                $path = view\directory . $name . DIRECTORY_SEPARATOR;
            }

            //入口文件
            $entrance_file = $path . $entrance;

            if( loader::include_file( $entrance_file ) )
            {
                if( class_exists( $instance ) )
                {
                    //接入视图引擎
                    $this->engine->$name = new $instance();
                    return true;
                }else{
                    //查询不到接口
                    \SF::report( 'Not Find View Engine Interface [ ' . $name . '/' . $instance . ' ]' );
                }
            }else{
                //加载失败
                \SF::report( 'View Engine Failed To Load [ ' . $name . ' ]' );
            }

            return false;
        }

        public function __get( $name )
        {
            return $this->engine->$name;
        }
    }
}

namespace SF\view
{
    class SF{}
}