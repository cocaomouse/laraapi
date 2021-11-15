<?php

namespace App\Models\Traits;

use App\Models\Topic;
use App\Models\Reply;
use App\Models\User;
use Carbon\Carbon;
use Cache;
use DB;
use Arr;
use Spatie\Permission\Models\Permission;

trait ActiveUserHelper
{
    // 用于存放临时用户数据
    protected $users = [];

    // 配置信息
    protected $topic_weight = 4; //话题权重
    protected $reply_weight = 1; //回复权重
    protected $pass_days = 7; //多少天内发表过内容
    protected $user_number = 6; //取出来多少用户

    //缓存相关配置
    protected $cache_key = 'larabbs_active_users';
    protected $cache_expire_in_seconds = 65 * 60;

    public function getActiveUsers()
    {
        // 尝试从缓存中取出 cache_key 对应的数据。如果能取到，便直接返回数据。
        // 否则运行匿名函数中的代码来取出活跃用户数据，返回的同时做了缓存。
        return Cache::remember($this->cache_key, $this->cache_expire_in_seconds, function () {
            return $this->calculateActiveUsers();
        });
    }

    public function calculateAndCacheActiveUsers()
    {
        // 取得活跃用户列表
        $active_users = $this->calculateActiveUsers();
        // 并加以缓存
        $this->cacheActiveUsers($active_users);
    }

    public function calculateActiveUsers()
    {
        $this->calculateTopicScore();
        $this->calculateReplyScore();

        if (!$this->users) {
            return collect([]);
        }

        // 数组按照得分排序
        // 只获取我们想要的数量
        return collect($this->users)->sortByDesc(function ($user) {
            return $user['score'];
        })->slice(0,$this->user_number)->map(function ($item) {
            return (object)$item; //将数组转换成对象，前端模版无需修改
        });

    }

    private function calculateTopicScore()
    {
        // 从话题数据表里取出限定时间范围（$pass_days）内，有发表过话题的用户
        // 并且同时取出用户此段时间内发布话题的数量
        $topic_users = User::withCount(['topics' => function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays($this->pass_days));
        }])
            ->orderBy('topics_count', 'desc')
            ->get();

        // 根据话题数量计算得分
        if ($topic_users->isNotEmpty()) {
            $topic_users->map(function ($topic_user) {
                $topic_score = $topic_user->topic_count * $this->topic_weight;
                if (isset($this->user[$topic_user->id])) {
                    $this->users[$topic_user->id]['score'] += $topic_score;
                } else {
                    $this->users[$topic_user->id]['score'] = $topic_score;
                }
                $this->users[$topic_user->id]['avatar'] = $topic_user->avatar;
                $this->users[$topic_user->id]['name'] = $topic_user->name;
                $this->users[$topic_user->id]['id'] = $topic_user->id;
                $this->users[$topic_user->id]['phone'] = $topic_user->phone;
                $this->users[$topic_user->id]['email'] = $topic_user->email;
                $this->users[$topic_user->id]['weixin_openid'] = $topic_user->weixin_openid;
                $this->users[$topic_user->id]['weixin_unionid'] = $topic_user->weixin_unionid;
                return $this->users;
            });
        }

    }

    private function calculateReplyScore()
    {
        // 从回复数据表里取出限定时间范围（$pass_days）内，有发表过回复的用户
        // 并且同时取出用户此段时间内发布回复的数量
        $reply_users = User::withCount(['replies' => function ($query) {
            $query->where('created_at', '>=', Carbon::now()->subDays($this->pass_days));
        }])
            ->orderBy('replies_count', 'desc')
            ->get();

        // 根据回复数量计算得分
        if ($reply_users->isNotEmpty()) {
            $reply_users->map(function ($reply_user) {
                $reply_score = $reply_user->replies_count * $this->reply_weight;
                if (isset($this->users[$reply_user->id])) {
                    $this->users[$reply_user->id]['score'] += $reply_score;
                } else {
                    $this->users[$reply_user->id]['score'] = $reply_score;
                }
                $this->users[$reply_user->id]['avatar'] = $reply_user->avatar;
                $this->users[$reply_user->id]['name'] = $reply_user->name;
                $this->users[$reply_user->id]['id'] = $reply_user->id;
                $this->users[$reply_user->id]['phone'] = $reply_user->phone;
                $this->users[$reply_user->id]['email'] = $reply_user->email;
                $this->users[$reply_user->id]['weixin_openid'] = $reply_user->weixin_openid;
                $this->users[$reply_user->id]['weixin_unionid'] = $reply_user->weixin_unionid;
                return $this->users;
            });
        }
    }

    private function cacheActiveUsers($active_users)
    {
        // 将数据放入缓存中
        Cache::put($this->cache_key, $active_users, $this->cache_expire_in_seconds);
    }
}
