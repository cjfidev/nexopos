<?php

namespace App\Models;

use App\Events\ProcurementReturnAfterCreateEvent;
use App\Events\ProcurementReturnAfterDeleteEvent;
use App\Events\ProcurementReturnAfterUpdateEvent;
use App\Events\ProcurementReturnBeforeDeleteEvent;
use App\Events\ProcurementReturnBeforeUpdateEvent;
use Illuminate\Database\Eloquent\Factories\HasFactory;

/**
 * @property int            $id
 * @property mixed          $name
 * @property int            $provider_id
 * @property float          $value
 * @property float          $cost
 * @property float          $tax_value
 * @property mixed          $invoice_reference
 * @property bool           $automatic_approval
 * @property \Carbon\Carbon $delivery_time
 * @property \Carbon\Carbon $invoice_date
 * @property mixed          $payment_status
 * @property mixed          $delivery_status
 * @property int            $total_items
 * @property string         $description
 * @property int            $author
 * @property mixed          $uuid
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 */
class ProcurementReturn extends NsModel
{
    use HasFactory;

    protected $table = 'nexopos_' . 'procurements_returns';

    /**
     * this status mention when the procurement
     * has been received
     *
     * @param string
     */
    const DELIVERED = 'delivered';

    /**
     * this status state when the procurement has
     * moved from the "draft" status to pending. Which
     * means it's ready to be processed or it's being proceesed.
     *
     * @param string
     */
    const PENDING = 'pending';

    /**
     * The procurement is in draft mode. Means it's not yet ready
     * to be processed or send to the provider
     *
     * @param string
     */
    const DRAFT = 'draft';

    /**
     * The procurement has affected the actual products stock.
     * Here the procurement has been delivered and integrated to the stock.
     *
     * @param string
     */
    const STOCKED = 'stocked';

    /**
     * The procurement hasn't been paid.
     */
    const PAYMENT_UNPAID = 'unpaid';

    /**
     * The procurement has been paid.
     */
    const PAYMENT_PAID = 'paid';

    protected $dispatchesEvents = [
        'creating' => ProcurementReturnAfterCreateEvent::class,
        'created' => ProcurementReturnAfterCreateEvent::class,
        'deleting' => ProcurementReturnBeforeDeleteEvent::class,
        'updating' => ProcurementReturnBeforeUpdateEvent::class,
        'updated' => ProcurementReturnAfterUpdateEvent::class,
        'deleted' => ProcurementReturnAfterDeleteEvent::class,
    ];

    public function transactionHistories()
    {
        return $this->hasMany( TransactionHistory::class, 'procurement_id' );
    }

    public function products()
    {
        return $this->hasMany( ProcurementReturnProduct::class, 'procurement_id' );
    }

    public function provider()
    {
        return $this->belongsTo( Provider::class );
    }

    public function scopePending( $query )
    {
        return $query->where( 'delivery_status', self::PENDING );
    }

    public function scopeAutoApproval( $query )
    {
        return $query->where( 'automatic_approval', true );
    }
}
