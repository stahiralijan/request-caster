## Laravel RequestCaster

[![Latest Stable Version](https://poser.pugx.org/stahiralijan/request-caster/version)](https://packagist.org/packages/stahiralijan/request-caster)
[![Total Downloads](https://poser.pugx.org/stahiralijan/request-caster/downloads)](https://packagist.org/packages/stahiralijan/request-caster)
[![Latest Unstable Version](https://poser.pugx.org/stahiralijan/request-caster/v/unstable)](//packagist.org/packages/stahiralijan/request-caster)
[![License](https://poser.pugx.org/stahiralijan/request-caster/license)](https://packagist.org/packages/stahiralijan/request-caster)
[![Monthly Downloads](https://poser.pugx.org/stahiralijan/request-caster/d/monthly)](https://packagist.org/packages/stahiralijan/request-caster)
[![Daily Downloads](https://poser.pugx.org/stahiralijan/request-caster/d/daily)](https://packagist.org/packages/stahiralijan/request-caster)

*Requirements: I've only tested this package with Laravel 5.5, please help me by testing this package in older versions of Laravel*

### Article about usage

A detailed article about the usage of this package is discussed here:

https://medium.com/@stahiralijan/laravel-formrequest-attribute-casting-db2fcb794db9

### Installation

Install this package by typing the following command:
```shell
composer require stahiralijan/request-caster
```
### Usage
Let's learn from an example:

You want to be able to save the submitted data but you don't want to make a mess in the controller method like this:
```php
public function store(UserFormRequest $request)
{
    ...
    $first_name = ucfirst($request->first_name); // or ucfirst($request->get('first_name')
    $last_name = ucfirst($request->last_name); // or ucfirst($request->get('last_name') 
    ...
    $user = User::create([
        ...
        'first_name' => $first_name,
        'last_name' => $last_name,
        ...
    ]);
    ...
    // after handling model stuff
    return redirect(route('users.index'))
            ->with('message'=>"User ({$user->first_name} {$user->last_name}) created!");
}
```

As you can see after a while you start to wondering what if there is a way you could automate this process so that 
your controller would look elegant and clean. With this package you can just do that:

### Step 1:
Use the `RequestCaster` Trait in your form request (in this case `UserFormRequest`):
```php
...
use Stahiralijan\RequestCaster\Traits\RequestCasterTrait;
...
class UserFormRequest extends FormRequest
{
    use RequestCasterTrait;
    ...
}
```

### Step 2:
Define the Request attributes that are required to be casted:
```php
class UserFormRequest extends FormRequest
{
    use RequestCasterTrait;
    
    protected $toUCFirstWords = ['first_name','last_name'];
    
    // More about this is explained below
    protected $joinStrings = ['fullname'=>' |first_name,last_name'];
    ...
}
```
### Finally
...and that's all you needed to do, `first_name` and `last_name` are automatically capitalized. Also, you don't need to worry about your form data being getting dirty before validation because these castings will run after the validator validates the form data. 
```php
public function store(UserFormRequest $request)
{
    // first_name and last_name  
    $user = User::create($request->all());
    ...
    // after handling model stuff
    return redirect(route('users.index'))
            ->with('message'=>"User ({$request->full_name}) created!");
}
```
### Available transformations / Casts
The following casts are available: 
 - `$toLowerCaseWords`: Applies `strtolower()` to the selected field(s).
 - `$toUpperCaseWords`: Applies `strtoupper()` to the selected field(s).
 - `$toUCFirstWords`: Applies `ucwords()` to the selected field(s).
 - `$toSlugs`: Applies `str_slug()` to the selected field(s).
 - `$toIntegers`: Casts selected field(s) to `int`.
 - `$toFloats`: Casts selected field(s) to `float`.
 - `$toBooleans`: Casts selected field(s) to `bool`.
 - `$toArrayFromJson`: Applies `json_decode()` to the selected fields.
 - `$joinStrings`: Joins two or more fields and sets the result in new field specified in the array key, syntax: `$joinStrings = ['newField' => 'glue|field1,field2,...,fieldn']`
 - `$newFields`: Creates a new field in `FormRequest` in the following format: `$newFields = ['name' => 'newFieldName|function1,function2,...,functionN'];` The functions to be applied must be globally accessible like: str_slug, trim, count etc

### Available methods
For now only `collection(array $keys)` method is available

You can use this method to get a collection (`Illuminate\Support\Collection`) of all the attributes

```php
public function store(UserFormReques $request)
{
    $request->collection()->filter(function($item){
        ...
    });
    // or
    $request->collection()->map(function($item){
        ...
    });
}
```
### How to cast
All of the properties are pretty straight forward, you define the attributes that needs to be casted like this:
```php
// Convert the defined attributes to Upper-case
$toUpperCaseWords = ['product_code'];

// Upper-case the first letter of the words defined below
$toUCFirstWords = ['display_name'];

// Convert the following attributes into slugs
$toSlugs = ['product_name'];
``` 
You got the idea about the usage of the simple stuff, now one special transformation / caster

### $joinStrings:

```php
$joinStrings = ['fullname'=>' |first_name,last_name'];
```
 - Here `fullname` will be a new attribute of the `FormRequest` which does not exists in the either the form or the FormRequest in the current context.
 - Notice a space `' '` in the starting of value is the glue of the two attributes
 - Next `|` is the separator between the glue and the desired attributes
 - Next you add the attributes that needs to be glued.

If `first_name` is `Tahir` and `last_name` is `Jan` the output will be `Tahir Jan` according to the above rule, and can be accessed with `$request->fullname` or `$request->get('fullname')`

### $newFields
This is used to create a new field in the `FormRequest`, for example:

#### Note: function order is important!

```
protected $newFields = ['name_slug' => 'display_name|trim,str_slug,strtoupper'];
// This will generate ['name_slug' => "SYED-TAHIR-ALI-JAN"] for 'display_name'=>'syed tahir ali jan'
//   as you might have guessed, str_slug() was called first, then strtoupper() was called
```
