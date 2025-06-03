<?php

namespace App\Services;

use App\Classes\Hook;
use App\Events\ProcurementReturnAfterCreateEvent;
use App\Events\ProcurementReturnAfterDeleteProductEvent;
use App\Events\ProcurementReturnAfterHandledEvent;
use App\Events\ProcurementReturnAfterSaveProductEvent;
use App\Events\ProcurementReturnAfterUpdateEvent;
use App\Events\ProcurementReturnBeforeCreateEvent;
use App\Events\ProcurementReturnBeforeDeleteProductEvent;
use App\Events\ProcurementReturnBeforeHandledEvent;
use App\Events\ProcurementReturnBeforeUpdateEvent;
use App\Exceptions\NotAllowedException;
use App\Exceptions\NotFoundException;
use App\Models\ProcurementReturn;
use App\Models\ProcurementReturnProduct;
use App\Models\Product;
use App\Models\ProductHistory;
use App\Models\ProductUnitQuantity;
use App\Models\Provider;
use App\Models\Role;
use App\Models\Unit;
use Exception;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use stdClass;

class ProcurementReturnService
{
    protected $providerService;

    protected $unitService;

    protected $productService;

    protected $currency;

    protected $dateService;

    /**
     * @param BarcodeService $barcodeservice
     **/
    protected $barcodeService;

    public function __construct(
        ProviderService $providerService,
        UnitService $unitService,
        ProductService $productService,
        CurrencyService $currency,
        DateService $dateService,
        BarcodeService $barcodeService
    ) {
        $this->providerService = $providerService;
        $this->unitService = $unitService;
        $this->productService = $productService;
        $this->dateService = $dateService;
        $this->currency = $currency;
        $this->barcodeService = $barcodeService;
    }

    /**
     * get a single procurementreturn
     * or retrieve a list of procurementreturn
     *
     * @param int procurementreturn id
     * @return Collection|ProcurementReturn
     */
    public function get( $id = null )
    {
        if ( $id !== null ) {
            $provider = ProcurementReturn::find( $id );

            if ( ! $provider instanceof ProcurementReturn ) {
                throw new Exception( __( 'Unable to find the requested procurementreturn using the provided identifier.' ) );
            }

            return $provider;
        }

        return ProcurementReturn::get();
    }

    public function procurementreturnName()
    {
        $lastProcurementReturn = ProcurementReturn::orderBy( 'id', 'desc' )->first();

        if ( $lastProcurementReturn instanceof ProcurementReturn ) {
            $number = str_pad( $lastProcurementReturn->id + 1, 5, '0', STR_PAD_LEFT );
        } else {
            $number = str_pad( 1, 5, '0', STR_PAD_LEFT );
        }

        return sprintf( __( '%s' ), $number );
    }

