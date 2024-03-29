# Elorest
Laravel eloquent REST API package

With it we can query the restapi using the laravel eloquent commands (methods & params)

Borrow the laravel eloquent syntax (methodes & params), including laravel pagination.

please check the laravel's eloquent documentation https://laravel.com/docs

Example queries :

    if the model namespace is "App\Models"
    https://your-domain-name/api/elorest/Models/Post?leftJoin=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=*&get=
    https://your-domain-name/api/elorest/Models/Post?join[]=authors,posts.id,authors.author_id&join[]=comments,posts.id,comments.post_id&whereIn=category_id,[2,4,5]&select=posts.*,authors.name as author_name,comments.title as comment_title&get=
    https://your-domain-name/api/elorest/Models/Post?&with=author,comment&get=*
    https://your-domain-name/api/elorest/Models/Post?&with=author(where=name,like,%dandisy%),comment&get=*
    multiple first level of nested closure deep
    https://your-domain-name/api/elorest/Models/Post?&with=author(where=name,like,%dandisy%)(where=nick,like,%dandisy%),comment&get=*
    the second level of nested closure deep
    https://your-domain-name/api/elorest/Models/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*
    https://your-domain-name/api/elorest/Models/Post?&with[]=author(where=name,like,%dandisy%)&with[]=comment(where=title,like,%test%)&get=*
    https://your-domain-name/api/elorest/Models/Post?paginate=10&page=1

    if the model namespace only "App"
    https://your-domain-name/api/elorest/User?paginate=10&page=1

    for sortBy by related field
    https://your-domain-name/api/elorest/Models/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*&sortBy=category.0.name
    https://your-domain-name/api/elorest/Models/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*&sortByDesc=category.0.name

    for select specific field/s in clousure of "with" command don't forget to include the foreign key to resolve the relationship, otherwise you'll get zero results for your relation
    http://localhost/gamify/public/api/elorest/Models/Character?with[]=categories.category&with[]=datasources(select=id,model_id,value)&with[]=user&get=*

    upload file
    https://your-domain-name/api/elorest/upload
    
    whereHas for where query in related clousure
    http://localhost/online-shop/public/api/elorest/Models/Merchant?with[]=cartItems.product.category&with[]=cartItems(where=created_by,1)&whereHas=cartItems(where=created_by,1)&get=*

    whereRaw
    http://localhost/online-shop/public/api/elorest/Models/Merchant?whereNotNull=label_id&whereRaw=find_in_set('3',label_id)&orWhereRaw=find_in_set('19',label_id)&get=*

### Installation

    composer require dandisy/elorest

### Usage

##### Paste it (elorest route) to your laravel project (routes/api.php)

    Elorest::routes();

    or with middleware

    Elorest::routes([
        'middleware' => ['auth:api', 'throttle:60,1'],
        // 'only' => ['post', 'put', 'patch', 'delete'],
        'except' => ['get']
    ]);


##### Exception handling 

