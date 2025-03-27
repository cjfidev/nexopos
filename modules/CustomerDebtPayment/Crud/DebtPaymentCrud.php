<?php
namespace Modules\CustomerDebtPayment\Crud;

use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\CustomerDebtSummary;
use Illuminate\Http\Request;
use App\Services\CrudService;
use App\Services\CrudEntry;
use App\Classes\CrudTable;
use App\Classes\CrudInput;
use App\Classes\CrudForm;
use App\Crud\UserCrud;
use App\Services\Helper;
use App\Exceptions\NotAllowedException;
use TorMorten\Eventy\Facades\Events as Hook;
use App\Models\CustomerDebtPayment;

class DebtPaymentCrud extends CrudService
{
    /**
     * Defines if the crud class should be automatically discovered.
     * If set to "true", no need register that class on the "CrudServiceProvider".
     */
    const AUTOLOAD = true;

    /**
     * define the base table
     * @param string
     */
    protected $table = 'nexopos_customers_debt_payments';

    /**
     * default slug
     * @param string
     */
    protected $slug = 'customers-debt-payments';

    /**
     * Define namespace
     * @param string
     */
    protected $namespace = 'customers.debt-payments';

    /**
     * To be able to autoload the class, we need to define
     * the identifier on a constant.
     */
    const IDENTIFIER = 'customers.debt-payments';

    /**
     * Model Used
     * @param string
     */
    protected $model = CustomerDebtPayment::class;

    /**
     * Define permissions
     * @param array
     */
    protected $permissions  =   [
        'create'    =>  true,
        'read'      =>  true,
        'update'    =>  true,
        'delete'    =>  true,
    ];

    /**
     * Adding relation
     * Example : [ 'nexopos_users as user', 'user.id', '=', 'nexopos_orders.author' ]
     * Other possible combinatsion includes "leftJoin", "rightJoin", "innerJoin"
     *
     * Left Join Example
     * public $relations = [
     *  'leftJoin' => [
     *      [ 'nexopos_users as user', 'user.id', '=', 'nexopos_orders.author' ]
     *  ]
     * ];
     *
     * @param array
     */
    public $relations   =  [
            ];

    /**
     * all tabs mentionned on the tabs relations
     * are ignored on the parent model.
     */
    protected $tabsRelations    =   [
        // 'tab_name'      =>      [ YourRelatedModel::class, 'localkey_on_relatedmodel', 'foreignkey_on_crud_model' ],
    ];

    /**
     * Export Columns defines the columns that
     * should be included on the exported csv file.
     */
    protected $exportColumns = []; // @getColumns will be used by default.

    /**
     * Pick
     * Restrict columns you retrieve from relation.
     * Should be an array of associative keys, where
     * keys are either the related table or alias name.
     * Example : [
     *      'user'  =>  [ 'username' ], // here the relation on the table nexopos_users is using "user" as an alias
     * ]
     */
    public $pick = [];

    /**
     * Define where statement
     * @var array
    **/
    protected $listWhere = [];

    /**
     * Define where in statement
     * @var array
     */
    protected $whereIn = [];

    /**
     * If few fields should only be filled
     * those should be listed here.
     */
    public $fillable = [];

    /**
     * If fields should be ignored during saving
     * those fields should be listed here
     */
    public $skippable = [];

    /**
     * Determine if the options column should display
     * before the crud columns
     */
    protected $prependOptions = false;

    /**
     * Will make the options column available per row if
     * set to "true". Otherwise it will be hidden.
     */
    protected $showOptions = true;

    /**
     * In case this crud instance is used on a search-select field,
     * the following attributes are used to auto-populate the "options" attribute.
     */
    protected $optionAttribute = [
        'value' => 'id',
        'label' => 'name'
    ];

