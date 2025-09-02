@extends('layouts.admin')

@section('title', 'Payments')
<link rel="icon" href="{{ asset('images/MAGALLANES_LOGO.png') }}" type="image/x-icon">

@section('content')
 {{-- Success Modal --}}
@if(session('success'))
<div class="modal fade" id="successModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-success">
      <div class="modal-header bg-success text-white">
        <h5 class="modal-title">Success</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        {{ session('success') }}
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-success" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
@endif

{{-- Error Modal --}}
@if($errors->any())
<div class="modal fade" id="errorModal" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content border-danger">
      <div class="modal-header bg-danger text-white">
        <h5 class="modal-title">Error</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body">
        <ul class="mb-0">
          @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
          @endforeach
        </ul>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-danger" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
@endif

<h1 class="mt-4">Payments</h1>
<ol class="breadcrumb mb-4">
    <li class="breadcrumb-item"><a href="{{ url('admin/dashboard') }}">Dashboard</a></li>
    <li class="breadcrumb-item active">Payments</li>
</ol>

<div class="card mb-4">
    <div class="card-body">
        Below is the table showing all client payment records.
    </div>
</div>

<div class="card mb-4">
    <div class="card-header">
        <i class="fas fa-table me-1"></i>
        Payment Records
    </div>
    <div class="card-body">
        <table id="datatablesSimple" class="table table-bordered">
            <thead>
                <tr>
                    <th>Full Name</th>
                    <th>Barangay</th>
                    <th>Purok</th>
                    <th>Billing Month</th>
                    <th>Current Bill</th>
                    <th>Remaining Current Bill</th>
                    <th>Arrears</th>
                    <th>Partial Payment Amount</th>
                    <th>Payment Type</th>
                    <th>Penalty</th>
                    <th>Total Amount</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                <tr>
                    <td>{{ $payment->client->full_name ?? 'N/A' }}</td>
                    <td>{{ $payment->client->barangay ?? 'N/A' }}</td>
                    <td>{{ $payment->client->purok ?? 'N/A' }}</td>
                    <td>{{ \Carbon\Carbon::parse($payment->billing_month)->format('M Y') }}</td>
                    <td>₱{{ number_format($payment->current_bill, 2) }}</td>
                    <td>₱{{ number_format($payment->remaining_current_balance ?? 0, 2) }}</td>
                    <td>₱{{ number_format($payment->arrears, 2) }}</td>
                    <td>₱{{ number_format($payment->partial_payment_amount ?? 0, 2) }}</td>
                    <td>{{$payment->payment_type_label}}</td>
                    <td>₱{{ number_format($payment->penalty, 2) }}</td>
                    <td><strong>₱{{ number_format($payment->total_amount, 2) }}</strong></td>
                    <td>{{ ucfirst($payment->status) }}</td>
                    <td>
                            <div class="d-inline-flex gap-1 flex-wrap">
                                <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#editModal{{ $payment->id }}">
                                    <i class="fas fa-edit"></i>
                                </button>
                                <form action="{{ route('admin.payments.destroy', $payment->id) }}" method="POST" class="d-inline m-0 p-0 delete-payment-form">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        <!-- Edit Modals -->
        @foreach($payments as $payment)
        <div class="modal fade" id="editModal{{ $payment->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $payment->id }}" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <form method="POST" action="{{ route('admin.payments.update', $payment->id) }}">
                        @csrf
                        @method('PUT')
                        <div class="modal-header">
                            <h5 class="modal-title" id="editModalLabel{{ $payment->id }}">Edit Payment Status</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body">
                            <div class="mb-3">
                              <label for="paymentType{{ $payment->id }}" class="form-label">Payment Type</label>
                              <select name="payment_type" id="paymentType{{ $payment->id }}" class="form-select"
                                      onchange="togglePartialInput({{ $payment->id }})">
                                  <option value="">-- Select --</option>
                                  <option value="full">Full Payment</option>
                                  <option value="arrears_only">Arrears Only</option>
                                  <option value="partial_current">Partial Current Bill</option>
                              </select>
                          </div>

                          <div class="mb-3 d-none" id="partialAmountDiv{{ $payment->id }}">
                              <label for="partialAmount{{ $payment->id }}" class="form-label">Partial Payment Amount</label>
                              <input type="number" step="0.01" name="partial_payment_amount" id="partialAmount{{ $payment->id }}" class="form-control">
                          </div>

                          <div class="mb-3">
                              <label for="status{{ $payment->id }}" class="form-label">Status</label>
                              <select name="status" id="status{{ $payment->id }}" class="form-select">
                                  <option value="unpaid" {{ $payment->status == 'unpaid' ? 'selected' : '' }}>Unpaid</option>
                                  <option value="paid" {{ $payment->status == 'paid' ? 'selected' : '' }}>Paid</option>
                                  <option value="partial" {{ $payment->status == 'partial' ? 'selected' : '' }}>Partial</option>
                                  <option value="disconnected" {{ $payment->status == 'disconnected' ? 'selected' : '' }}>Disconnected</option>
                              </select>
                          </div>

                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                            <button type="submit" class="btn btn-primary">Update Status</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @endforeach

        <div class="mt-3">
            {{ $payments->links() }}
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- DataTables Scripts -->
<script src="https://cdn.jsdelivr.net/npm/simple-datatables@7.1.2/dist/umd/simple-datatables.min.js"></script>
<script src="{{ asset('admin/js/datatables-simple-demo.js') }}"></script>
<input type="hidden" id="hasSuccess" value="{{ session('success') ? '1' : '0' }}">
<input type="hidden" id="successMessage" value="{{ session('success') }}">
<input type="hidden" id="hasErrors" value="{{ $errors->any() ? '1' : '0' }}">
<input type="hidden" id="errorMessages" value="{{ implode(' | ', $errors->all()) }}">
@vite('resources/js/payments.js')
@endsection
