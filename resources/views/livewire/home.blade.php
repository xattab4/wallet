<div>
    <div class="alert alert-secondary d-flex justify-content-between" wire:poll.keep-alive>
        {{ __('Balance') }}

        <b>{{ $user->balance }}</b>
    </div>

    <div class="card-body">
        <ul class="list-group list-group-flush" wire:poll.keep-alive>
            @forelse ($user->transactions()->latest()->limit(5)->get() as $transaction)
                <li class="list-group-item d-flex justify-content-between">
                    <p>
                        <small class="text-muted">{{ $transaction->uuid }}</small> <br/>
                        {{ $transaction->comment }}
                    </p>

                    <b>
                        {{ $transaction->amountWithSymbol }}
                    </b>
                </li>
            @empty
                <li class="list-group-item">{{ __('Transactions empty!') }}</li>
            @endforelse
        </ul>
    </div>
</div>