    /**
     * Return the label used for the crud object.
    **/
    public function getLabels(): array
    {
        return CrudTable::labels(
            list_title:  __( 'Debt Payments List' ),
            list_description:  __( 'Display all debt payments.' ),
            no_entry:  __( 'No debt payments has been registered' ),
            create_new:  __( 'Add a new debt payment' ),
            create_title:  __( 'Create a new debt payment' ),
            create_description:  __( 'Register a new debt payment and save it.' ),
            edit_title:  __( 'Edit debt payment' ),
            edit_description:  __( 'Modify  Debt payment.' ),
            back_to_list:  __( 'Return to Debt Payments' ),
        );
    }

    /**
     * Defines the forms used to create and update entries.
     * @param CustomerDebtPayment $entry
     * @return array
     */
    public function getForm( CustomerDebtPayment $entry = null ): array
    {
        return CrudForm::form(
            main: CrudInput::text(
                label: __( 'Name' ),
                name: 'name',
                validation: 'required',
                description: __( 'Provide a name to the resource.' ),
            ),
            tabs: CrudForm::tabs(
                CrudForm::tab(
                    identifier: 'general',
                    label: __( 'General' ),
                    fields: CrudForm::fields(                        
                        CrudInput::searchSelect(
                            label: __( 'Customer' ),
                            name: 'customer_id',
                            validation: 'required',
                            options: Helper::toJsOptions( User::all(), [ 'id', 'first_name' ] ),
                            description: __( 'Provide a name to the resource.' ),
                        ),
                        CrudInput::date(
                            label: __( 'Payment Date' ),
                            name: 'payment_date',
                            validation: 'required',
                            value: $entry->delivery_time ?? ns()->date->now()->format( 'Y-m-d' ),
                            description: __( 'Provide a name to the resource.' ),
                        ),
                        CrudInput::select(
                            label: __( 'Debt Remaining' ),
                            name: 'debt_remaining',
                            validation: 'required',
                            options: Helper::toJsOptions( CustomerDebtSummary::all(), [ 'customer_id', 'total_debt' ] ),
                            description: __( 'Provide a name to the resource.' ),
                        ),
                        CrudInput::text(
                            label: __( 'Amount Paid' ),
                            name: 'amount_paid',
                            validation: 'required',
                            description: __( 'Provide a name to the resource.' ),
                        ),                        
                            )
                )
            )
        );
    }

    /**
     * Filter POST input fields
     * @param array of fields
     * @return array of fields
     */
    public function filterPostInputs( $inputs ): array
    {
        return $inputs;
    }

    /**
     * Filter PUT input fields
     * @param array of fields
     * @return array of fields
     */
    public function filterPutInputs( array $inputs, CustomerDebtPayment $entry )
    {
        return $inputs;
    }

    /**
     * Trigger actions that are executed before the
     * crud entry is created.
     */
    public function beforePost( array $request ): array
    {
        $this->allowedTo( 'create' );

        return $request;
    }

    /**
     * Trigger actions that will be executed 
     * after the entry has been created.
     */
    public function afterPost( array $request, CustomerDebtPayment $entry ): array
    {
        return $request;
    }


    /**
     * A shortcut and secure way to access
     * senstive value on a read only way.
     */
    public function get( string $param ): mixed
    {
        switch( $param ) {
            case 'model' : return $this->model ; break;
        }
    }

    /**
     * Trigger actions that are executed before
     * the crud entry is updated.
     */
    public function beforePut( array $request, CustomerDebtPayment $entry ): array
    {
        $this->allowedTo( 'update' );

        return $request;
    }

    /**
     * This trigger actions that are executed after
     * the crud entry is successfully updated.
     */
    public function afterPut( array $request, CustomerDebtPayment $entry ): array
    {
        return $request;
    }

