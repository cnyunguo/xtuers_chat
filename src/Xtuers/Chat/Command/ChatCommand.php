<?php
namespace Xtuers\Chat\Command;

use Config;
use DateTime;
use DB;
use Illuminate\Console\Command;
use Message;
use Ratchet\Http\HttpServer;
use Ratchet\Server\IoServer;
use Ratchet\WebSocket\WsServer;
use Symfony\Component\Console\Input\InputOption;
use Xtuers\Chat\ClientInterface;
use Xtuers\Chat\ChatInterface;
use Xtuers\Chat\Exception\UnknownMessageTypeException;
use Xtuers\Chat\Request\AccountRequest;
use Xtuers\Chat\Request\ErrorRequest;
use Xtuers\Chat\Request\InfoRequest;
use Xtuers\Chat\Request\MessageRequest;
use Xtuers\Chat\Request\OauthRequest;
use Xtuers\Chat\Request\TypingRequest;
use Xtuers\Chat\Request\UnknownRequest;
use Xtuers\Chat\Request\ViewRequest;

class ChatCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'chat:server';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = '供私信WebSocket连接';

    protected function getClientName(ClientInterface $client)
    {
        $suffix =  " (" . $client->getUser()->uid . ")";

        $name = $client->getUser()->nickname;
        return $name . $suffix;
    }

    /**
     * @var ChatInterface
     */
    protected $chat;

    public function __construct(ChatInterface $chat)
    {
        parent::__construct();

        $this->chat = $chat;

        $this->chat->getEmitter()->on("open", function(ClientInterface $client)
        {
            $this->line("<info> new client connected.</info>, total:<info>" . $this->chat->getClients()->count()."</info>" );
        });

        $this->chat->getEmitter()->on("close", function(ClientInterface $client)
        {
            $name = $this->getClientName($client);
            $this->line("<info>" . $name . " disconnected.</info>");
        });

        /**
         * 未知的请求类型
         */
        $this->chat->getEmitter()->on('unknown', function(ClientInterface $client, UnknownRequest $request){
            $this->error('未知的消息类型:'.$request->getType());
            $client->getSocket()->send($request->toJson());
        });
        /**
         * 用户向其他用户发送消息
         */
        $this->chat->getEmitter()->on("message", function(ClientInterface $client, MessageRequest $request)
        {
            $name = $this->getClientName($client);
            $this->line("<info>New message from " . $name . ":</info> to {$request->getTo()} <comment>" . $request->getContent() . "</comment><info>.</info>");
            $user = $client->getUser();
            if(!$user){
                $this->error('未认证用户的非法请求,断开连接');
                $client->getSocket()->close();
                return;
            }
            $poster = $client->getUser();
            $receiverClient = $this->chat->findClientByUid($request->getTo());

            echo $client->getUser()->uid .'  '.$request->getTo()."\n";
            $session = $request->getSessionId() ? \ChatSession::find($request->getSessionId())
                : \ChatSession::findOrCreateSessionByUser($client->getUser()->uid, $request->getTo());
            if(!$session){
                $errorMsg = new ErrorRequest(404, '会话不存在');
                $client->getSocket()->send($errorMsg->toJson());
                return;
            }
            $session->updated_at = new DateTime;
            $session->save();
            if(!$request->getContent()){
                $errorMsg = new ErrorRequest(400, '内容不可为空');
                $client->getSocket()->send($errorMsg->toJson());
                return;
            }
            $msg = new \Message();
            $msg->from = $poster->uid;
            $msg->to = $request->getTo();
            $msg->session()->associate($session);
            $msg->content = $request->getContent();
            if($receiverClient){
                $msg->viewed_at = new \DateTime();
            }
            if($msg->save()){
                $request->setData($msg);
                if($receiverClient){
                    $this->line('<info>发送消息给'.$this->getClientName($receiverClient).'</info>');
                    $receiverClient->getSocket()->send($request->toJson());
                }
                $client->getSocket()->send($request->toJson());
                $this->line('<info>保存消息成功</info>');
            }
        });
        /**
         * 用户在输入
         */
        $this->chat->getEmitter()->on('typing', function(ClientInterface $client, TypingRequest $request){
            $targetClient = $this->chat->findClientByUid($request->getTo());
            if(!$client){
                $this->error('用户正在输入,目标用户未连接');
                return;
            }
            $targetClient->getSocket()->send($request->toJson());
        });

        /**
         * 用户查看了会话
         */
        $this->chat->getEmitter()->on('view', function(ClientInterface $client, ViewRequest $request){
            $user = $client->getUser();
            $result = Message::whereSessionId($request->getSessionId())->whereTo($user->uid)->whereNull('viewed_at')->update(
                array(
                    'viewed_at'=>new \DateTime()
                ));
            $this->info('用户查看了'.$result.'条未读消息');
        });

        /**
         * 用户进行身份认证
         */
        $this->chat->getEmitter()->on('oauth', function(ClientInterface $client, OauthRequest $message){
            $user = $this->chat->getDriver()->validate($message->getToken());
            if(!$user){
                $this->error('认证失败,断开连接');
                $client->getSocket()->close();
                return;
            }
            if($exists_client = $this->chat->findClientByUid($user->uid)){
                $infoMsg = new InfoRequest(200, '您已打开新会话，之前的会话自动关闭');
                $client->getSocket()->send($infoMsg->toJson());
                $exists_client->getSocket()->close();
            }
            $client->setUser($user);
            $accountMessage = new AccountRequest();
            $accountMessage->setAccount($user);
            $client->getSocket()->send($accountMessage->toJson());
            $this->line('<info>'.$this->getClientName($client).'</info>'.' auth 认证成功' );
        });

        $this->chat->getEmitter()->on("error", function(ClientInterface $client, \Exception $exception)
        {
//            var_dump($exception);
            $this->line("<info>User encountered an exception:</info> <comment>" . $exception->getMessage() . "</comment><info>.</info>");
        });
    }

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function fire()
	{
        DB::disableQueryLog();
        $port = (integer) $this->option("port");

        if (!$port)
        {
            $port = Config::get('chat::port');
        }

        $server = IoServer::factory(
            new HttpServer(
                new WsServer(
                    $this->chat
                )
            ),
            $port
        );

        $this->line("<info>Listening on port</info> <comment>" . $port . "</comment><info>.</info>");

        $server->run();
	}

    protected function getOptions()
    {
        return [
            ["port", null, InputOption::VALUE_REQUIRED, "Port to listen on.", null]
        ];
    }

}
