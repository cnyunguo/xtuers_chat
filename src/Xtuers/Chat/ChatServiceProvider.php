<?php namespace Xtuers\Chat;

use Evenement\EventEmitter;
use Illuminate\Support\ServiceProvider;
use Xtuers\Chat\Command\ChatCommand;
class ChatServiceProvider extends ServiceProvider {

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = true;

	/**
	 * Bootstrap the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->package('xtuers/chat', 'chat');
	}

	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
        $this->app->bind("chat.emitter", function()
        {
            return new EventEmitter();
        });

        $this->app->bind("chat.chat", function()
        {
            $driver = $this->app['tappleby.auth.token']->driver();
            return new Chat(
                $this->app->make("chat.emitter"),
                $driver
            );
        });

        $this->app->bind("chat.client", function()
        {
            return new Client();
        });

        $this->app->bind("chat.command.server", function()
        {
            return new ChatCommand(
                $this->app->make("chat.chat")
            );
        });

        $this->commands("chat.command.server");
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
        return [
            "chat.chat",
            "chat.command.server",
            "chat.emitter",
            "chat.client",
            "chat.server"
        ];
	}

}