    /**
     * This triggers actions that will be executed ebfore
     * the crud entry is deleted.
     */
    public function beforeDelete( $namespace, $id, $model ): void
    {
        if ( $namespace == 'customers.debt-payments' ) {
            /**
             *  Perform an action before deleting an entry
             *  In case something wrong, this response can be returned
             *
             *  return response([
             *      'status'    =>  'danger',
             *      'message'   =>  __( 'You\re not allowed to do that.' )
             *  ], 403 );
            **/
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }
        }
    }

    /**
     * Define columns and how it is structured.
     */
    public function getColumns(): array
    {
        return CrudTable::columns(
                        CrudTable::column(
                identifier: 'id',
                label: __( 'Id' ),
            ),
                        CrudTable::column(
                identifier: 'customer_id',
                label: __( 'Customer ID' ),
            ),
                        CrudTable::column(
                identifier: 'payment_date',
                label: __( 'Payment Date' ),
            ),
                        CrudTable::column(
                identifier: 'amount_paid',
                label: __( 'Amount Paid' ),
            ),
                        CrudTable::column(
                identifier: 'debt_remaining',
                label: __( 'Debt Remaining' ),
            ),
                        CrudTable::column(
                identifier: 'author',
                label: __( 'Author' ),
            ),
                        CrudTable::column(
                identifier: 'created_at',
                label: __( 'Created At' ),
            ),
                        CrudTable::column(
                identifier: 'updated_at',
                label: __( 'Updated At' ),
            ),
                    );
    }

    /**
     * Define row actions.
     */
    public function setActions( CrudEntry $entry ): CrudEntry
    {
        /**
         * Declaring entry actions
         */
        $entry->action( 
            identifier: 'edit',
            label: __( 'Edit' ),
            url: ns()->url( '/dashboard/' . $this->slug . '/edit/' . $entry->id )
        );
        
        $entry->action( 
            identifier: 'delete',
            label: __( 'Delete' ),
            type: 'DELETE',
            url: ns()->url( '/api/crud/customers.debt-payments/' . $entry->id ),
            confirm: [
                'message'  =>  __( 'Would you like to delete this ?' ),
            ]
        );
        
        return $entry;
    }


    /**
     * trigger actions that are executed
     * when a bulk actio is posted.
     */
    public function bulkAction( Request $request ): array
    {
        /**
         * Deleting licence is only allowed for admin
         * and supervisor.
         */

        if ( $request->input( 'action' ) == 'delete_selected' ) {

            /**
             * Will control if the user has the permissoin to do that.
             */
            if ( $this->permissions[ 'delete' ] !== false ) {
                ns()->restrict( $this->permissions[ 'delete' ] );
            } else {
                throw new NotAllowedException;
            }

            $status     =   [
                'success'   =>  0,
                'error'    =>  0
            ];

            foreach ( $request->input( 'entries' ) as $id ) {
                $entity     =   $this->model::find( $id );
                if ( $entity instanceof CustomerDebtPayment ) {
                    $entity->delete();
                    $status[ 'success' ]++;
                } else {
                    $status[ 'error' ]++;
                }
            }
            return $status;
        }

        return Hook::filter( $this->namespace . '-catch-action', false, $request );
    }

    /**
     * Defines links used on the CRUD object.
     */
    public function getLinks(): array
    {
        return  CrudTable::links(
            list:  ns()->url( 'dashboard/' . 'customers-debt-payments' ),
            create:  ns()->url( 'dashboard/' . 'customers-debt-payments/create' ),
            edit:  ns()->url( 'dashboard/' . 'customers-debt-payments/edit/' ),
            post:  ns()->url( 'api/crud/' . 'customers.debt-payments' ),
            put:  ns()->url( 'api/crud/' . 'customers.debt-payments/{id}' . '' ),
        );
    }

    /**
     * Defines the bulk actions.
    **/
    public function getBulkActions(): array
    {
        return Hook::filter( $this->namespace . '-bulk', [
            [
                'label'         =>  __( 'Delete Selected Entries' ),
                'identifier'    =>  'delete_selected',
                'url'           =>  ns()->route( 'ns.api.crud-bulk-actions', [
                    'namespace' =>  $this->namespace
                ])
            ]
        ]);
    }

    /**
     * Defines the export configuration.
    **/
    public function getExports(): array
    {
        return [];
    }
}
