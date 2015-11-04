<?php
    /**
     *
     * User: android
     * Date: 2015/11/3
     * Time: 21:59
     */

    /**
     * 消息列队..如果不列队.导致大量并发..卡死...就像短信接口并发只是10 你突然有20.就卡死了.
     */

    /**
     * 创建memcache  内存服务器
     */
    $memcache = new  Memcache();

    $memcache->addServer('localhost', 11211);


    /**
     *  模拟300个用户
     */



    /**
     *  最头部分, 最尾部分,和下一个节点..
     */
    $memcache->set('header', null);
    $memcache->set('next', null);
    $memcache->set('last', null);

    class iter
    {


        public $next;

        public $content;

        public $uid;


        /**  用链表的形式存储一个列队
         * @param $person 值
         * @param Memcache $memcache
         */
        function put($person, Memcache $memcache)
        {

            //获取一个唯一值做key
            $new = new iter();
            $new->uid = uniqid('put', true);
            $new->content = $person;

            //列队头
            if ($memcache->get('header') == null) {



                $memcache->set($new->uid, $new);

                $memcache->set('header', $new->uid);

                $memcache->set('last', $new->uid);


            }else{
                //获取最尾节点
                $first = $memcache->get('last');
                $value = $memcache->get($first);


                //最尾节点..添加next 节点
                $value->next = $new->uid;

                //设置节点
                $memcache->set($first,$value);


                //添加节点的
                $memcache->set($new->uid, $new);
                //连上节点
                $memcache->set('last', $new->uid);
            }


        }


        /**
         * @return bool 返回一个列队值
         */
        function get(Memcache $memcache)
        {

            $header = $memcache->get('header');
            if($header == null){
                return false;
            }
            $value = $memcache->get($header);



            $memcache->set('header',$value->next);


            return $value->content;
        }


    }


    $iter = new iter();

    for ($i = 0; $i < 5; $i++) {

        /**
         *  列队进入 md5加密 加盐值
         */
        $iter->put(md5($i.'only_To_me'),$memcache);
    }




    //全部列队出来
    while($key = $iter->get($memcache)){
        //列队出来
        echo $key;
        echo '<br/>';
    }



    //进行列队
    for ($i = 0; $i < 10; $i++) {
        /**
         *  列队进入 md5加密 加盐值
         */
        $iter->put($i,$memcache);
    }



    //先出3个列队
    for ($i = 0; $i < 3; $i++) {
       $key = $iter->get($memcache);
            //列队出来
            echo $key;
            echo '<br/>';
    }



    // 在列队5个
    for ($i = 0; $i < 5; $i++) {

        /**
         *  列队进入 md5加密 加盐值
         */
        $iter->put(md5($i.'only_To_me'),$memcache);
    }

    //在全部列队出来
    while($key = $iter->get($memcache)){
        //列队出来
        echo $key;
        echo '<br/>';
    }

























