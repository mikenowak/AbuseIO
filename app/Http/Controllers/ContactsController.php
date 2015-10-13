<?php

namespace AbuseIO\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use AbuseIO\Http\Requests;
use AbuseIO\Http\Requests\ContactFormRequest;
use AbuseIO\Http\Controllers\Controller;
use AbuseIO\Models\Contact;
use Redirect;
use Input;

class ContactsController extends Controller
{

    /*
     * Call the parent constructor to generate a base ACL
     */
    public function __construct()
    {
        parent::__construct('createDynamicACL');
    }

    /**
     * Display a listing of the resource.
     * @param Request $request
     * @return Response
     */
    public function index(Request $request)
    {
        $contacts = Contact::paginate(10);

        return view('contacts.index')
            ->with('contacts', $contacts)
            ->with('user', $request->user());
    }

    /**
     * Show the form for creating a new resource.
     * @param Request $request
     * @return Response
     */
    public function create(Request $request)
    {
        return view('contacts.create')
            ->with('user', $request->user());
    }

    /**
     * Export listing to CSV format.
     * @return Response
     */
    public function export()
    {
        $contacts  = Contact::all();

        $columns = [
            'reference'     => 'Reference',
            'contact'       => 'name',
            'enabled'       => 'Status',
            'email'         => 'E-Mail address',
            'rpc_host'      => 'RPC address',
            'rpc_key'       => 'RPC key',
            'auto_notify'   => 'Notifications',
        ];

        $output = '"' . implode('", "', $columns) . '"' . PHP_EOL;

        foreach ($contacts as $contact) {
            $row = [
                $contact->reference,
                $contact->name,
                $contact['enabled'] ? 'Enabled' : 'Disabled',
                $contact['email'],
                $contact['rpc_host'],
                $contact['rpc_key'],
                $contact['auto_notify'] ? 'Automatic' : 'Manual',
            ];

            $output .= '"' . implode('", "', $row) . '"' . PHP_EOL;
        }

        return response(substr($output, 0, -1), 200)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="Contacts.csv"');
    }

    /**
     * Store a newly created resource in storage.
     * @return Response
     */
    public function store(ContactFormRequest $contact)
    {
        $input = Input::all();
        Contact::create($input);

        return Redirect::route('admin.contacts.index')
            ->with('message', 'Contact has been created');
    }

    /**
     * Display the specified resource.
     * @param Request $request
     * @param Contact $contact
     * @return Response
     * @internal param int $id
     */
    public function show(Request $request, Contact $contact)
    {
        return view('contacts.show')
            ->with('contact', $contact)
            ->with('user', $request->user());
    }

    /**
     * Show the form for editing the specified resource.
     * @param Request $request
     * @param Contact $contact
     * @return Response
     * @internal param int $id
     */
    public function edit(Request $request, Contact $contact)
    {
        return view('contacts.edit')
            ->with('contact', $contact)
            ->with('user', $request->user());
    }

    /**
     * Update the specified resource in storage.
     * @param  int  $id
     * @return Response
     */
    public function update(Contact $contact)
    {
        $input = array_except(Input::all(), '_method');

        $contact->update($input);

        return Redirect::route('admin.contacts.show', $contact->id)
            ->with('message', 'Contact has been updated.');
    }

    /**
     * Remove the specified resource from storage.
     * @param  int  $id
     * @return Response
     */
    public function destroy(Contact $contact)
    {
        if ($contact->domains->count() > 0) {
            return Redirect::route('admin.contacts.index')->with(
                'message',
                "Contact could not be deleted because ".  $contact->domains->count()
                . " domain(s) is stil pointing to this contact."
            );
        }

        if ($contact->netblocks->count() > 0) {
            return Redirect::route('admin.contacts.index')->with(
                'message',
                "Contact could not be deleted because ".  $contact->domains->count()
                . " domain(s) is stil pointing to this contact."
            );
        }

        $contact->delete();

        return Redirect::route('admin.contacts.index')
            ->with('message', 'Contact has been deleted.');
    }
}