Using laravel errors handler (https://laravel.com/docs/8.x/errors)

Publish exception hendler class to your laravel webcore project

    php artisan vendor:publish --provider="Dandisy\Elorest\ElorestServiceProvider" --force

##### Authentication
  
Using laravel passport (https://laravel.com/docs/8.x/passport)

##### Authorization

Using laravel gates & policies (https://laravel.com/docs/8.x/authorization)

Create policy for each model

    php artisan make:policy PostPolicy --model=Post

Register policies

    class AuthServiceProvider extends ServiceProvider
    {
        /**
        * The policy mappings for the application.
        *
        * @var array
        */
        protected $policies = [
            Post::class => PostPolicy::class,
        ];
    
        /**
        * Register any application authentication / authorization services.
        *
        * @return void
        */
        public function boot()
        {
            $this->registerPolicies();
    
            //
        }
    }

or use Policy Auto-Discovery

    use Illuminate\Support\Facades\Gate;
 
    Gate::guessPolicyNamesUsing(function ($modelClass) {
        // return policy class name...
    });

##### Model

to prevent some field/s being set (for ignoring fields, not for authorization)

    ...

    public static $elorestPreventSetOnCreate = [
        'user_id', 'phone', 'id_card',
    ];

    public static $elorestPreventSetOnUpdate = [
        'user_id', 'id_card',
    ];

    ...

insert additional logic to the "Elorest" add this methods and property in your Model class if you need

    ...

    /**
     * Elorest file fields
     *
     */
    public static elorestFileFields = [
        'file_url', 'video_url'
    ]

    /**
     * Elorest before
     *
     * @param Request $request
     */
    public function elorestBefore($request) {
        // write your code here;
    }

    /**
     * Elorest append
     *
     * @param Post $post
     */
    public function elorestAppend($post) {
        // write your code here;
    }

    /**
     * Elorest after
     *
     * @param Request $request
     * @param Post $post
     */
    public function elorestAfter($request, $post) {
        // write your code here;
    }

    ...

##### Policy

to check user authorization

    $check = false; // false first

    ...

    return $user->id && $check || $user->is_admin;

to disable hidden field/s

    public function viewAny(User $user, Model $data)
    {
        ...

        if($user->id == $userId || $user->is_admin) {
            $data->elorestDisableHiddenProperty = true;
        }

        ...
    }

##### Formatable JSON response (Beta)

see the sample/response_format.blade.php file

### Documentation

Get All

    https://your-domain-name/api/elorest/Models/Post

    for

    App\Models\Post::all();

Find By ID

    https://your-domain-name/api/elorest/Models/Post/7

    for

    App\Models\Post::find(7);

Get Where and First

    https://your-domain-name/api/elorest/Models/Author?where=name,like,%dandi setiyawan%&select=*&first=

    for

    App\Models\Author::where('name', 'like', '%dandi setiyawan%')->first();

Get Count (aggregate)

    https://your-domain-name/api/elorest/Models/Author?count=*

    for

    App\Models\Author::count();

Get Join (multi join)

    https://your-domain-name/api/elorest/Models/User?join[]=contacts,users.id,contacts.user_id&join[]=orders,users.id,orders.user_id&select=users.*,contacts.phone as user_phone,orders.price as order_price&get=

    for

    App\Model\User::join('contacts', 'users.id', '=', 'contacts.user_id')
        ->join('orders', 'users.id', '=', 'orders.user_id')
        ->select('users.*', 'contacts.phone', 'orders.price')
        ->get();

Get WhereIn

    https://your-domain-name/api/elorest/Models/Author?whereIn=id,[1,2,3]&get=*

    for

    App\Models\Author::whereNotIn('id', [1, 2, 3])
        ->get(*);

Get Multi With and Where (multi nested closure)

    https://your-domain-name/api/elorest/Models/Post?&with=author(with=city(where=name,like,%jakarta%)),comment&get=*

    for

    App\Models\Post::with(['author' => function($query) {
        $query->with(['city' => function($query) {
            $query->where('name', 'like', %jakarta%)
        }]);
    }])
    ->with('comment')
    ->get(*);

Update By ID

    var settings = {
        "async": true,
        "crossDomain": true,
        "url": "http://localhost/webcore/public/api/elorest/User/2",
        "method": "PUT",
        "headers": {
            "content-type": "application/json",
            "cache-control": "no-cache",
            "postman-token": "833c6c7e-87f7-e527-e1ba-62a21aa39aff"
        },
        "processData": false,
        "data": {votes: 1}
    }

    $.ajax(settings).done(function (response) {
        console.log(response);
    });

Update Where (* in laravel 10, this will only work without trailing 'get' or 'first' function)

    PUT /webcore/public/api/elorest/User?where=email,dandi@sgdigitals.com&amp;select=*&amp;first= HTTP/1.1
    Host: localhost
    Content-Type: application/json
    Cache-Control: no-cache
    Postman-Token: fa3347b0-1f44-8622-a078-42b1369bbdd7

    {
        "votes": 1
    }

    or

    var data = JSON.stringify({
        "votes": 1
    });

    var xhr = new XMLHttpRequest();
    xhr.withCredentials = true;

    xhr.addEventListener("readystatechange", function () {
        if (this.readyState === 4) {
            console.log(this.responseText);
        }
    });

    xhr.open("PUT", "http://localhost/webcore/public/api/elorest/User?where=email,dandi@sgdigitals.com&select=*&first=");
    xhr.setRequestHeader("content-type", "application/json");
    xhr.setRequestHeader("cache-control", "no-cache");
    xhr.setRequestHeader("postman-token", "0ea7fa6a-b7d2-73e2-81e6-10ca983de686");

    xhr.send(data);

    for

    App\Models\Author::where('email', 'dandi@sgdigitals.com')
        ->update(['votes' => 1]);

### Extensible

    - create your classes inherit or implement from the Elorest artifacts
    - override or create new methods
    - register your route

### Notes

    add a public property "elorest = true or false" to the model class to enable or disable Elorest for that model
    include the header Accept: application/json in the http request
    for PUT/PATCH requests, use Content-type: application/x-www-form-urlencoded or application/json and for updating file, use POST with request body containing _method = PUT or PATCH

### Refs

    - 201	New resource has been created successfully
    - 400	Bad request (something wrong with URL or parameters)
    - 401	Not authorized (not logged in)
    - 403	Logged in but access to requested area is forbidden
    - 404	Not Found (page or other resource doesn’t exist)
    - 410	Resource not avalibale
    - 422	Unprocessable Entity (validation failed)
    - 500	General server error