    /**
     * create a procurementreturn
     * using the provided informations
     *
     * @param array procurementreturn data
     * @return array|Exception
     */
    public function create( $data )
    {
        extract( $data );

        /**
         * try to find the provider
         * or return an error
         */
        $provider = $this->providerService->get( $data[ 'general' ][ 'provider_id' ] );

        if ( ! $provider instanceof Provider ) {
            throw new Exception( __( 'Unable to find the assigned provider.' ) );
        }

        /**
         * We'll create a new instance
         * of the procurementreturn
         *
         * @param ProcurementReturn
         */
        $procurementreturn = new ProcurementReturn;

        /**
         * we'll make sure to trigger some event before
         * performing some change on the procurementreturn
         */
        event( new ProcurementReturnBeforeCreateEvent( $procurementreturn, $data ) );

        /**
         * We don't want the event ProcurementReturnBeforeCreateEvent
         * and ProcurementReturnAfterCreateEvent to trigger while saving
         */
        ProcurementReturn::withoutEvents( function () use ( $procurementreturn, $data ) {
            $procurementreturn->name = $data[ 'name' ] ?: $this->procurementreturnName();

            foreach ( $data[ 'general' ] as $field => $value ) {
                $procurementreturn->$field = $value;
            }

            if ( ! empty( $procurementreturn->created_at ) || ! empty( $procurementreturn->updated_at ) ) {
                $procurementreturn->timestamps = false;
            }

            $procurementreturn->author = Auth::id();
            $procurementreturn->cost = 0;
            $procurementreturn->save();
        } );

        /**
         * Let's save the product that are procured
         * This doesn't affect the stock but only store the product
         */
        if ( $data[ 'products' ] ) {
            $this->saveProducts( $procurementreturn, collect( $data[ 'products' ] ) );
        }

        /**
         * We can now safely trigger the event here
         * that will ensure correct computing
         */
        event( new ProcurementReturnAfterCreateEvent( $procurementreturn ) );

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn has been created.' ),
            'data' => [
                'products' => $procurementreturn->products,
                'procurementreturn' => $procurementreturn,
            ],
        ];
    }

    /**
     * Editing a specific procurementreturn using the provided informations
     *
     * @param int procurementreturn id
     * @param array data to update
     * @return array
     */
    public function edit( $id, $data )
    {
        /**
         * @param array  $general
         * @param string $name
         * @param array  $products
         */
        extract( $data );

        /**
         * try to find the provider
         * or return an error
         */
        $provider = $this->providerService->get( $data[ 'general' ][ 'provider_id' ] );

        if ( ! $provider instanceof Provider ) {
            throw new Exception( __( 'Unable to find the assigned provider.' ) );
        }

        $procurementreturn = ProcurementReturn::findOrFail( $id );

        /**
         * we'll make sure to trigger some event before
         * performing some change on the procurementreturn
         */
        event( new ProcurementReturnBeforeUpdateEvent( $procurementreturn ) );

        /**
         * We won't dispatch the even while savin the procurementreturn
         * however we'll do that once the product has been stored.
         */
        ProcurementReturn::withoutEvents( function () use ( $data, $procurementreturn ) {
            if ( $procurementreturn->delivery_status === 'stocked' ) {
                throw new Exception( __( 'Unable to edit a procurementreturn that has already been stocked. Please consider performing and stock adjustment.' ) );
            }

            $procurementreturn->name = $data[ 'name' ];

            foreach ( $data[ 'general' ] as $field => $value ) {
                $procurementreturn->$field = $value;
            }

            if ( ! empty( $procurementreturn->created_at ) || ! empty( $procurementreturn->updated_at ) ) {
                $procurementreturn->timestamps = false;
            }

            $procurementreturn->author = Auth::id();
            $procurementreturn->cost = 0;
            $procurementreturn->save();
        } );

        /**
         * We can now safely save
         * the procurementreturn products
         */
        if ( $data[ 'products' ] ) {
            $this->saveProducts( $procurementreturn, collect( $data[ 'products' ] ) );
        }

        /**
         * we want to dispatch the event
         * only when the product has been created
         */
        event( new ProcurementReturnAfterUpdateEvent( $procurementreturn ) );

        return [
            'status' => 'success',
            'message' => __( 'The provider has been edited.' ),
            'data' => compact( 'procurementreturn' ),
        ];
    }

    /**
     * delete a specific procurementreturn
     * using the provided id
     *
     * @param int procurementreturn id
     * @return void
     */
    public function delete( $id )
    {
        $procurementreturn = ProcurementReturn::find( $id );

        if ( ! $procurementreturn instanceof ProcurementReturn ) {
            throw new Exception( 'Unable to find the requested procurementreturn using the provided id.' );
        }

        $procurementreturn->delete();

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn has been deleted.' ),
        ];
    }

    /**
     * Attempt a product stock removal
     * if the procurementreturn has been stocked
     *
     * @throws NotAllowedException
     */
    public function attemptProductsStockRemoval( ProcurementReturn $procurementreturn ): void
    {
        if ( $procurementreturn->delivery_status === 'stocked' ) {
            $procurementreturn->products->each( function ( ProcurementReturnProduct $procurementreturnProduct ) {
                /**
                 * We'll handle products that was converted a bit
                 * differently to ensure converted product inventory is taken in account.
                 */
                if ( empty( $procurementreturnProduct->convert_unit_id ) ) {
                    $unitQuantity = ProductUnitQuantity::withProduct( $procurementreturnProduct->product_id )
                        ->withUnit( $procurementreturnProduct->unit_id )
                        ->first();

                    $quantity = $procurementreturnProduct->quantity;
                    $unitName = $procurementreturnProduct->unit->name;
                } else {
                    $fromUnit = $procurementreturnProduct->unit;
                    $toUnit = Unit::find( $procurementreturnProduct->convert_unit_id );

                    $quantity = $this->unitService->getConvertedQuantity(
                        from: $fromUnit,
                        to: $toUnit,
                        quantity: $procurementreturnProduct->quantity
                    );

                    $unitName = $toUnit->name;
                    $unitQuantity = ProductUnitQuantity::withProduct( $procurementreturnProduct->product_id )
                        ->withUnit( $toUnit->id )
                        ->first();
                }

                if ( $unitQuantity instanceof ProductUnitQuantity ) {
                    if ( floatval( $unitQuantity->quantity ) - floatval( $quantity ) < 0 ) {
                        throw new NotAllowedException(
                            sprintf(
                                __( 'Unable to delete the procurementreturn as there is not enough stock remaining for "%s" on unit "%s". This likely means the stock count has changed either with a sale, adjustment after the procurementreturn has been stocked.' ),
                                $procurementreturnProduct->product->name,
                                $unitName
                            )
                        );
                    }
                }
            } );
        }
    }

    /**
     * This will delete product available on a procurementreturn
     * and dispatch some events before and after that occurs.
     */
    public function deleteProcurementReturnProducts( ProcurementReturn $procurementreturn ): void
    {
        $procurementreturn->products->each( function ( ProcurementReturnProduct $product ) use ( $procurementreturn ) {
            $this->deleteProduct( $product, $procurementreturn );
        } );
    }

    /**
     * This helps to compute the unit value and the total cost
     * of a procurementreturn product. It return various value as an array of
     * the product updated along with an array of errors.
     */
    private function __computeProcurementReturnProductValues( array $data )
    {
        /**
         * @var ProcurementReturnProduct $procurementreturnProduct
         * @var $storeUnitReference
         * @var ProcurementReturn $procurementreturn
         * @var $itemsToSave
         * @var $item
         */
        extract( $data, EXTR_REFS );

        if ( $item->purchase_unit_type === 'unit' ) {
            extract( $this->__procureForSingleUnit( compact( 'procurementreturnProduct', 'storedUnitReference', 'itemsToSave', 'item' ) ) );
        } elseif ( $item->purchase_unit_type === 'unit-group' ) {
            if ( ! isset( $procurementreturnProduct->unit_id ) ) {
                /**
                 * this is made to ensure
                 * we have a self explanatory error,
                 * that describe why a product couldn't be processed
                 */
                $keys = array_keys( (array) $procurementreturnProduct );

                foreach ( $keys as $key ) {
                    if ( in_array( $key, [ 'id', 'sku', 'barcode' ] ) ) {
                        $argument = $key;
                        $identifier = $procurementreturnProduct->$key;
                        break;
                    }
                }

                $errors[] = [
                    'status' => 'error',
                    'message' => sprintf( __( 'Unable to have a unit group id for the product using the reference "%s" as "%s"' ), $identifier, $argument ),
                ];
            }

            try {
                extract( $this->__procureForUnitGroup( compact( 'procurementreturnProduct', 'storedunitReference', 'itemsToSave', 'item' ) ) );
            } catch ( Exception $exception ) {
                $errors[] = [
                    'status' => 'error',
                    'message' => $exception->getMessage(),
                    'data' => [
                        'product' => collect( $item )->only( [ 'id', 'name', 'sku', 'barcode' ] ),
                    ],
                ];
            }
        }

        return $data;
    }

    /**
     * This only save the product
     * but doesn't affect the stock.
     */
    public function saveProducts( ProcurementReturn $procurementreturn, Collection $products )
    {
        /**
         * We'll just make sure to have a reference
         * of all the product that has been procured.
         */
        $procuredProducts = $products->map( function ( $procuredProduct ) use ( $procurementreturn ) {
            $product = Product::find( $procuredProduct[ 'product_id' ] );

            if ( ! $product instanceof Product ) {
                throw new Exception( sprintf( __( 'Unable to find the product using the provided id "%s"' ), $procuredProduct[ 'product_id' ] ) );
            }

            if ( $product->stock_management === 'disabled' ) {
                throw new Exception( sprintf( __( 'Unable to procure the product "%s" as the stock management is disabled.' ), $product->name ) );
            }

            if ( $product->type === 'grouped' ) {
                throw new Exception( sprintf( __( 'Unable to procure the product "%s" as it is a grouped product.' ), $product->name ) );
            }

            /**
             * as the id might not always be provided
             * We'll find some record having an id set to 0
             * as not result will pop, that will create a new instance.
             */
            $procurementreturnProduct = ProcurementReturnProduct::find( $procuredProduct[ 'id' ] ?? 0 );

            if ( ! $procurementreturnProduct instanceof ProcurementReturnProduct ) {
                $procurementreturnProduct = new ProcurementReturnProduct;
            }

            /**
             * @todo these value might also
             * be calculated automatically.
             */
            $procurementreturnProduct->name = $product->name;
            $procurementreturnProduct->gross_purchase_price = $procuredProduct[ 'gross_purchase_price' ];
            $procurementreturnProduct->net_purchase_price = $procuredProduct[ 'net_purchase_price' ];
            $procurementreturnProduct->procurement_return_id = $procurementreturn->id;
            $procurementreturnProduct->product_id = $procuredProduct[ 'product_id' ];
            $procurementreturnProduct->purchase_price = $procuredProduct[ 'purchase_price' ];
            $procurementreturnProduct->quantity = $procuredProduct[ 'quantity' ];
            $procurementreturnProduct->available_quantity = $procuredProduct[ 'quantity' ];
            $procurementreturnProduct->tax_group_id = $procuredProduct[ 'tax_group_id' ] ?? 0;
            $procurementreturnProduct->tax_type = $procuredProduct[ 'tax_type' ];
            $procurementreturnProduct->tax_value = $procuredProduct[ 'tax_value' ];
            $procurementreturnProduct->expiration_date = $procuredProduct[ 'expiration_date' ] ?? null;
            $procurementreturnProduct->total_purchase_price = $procuredProduct[ 'total_purchase_price' ];
            $procurementreturnProduct->convert_unit_id = $procuredProduct[ 'convert_unit_id' ] ?? null;
            $procurementreturnProduct->unit_id = $procuredProduct[ 'unit_id' ];
            $procurementreturnProduct->author = Auth::id();
            $procurementreturnProduct->save();
            $procurementreturnProduct->barcode = str_pad( $product->barcode, 5, '0', STR_PAD_LEFT ) . '-' . str_pad( $procurementreturnProduct->unit_id, 3, '0', STR_PAD_LEFT ) . '-' . str_pad( $procurementreturnProduct->id, 3, '0', STR_PAD_LEFT );
            $procurementreturnProduct->save();

            event( new ProcurementReturnAfterSaveProductEvent( $procurementreturn, $procurementreturnProduct, $procuredProduct ) );

            return $procurementreturnProduct;
        } );

        return $procuredProducts;
    }

    /**
     * prepare the procurementreturn entry.
     */
    private function __procureForUnitGroup( array $data )
    {
        /**
         * @var $storeUnitReference
         * @var ProcurementReturnProduct $procurementreturnProduct
         * @var $storedBase
         * @var $item
         */
        extract( $data );

        if ( empty( $stored = @$storedUnitReference[ $procurementreturnProduct->unit_id ] ) ) {
            $unit = $this->unitService->get( $procurementreturnProduct->unit_id );
            $group = $this->unitService->getGroups( $item->purchase_unit_id ); // which should retrieve the group
            $base = $unit->base_unit ? $unit : $this->unitService->getBaseUnit( $group );
            $base_quantity = $this->unitService->computeBaseUnit( $unit, $base, $procurementreturnProduct->quantity );
            $storedBase[ $procurementreturnProduct->unit_id ] = compact( 'base', 'unit', 'group' );
        } else {
            extract( $stored );
            $base_quantity = $this->unitService->computeBaseUnit( $unit, $base, $procurementreturnProduct->quantity );
        }

        /**
         * let's check if the unit assigned
         * during the purchase is a sub unit of the
         * unit assigned to the item.
         */
        if ( $group->id !== $item->purchase_unit_id ) {
            throw new Exception( sprintf( __( 'The unit used for the product %s doesn\'t belongs to the Unit Group assigned to the item' ), $item->name ) );
        }

        $itemData = [
            'product_id' => $item->id,
            'unit_id' => $procurementreturnProduct->unit_id,
            'base_quantity' => $base_quantity,
            'quantity' => $procurementreturnProduct->quantity,
            'purchase_price' => $this->currency->value( $procurementreturnProduct->purchase_price )->get(),
            'total_purchase_price' => $this->currency->value( $procurementreturnProduct->purchase_price )->multiplyBy( $procurementreturnProduct->quantity )->get(),
            'author' => Auth::id(),
            'name' => $item->name,
        ];

        $itemsToSave[] = $itemData;

        return compact( 'itemsToSave', 'storedUnitReference' );
    }

    private function __procureForSingleUnit( $data )
    {
        extract( $data );

        /**
         * if the purchase unit id hasn't already been
         * recorded, then let's save it
         */
        if ( empty( $stored = @$storedUnitReference[ $item->purchase_unit_id ] ) ) {
            $unit = $this->unitService->get( $item->purchase_unit_id );
            $group = $unit->group;
            $base = $unit->base_unit ? $unit : $this->unitService->getBaseUnit( $group );
            $base_quantity = $this->unitService->computeBaseUnit( $unit, $base, $procurementreturnProduct->quantity );
            $storedUnitReference[ $item->purchase_unit_id ] = compact( 'base', 'unit' );
        } else {
            extract( $stored );
            $base_quantity = $this->unitService->computeBaseUnit( $unit, $base, $procurementreturnProduct->quantity );
        }

        $itemData = [
            'product_id' => $item->id,
            'unit_id' => $item->purchase_unit_id,
            'base_quantity' => $base_quantity,
            'quantity' => $procurementreturnProduct->quantity,
            'purchase_price' => $this->currency->value( $procurementreturnProduct->purchase_price )->get(),
            'total_price' => $this->currency->value( $procurementreturnProduct->purchase_price )->multiplyBy( $procurementreturnProduct->quantity )->get(),
            'author' => Auth::id(),
            'name' => $item->name,
        ];

        $itemsToSave[] = $itemData;

        return compact( 'itemsToSave', 'storedUnitReference' );
    }

    /**
     * save a defined procurementreturn products
     *
     * @param int procurementreturn id
     * @param array items
     * @return array;
     */
    public function saveProcurementReturnProducts( $procurement_return_id, $items )
    {
        $procuredItems = [];

        foreach ( $items as $item ) {
            $product = new ProcurementReturnProduct;

            foreach ( $item as $field => $value ) {
                $product->$field = $value;
            }

            $product->author = Auth::id();
            $product->procurement_return_id = $procurement_return_id;
            $product->save();

            $procuredItems[] = $product->toArray();
        }

        return [
            'status' => 'success',
            'message' => __( 'The operation has completed.' ),
            'data' => [
                'success' => $procuredItems,
            ],
        ];
    }

    /**
     * refresh a procurementreturn
     * by counting the total items & value
     *
     * @param  ProcurementReturn $provided procurementreturn
     * @return array
     */
    public function refresh( ProcurementReturn $procurementreturn )
    {
        /**
         * @var ProductService
         */
        $productService = app()->make( ProductService::class );

        ProcurementReturn::withoutEvents( function () use ( $procurementreturn, $productService ) {
            /**
             * Let's loop all procured produt
             * and get unit quantity if that exists
             * otherwise we'll create a new one.
             */
            $purchases = $procurementreturn
                ->products()
                ->get()
                ->map( function ( $procurementreturnProduct ) use ( $productService ) {
                    $unitPrice = 0;
                    $unit = $productService->getUnitQuantity( $procurementreturnProduct->product_id, $procurementreturnProduct->unit_id );

                    if ( $unit instanceof ProductUnitQuantity ) {
                        $unitPrice = $unit->sale_price * $procurementreturnProduct->quantity;
                    }

                    /**
                     * We'll return the total purchase
                     * price to update the procurementreturn total fees.
                     */
                    return [
                        'total_purchase_price' => $procurementreturnProduct->total_purchase_price,
                        'tax_value' => $procurementreturnProduct->tax_value,
                        'total_price' => $unitPrice,
                    ];
                } );

            $procurementreturn->cost = $purchases->sum( 'total_purchase_price' );
            $procurementreturn->tax_value = $purchases->sum( 'tax_value' );
            $procurementreturn->value = $purchases->sum( 'total_price' );
            $procurementreturn->total_items = count( $purchases );
            $procurementreturn->save();
        } );

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn has been refreshed.' ),
            'data' => compact( 'procurementreturn' ),
        ];
    }

    /**
     * delete procurementreturn
     * products
     *
     * @param ProcurementReturn
     * @return array
     */
    public function deleteProducts( ProcurementReturn $procurementreturn )
    {
        $procurementreturn->products->each( function ( $product ) {
            $product->delete();
        } );

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn products has been deleted.' ),
        ];
    }

    /**
     * helps to determine if a procurementreturn
     * includes a specific product using their id.
     * The ID of the product should be the one of the products of the procurementreturns
     *
     * @param int procurementreturn id
     * @param int product id
     */
    public function hasProduct( int $procurement_return_id, int $product_id )
    {
        $procurementreturn = $this->get( $procurement_return_id );

        return $procurementreturn->products->filter( function ( $product ) use ( $product_id ) {
            return (int) $product->id === (int) $product_id;
        } )->count() > 0;
    }

    /**
     * @deprecated
     */
    public function updateProcurementReturnProduct( $product_id, $fields )
    {
        $procurementreturnProduct = $this->getProcurementReturnProduct( $product_id );
        $item = $this->productService->get( $procurementreturnProduct->product_id );
        $storedUnitReference = [];
        $itemsToSave = [];

        /**
         * the idea here it to update the procurementreturn
         * quantity, unit_id and purchase price, since that information
         * is used on __computeProcurementReturnProductValues
         */
        foreach ( $fields as $field => $value ) {
            $procurementreturnProduct->$field = $value;
        }

        /**
         * @var array $itemsToSave
         * @var array errors
         */
        extract( $this->__computeProcurementReturnProductValues( compact( 'item', 'procurementreturnProduct', 'storeUnitReference', 'itemsToSave', 'errors' ) ) );

        /**
         * typically since the items to save should be
         * only a single entry, we'll harcode it to be "0"
         */
        foreach ( $itemsToSave[0] as $field => $value ) {
            $procurementreturnProduct->$field = $value;
        }

        $procurementreturnProduct->author = Auth::id();
        $procurementreturnProduct->save();

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn product has been updated.' ),
            'data' => [
                'product' => $procurementreturnProduct,
            ],
        ];
    }

    public function getProcurementReturnProduct( $product_id )
    {
        $product = ProcurementReturnProduct::find( $product_id );

        if ( ! $product instanceof ProcurementReturnProduct ) {
            throw new Exception( __( 'Unable to find the procurementreturn product using the provided id.' ) );
        }

        return $product;
    }

    /**
     * Delete a procurementreturn product
     *
     * @param int procurementreturn product id
     * @return array response
     */
    public function deleteProduct( ProcurementReturnProduct $procurementreturnProduct, ProcurementReturn $procurementreturn )
    {
        /**
         * this could be useful to prevent deletion for
         * product which might be in use by another resource
         */
        event( new ProcurementReturnBeforeDeleteProductEvent( $procurementreturnProduct ) );

        /**
         * we'll reduce the stock only if the
         * procurementreturn has been stocked.
         */
        if ( $procurementreturn->delivery_status === 'stocked' ) {
            /**
             * if the product was'nt convered into a different unit
             * then we'll directly perform a stock adjustment on that product.
             */
            if ( ! empty( $procurementreturnProduct->convert_unit_id ) ) {
                $from = Unit::find( $procurementreturnProduct->unit_id );
                $to = Unit::find( $procurementreturnProduct->convert_unit_id );
                $convertedQuantityToRemove = $this->unitService->getConvertedQuantity(
                    from: $from,
                    to: $to,
                    quantity: $procurementreturnProduct->quantity
                );

                $purchasePrice = $this->unitService->getPurchasePriceFromUnit(
                    purchasePrice: $procurementreturnProduct->purchase_price,
                    from: $from,
                    to: $to
                );

                $this->productService->stockAdjustment( ProductHistory::ACTION_DELETED, [
                    'total_price' => ns()->currency->define( $purchasePrice )->multipliedBy( $convertedQuantityToRemove )->toFloat(),
                    'unit_price' => $purchasePrice,
                    'unit_id' => $procurementreturnProduct->convert_unit_id,
                    'product_id' => $procurementreturnProduct->product_id,
                    'quantity' => $convertedQuantityToRemove,
                    'procurementreturnProduct' => $procurementreturnProduct,
                ] );
            } else {
                /**
                 * Record the deletion on the product
                 * history
                 */
                $this->productService->stockAdjustment( ProductHistory::ACTION_DELETED, [
                    'total_price' => $procurementreturnProduct->total_purchase_price,
                    'unit_price' => $procurementreturnProduct->purchase_price,
                    'unit_id' => $procurementreturnProduct->unit_id,
                    'product_id' => $procurementreturnProduct->product_id,
                    'quantity' => $procurementreturnProduct->quantity,
                    'procurementreturnProduct' => $procurementreturnProduct,
                ] );
            }
        }

        $procurementreturnProduct->delete();

        /**
         * the product has been deleted, so we couldn't pass
         * the Model Object anymore
         */
        event( new ProcurementReturnAfterDeleteProductEvent( $procurementreturnProduct->id, $procurementreturn ) );

        return [
            'status' => 'sucecss',
            'message' => sprintf(
                __( 'The product %s has been deleted from the procurementreturn %s' ),
                $procurementreturnProduct->name,
                $procurementreturn->name,
            ),
        ];
    }

    public function getProcurementReturnProducts( $procurement_return_id )
    {
        return ProcurementReturnProduct::getByProcurementReturn( $procurement_return_id )
            ->get();
    }

    /**
     * Update a procurementreturn products
     * using the provided product collection
     *
     * @param int procurementreturn id
     * @param array array
     * @return array status
     *
     * @deprecated
     */
    public function bulkUpdateProducts( $procurement_return_id, $products )
    {
        $productsId = $this->getProcurementReturnProducts( $procurement_return_id )
            ->pluck( 'id' );

        $result = collect( $products )
            ->map( function ( $product ) use ( $productsId ) {
                if ( ! in_array( $product[ 'id' ], $productsId ) ) {
                    throw new Exception( sprintf( __( 'The product with the following ID "%s" is not initially included on the procurementreturn' ), $product[ 'id' ] ) );
                }

                return $product;
            } )
            ->map( function ( $product ) {
                return $this->updateProcurementReturnProduct( $product[ 'id' ], $product );
            } );

        return [
            'status' => 'success',
            'message' => __( 'The procurementreturn products has been updated.' ),
            'data' => compact( 'result' ),
        ];
    }

    /**
     * Get the procurementreturns product
     *
     * @param int procurementreturn id
     */
    public function getProducts( $procurement_return_id ): EloquentCollection
    {
        $procurementreturn = $this->get( $procurement_return_id );

        return $procurementreturn->products;
    }

    public function setDeliveryStatus( ProcurementReturn $procurementreturn, string $status )
    {
        ProcurementReturn::withoutEvents( function () use ( $procurementreturn, $status ) {
            $procurementreturn->delivery_status = $status;
            $procurementreturn->save();
        } );
    }

    /**
     * When a procurementreturn is being made
     * this will actually save the history and update
     * the product stock
     *
     * @return void
     */
    public function handleProcurementReturn( ProcurementReturn $procurementreturn )
    {
        event( new ProcurementReturnBeforeHandledEvent( $procurementreturn ) );

        if ( $procurementreturn->delivery_status === ProcurementReturn::DELIVERED ) {
            $procurementreturn->products->map( function ( ProcurementReturnProduct $product ) {
                /**
                 * We'll keep an history of what has just happened.
                 * in order to monitor how the stock evolve.
                 */
                $this->productService->saveHistory( ProductHistory::ACTION_STOCKED, [
                    'procurement_return_id' => $product->procurement_return_id,
                    'product_id' => $product->product_id,
                    'procurementreturn_product_id' => $product->id,
                    'operation_type' => ProductHistory::ACTION_STOCKED,
                    'quantity' => $product->quantity,
                    'unit_price' => $product->purchase_price,
                    'total_price' => $product->total_purchase_price,
                    'unit_id' => $product->unit_id,
                ] );

                $currentQuantity = $this->productService->getQuantity(
                    $product->product_id,
                    $product->unit_id,
                    $product->id
                );

                $newQuantity = $this->currency
                    ->define( $currentQuantity )
                    ->additionateBy( $product->quantity )
                    ->get();

                $this->productService->setQuantity( $product->product_id, $product->unit_id, $newQuantity, $product->id );

                /**
                 * will generate a unique barcode for the procured product
                 */
                $this->generateBarcode( $product );

                /**
                 * We'll now check if the product is about to be
                 * converted in another unit
                 */
                if ( ! empty( $product->convert_unit_id ) ) {
                    $this->productService->convertUnitQuantities(
                        product: $product->product,
                        quantity: $product->quantity,
                        from: $product->unit,
                        procurementreturnProduct: $product,
                        to: Unit::find( $product->convert_unit_id )
                    );
                }
            } );

            $this->setDeliveryStatus( $procurementreturn, ProcurementReturn::STOCKED );
        }

        event( new ProcurementReturnAfterHandledEvent( $procurementreturn ) );
    }

    public function generateBarcode( ProcurementReturnProduct $procurementreturnProduct )
    {
        $this->barcodeService->generateBarcode(
            $procurementreturnProduct->barcode,
            BarcodeService::TYPE_CODE128
        );
    }

    /**
     * Make sure to procure procurementreturn that
     * are awaiting auto-submittion
     *
     * @return void
     */
    public function stockAwaitingProcurementReturns()
    {
        $startOfDay = $this->dateService->copy();
        $procurementreturns = ProcurementReturn::where( 'delivery_time', '<=', $startOfDay )
            ->pending()
            ->autoApproval()
            ->get();

        $procurementreturns->each( function ( ProcurementReturn $procurementreturn ) {
            $this->setDeliveryStatus( $procurementreturn, ProcurementReturn::DELIVERED );
            $this->handleProcurementReturn( $procurementreturn );
        } );

        if ( $procurementreturns->count() ) {
            ns()->notification->create( [
                'title' => __( 'ProcurementReturn Automatically Stocked' ),
                'identifier' => 'ns-warn-auto-procurementreturn',
                'url' => url( '/dashboard/procurementreturns' ),
                'description' => sprintf( __( '%s procurementreturn(s) has recently been automatically procured.' ), $procurementreturns->count() ),
            ] )->dispatchForGroup( [
                Role::namespace( 'admin' ),
                Role::namespace( 'nexopos.store.administrator' ),
            ] );
        }
    }

    public function getDeliveryLabel( $label )
    {
        switch ( $label ) {
            case ProcurementReturn::DELIVERED:
                return __( 'Delivered' );
            case ProcurementReturn::DRAFT:
                return __( 'Draft' );
            case ProcurementReturn::PENDING:
                return __( 'Pending' );
            case ProcurementReturn::STOCKED:
                return __( 'Stocked' );
            default:
                return $label;
        }
    }

    public function getPaymentLabel( $label )
    {
        switch ( $label ) {
            case ProcurementReturn::PAYMENT_PAID:
                return __( 'Paid' );
            case ProcurementReturn::PAYMENT_UNPAID:
                return __( 'Unpaid' );
            default:
                return $label;
        }
    }

    public function searchQuery()
    {
        return Product::query()
            ->whereIn( 'type', Hook::filter( 'ns-procurementreturn-searchable-product-type', [
                Product::TYPE_DEMATERIALIZED,
                Product::TYPE_MATERIALIZED,
            ]) )
            ->notGrouped()
            ->withStockEnabled()
            ->with( 'unit_quantities.unit' );
    }

    public function searchReturnQuery()
    {
        return ProcurementReturnProduct::query()
            ->with( 'procurementreturn', 'product.unit_quantities.unit' );
    }

    public function searchProcurementReturn($argument, $limit = 10, $providerId = null)
    {
        return $this->searchReturnQuery()
            ->limit($limit)
            ->whereHas('procurementreturn', function ($query) use ($argument, $providerId) {
                $query->where('invoice_reference', 'LIKE', "%{$argument}%");

                if ($providerId) {
                    $query->where('provider_id', $providerId);
                }
            })
            ->get()
            ->map(function ($product) {
                return $this->populateLoadedReturnProduct($product);
            });
    }

    public function searchProduct( $argument, $limit = 10 )
    {
        return $this->searchQuery()
            ->limit( $limit )
            ->where( function ( $query ) use ( $argument ) {
                $query->orWhere( 'name', 'LIKE', "%{$argument}%" )
                    ->orWhere( 'sku', 'LIKE', "%{$argument}%" )
                    ->orWhere( 'barcode', 'LIKE', "%{$argument}%" );
            } )
            ->get()
            ->map( function ( $product ) {
                return $this->populateLoadedProduct( $product );
            } );
    }

    public function populateLoadedProduct( $product )
    {
        $units = json_decode( $product->purchase_unit_ids );

        if ( $units ) {
            $product->purchase_units = collect();
            collect( $units )->each( function ( $unitID ) use ( &$product ) {
                $product->purchase_units->push( Unit::find( $unitID ) );
            } );
        }

        /**
         * We'll pull the last purchase
         * price for the item retreived
         */
        $product->unit_quantities->each( function ( $unitQuantity ) use ( $product ) {
            $unitQuantity->load( 'unit' );

            /**
             * just in case it's not a valid instance
             * we'll provide a default value "0"
             */
            $unitQuantity->last_purchase_price = $this->productService->getLastPurchasePrice( 
                product: $product,
                unit: $unitQuantity->unit
            );
        } );

        return $product;
    }

    public function populateLoadedReturnProduct( $product )
    {
        $units = json_decode( $product->purchase_unit_ids );

        if ( $units ) {
            $product->purchase_units = collect();
            collect( $units )->each( function ( $unitID ) use ( &$product ) {
                $product->purchase_units->push( Unit::find( $unitID ) );
            } );
        }

        /**
         * We'll pull the last purchase
         * price for the item retreived
         */
        // $product->unit_quantities->each( function ( $unitQuantity ) use ( $product ) {
        //     $unitQuantity->load( 'unit' );

        //     /**
        //      * just in case it's not a valid instance
        //      * we'll provide a default value "0"
        //      */
        //     $unitQuantity->last_purchase_price = $this->productService->getLastPurchasePrice( 
        //         product: $product,
        //         unit: $unitQuantity->unit
        //     );
        // } );

        return $product;
    }

    public function searchProcurementReturnProduct( $argument )
    {
        $procurementreturnProduct = ProcurementReturnProduct::where( 'barcode', $argument )
            ->with( [ 'unit', 'procurementreturn' ] )
            ->first();

        if ( $procurementreturnProduct instanceof ProcurementReturnProduct ) {
            $procurementreturnProduct->unit_quantity = $this->productService->getUnitQuantity(
                $procurementreturnProduct->product_id,
                $procurementreturnProduct->unit_id
            );
        }

        return $procurementreturnProduct;
    }

    public function preload( string $hash )
    {
        if ( Cache::has( 'procurementreturns-' . $hash ) ) {
            $data  =   Cache::get( 'procurementreturns-' . $hash );
            
            return [
                'items' =>  collect( $data[ 'items' ] )->map( function( $item ) use ( $data ) {
                    $query    =   $this->searchQuery()
                        ->where( 'id', $item[ 'product_id' ] )
                        ->whereHas( 'unit_quantities', function( $query ) use( $item ) {
                            $query->where( 'unit_id', $item[ 'unit_id' ] );
                        } );

                    $product   =   $query->first();

                    if ( $product instanceof Product ) {

                        /**
                         * This will be helpful to set the desired unit
                         * and quantity provided on the preload configuration.
                         */
                        $product->procurementreturn   =   new stdClass;
                        $product->procurementreturn->unit_id  =   $item[ 'unit_id' ];
                        $product->procurementreturn->quantity =   ns()->currency
                            ->define( $item[ 'quantity' ] )
                            ->multipliedBy( $data[ 'multiplier' ] )->toFloat();

                        return $this->populateLoadedProduct( $product );
                    }

                    return false;
                })->filter()
            ];
        }
        
        throw new NotFoundException( __( 'Unable to preload products. The hash might have expired or is invalid.' ) );
    }

    public function storePreload( string $hash, Collection | array $items, $expiration = 86400, $multiplier = 1 )
    {
        if ( ! empty( $items ) ) {
            $data       =   [];
            $data[ 'multiplier' ]   =   $multiplier;
            $data[ 'items' ]        =   $items;

            Cache::put( 'procurementreturns-' . $hash, $data, $expiration );
            
            return [
                'status' => 'success',
                'message' => __( 'The procurementreturn has been saved for later use.' ),
            ];
        }

        throw new Exception( __( 'Unable to save the procurementreturn for later use.' ) );
    }
}
