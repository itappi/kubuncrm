<?php

namespace VentureDrake\LaravelCrm\Http\Controllers;

use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use VentureDrake\LaravelCrm\Http\Requests\StorePurchaseOrderRequest;
use VentureDrake\LaravelCrm\Http\Requests\UpdatePurchaseOrderRequest;
use VentureDrake\LaravelCrm\Models\PurchaseOrder;
use VentureDrake\LaravelCrm\Models\Order;
use VentureDrake\LaravelCrm\Models\Organisation;
use VentureDrake\LaravelCrm\Models\Person;
use VentureDrake\LaravelCrm\Services\PurchaseOrderService;
use VentureDrake\LaravelCrm\Services\OrganisationService;
use VentureDrake\LaravelCrm\Services\PersonService;
use VentureDrake\LaravelCrm\Services\SettingService;

class PurchaseOrderController extends Controller
{
    /**
     * @var SettingService
     */
    private $settingService;

    /**
     * @var PersonService
     */
    private $personService;

    /**
     * @var OrganisationService
     */
    private $organisationService;

    /**
     * @var PurchaseOrderService
     */
    private $purchaseOrderService;

    public function __construct(SettingService $settingService, PersonService $personService, OrganisationService $organisationService, PurchaseOrderService $purchaseOrderService)
    {
        $this->settingService = $settingService;
        $this->personService = $personService;
        $this->organisationService = $organisationService;
        $this->purchaseOrderService = $purchaseOrderService;
    }

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        PurchaseOrder::resetSearchValue($request);
        $params = PurchaseOrder::filters($request);

        if (PurchaseOrder::filter($params)->get()->count() < 30) {
            $purchaseOrders = PurchaseOrder::filter($params)->latest()->get();
        } else {
            $purchaseOrders = PurchaseOrder::filter($params)->latest()->paginate(30);
        }

        return view('laravel-crm::purchase-orders.index', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request)
    {
        switch ($request->model) {
            case "person":
                $person = Person::find($request->id);

                break;

            case "organisation":
                $organisation = Organisation::find($request->id);

                break;

            case "order":
                $order = Order::find($request->id);

                break;
        }

        return view('laravel-crm::purchase-orders.create', [
            'person' => $person ?? null,
            'organisation' => $organisation ?? null,
            'order' => $order ?? null,
            'prefix' => $this->settingService->get('purchase_order_prefix'),
            'number' => (PurchaseOrder::latest()->first()->number ?? 1000) + 1,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePurchaseOrderRequest $request)
    {
        if ($request->person_name && ! $request->person_id) {
            $person = $this->personService->createFromRelated($request);
        } elseif ($request->person_id) {
            $person = Person::find($request->person_id);
        }

        if ($request->organisation_name && ! $request->organisation_id) {
            $organisation = $this->organisationService->createFromRelated($request);
        } elseif ($request->organisation_id) {
            $organisation = Organisation::find($request->organisation_id);
        }

        $this->purchaseOrderService->create($request, $person ?? null, $organisation ?? null);

        flash(ucfirst(trans('laravel-crm::lang.purchase_order_created')))->success()->important();

        return redirect(route('laravel-crm.purchase-orders.index'));
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->person) {
            $email = $purchaseOrder->person->getPrimaryEmail();
            $phone = $purchaseOrder->person->getPrimaryPhone();
            $address = $purchaseOrder->person->getPrimaryAddress();
        }

        if ($purchaseOrder->organisation) {
            $organisation_address = $purchaseOrder->organisation->getPrimaryAddress();
        }

        return view('laravel-crm::purchase-orders.show', [
            'purchaseOrder' => $purchaseOrder,
            'email' => $email ?? null,
            'phone' => $phone ?? null,
            'address' => $address ?? null,
            'organisation_address' => $organisation_address ?? null,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->person) {
            $email = $purchaseOrder->person->getPrimaryEmail();
            $phone = $purchaseOrder->person->getPrimaryPhone();
        }

        if ($purchaseOrder->organisation) {
            $address = $purchaseOrder->organisation->getPrimaryAddress();
        }

        return view('laravel-crm::purchase-orders.edit', [
            'purchaseOrder' => $purchaseOrder,
            'email' => $email ?? null,
            'phone' => $phone ?? null,
            'address' => $address ?? null,
        ]);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePurchaseOrderRequest $request, PurchaseOrder $purchaseOrder)
    {
        if ($request->person_name && ! $request->person_id) {
            $person = $this->personService->createFromRelated($request);
        } elseif ($request->person_id) {
            $person = Person::find($request->person_id);
        }

        if ($request->organisation_name && ! $request->organisation_id) {
            $organisation = $this->organisationService->createFromRelated($request);
        } elseif ($request->organisation_id) {
            $organisation = Organisation::find($request->organisation_id);
        }

        $purchaseOrder = $this->purchaseOrderService->update($request, $purchaseOrder, $person ?? null, $organisation ?? null);

        flash(ucfirst(trans('laravel-crm::lang.purchase_order_updated')))->success()->important();

        return redirect(route('laravel-crm.purchase-orders.show', $purchaseOrder));
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();

        flash(ucfirst(trans('laravel-crm::lang.purchase_order_deleted')))->success()->important();

        return redirect(route('laravel-crm.purchase-orders.index'));
    }

    public function download(PurchaseOrder $purchaseOrder)
    {
        if ($purchaseOrder->person) {
            $email = $purchaseOrder->person->getPrimaryEmail();
            $phone = $purchaseOrder->person->getPrimaryPhone();
            $address = $purchaseOrder->person->getPrimaryAddress();
        }

        if ($purchaseOrder->organisation) {
            $organisation_address = $purchaseOrder->organisation->getPrimaryAddress();
        }

        return Pdf::setOption([
            'fontDir' => public_path('vendor/laravel-crm/fonts'),
        ])
            ->loadView('laravel-crm::purchase-orders.pdf', [
                'purchaseOrder' => $purchaseOrder,
                'contactDetails' => $this->settingService->get('purchase_order_contact_details')->value ?? null,
                'email' => $email ?? null,
                'phone' => $phone ?? null,
                'address' => $address ?? null,
                'organisation_address' => $organisation_address ?? null,
                'fromName' => $this->settingService->get('organisation_name')->value ?? null,
                'logo' => $this->settingService->get('logo_file')->value ?? null,
            ])->download('purchase-order-'.strtolower($purchaseOrder->purchase_order_id).'.pdf');
    }
}
