# Eloquent Filter


![alt text](./eloquent-filter.jpg "eloquent-filter")

[![Latest Stable Version](https://poser.pugx.org/mehdi-fathi/eloquent-filter/v/stable)](https://packagist.org/packages/mehdi-fathi/eloquent-filter)
![Run tests](https://github.com/mehdi-fathi/eloquent-filter/workflows/Run%20tests/badge.svg?branch=master)
[![License](https://poser.pugx.org/mehdi-fathi/eloquent-filter/license)](https://packagist.org/packages/mehdi-fathi/eloquent-filter)
[![GitHub stars](https://img.shields.io/github/stars/mehdi-fathi/eloquent-filter)](https://github.com/mehdi-fathi/eloquent-filter/stargazers)
[![StyleCI](https://github.styleci.io/repos/149638067/shield?branch=master)](https://github.styleci.io/repos/149638067)
[![Build Status](https://travis-ci.org/mehdi-fathi/eloquent-filter.svg?branch=master)](https://travis-ci.org/mehdi-fathi/eloquent-filter)
[![Total Downloads](https://poser.pugx.org/mehdi-fathi/eloquent-filter/downloads)](//packagist.org/packages/mehdi-fathi/eloquent-filter)

A package for filter data of models by query string. Easy to use and full dynamic.

The Eloquent Filter is stable on PHP 7.1,7.2,7.3,7.4 and Laravel 5.x,6.x,7.x.

## Installation

1- Run the Composer command

      $ composer require mehdi-fathi/eloquent-filter
2- Add `eloquentFilter\ServiceProvider::class` to provider app.php
   
   ```php
   'providers' => [
     /*
      * Package Service Providers...
      */
       eloquentFilter\ServiceProvider::class
   ],
   ```
3- Add Facade `'EloquentFilter' => eloquentFilter\Facade\EloquentFilter::class` to aliases app.php

```php
'alias' => [
  /*
   * Facade alias...
   */
    'EloquentFilter' => eloquentFilter\Facade\EloquentFilter::class,
],
```
That's it enjoy!
## Basic Usage

Add Filterable trait to your models and set fields that you will want filter in whitelist.You can override this method in your models.

```php
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class User extends Model
{
    use Filterable;
    
    private static $whiteListFilter =[
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
    ];
}
```
You can set `*` char for filter in all fields as like below example:
 
```php
private static $whiteListFilter = ['*'];
```
You can add or set `$whiteListFilter` on the fly in your method.For example:

#### Set array to WhiteListFilter
Note that this method override `$whiteListFilter`
```php
User::setWhiteListFilter(['name']); 
```
#### Add new field to WhiteListFilter
```php
User::addWhiteListFilter('name'); 
```

### Use in your controller

Change your code on controller as like below example:

```php

namespace App\Http\Controllers;

/**
 * Class UsersController.
 */
class UsersController
{

    public function list()
    {
          if (!empty(request()->get('username'))) {
          
              $users = User::ignoreRequest('perpage')->filter()->with('posts')
                        ->orderByDesc('id')->paginate(request()->get('perpage'),['*'],'page');

          } else {
              $users = User::filter(
                [
                'username' => ['mehdi','ali']
                ]           
                )->with('posts')->orderByDesc('id')->paginate(10,['*'],'page');
          }
    }
}
```
- Note that Eloquent filter by default using query string to make query. Also you can set array to `filter` method Model for make
your own custom condition without querystring.

- Note that you must unset your own param as perpage. Just you can set page param for paginate this param ignore from filter.

You can ignore some request param by use of code it

```php

User::ignoreRequest(['perpage'])->filter()
            ->paginate(request()->get('perpage'), ['*'], 'page');
```

Call `ignoreRequest` will ignore some request that you don't want to use in conditions eloquent filter.
For example perpage param will never be in the conditions eloquent filter. it's releated to paginate method.
`page` param ignore by default in the Eloquent filter.
 

### Simple Examples

You just pass data blade form to query string or generate query string in controller method.For example:

**Simple Where**
```
/users/list?email=mehdifathi.developer@gmail.com

SELECT ... WHERE ... email = 'mehdifathi.developer@gmail.com'
```

```
/users/list?first_name=mehdi&last_name=fathi

SELECT ... WHERE ... first_name = 'mehdi' AND last_name = 'fathi'
```

```
/users/list?username[]=ali&username[]=ali22&family=ahmadi

SELECT ... WHERE ... username = 'ali' OR username = 'ali22' AND family = 'ahmadi'
```

***Where like***

If you are going to make query by like conditions. You can do it that by this example.

```
/users/list?first_name[like]=%John%

SELECT ... WHERE ... first_name LIKE '%John%'

```

***Where by operator***

You can set any operator mysql in query string.

```
/users/list?count_posts[operator]=>&count_posts[value]=35

SELECT ... WHERE ... count_posts > 35
```
```
/users/list?username[operator]=!=&username[value]=ali

SELECT ... WHERE ... username != 'ali'
```
```
/users/list?count_posts[operator]=<&count_posts[value]=25

SELECT ... WHERE ... count_posts < 25
```

**Where nested relation Model (New feature)**

-You can set all nested relation in the query string just by array query string.For example, the users model has a relation with posts.
 and posts table has relation with orders. You can make query condition by set `posts[count_post]` and `posts[orders][name]`
 in the query string. Just be careful you must set `posts.count_post` and `posts.orders.name` in the User model.

```php
use eloquentFilter\QueryFilter\ModelFilters\Filterable;

class User extends Model
{
    use Filterable;
   
    private static $whiteListFilter =[
        'username',
        'posts.count_post',
        'posts.orders.name',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\belongsTo
     */
    public function posts()
    {
        return $this->belongsTo('Tests\Models\Post');
    }

}
``` 

```
/users/list?posts[count_post]=876&username=mehdi

select * from "users" where exists 
         (select * from "posts" where 
         "posts"."user_id" = "users"."id" 
         and "posts"."count_post" = 876)
         and "username" = "mehdi"

```
-The above example as the same code that you use without eloquent filter. Check it under code

```php
$user = new User();
$builder = $user->with('posts');
        $builder->whereHas('posts', function ($q) {
            $q->where('count_post', 876);
        })->where('username','mehdi');

```

****Special Params****

You can set special params `limit` and `orderBy` in query string for make query by that.
```
/users/list?f_params[limit]=1

SELECT ... WHERE ... order by `id` desc limit 1 offset 0
```

```
/users/list?f_params[orderBy][field]=id&f_params[orderBy][type]=ASC

SELECT ... WHERE ... order by `id` ASC limit 10 offset 0
```
***Where between***

If you are going to make query whereBetween.You must fill keys `start` and `end` in query string.
you can set it on query string as you know. this params is good fit for filter by date.

```
/users/list?created_at[start]=2016/05/01&created_at[end]=2017/10/01

SELECT ... WHERE ... created_at BETWEEN '2016/05/01' AND '2017/10/01'
```

****Advanced Where****
```
/users/list?count_posts[operator]=>&count_posts[value]=10&username[]=ali&username[]=mehdi&family=ahmadi&created_at[start]=2016/05/01&created_at[end]=2020/10/01
&f_params[orderBy][field]=id&f_params[orderBy][type]=ASC

select * from `users` where `count_posts` > 10 and `username` in ('ali', 'mehdi') and 
`family` = ahmadi and `created_at` between '2016/05/01' and '2020/10/01' order by 'id' asc limit 10 offset 0
```

Just fields of query string be same rows table database in `$whiteListFilter` in your model or declare method in your model as override method.
Override method can be considered custom query filter.

### Custom query filter
If you are going to make yourself query filter you can do it easily.You just make a trait and use it on model:

```php
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait usersFilter.
 */
trait usersFilter
{
    /**
     * @param \Illuminate\Database\Eloquent\Builder $builder
     * @param                                       $value
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function username_like(Builder $builder, $value)
    {
        return $builder->where('username', 'like', '%'.$value.'%');
    }
}
```

Note that fields of query string be same methods of trait.Use trait in your model:

```
/users/list?username_like=a

select * from `users` where `username` like %a% order by `id` desc limit 10 offset 0
```

```php
class User extends Model
{
    use usersFilter,Filterable;

    protected $table = 'users';
    protected $guarded = [];
    private static $whiteListFilter =[
        'id',
        'username',
        'email',
        'created_at',
        'updated_at',
    ];
    
}
```
- If you have any idea about the Eloquent Filter i will glad to hear that.You can make an issue or contact me by email. My email is mehdifathi.developer@gmail.com.
