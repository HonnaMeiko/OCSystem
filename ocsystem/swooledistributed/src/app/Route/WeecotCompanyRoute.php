<?php
/**
 * Created by PhpStorm.
 * 唯物链企业端路由
 * User: suzhixiang
 * Date: 16-7-15
 * Time: 下午3:11
 */

namespace app\Route;

use Server\Route\IRoute;
use Server\CoreBase\SwooleException;

class WeecotCompanyRoute implements IRoute
{
    /**
     * 客户端
     * @var \stdClass
     */
    private $client_data;

    /**
     * 属于哪个端
     * @var string
     */
    private $direction = 'Company';//企业端
    public function __construct()
    {
        $this->client_data = new \stdClass();
    }

    /**
     * 设置反序列化后的数据 Object
     * @param $data
     * @return \stdClass
     * @throws SwooleException
     */
    public function handleClientData($data)
    {
        $this->client_data = $data;
        if (isset($this->client_data->controller_name) && isset($this->client_data->method_name)) {
            return $this->client_data;
        } else {
            throw new SwooleException('route 数据缺少必要字段');
        }

    }

    /**
     * 处理http request
     * @param $request
     */
    public function handleClientRequest($request)
    {
        $this->client_data->path = $request->server['path_info'];
        $route = explode('/', $request->server['path_info']);
        $count = count($route);
        if ($count == 3) {
            $this->client_data->controller_name = $route[$count - 1] ?? 'AntiFake';
            $this->client_data->method_name = null;
            return;
        }
        $this->client_data->method_name = $route[3] ?? 'loginCompany';
        $this->client_data->version = $route[1] ?? 'v_1_0_1';
        unset($route[0]);
        $this->client_data->controller_name = $route[2] ?? 'Index';
    }

    /**
     * 获取版本名称
     * @return string
     */
    public function getVersionName()
    {
        return $this->client_data->version;
    }

    /**
     * 获取项目端接口
     * @return string
     */
    public function getDirectionName()
    {
        return $this->client_data->direction;
    }

    /**
     * 获取控制器名称
     * @return string
     */
    public function getControllerName()
    {
        return $this->direction.'\\'.$this->getVersionName().'\\'.$this->client_data->controller_name.'Controller';
    }

    /**
     * 获取方法名称
     * @return string
     */
    public function getMethodName()
    {
        return $this->client_data->method_name;
    }

    public function getPath()
    {
        return $this->client_data->path ?? "";
    }

    public function getParams()
    {
        return $this->client_data->params??null;
    }

    public function errorHandle(\Throwable $e, $fd)
    {
        get_instance()->send($fd, "Error:" . $e->getMessage(), true);
        get_instance()->close($fd);
    }

    public function errorHttpHandle(\Throwable $e, $request, $response)
    {
        //重定向到404
        $response->status(302);
        $location = 'http://' . $request->header['host'] . "/" . '404';
        $response->header('Location', $location);
        $response->end('');
    }
}