<?php
/**
 * controller.php
 * @Copyright LemonCarlJ All rights reserved.
 * @License http://www.56code.com/License.txt
 * @Version 1.0.0
 * @Email rpgshifa@aliyun.com
 */
namespace SF;

class controller
{
    /**
     * @var array $record 控制器记录
     */
    protected $record = array();

    /**
     * 载入控制器
     * @param string $name 控制器名
     * @return bool 返回是否载入成功
     */
    public function loader( $name )
    {
        //控制器文件
        $controller_file = controller\directory . $name . '.php';

        if( loader::include_file( $controller_file ) )
        {
            if( class_exists( 'SF\controller\\' . $name ) )
            {
                //记录加载
                $this->record[] = $name;

                return true;
            }else{
                \SF::report( 'Not Found Controller [ ' . $name . ' ]' );
            }
        }else{
            \SF::report( 'Controller Failed To Load [ ' . $name . ' ]' );
        }

        return false;
    }

    /**
     * 判断是否存在控制器
     * @param string $name 控制器名
     * @return bool 返回是否存在
     */
    public function has( $name )
    {
        return in_array( $name, $this->record );
    }

    /**
     * 返回加载记录
     * @param $name
     * @return mixed
     */
    public function __get( $name )
    {
        return $this->$name;
    }
}
