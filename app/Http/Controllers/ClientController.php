<?php

namespace App\Http\Controllers;

use App\Models\Clients;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Validation\ValidationException;

class ClientController extends Controller
{
    // Display a listing of the resource.
    public function index(Request $request)
    {
        try {
            $status = $request->input('status');
            $clients = Clients::when($status, function($query, $status){
                return $query->where('status', $status);
            })->orderBy('id', 'desc')->paginate(25);
            
            return view('admin.clients', compact('clients', 'status'));
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Database error: Unable to fetch clients. ' . $this->getFriendlyErrorMessage($e));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'An unexpected error occurred while loading clients.');
        }
    }

    // Show the form for creating a new resource.
    public function create()
    {
        return view('admin.clients.create');
    }

    // Store a newly created resource in storage.
    public function store(Request $request)
{   
    try {
        $validated = $request->validate([
            'full_name' => 'required|string|max:255',
            'meter_no' => 'required|string|max:255|unique:clients',
            'group' => 'required|string|max:255',
            'barangay' => 'required|string|max:255',
            'purok' => 'required|string|max:255',
            'contact_number' => 'required|string|max:11|unique:clients,contact_number',
            'installation_date' => 'nullable|date',
            'date_cut' => 'nullable|date',
            'meter_status'     => 'nullable|in:old,replacement',
            'replacement_date' => 'nullable|date',
            'meter_series' => 'required|string|max:255',
        ], [
            'meter_no.unique' => 'The meter number has already been taken.',
            'contact_number.unique' => 'This contact number is already registered.',
            'full_name.required' => 'The full name field is required.',
        ]);

        $validated['old_meter_no'] = null;
        Clients::create($validated);

        return redirect()->route('admin.clients.index')->with('success', 'Client created successfully.');
        
    } catch (ValidationException $e) {
        return redirect()->back()
            ->withErrors($e->validator)
            ->withInput();
    } catch (QueryException $e) {
        // Check for unique constraint violations
        if (str_contains($e->getMessage(), 'clients_contact_number_unique')) {
            return redirect()->back()
                ->with('duplicate_contact', $request->contact_number)
                ->withInput();
        }
        
        if (str_contains($e->getMessage(), 'clients_meter_no_unique')) {
            return redirect()->back()
                ->with('duplicate_meter', $request->meter_no)
                ->withInput();
        }

        // Other database errors - mark as add_client_error
        return redirect()->back()
            ->with('add_client_error', $this->getFriendlyErrorMessage($e))
            ->withInput();
    } catch (\Exception $e) {
        return redirect()->back()
            ->with('add_client_error', 'An unexpected error occurred. Please try again.')
            ->withInput();
    }
}

    // Display the specified resource.
    public function show(Clients $client)
    {
        try {
            return view('admin.clients.show', compact('client'));
        } catch (\Exception $e) {
            return redirect()->route('admin.clients.index')->with('error', 'Client not found or error loading details.');
        }
    }

    // Show the form for editing the specified resource.
    public function edit(Clients $client)
    {
        try {
            return view('admin.clients.edit', compact('client'));
        } catch (\Exception $e) {
            return redirect()->route('admin.clients.index')->with('error', 'Unable to load client for editing.');
        }
    }           

    // Update the specified resource in storage.
    public function update(Request $request, Clients $client)
    {
        try {
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
                'replacement_date'  => 'nullable|date',                
                'meter_status' => 'nullable|string|max:50',
                'status' => 'nullable|string|max:50',
            ],[
                'meter_no.unique' => 'The meter number has already been taken.',
            ]);

            // old_meter_no is automatically handled by the model's booted() method
            $client->update($validated);

            return redirect()->route('admin.clients.index')->with('success', 'Client updated successfully. Meter number synchronized.');
            
        } catch (ValidationException $e) {
            return redirect()->back()
                ->withErrors($e->validator)
                ->withInput();
        } catch (QueryException $e) {
            return redirect()->back()
                ->with('error', 'Database error: ' . $this->getFriendlyErrorMessage($e))
                ->withInput();
        } catch (\Exception $e) {
            return redirect()->back()
                ->with('error', 'An unexpected error occurred while updating. Please try again.')
                ->withInput();
        }
    }

    // Remove the specified resource from storage.
    public function destroy(Clients $client)
    {
        try {
            $client->delete();
            return redirect()->route('admin.clients.index')->with('success', 'Client deleted successfully.');
        } catch (QueryException $e) {
            // Check for foreign key constraint violations
            if ($e->getCode() == 23000) {
                return redirect()->route('admin.clients.index')
                    ->with('error', 'Cannot delete this client because they have associated records (bills, readings, or payments). Please remove those first.');
            }
            return redirect()->route('admin.clients.index')
                ->with('error', 'Database error: ' . $this->getFriendlyErrorMessage($e));
        } catch (\Exception $e) {
            return redirect()->route('admin.clients.index')
                ->with('error', 'Failed to delete client. Please try again.');
        }
    }

    public function print(Request $request)
    {
        try {
            $query = Clients::query();

            if($request->has('status') && $request->status !== ''){
                $query->where('status', $request->status);
            }
            $clients = $query->get()->chunk(10);

            $pdf = Pdf::loadView('admin.print_clients', compact('clients'));
            $pdf->setPaper('A4', 'portrait');

            return $pdf->stream('clients.pdf');
            
        } catch (QueryException $e) {
            return redirect()->back()->with('error', 'Database error: Unable to generate PDF. ' . $this->getFriendlyErrorMessage($e));
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Failed to generate PDF. Please try again.');
        }
    }

    /**
     * Convert SQL errors to user-friendly messages
     */
    private function getFriendlyErrorMessage(QueryException $e)
    {
        $message = $e->getMessage();
        
        // MySQL error codes and messages
        if (str_contains($message, 'Duplicate entry')) {
            preg_match("/Duplicate entry '(.+)' for key '(.+)'/", $message, $matches);
            $value = $matches[1] ?? 'unknown';
            $field = $matches[2] ?? 'field';
            return "The value '{$value}' already exists for {$field}. Please use a unique value.";
        }
        
        if (str_contains($message, 'Cannot delete or update a parent row')) {
            return "This record cannot be deleted because it is linked to other records in the system.";
        }
        
        if (str_contains($message, 'Data too long')) {
            return "One of the fields contains too much text. Please shorten your input.";
        }
        
        if (str_contains($message, 'Incorrect date value')) {
            return "Invalid date format provided. Please use a valid date.";
        }
        
        if (str_contains($message, 'Column not found')) {
            return "System configuration error. Please contact support.";
        }
        
        if (str_contains($message, 'Connection refused') || str_contains($message, 'No such host')) {
            return "Unable to connect to database. Please try again later.";
        }
        
        // Default message for unhandled SQL errors
        return "A database error occurred. Please try again or contact support if the problem persists.";
    }
}