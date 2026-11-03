<?php

declare(strict_types=1);

namespace Tests\Feature\Notification;

use App\DDD\Administration\Domain\Event\RoleUpdatedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestApprovedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestCreatedEvent;
use App\DDD\Holiday\Domain\Event\HolidayRequestRejectedEvent;
use App\DDD\Notification\Infrastructure\Subscriber\SendDomainEventEmailSubscriber;
use App\DDD\Shared\Domain\Event\EventBusInterface;
use App\DDD\TimeTracking\Domain\Event\UserClockedInEvent;
use App\DDD\TimeTracking\Domain\Event\UserClockedOutEvent;
use App\DDD\User\Domain\Entity\User as DomainUser;
use App\DDD\User\Domain\Event\UserActivationToggledEvent;
use App\DDD\User\Domain\Event\UserCreatedEvent;
use App\DDD\User\Domain\Event\UserDeletedEvent;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\Mail\DomainEventMail;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleSeeder;
use Illuminate\Contracts\Mail\Mailer;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Psr\Log\LoggerInterface;
use RuntimeException;
use Tests\TestCase;

class SendDomainEventEmailSubscriberTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(RoleSeeder::class);
        Mail::fake();
    }

    public function test_it_subscribes_to_every_domain_event(): void
    {
        $this->assertEqualsCanonicalizing(
            [
                UserCreatedEvent::eventName(),
                UserDeletedEvent::eventName(),
                UserActivationToggledEvent::eventName(),
                UserClockedInEvent::eventName(),
                UserClockedOutEvent::eventName(),
                HolidayRequestCreatedEvent::eventName(),
                HolidayRequestApprovedEvent::eventName(),
                HolidayRequestRejectedEvent::eventName(),
                RoleUpdatedEvent::eventName(),
            ],
            SendDomainEventEmailSubscriber::subscribedTo(),
        );
    }

    public function test_publishing_an_event_emails_every_super_admin(): void
    {
        $firstSuperAdmin = User::factory()->admin()->create();
        $secondSuperAdmin = User::factory()->admin()->create();
        $employee = User::factory()->create();

        $this->app->make(EventBusInterface::class)->publish(
            new UserClockedInEvent(Str::uuid()->toString(), $employee->id, time()),
        );

        foreach ([$firstSuperAdmin, $secondSuperAdmin] as $superAdmin) {
            Mail::assertQueued(
                DomainEventMail::class,
                fn (DomainEventMail $mail): bool => $mail->hasTo($superAdmin->email)
                    && $mail->eventName === UserClockedInEvent::eventName(),
            );
        }
        Mail::assertNotQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->hasTo($employee->email));
    }

    public function test_admins_without_super_admin_role_do_not_receive_emails(): void
    {
        $superAdmin = User::factory()->admin()->create();
        $admin = User::factory()->create();
        $admin->roles()->syncWithoutDetaching([Role::where('slug', 'admin')->value('id')]);

        $this->app->make(EventBusInterface::class)->publish(
            new UserActivationToggledEvent(Str::uuid()->toString(), $admin->id, true),
        );

        Mail::assertQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->hasTo($superAdmin->email));
        Mail::assertNotQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->hasTo($admin->email));
    }

    public function test_publishing_several_events_sends_one_email_per_event(): void
    {
        $admin = User::factory()->admin()->create();
        $aggregateId = Str::uuid()->toString();

        $this->app->make(EventBusInterface::class)->publish(
            new HolidayRequestCreatedEvent($aggregateId, $admin->id, time(), time() + 86400),
            new HolidayRequestApprovedEvent($aggregateId, $admin->id, time(), time() + 86400),
        );

        $superAdmins = app(UserRepositoryInterface::class)->findByRole('super_admin');
        Mail::assertQueuedCount(2 * count($superAdmins));
        Mail::assertQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->hasTo($admin->email) && $mail->eventName === 'holiday_request.created');
        Mail::assertQueued(DomainEventMail::class, fn (DomainEventMail $mail): bool => $mail->hasTo($admin->email) && $mail->eventName === 'holiday_request.approved');
    }

    public function test_no_email_is_sent_when_there_are_no_admins(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByRole')->with('super_admin')->willReturn([]);
        $this->app->instance(UserRepositoryInterface::class, $userRepository);

        $subscriber = $this->app->make(SendDomainEventEmailSubscriber::class);
        $subscriber(new UserDeletedEvent(Str::uuid()->toString(), 1, 'deleted@test.com'));

        Mail::assertNothingQueued();
        Mail::assertNothingSent();
    }

    public function test_a_mail_failure_is_logged_and_does_not_break_the_flow(): void
    {
        $userRepository = $this->createMock(UserRepositoryInterface::class);
        $userRepository->method('findByRole')->with('super_admin')->willReturn([$this->domainAdmin('admin@test.com')]);

        $mailer = $this->createMock(Mailer::class);
        $mailer->method('to')->willThrowException(new RuntimeException('SMTP caído'));

        $logger = $this->createMock(LoggerInterface::class);
        $logger->expects($this->once())
            ->method('error')
            ->with(
                $this->anything(),
                $this->callback(fn (array $context): bool => $context['event'] === 'user.created'
                    && $context['recipient'] === 'admin@test.com'
                    && $context['exception'] === 'SMTP caído'),
            );

        $subscriber = new SendDomainEventEmailSubscriber($mailer, $userRepository, $logger);
        $subscriber(new UserCreatedEvent(Str::uuid()->toString(), 1, 'nuevo@test.com', 'Nuevo'));
    }

    public function test_mail_renders_event_name_and_primitives(): void
    {
        $event = new RoleUpdatedEvent(Str::uuid()->toString(), 'Administrador', 'admin', ['user.view', 'user.edit']);

        $mail = DomainEventMail::fromDomainEvent($event);

        $mail->assertHasSubject(sprintf('[%s] Evento role.updated', config('app.name')));
        $mail->assertSeeInHtml('role.updated');
        $mail->assertSeeInHtml($event->aggregateId());
        $mail->assertSeeInHtml('Administrador');
        $mail->assertSeeInHtml('["user.view","user.edit"]', false);
    }

    public function test_mail_renders_boolean_and_null_primitives_as_text(): void
    {
        $roleMail = DomainEventMail::fromDomainEvent(
            new RoleUpdatedEvent(Str::uuid()->toString(), 'Empleado', 'employee'),
        );
        $activationMail = DomainEventMail::fromDomainEvent(
            new UserActivationToggledEvent(Str::uuid()->toString(), 7, false),
        );

        $this->assertSame('null', $roleMail->fields['permissions_synced'] ?? null);
        $this->assertSame('false', $activationMail->fields['is_active'] ?? null);
        $this->assertSame('7', $activationMail->fields['user_id'] ?? null);
    }

    private function domainAdmin(string $email): DomainUser
    {
        return DomainUser::fromPrimitives(
            id: 1,
            uuid: Str::uuid()->toString(),
            email: $email,
            name: 'Admin',
            isActive: true,
            roleSlugs: ['super_admin'],
        );
    }
}
