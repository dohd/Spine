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

namespace App\Http\Controllers\Focus\client_vendor_ticket;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Responses\RedirectResponse;
use App\Http\Responses\ViewResponse;
use App\Models\client_vendor_ticket\ClientVendorReply;
use App\Models\client_vendor_ticket\ClientVendorTicket;
use App\Repositories\Focus\client_vendor_ticket\ClientVendorTicketRepository;

class ClientVendorTicketsController extends Controller
{
    /**
     * variable to store the repository object
     * @var ProductcategoryRepository
     */
    protected $repository;

    /**
     * contructor to initialize repository object
     * @param ProductcategoryRepository $repository ;
     */
    public function __construct(ClientVendorTicketRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Display a listing of the resource.
     *
     * @param App\Http\Requests\Focus\productcategory\ManageProductcategoryRequest $request
     * @return \App\Http\Responses\ViewResponse
     */
    public function index(Request $request)
    {
        return new ViewResponse('focus.client_vendor_tickets.index');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @param CreateProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\CreateResponse
     */
    public function create()
    {   
        return view('focus.client_vendor_tickets.create');
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param StoreProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\RedirectResponse
     */
    public function store(Request $request)
    {
        $request->validate([
            'category_id' => 'required',
            'priority' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $this->repository->create($request->except(['_token']));
        } catch (\Throwable $th) { 
            return errorHandler('Error Creating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Created']);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param App\Models\productcategory\Productcategory $productcategory
     * @param EditProductcategoryRequestNamespace $request
     * @return \App\Http\Responses\Focus\productcategory\EditResponse
     */
    public function edit(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        return view('focus.client_vendor_tickets.edit', compact('client_vendor_ticket'));
    }

    /**
     * Update Resource in Storage
     * 
     */
    public function update(Request $request, ClientVendorTicket $client_vendor_ticket)
    {
        $request->validate([
            'category_id' => 'required',
            'priority' => 'required',
            'subject' => 'required',
            'message' => 'required',
        ]);

        try {
            $this->repository->update($client_vendor_ticket, $request->except(['_token']));
        } catch (\Throwable $th) { dd($th);
            return errorHandler('Error Updating Ticket!', $th);
        }
        
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Updated']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function destroy(ClientVendorTicket $client_vendor_ticket)
    {
        try {
            $this->repository->delete($client_vendor_ticket);
        } catch (\Throwable $th) {
            return errorHandler('Error Deleting Ticket!', $th);
        }

        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Deleted']);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param DeleteProductcategoryRequestNamespace $request
     * @param App\Models\productcategory\Productcategory $productcategory
     * @return \App\Http\Responses\RedirectResponse
     */
    public function show(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        return new ViewResponse('focus.client_vendor_tickets.view', compact('client_vendor_ticket'));
    }

    /**
     * Update Ticket Status
     * 
     */
    public function status(ClientVendorTicket $client_vendor_ticket, Request $request)
    {
        try {
            $client_vendor_ticket->update(['status' => 'Closed', 'closed_at' => now()]);
        } catch (\Throwable $th) {
            return errorHandler('', $th);
        }
        return new RedirectResponse(route('biller.client_vendor_tickets.index'), ['flash_success' => 'Ticket  Successfully Closed']);
    }

    /**
     * Ticket Reply
     * 
     */
    public function reply(Request $request)
    {
        $request->validate(['message' => 'required']);
        try {
            $input = $request->only('tenant_ticket_id', 'message');
            $client_vendor_reply = ClientVendorReply::create($input);
            if ($client_vendor_reply->ticket) {
                $client_vendor_reply->ticket->update(['status' => 'Open', 'closed_at' => null]);
            }
        } catch (\Throwable $th) {
            return errorHandler('', $th);
        }
        
        return redirect()->back();
    }
}
