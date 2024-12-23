<div>
    <div class="page-body">
        <div wire:ignore.self class="modal modal-blur fade" id="pay_for_seminar" tabindex="-1" role="dialog"
            aria-hidden="true" data-bs-backdrop='static'>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Pay For Seminar</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form wire:submit="payForSeminar">
                        <div class="modal-body">
                            <p>Are you sure you want to pay for this seminar(s)?</p>
                            <ul>
                                @foreach ($notPaidSeminars as $notPaidSeminar)
                                    <li>{{ $notPaidSeminar->seminar?->title }}</li>
                                @endforeach
                            </ul>
                            <p><span class="fw-bold">Amount :</span> ₹{{ $totalAmount }}</p>
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary" data-bs-dismiss="modal">Paid</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div wire:ignore.self class="modal modal-blur fade" id="update_payment_status" tabindex="-1" role="dialog"
            aria-hidden="true" data-bs-backdrop='static'>
            <div class="modal-dialog modal-dialog-centered" role="document">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Payment Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form wire:submit="updatePaymentStatus">
                        <div class="modal-body">
                            @foreach ($delegateSeminars as $index => $eventDelegate)
                                <div class="row col-md-12 mb-1">
                                    <div class="col-md-1">{{ $index + 1 }}.</div>
                                    <div class="col-md-7">{{ $eventDelegate->seminar?->title }}</div>
                                    <div class="col-md-4">
                                        <select class="form-select"
                                            wire:model.defer="paymentStatus.{{ $eventDelegate->id }}">
                                            <option value="">Select Status</option>
                                            <option value="paid">Paid</option>
                                            <option value="pay_later">Pay Later</option>
                                        </select>
                                        @error("paymentStatus.{$eventDelegate->id}")
                                            <span class="text-danger">{{ $message }}</span>
                                        @enderror
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <div class="modal-footer">
                            <button type="submit" class="btn btn-primary">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @if (isset($eventId))
            @livewire('appointments-modal')
        @endif
        <div class="container-xl">
            @include('includes.alerts')
            <div class="row">
                <div class="col-lg-12">
                    <h3>List of Delegates</h3>
                    <div class="card">
                        <div class="card-header d-flex justify-content-end">
                            @if (!isset($eventId))
                                @if ($showToggle == true)
                                    <div class="d-flex gap-1 mb-2 pt-2">
                                        <select class="form-select" wire:model.live="event_id">
                                            <option value="">Select Event</option>
                                            @foreach ($events as $eventID => $eventTitle)
                                                <option value="{{ $eventID }}">{{ $eventTitle }}</option>
                                            @endforeach
                                        </select>
                                        <button class="btn text-white" style="background-color: #f1a922;"
                                            wire:click="moveDelegatesToAnotherEvent"
                                            {{ empty($event_id) ? 'disabled' : '' }}>Add</button>
                                    </div>
                                @endif
                                @can('Transfer Delegate')
                                    <a href="#" class="mb-2 text-decoration-none pe-3" data-bs-toggle="tooltip"
                                        title="Move to Another Event"
                                        wire:click="toggleEvents">@include('icons.cloud-upload')</a>
                                @endcan
                            @endif
                            <div class="w-25 me-2">
                                <select class="form-select" wire:model.live="seminar" placeholder="Select Seminar">
                                    <option value="">Select Seminar</option>
                                    @foreach ($seminars as $seminarId => $seminar)
                                        <option value={{ $seminarId }}>{{ substr($seminar, 0, 50) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="input-group input-group-flat w-25">
                                <input type="text" wire:model.live="search" value="" class="form-control"
                                    placeholder="Search…">
                                <span class="input-group-text pe-3">
                                    <a href="#" wire:click="$set('search', '')" class="link-secondary"
                                        title="Clear search" data-bs-toggle="tooltip">
                                        @include('icons.close')
                                    </a>
                                </span>
                            </div>
                            <div class="col-auto ps-2">
                                @can('Export Delegate')
                                    <button class="btn w-10" wire:click="exportToExcel" wire:loading.attr="disabled"
                                        {{ isset($delegates) && count($delegates) == 0 ? 'disabled' : '' }}>
                                        @include('icons.file-export')
                                        <span wire:loading wire:target="exportToExcel">Exporting...</span>
                                        <span wire:loading.remove wire:target="exportToExcel">Export to Excel</span>
                                    </button>
                                @endcan
                            </div>
                        </div>

                        <div class="table-responsive">
                            <table class="table table-vcenter card-table" style="width: 140%">
                                <thead>
                                    <tr>
                                        {{-- @if (!isset($eventId))
                                            <th>
                                                <div>
                                                    <label class="form-check">
                                                        <input class="form-check-input" type="checkbox"
                                                            wire:model.live="selectAll"
                                                            style="border-color:rgb(134, 132, 132);">
                                                    </label>
                                                </div>
                                            </th>
                                        @endif --}}
                                        <th>#</th>
                                        <th>Name
                                            <span wire:click.prevent="sortColumn('name','asc')" style="cursor:pointer;"
                                                data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                                @include('icons.arrow-narrow-up')
                                            </span>
                                            <span wire:click.prevent="sortColumn('name','desc')" style="cursor:pointer;"
                                                data-toggle="tooltip" data-placement="top" title="Sort Descending">
                                                @include('icons.arrow-narrow-down')
                                            </span>
                                        </th>

                                        <th>Mobile.No.
                                            <span wire:click.prevent="sortColumn('mobile_number','asc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Ascending">
                                                @include('icons.arrow-narrow-up')
                                            </span>
                                            <span wire:click.prevent="sortColumn('mobile_number','desc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Descending">
                                                @include('icons.arrow-narrow-down')
                                            </span>
                                        </th>
                                        <th>Email
                                            <span wire:click.prevent="sortColumn('email','asc')" style="cursor:pointer;"
                                                data-toggle="tooltip" data-placement="top" title="Sort Ascending">
                                                @include('icons.arrow-narrow-up')
                                            </span>
                                            <span wire:click.prevent="sortColumn('email','desc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Descending">
                                                @include('icons.arrow-narrow-down')
                                            </span>
                                        </th>

                                        <th>Organization
                                            <span wire:click.prevent="sortColumn('organization','asc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Ascending">
                                                @include('icons.arrow-narrow-up')
                                            </span>
                                            <span wire:click.prevent="sortColumn('organization','desc')"
                                                style="cursor:pointer;" data-toggle="tooltip" data-placement="top"
                                                title="Sort Descending">
                                                @include('icons.arrow-narrow-down')
                                            </span>
                                        </th>
                                        <th>Designation</th>
                                        <th>Seminars_to_attend</th>
                                        <th class="overflow-auto">Payment Status</th>
                                        <th class="w-1"></th>
                                        <th class="w-1"></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if (isset($delegates) && count($delegates) > 0)
                                        @foreach ($delegates as $delegatesIndex => $delegate)
                                            @php
                                                $eventDelegates = $delegate->eventDelegates->where(
                                                    'event_id',
                                                    $eventId,
                                                );
                                                $paidCount = $eventDelegates->where('payment_status', 'paid')->count();
                                                $payLaterCount = $eventDelegates
                                                    ->where('payment_status', 'pay_later')
                                                    ->count();
                                                $totalSeminars = $eventDelegates->count();

                                                if ($paidCount == $totalSeminars) {
                                                    $paymentStatus = 'paid';
                                                } elseif ($payLaterCount == $totalSeminars) {
                                                    $paymentStatus = 'not paid';
                                                } elseif ($paidCount != $totalSeminars && $paidCount > 0) {
                                                    $paymentStatus = 'partially paid';
                                                } else {
                                                    $paymentStatus = '';
                                                }
                                            @endphp
                                            <tr wire:key="{{ $delegate->id }}">
                                                {{-- @if (!isset($eventId))
                                                    <td>
                                                        <div>
                                                            <label class="form-check">
                                                                <input class="form-check-input" type="checkbox"
                                                                    wire:model="selectedDelegates"
                                                                    value="{{ $delegate->id }}"
                                                                    style="border-color:rgb(134, 132, 132);">
                                                            </label>
                                                        </div>
                                                    </td>
                                                @endif --}}
                                                <td>
                                                    {{ $delegatesIndex + $delegates->firstItem() }}
                                                </td>
                                                <td>
                                                    <div class="text-capitalize small lh-base">
                                                        {{ $delegate->name }}
                                                    </div>

                                                    </span>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize small lh-base">
                                                        {{ $delegate->mobile_number }}</div>
                                                </td>
                                                <td>
                                                    <div class="small lh-base">
                                                        {{ strtolower($delegate->email) }}

                                                    </div>
                                                </td>

                                                <td>
                                                    <div class="text-capitalize small lh-base">
                                                        {{ $delegate->organization }}</div>
                                                </td>
                                                <td>
                                                    <div class="text-capitalize small lh-base">
                                                        {{ $delegate->designation }}</div>
                                                </td>

                                                <td class="text-capitalize small lh-base">
                                                    @foreach ($eventDelegates as $eventDelegate)
                                                        <span
                                                            class="{{ $eventDelegate->payment_status == 'paid' ? 'bg-green-lt' : 'bg-red-lt' }}">{{ $eventDelegate->seminar?->title }}</span>
                                                        @if (!$loop->last)
                                                            {{ ',' }}
                                                        @endif
                                                    @endforeach
                                                </td>
                                                <td class="text-capitalize small lh-base">
                                                    <span @class([
                                                        'px-2 rounded',
                                                        'bg-green-lt' => $paymentStatus == 'paid' ? true : false,
                                                        'bg-red-lt' => $paymentStatus == 'not paid' ? true : false,
                                                        'bg-yellow-lt' => $paymentStatus == 'partially paid' ? true : false,
                                                    ])>{{ $paymentStatus }}</span>
                                                </td>
                                                <td>
                                                    @if ($paymentStatus !== 'paid')
                                                        <a href="#"
                                                            wire:click="getDelegateId({{ $delegate->id }})"
                                                            data-bs-toggle="modal" data-bs-target="#pay_for_seminar">
                                                            Pay
                                                        </a>
                                                    @endif
                                                </td>
                                                <td>
                                                    @can('Update Payment Status')
                                                        <a href="#" wire:click="getDelegateId({{ $delegate->id }})"
                                                            data-bs-toggle="modal"
                                                            data-bs-target="#update_payment_status">
                                                            @include('icons.edit')
                                                        </a>
                                                    @endcan
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                    @if (isset($delegates) && count($delegates) == 0)
                                        @livewire('not-found-record-row', ['colspan' => 8])
                                    @endif
                                </tbody>
                            </table>
                        </div>
                        <div class="card-footer">
                            <div class="d-flex justify-content-end">
                                @if (isset($delegates) && $delegates->count() > 0)
                                    {{ $delegates->links() }}
                                @endif
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
