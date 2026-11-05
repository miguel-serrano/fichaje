<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Holiday\Domain\Entity\HolidayRequest;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Entity\User;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class IndexHolidaysAdminController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(): View
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());
        $userId = $authenticatedUser->id()->value();

        $pendingResponse = $this->queryBus->dispatch(
            GetPendingHolidaysQuery::create($userId)
        );

        $approvedResponse = $this->queryBus->dispatch(
            GetApprovedHolidaysQuery::create($userId)
        );

        $pendingHolidays = $pendingResponse->holidays();
        $approvedHolidays = $approvedResponse->holidays();

        $usersById = $this->userRepository->findByIds(array_map(
            fn (HolidayRequest $holiday): UserId => $holiday->userId(),
            [...$pendingHolidays, ...$approvedHolidays],
        ));

        return view('admin.holidays.index', [
            'pendingWithUsers' => $this->withUsers($pendingHolidays, $usersById),
            'approvedWithUsers' => $this->withUsers($approvedHolidays, $usersById),
        ]);
    }

    /**
     * @param HolidayRequest[] $holidays
     * @param array<int, User> $usersById
     *
     * @return array<int, array{holiday: HolidayRequest, user: ?User}>
     */
    private function withUsers(array $holidays, array $usersById): array
    {
        return array_map(
            fn (HolidayRequest $holiday): array => [
                'holiday' => $holiday,
                'user' => $usersById[$holiday->userId()->value()] ?? null,
            ],
            $holidays,
        );
    }
}
