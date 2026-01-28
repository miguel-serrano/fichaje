<?php

declare(strict_types=1);

namespace App\Http\Controllers\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Domain\Services\PermissionCheckerInterface;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
use App\DDD\User\Domain\ValueObjects\UserId;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class IndexHolidaysController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private PermissionCheckerInterface $permissionChecker,
        private UserRepositoryInterface $userRepository,
    ) {
    }

    public function __invoke(): View
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $userId = $authenticatedUser->id()->value();

        $holidaysResponse = $this->queryBus->dispatch(
            GetUserHolidaysQuery::create($userId)
        );

        $user = $this->userRepository->findByIdOrFail(UserId::make($userId));

        $canRequestHoliday = $this->permissionChecker->hasPermission($user, HolidayPermission::Request->value);

        return view('holidays.index', [
            'holidays' => $holidaysResponse->holidays(),
            'canRequestHoliday' => $canRequestHoliday,
        ]);
    }
}
