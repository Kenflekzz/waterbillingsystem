<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;

class ClientController extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {
       $status = $request->input('status');
       $clients = Clients::when($status, function($query, $status){
        return $query->where('status', $status);
       })->orderBy('id', 'desc')->paginate(25);
       
        return view('admin.clients', compact('clients', 'status'));
    }

    // Show the form for creating a new resource.
    public function create()
    {
        return view('admin.clients.create');
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
    {   
        
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'meter_no' => 'required|string|max:255|unique:clients',
            'group' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'purok' => 'required|string|max:255',
            'contact_number' => 'required|string|max:11',
            'installation_date' => 'nullable|date',
            'date_cut' => 'nullable|date',
            'meter_series' => 'required|string|max:255',
        ], [
            'meter_no.unique' => 'The meter number has already been taken.',
            'full_name.required' => 'The full name field is required.',

        ]);

        Clients::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
    
    }

    // Display the specified resource.
    public function show(Clients $client)
    {
        return view('admin.clients.show', compact('client'));
    }

    // Show the form for editing the specified resource.
    public function edit(Clients $client)
    {
        return view('admin.clients.edit', compact('client'));
    }

    // Update the specified resource in storage.
    public function update(Request $request, Clients $client)
    {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'meter_no' => 'required|string|max:255|unique:clients,meter_no,' . $client->id,
            'group' => 'nullable|string|max:255',
            'barangay' => 'nullable|string|max:255',
            'purok' => 'nullable|string|max:255',
            'contact_number' => 'required|string|max:11',
            'date_cut' => 'nullable|date',
            'installation_date' => 'nullable|date',
            'meter_series' => 'nullable|string|max:255',
            'status' => 'nullable|string|max:50',
        ],[
            'meter_no.unique' => 'The meter number has already been taken.',
        ]);

        $client->update($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully.');
    }

    // Remove the specified resource from storage.
    public function destroy(Clients $client)
    {
       try{
        $client = Clients::findOrFail($client->id);
        $client->delete();

        return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
       }catch(\Exception $e) {
            return redirect()->route('admin.clients.index')->with('error', 'Failed to delete client: ' . $e->getMessage());
        }   

    }
    public function print(Request $request){

        $query = Clients::query();


        if($request->has('status') && $request->status !== ''){
            $query->where('status', $request->status);
        }
        $clients = $query->get()->chunk(10);

        $pdf = Pdf::loadView('admin.print_clients', compact('clients'));

        $pdf ->setPaper('A4', 'portrait');

        return $pdf->stream('clients.pdf');
    }
}
