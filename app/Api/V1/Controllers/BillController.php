<?php
/**
 * BillController.php
 * Copyright (c) 2018 thegrumpydictator@gmail.com
 *
 * This file is part of Firefly III.
 *
 * Firefly III is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * Firefly III is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with Firefly III. If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);

namespace FireflyIII\Api\V1\Controllers;

use Auth;
use Carbon\Carbon;
use FireflyIII\Models\Bill;
use FireflyIII\Repositories\Bill\BillRepositoryInterface;
use FireflyIII\Transformers\BillTransformer;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use League\Fractal\Manager;
use League\Fractal\Pagination\IlluminatePaginatorAdapter;
use League\Fractal\Resource\Collection as FractalCollection;
use League\Fractal\Resource\Item;
use League\Fractal\Serializer\JsonApiSerializer;
use Preferences;
use Response;

/**
 * Class BillController
 */
class BillController extends Controller
{
    /** @var BillRepositoryInterface */
    private $repository;

    /**
     * BillController constructor.
     *
     * @throws \FireflyIII\Exceptions\FireflyException
     */
    public function __construct()
    {
        parent::__construct();
        $this->middleware(
            function ($request, $next) {
                /** @var BillRepositoryInterface repository */
                $this->repository = app(BillRepositoryInterface::class);
                $this->repository->setUser(auth()->user());

                return $next($request);
            }
        );
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \FireflyIII\Models\Bill $bill
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Bill $bill)
    {
        $this->repository->destroy($bill);
        return response()->json(null, 204);
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index(Request $request)
    {
        $user     = Auth::guard('api')->user();
        $pageSize = intval(Preferences::getForUser($user, 'listPageSize', 50)->data);
        $start    = null;
        $end      = null;
        if (null !== $request->get('start')) {
            $start = new Carbon($request->get('start'));
        }
        if (null !== $request->get('end')) {
            $end = new Carbon($request->get('end'));
        }
        $paginator = $this->repository->getPaginator($pageSize);
        /** @var Collection $bills */
        $bills = $paginator->getCollection();

        $manager = new Manager();
        $baseUrl = $request->getSchemeAndHttpHost() . '/api/v1';
        $manager->setSerializer(new JsonApiSerializer($baseUrl));

        $resource = new FractalCollection($bills, new BillTransformer($start, $end), 'bills');
        $resource->setPaginator(new IlluminatePaginatorAdapter($paginator));

        return Response::json($manager->createData($resource)->toArray());
    }


    /**
     * @param Request $request
     * @param Bill    $bill
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(Request $request, Bill $bill)
    {
        $start = null;
        $end   = null;
        if (null !== $request->get('start')) {
            $start = new Carbon($request->get('start'));
        }
        if (null !== $request->get('end')) {
            $end = new Carbon($request->get('end'));
        }


        $manager = new Manager();
        $manager->parseIncludes(['attachments']);
        $baseUrl = $request->getSchemeAndHttpHost() . '/api/v1';
        $manager->setSerializer(new JsonApiSerializer($baseUrl));

        $resource = new Item($bill, new BillTransformer($start, $end), 'bill');

        return Response::json($manager->createData($resource)->toArray());
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request $request
     * @param  \FireflyIII\Models\Bill  $bill
     *
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Bill $bill)
    {
        //
    }
}
