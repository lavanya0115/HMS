<div>
    <div class="page-body">
        <div class="container-xl">
            @include('includes.alerts')

            <div class="row">

                <div class="col-lg-12">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h4 class="mb-0">List of Email Templates</h4>
                        <a href="{{ route('email-templates.create') }}" class="btn btn-primary ml-auto">
                            Add New Email Template
                        </a>
                    </div>

                    <div class="card">

                        <div class="table-responsive">
                            <table class="table table-vcenter card-table">

                                <thead>
                                    <tr>
                                        <th>Target</th>
                                        {{-- <th>Hall</th> --}}
                                        <th>Subject</th>
                                        <th>Message Content</th>
                                        {{-- <th>Exhibitors</th> --}}
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @if ($emailTemplates->isEmpty())
                                        <tr>
                                            <td colspan="4" class="text-center text-danger">No email templates found
                                            </td>
                                        </tr>
                                    @else
                                        @foreach ($emailTemplates as $template)
                                            <tr>
                                                <td>
                                                    @if ($template->target_id == 'all-exhibitors')
                                                        All Exhibitors
                                                    @elseif($template->target_id == 'hall')
                                                        Hall ({{ $template->hall_id }})
                                                    @elseif($template->target_id == 'specific-exhibitors')
                                                        Specific Exhibitors
                                                    @endif

                                                </td>
                                                {{-- <td class="text-secondary">
                                                {{ $template->hall_id }}
                                            </td> --}}
                                                <td class="text-secondary">
                                                    {{ $template->subject }}</a>
                                                </td>
                                                <td class="text-secondary">
                                                    {{ $template->message_content }}
                                                </td>
                                                <td>
                                                    <a href="javascript:void(0);"
                                                        wire:click.prevent="sendEmail({{ $template->id }})">@include('icons.send')</a>
                                                    <a href="{{ route('email-templates.edit', $template->id) }}"
                                                        class="text-primary">@include('icons.edit')</a>
                                                    <a href="javascript:void(0);"
                                                        wire:confirm="Are you sure you want to delete this email template?"
                                                        wire:click.prevent="deleteEmailTemplate({{ $template->id }})"
                                                        class="text-danger">
                                                        @include('icons.trash')</a>
                                                </td>
                                            </tr>
                                        @endforeach
                                    @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
