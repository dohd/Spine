<?php
/*
 * Rose Business Suite - Accounting, CRM and POS Software
 * Copyright (c) UltimateKode.com. All Rights Reserved
 * ***********************************************************************
 *
 *  Email: support@ultimatekode.com
 *  Website: https://www.ultimatekode.com
 *
 *  ************************************************************************
 *  * This software is furnished under a license and may be used and copied
 *  * only  in  accordance  with  the  terms  of such  license and with the
 *  * inclusion of the above copyright notice.
 *  * If you Purchased from Codecanyon, Please read the full License from
 *  * here- http://codecanyon.net/licenses/standard/
 * ***********************************************************************
 */
namespace App\Http\Controllers\Focus\term;

use App\Http\Requests\Focus\general\ManageCompanyRequest;
use App\Models\term\Term;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Http\Responses\Focus\term\CreateResponse;
use App\Http\Responses\Focus\term\EditResponse;
use App\Repositories\Focus\term\TermRepository;

/**
 * TermsController
 */
class TermsController extends Controller
{
    /**
     * variable to store the repository object
     * @var TermRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param TermRepository $repository ;
     */
    public function __construct(TermRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\term\ManageTermRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(ManageCompanyRequest $request)
    {
        return new ViewResponse('focus.terms.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateTermRequestNamespace $request
     * @return \App\Http\Responses\Focus\term\CreateResponse
     */
    public function create(ManageCompanyRequest $request)
    {
        return new CreateResponse('focus.terms.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreTermRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(ManageCompanyRequest $request)
    {
        $input = $request->except(['_token', 'ins']);
        try {
            $this->repository->create($input);
        } catch (\Throwable $th) {
            return errorHandler('Error Creating Terms', $th);
        }
        return new RedirectResponse(route('biller.terms.index'), ['flash_success' => trans('alerts.backend.terms.created')]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\term\Term $term
     * @param EditTermRequestNamespace $request
     * @return \App\Http\Responses\Focus\term\EditResponse
     */
    public function edit(Term $term)
    {
        return new EditResponse($term);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param UpdateTermRequestNamespace $request
     * @param App\Models\term\Term $term
     * @return \App\Http\Responses\RedirectResponse
     */
    public function update(ManageCompanyRequest $request, Term $term)
    {
        $input = $request->except(['_token', 'ins']);
        try {
            $this->repository->update($term, $input);
        } catch (\Throwable $th) {
            return errorHandler('Error Updating Terms', $th);
        }
        return new RedirectResponse(route('biller.terms.index'), ['flash_success' => trans('alerts.backend.terms.updated')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTermRequestNamespace $request
     * @param App\Models\term\Term $term
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(Term $term, ManageCompanyRequest $request)
    {
        try {
            $this->repository->delete($term);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Terms', $th);
        }
        return new RedirectResponse(route('biller.terms.index'), ['flash_success' => trans('alerts.backend.terms.deleted')]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteTermRequestNamespace $request
     * @param App\Models\term\Term $term
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(Term $term, ManageCompanyRequest $request)
    {
        switch ($term->type) {
            case 1 :
                $term->type = trans('invoices.invoices');
                break;
            case 2 :
                $term->type = trans('quotes.quotes');
                break;
            case 3:
                $term->type = trans('orders.general_bills');
                break;
            default :
                $term->type = trans('general.all');
        }

        //returning with successfull message
        return new ViewResponse('focus.terms.view', compact('term'));
    }

}
