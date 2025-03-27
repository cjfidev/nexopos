<?php
namespace Modules\ServiceManagement\Crud;

use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use App\Services\Helper;
use App\Services\CrudService;
use App\Services\CrudEntry;
use App\Classes\CrudTable;
use App\Classes\CrudInput;
use App\Classes\FormInput;
use App\Classes\CrudForm;
use App\Crud\CustomerGroupCrud;
use App\Models\CustomerGroup;
use App\Exceptions\NotAllowedException;
use TorMorten\Eventy\Facades\Events as Hook;
use Modules\ServiceManagement\Models\Service;

class ServiceCrud extends CrudService
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
    protected $table = 'service_managements';

    /**
     * default slug
     * @param string
     */
    protected $slug = 'service-managements';

    /**
     * Define namespace
     * @param string
     */
    protected $namespace = 'service-management.services';

    /**
     * To be able to autoload the class, we need to define
     * the identifier on a constant.
     */
    const IDENTIFIER = 'service-management.services';

    /**
     * Model Used
     * @param string
     */
    protected $model = Service::class;

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
            list_title:  __( 'Services List' ),
            list_description:  __( 'Display all services.' ),
            no_entry:  __( 'No services has been registered' ),
            create_new:  __( 'Add a new service' ),
            create_title:  __( 'Create a new service' ),
            create_description:  __( 'Register a new service and save it.' ),
            edit_title:  __( 'Edit service' ),
            edit_description:  __( 'Modify  Service.' ),
            back_to_list:  __( 'Return to Services' ),
        );
    }

    /**
     * Defines the forms used to create and update entries.
     * @param Service $entry
     * @return array
     */
    public function getForm( Service $entry = null ): array
    {
        return CrudForm::form(
            main: CrudInput::text(
                label: __( 'Name' ),
                name: 'service_name',
                validation: 'required',
                value: $entry?->service_name,
                description: __( 'Provide a name to the resource.' ),
            ),
            tabs: CrudForm::tabs(
                CrudForm::tab(
                    identifier: 'general',
                    label: __( 'General' ),
                    fields: CrudForm::fields(
                        [
                            'type' => 'text',
                            'name' => 'service_price',
                            'value' => $entry?->service_price,
                            'label' => __( 'Price' ),
                            'description' => __( 'Provide the service price.' ),
                            'validation' => 'required', 
                        ],
                        [
                            'type' => 'search-select',
                            'name' => 'group_id',
                            'value' => $entry?->group_id,
                            'label' => __( 'Group' ),
                            'description' => __( 'Assign the customer to a group.' ),
                            'validation' => 'required',
                            // 'component' => 'nsCrudForm',
                            'props' => CustomerGroupCrud::getFormConfig(),
                            'options' => Helper::toJsOptions( CustomerGroup::all(), [ 'id', 'name' ] ),
                        ],
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
    public function filterPutInputs( array $inputs, Service $entry )
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
    public function afterPost( array $request, Service $entry ): array
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
    public function beforePut( array $request, Service $entry ): array
    {
        $this->allowedTo( 'update' );

        return $request;
    }

    /**
     * This trigger actions that are executed after
     * the crud entry is successfully updated.
     */
    public function afterPut( array $request, Service $entry ): array
    {
        return $request;
    }

    /**
     * This triggers actions that will be executed ebfore
     * the crud entry is deleted.
     */
    public function beforeDelete( $namespace, $id, $model ): void
    {
        if ( $namespace == 'service-management.services' ) {
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
            CrudTable::column( __( 'Name' ), 'service_name' ),
            CrudTable::column( __( 'Price' ), 'service_price' ),
            CrudTable::column( __( 'Created At' ), 'created_at' ),
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
            url: ns()->url( '/api/crud/service-management.services/' . $entry->id ),
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
                if ( $entity instanceof Service ) {
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
            list:  ns()->url( 'dashboard/' . 'service-managements' ),
            create:  ns()->url( 'dashboard/' . 'service-managements/create' ),
            edit:  ns()->url( 'dashboard/' . 'service-managements/edit/{id}' ),
            post:  ns()->url( 'api/crud/' . 'service-management.services' ),
            put:  ns()->url( 'api/crud/' . 'service-management.services/{id}' . '' ),
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
