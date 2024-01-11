<?php

namespace App\Repositories\Focus\client_vendor_ticket;

use App\Models\client_vendor_ticket\ClientVendorTicket;
use App\Repositories\BaseRepository;

class ClientVendorTicketRepository extends BaseRepository
{
    /**
     * Associated Repository Model.
     */
    const MODEL = ClientVendorTicket::class;

    /**
     * This method is used by Table Controller
     * For getting the table data to show in
     * the grid
     * @return mixed
     */
    public function getForDataTable()
    {

        $q = $this->query();

        return $q->get();
    }

    /**
     * For Creating the respective model in storage
     *
     * @param array $input
     * @throws GeneralException
     * @return bool
     */
    public function create(array $input)
    {   
        $ticket = ClientVendorTicket::create($input);
        return $ticket;
    }

    /**
     * For updating the respective Model in storage
     *
     * @param ClientVendorTicket $client_vendor_ticket
     * @param  $input
     * @throws GeneralException
     * return bool
     */
    public function update(ClientVendorTicket $client_vendor_ticket, array $input)
    {   
        return $client_vendor_ticket->update($input);
    }

    /**
     * For deleting the respective model from storage
     *
     * @param ClientVendorTicket $client_vendor_ticket
     * @throws GeneralException
     * @return bool
     */
    public function delete(ClientVendorTicket $client_vendor_ticket)
    {
        return $client_vendor_ticket->delete();
    }
}
