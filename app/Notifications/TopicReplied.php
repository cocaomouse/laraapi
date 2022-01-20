<?php

namespace App\Notifications;

use App\Models\Reply;
use App\Notifications\Channels\JPushChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use JPush\PushPayload;

class TopicReplied extends Notification implements ShouldQueue
{
    use Queueable;

    public $reply;

    /**
     * Create a new notification instance.
     *
     * @return void
     */
    public function __construct(Reply $reply)
    {
        // 注入回复实体，方便 toDatabase 方法中的使用
        $this->reply = $reply;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function via($notifiable)
    {
        // 开启通知的频道
        return ['database',JPushChannel::class];
        //return ['database','mail'];
    }

    /**
     * @param $notifiable
     * @return array
     * 接收 $notifiable 实例参数并返回一个普通的 PHP 数组。
     * 这个返回的数组将被转成 JSON 格式并存储到通知数据表的 data 字段中。
     */
    public function toDatabase($notifiable)
    {
        $topic = $this->reply->topic;
        $link = $topic->link(['#reply' . $this->reply->id]);

        //存入数据库表单data字段中的数组
        return [
            'reply_id' => $this->reply->id,
            'reply_content'=> $this->reply->content,
            'user_id' => $this->reply->user->id,
            'user_name' => $this->reply->user->name,
            'user_avatar' => $this->reply->user->avatar,
            'topic_link' => $link,
            'topic_id' => $topic->id,
            'topic_title' => $topic->title,
        ];
    }

    /**
     * Get the mail representation of the notification.
     *
     * @param mixed $notifiable
     * @return \Illuminate\Notifications\Messages\MailMessage
     */
    public function toMail($notifiable)
    {
        $url = $this->reply->topic->link(['#reply'.$this->reply->id]);

        return (new MailMessage)
            ->subject('测试邮件通知') //邮件通知的标题
            ->greeting('Hello!') //邮件开头招呼
            ->salutation('你好!') //邮件结尾招呼
            ->line('你的话题有新回复！') //邮件内容
            ->action('查看回复', $url); //客户跳转的动作
    }

    /**
     * Get the array representation of the notification.
     *
     * @param mixed $notifiable
     * @return array
     */
    public function toArray($notifiable)
    {
        return [
            //
        ];
    }

    public function toJPush($notifiable,PushPayload $payload):PushPayload
    {
        return $payload
            ->setPlatform('all')
            ->addRegistrationId($notifiable->registration_id)
            ->setNotificationAlert(strip_tags($this->reply->content));
    }
}
