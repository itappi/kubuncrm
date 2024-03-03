@component('laravel-crm::components.card')

    @component('laravel-crm::components.card-header')

        @slot('title')
            {{ ucfirst(__('laravel-crm::lang.purchase_orders')) }}
        @endslot

        @slot('actions')
            @include('laravel-crm::partials.filters', [
                'action' => route('laravel-crm.purchase-orders.filter'),
                'model' => '\VentureDrake\LaravelCrm\Models\PurchaseOrder'
            ])
            @can('create crm purchase orders')
            <span class="float-right"><a type="button" class="btn btn-primary btn-sm" href="{{ url(route('laravel-crm.purchase-orders.create')) }}"><span class="fa fa-plus"></span>  {{ ucfirst(__('laravel-crm::lang.add_purchase_order')) }}</a></span>
            @endcan
        @endslot

    @endcomponent

    @component('laravel-crm::components.card-table')
        <table class="table mb-0 card-table table-hover">
            <thead>
            <tr>
                <th scope="col">{{ ucwords(__('laravel-crm::lang.number')) }}</th>
                <th scope="col">{{ ucwords(__('laravel-crm::lang.reference')) }}</th>
                @hasordersenabled
                    <th scope="col">{{ ucwords(__('laravel-crm::lang.order')) }}</th>
                @endhasordersenabled
                <th scope="col">{{ ucwords(__('laravel-crm::lang.to')) }}</th>
                <th scope="col">{{ ucwords(__('laravel-crm::lang.date')) }}</th>
                <th scope="col">{{ ucwords(__('laravel-crm::lang.delivery_date')) }}</th>
                <th scope="col">{{ ucwords(__('laravel-crm::lang.sent')) }}</th>
                <th scope="col" width="280"></th>
            </tr>
            </thead>
            <tbody>
            @foreach($purchaseOrders as $purchaseOrder)
               <tr @if(! $purchaseOrder->xeroPurchaseOrder) class="has-link" data-url="{{ url(route('laravel-crm.purchase-orders.show', $purchaseOrder)) }}" @endif>
                   <td>{{ $purchaseOrder->xeroPurchaseOrder->number ?? $purchaseOrder->purchase_order_id }}</td>
                   <td>{{ $purchaseOrder->xeroPurchaseOrder->reference ?? $purchaseOrder->reference }}</td>
                   @hasordersenabled
                   <td>
                       @if($purchaseOrder->order)
                           <a href="{{ route('laravel-crm.orders.show', $purchaseOrder->order) }}">{{ $purchaseOrder->order->order_id }}</a>
                       @endif
                   </td>
                   @endhasordersenabled
                   <td>
                       {{ $purchaseOrder->organisation->name ?? null }}
                       @if($purchaseOrder->person)
                           <br /><small>{{ $purchaseOrder->person->name }}</small>
                       @endif    
                   </td>
                   <td>{{ $purchaseOrder->issue_date->format($dateFormat) }}</td>
                   <td>{{ ($purchaseOrder->delivery_date) ? $purchaseOrder->delivery_date->format($dateFormat) : null }}</td>
                   <td>
                       @if($purchaseOrder->sent == 1)
                           <span class="text-success">Sent</span>
                       @endif
                   </td>
                    <td class="disable-link text-right">
                        @if(! $purchaseOrder->xeroPurchaseOrder)
                            {{--@livewire('send-invoice',[
                                'invoice' => $invoice
                            ])--}}
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('laravel-crm.purchase-orders.download', $purchaseOrder) }}"><span class="fa fa-download" aria-hidden="true"></span></a>
                        
                            @can('view crm purchase orders')
                            <a href="{{ route('laravel-crm.purchase-orders.show',$purchaseOrder) }}" class="btn btn-outline-secondary btn-sm"><span class="fa fa-eye" aria-hidden="true"></span></a>
                            @endcan
                        @else
                            <img src="/vendor/laravel-crm/img/xero-icon.png" height="30" />
                        @endif    
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>

    @endcomponent

    @if($purchaseOrders instanceof \Illuminate\Pagination\LengthAwarePaginator )
        @component('laravel-crm::components.card-footer')
            {{ $purchaseOrders->links() }}
        @endcomponent
    @endif

@endcomponent
