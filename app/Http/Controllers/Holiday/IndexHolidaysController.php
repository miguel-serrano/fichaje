<?php

declare(strict_types=1);

namespace App\Http\Controllers\Holiday;

use App\DDD\Authentication\Application\Query\GetAuthenticatedUserQuery;
use App\DDD\Authorization\Application\Service\AuthorizationServiceInterface;
use App\DDD\Holiday\Application\Query\GetUserHolidaysQuery;
use App\DDD\Holiday\Domain\Permission\HolidayPermission;
use App\DDD\Shared\Domain\Bus\QueryBusInterface;
use App\Http\Controllers\Controller;
use Illuminate\View\View;

class IndexHolidaysController extends Controller
{
    public function __construct(
        private QueryBusInterface $queryBus,
        private AuthorizationServiceInterface $authorizationService,
    ) {
    }

    public function __invoke(): View
    {
        $authenticatedUser = $this->queryBus->dispatch(new GetAuthenticatedUserQuery());

        $userId = $authenticatedUser->id()->value();

        $holidaysResponse = $this->queryBus->dispatch(
            GetUserHolidaysQuery::create($userId)
        );

        $canRequestHoliday = $this->authorizationService->isGranted(HolidayPermission::Request->value, $userId);

        return view('holidays.index', [
            'holidays' => $holidaysResponse->holidays(),
            'canRequestHoliday' => $canRequestHoliday,
        ]);
    }
}
