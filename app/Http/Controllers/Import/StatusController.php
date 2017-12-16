<?php
declare(strict_types=1);

namespace FireflyIII\Http\Controllers\Import;


use FireflyIII\Http\Controllers\Controller;
use FireflyIII\Models\ImportJob;
use FireflyIII\Repositories\Tag\TagRepositoryInterface;
use Response;

/**
 * Class StatusController
 */
class StatusController extends Controller
{

    /**
     *
     */
    public function __construct()
    {
        parent::__construct();

        $this->middleware(
            function ($request, $next) {
                app('view')->share('mainTitleIcon', 'fa-archive');
                app('view')->share('title', trans('firefly.import_index_title'));

                return $next($request);
            }
        );
    }

    /**
     * @param ImportJob $job
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\Http\RedirectResponse|\Illuminate\Routing\Redirector|\Illuminate\View\View
     */
    public function index(ImportJob $job)
    {
        $statuses = ['configured', 'running', 'finished', 'errored'];
        if (!in_array($job->status, $statuses)) {
            return redirect(route('import.file.configure', [$job->key]));
        }
        $subTitle     = trans('firefly.import_status_sub_title');
        $subTitleIcon = 'fa-star';

        return view('import.status', compact('job', 'subTitle', 'subTitleIcon'));
    }

    /**
     * Show status of import job in JSON.
     *
     * @param ImportJob $job
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function json(ImportJob $job)
    {
        $result = [
            'started'         => false,
            'finished'        => false,
            'running'         => false,
            'errors'          => array_values($job->extended_status['errors']),
            'percentage'      => 0,
            'show_percentage' => false,
            'steps'           => $job->extended_status['steps'],
            'done'            => $job->extended_status['done'],
            'statusText'      => trans('firefly.import_status_job_' . $job->status),
            'status'          => $job->status,
            'finishedText'    => '',
        ];

        if (0 !== $job->extended_status['steps']) {
            $result['percentage']      = round(($job->extended_status['done'] / $job->extended_status['steps']) * 100, 0);
            $result['show_percentage'] = true;
        }

        if ('finished' === $job->status) {
            $tagId = $job->extended_status['tag'];
            /** @var TagRepositoryInterface $repository */
            $repository             = app(TagRepositoryInterface::class);
            $tag                    = $repository->find($tagId);
            $result['finished']     = true;
            $result['finishedText'] = trans('firefly.import_status_finished_job', ['link' => route('tags.show', [$tag->id, 'all']), 'tag' => $tag->tag]);
        }

        if ('running' === $job->status) {
            $result['started'] = true;
            $result['running'] = true;
        }

        // TODO cannot handle 'errored'

        return Response::json($result);
    }

}