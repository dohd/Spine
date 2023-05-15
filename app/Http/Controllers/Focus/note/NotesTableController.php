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
namespace App\Http\Controllers\Focus\note;

use Illuminate\Http\Request;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Yajra\DataTables\Facades\DataTables;
use App\Repositories\Focus\note\NoteRepository;
use App\Http\Requests\Focus\note\ManageNoteRequest;

/**
 * Class NotesTableController.
 */
class NotesTableController extends Controller
{
    /**
     * variable to store the repository object
     * @var NoteRepository
     */
    protected $note;

    /**
     * contructor to initialize repository object
     * @param NoteRepository $note ;
     */
    public function __construct(NoteRepository $note)
    {
        $this->note = $note;
    }

    /**
     * This method return the data of the model
     * @param ManageNoteRequest $request
     *
     * @return mixed
     */
    public function __invoke(Request $request)
    {
        $core = $this->note->getForDataTable();
        
        return Datatables::of($core)
            ->escapeColumns(['id'])
            ->addIndexColumn()
            ->addColumn('created_at', function ($note) {
                return Carbon::parse($note->created_at)->toDateString();
            })
            ->addColumn('user', function ($note) {
                return @$note->creator->fullname;
            })
            ->addColumn('actions', function ($note) {
                if (request('project_id')) {
                    $btn_view = '<a href="'. route('biller.notes.show', [$note->id]) .'" class="btn btn-primary round" data-toggle="tooltip" data-placement="top" title="View"><i class="fa fa-eye"></i></a>';
                    $btn_edit = '<a href="'. route('biller.notes.edit', [$note->id]) .'" class="btn btn-warning round" data-toggle="tooltip" data-placement="top" title="Edit"><i class="fa fa-pencil "></i> </a>';
                    $btn_del = '<a class="btn btn-danger round" table-method="delete" data-trans-button-cancel="Cancel" data-trans-button-confirm="Delete" data-trans-title="Are you sure you want to do this?" data-toggle="tooltip" data-placement="top" title="Delete" style="cursor:pointer;" onclick="$(this).find(&quot;form&quot;).submit();">
                    <i class="fa fa-trash"></i> <form action="' . route('biller.notes.show', [$note->id]) . '" method="POST" name="delete_table_item" style="display:none"></form></a>';
                
                    return $btn_edit . ' ' . $btn_del;
                }
                // return $note->action_buttons;
            })
            ->make(true);
    }
}
