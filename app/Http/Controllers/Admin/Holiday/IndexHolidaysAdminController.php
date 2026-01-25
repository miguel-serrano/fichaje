<?php

declare(strict_types=1);

namespace App\Http\Controllers\Admin\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Holiday\Application\Query\GetApprovedHolidaysQuery;
use App\DDD\Holiday\Application\Query\GetPendingHolidaysQuery;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\DDD\User\Domain\Interface\UserRepositoryInterface;
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

        $pendingWithUsers = [];
        foreach ($pendingResponse->holidays() as $holiday) {
            $user = $this->userRepository->findById($holiday->userId());
            $pendingWithUsers[] = [
                'holiday' => $holiday,
                'user' => $user,
            ];
        }

        $approvedWithUsers = [];
        foreach ($approvedResponse->holidays() as $holiday) {
            $user = $this->userRepository->findById($holiday->userId());
            $approvedWithUsers[] = [
                'holiday' => $holiday,
                'user' => $user,
            ];
        }

        return view('admin.holidays.index', [
            'pendingWithUsers' => $pendingWithUsers,
            'approvedWithUsers' => $approvedWithUsers,
        ]);
    }
}
