<?php

namespace Tests\Unit;
use Tests\TestCase;
use App\Modules\Notification\NotificationDispatcher;
use App\Modules\Notification\NotifierInterface;

class NotificationDispatcherTest extends TestCase
{
    /** @test */
    public function it_dispatches_to_all_registered_notifiers()
    {
        $dispatcher = new NotificationDispatcher();

        $calls = [];
        $notifierA = new class implements NotifierInterface {
            public array $calls = [];
            public function notify($user, string $title, string $message, array $data = []): void
            { $this->calls[] = compact('user','title','message','data'); }
        };
        $notifierB = new class implements NotifierInterface {
            public array $calls = [];
            public function notify($user, string $title, string $message, array $data = []): void
            { $this->calls[] = compact('user','title','message','data'); }
        };

        $dispatcher->register($notifierA);
        $dispatcher->register($notifierB);

        $dispatcher->dispatch((object)['id' => 1], 'Hello', 'World', ['x' => 1]);

        $this->assertCount(1, $notifierA->calls);
        $this->assertCount(1, $notifierB->calls);
    }
}
