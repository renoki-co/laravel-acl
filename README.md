# Laravel ACL 🔐

![CI](https://github.com/renoki-co/laravel-acl/workflows/CI/badge.svg?branch=master)
[![codecov](https://codecov.io/gh/renoki-co/laravel-acl/branch/master/graph/badge.svg)](https://codecov.io/gh/renoki-co/laravel-acl/branch/master)
[![StyleCI](https://github.styleci.io/repos/576949144/shield?branch=master)](https://github.styleci.io/repos/576949144)
[![Latest Stable Version](https://poser.pugx.org/renoki-co/laravel-acl/v/stable)](https://packagist.org/packages/renoki-co/laravel-acl)
[![Total Downloads](https://poser.pugx.org/renoki-co/laravel-acl/downloads)](https://packagist.org/packages/renoki-co/laravel-acl)
[![Monthly Downloads](https://poser.pugx.org/renoki-co/laravel-acl/d/monthly)](https://packagist.org/packages/renoki-co/laravel-acl)
[![License](https://poser.pugx.org/renoki-co/laravel-acl/license)](https://packagist.org/packages/renoki-co/laravel-acl)

Simple, AWS IAM-style ACL for Laravel applications, leveraging granular permissions in your applications with strong declarations. 🔐

## 🚀 Installation

You can install the package via composer:

```bash
composer require renoki-co/laravel-acl
```

Publish the config:

```bash
php artisan vendor:publish --provider="RenokiCo\LaravelAcl\LaravelAclServiceProvider" --tag="config"
```

Publish the migrations:

```bash
php artisan vendor:publish --provider="RenokiCo\LaravelAcl\LaravelAclServiceProvider" --tag="migrations"
```

## 🙌 Usage

This package is based on [renoki-co/acl](https://github.com/renoki-co/acl), but leveraging the Laravel environment to quickly set up permissions.

In case you are familiar with how [ARNs](https://docs.aws.amazon.com/general/latest/gr/aws-arns-and-namespaces.html) and [Policies](https://docs.aws.amazon.com/IAM/latest/UserGuide/introduction_access-management.html) work, you can now use the same syntax to define and check your ACL policies.

You can check more [IAM examples](https://docs.aws.amazon.com/IAM/latest/UserGuide/access_policies_examples.html) to get a sense how to define your policies.

The role of an ACL system is to assign policies or rules/statements to specific entities that can perform certain actions on a set of resources, so later you can verify them throughout the app.

In Laravel, the actor class is most of the times the authenticatable model. To set up the actor class, you have to trait it up with `HasPolicies`:

```php
use RenokiCo\LaravelAcl\Concerns\HasPolicies;
use RenokiCo\LaravelAcl\Contracts\RuledByPolicies;
// ...

class User extends Authenticatable implements RuledByPolicies
{
    use HasPolicies;

    // ...
}
```

Whenever you require the actor to check for permissions, the package will automatically pull the attached permissions. To attach a policy, simply define a static AclPolicy and attach it directly to the actor, with a custom name (that can be helpful to identify a specific attachment):

```php
use RenokiCo\Acl\Acl;
use RenokiCo\Acl\Statement;

$policy = Acl::createPolicy([
    Statement::make(
        effect: 'Allow',
        action: 'server:List',
        resource: [
            'arn:php:default:local:123:server',
        ],
    ),
]);

$user = Auth::user();

// Do this once, as it stores the policy in the database, in relation to the user.
$user->attachPolicy('ListServers', $policy);

$user->isAllowedTo(
    'server:List',
    'arn:php:default:local:123:server',
); // true
```

## 🧬 ARNables

**This documentation part might be outdated and was tweaked to represent this specific use case. Consider [reading the ACL documentation](https://github.com/renoki-co/acl#-arnables), taking into account this current guide.**

PHP is more object-oriented. ARNables can help turn your models or classes into a simpler version of ARNs, so you don't have to write all your ARNs each time, but instead pass them to the `isAllowedTo()` method, depending on either it's an ARN that is resource-agnostic, or an ARN that points to a specific resource.

### Resource-agnostic ARN vs Resource ARN

Resource-agnostic ARNs are the ones that are used for actions like `list` or `create`. They are not pointing to a specific resource, but rather to a "general" permission for that resource, that can lead to allowing listing or creating resources. For example, `arn:php:default:local:123:server`.

Resource ARNs are the ARNs that point to a specific resource. Actions like `delete`, `modify` and such are good examples that can be used in combination with these ARNs. For example, `arn:php:default:local:123:server/1` or `arn:php:default:local:123:backup/1`.

### Resolving the Region and Account IDs

Let's take this ARN example: `arn:php:default:local:123:server`.

Since this ARN is agnostic, the `Server` class cannot be properly converted to an ARN without two key components:

- the region, in this case `local`
- the account ID, in this case `123`

Although the values do have defaults, you **must** let the ACL service know what the values should be.

For these values, you can take AWS' example: it lets you select the region (in console: by manually changing the region via the top-right selector; in the API: by specifying the `--region` parameter), and you must be authenticated to an account, in this case your current login session knows your Account ID.

### Resolving within Models

Any actor that has the `HasPolicies` trait alredy resolves the region as `local` and the account ID as the primary key. The best approach is to override the region resolver based on the current request session, for example:

```php
class User extends Authenticatable implements RuledByPolicies
{
    use HasPolicies;

    /**
     * Resolve the account ID of the current actor.
     * This value will be used in ARNs for ARNable static instances,
     * to see if the current actor can perform ID-agnostic resource actions.
     *
     * @return null|string|int
     */
    public function resolveArnAccountId()
    {
        return $this->getKey();
    }

    /**
     * Resolve the region of the current actor.
     * This value will be used in ARNs for ARNable static instances,
     * to see if the current actor can perform ID-agnostic resource actions.
     *
     * @return null|string|int
     */
    public function resolveArnRegion()
    {
        return session('region', 'local');
    }
}

// i.e. Handle region change via HTTP requests
public function changeRegion(Request $request)
{
    $data = $request->validate([
        'region' => ['required', 'string', 'in:us-east-1,eu-central-1'],
    ]);

    session(['region' => $data['region']]);

    return redirect('/');
}
```

### Using ARNables with actors

Let's say you have a model called `Server` instance that belongs to an user:

```php
use RenokiCo\LaravelAcl\Concerns\HasArn;
use RenokiCo\LaravelAcl\Contracts\Arnable;
use RenokiCo\LaravelAcl\BuildResourceArn;

class Server extends Model implements Arnable
{
    use HasArn;

    protected $fillable = [
        'user_id',
        'name',
        'ip',
    ];

    public function arnResourceAccountId()
    {
        return $this->user_id;
    }
}
```

Instead of passing full ARNs to `->isAllowedTo`, you can now pass the server class name instead:

```php
$policy = Acl::createPolicy([
    Statement::make(
        effect: 'Allow',
        action: 'server:List',
        resource: [
            'arn:php:default:local:123:server',
        ],
    ),
    Statement::make(
        effect: 'Allow',
        action: 'server:Delete',
        resource: [
            'arn:php:default:local:123:server/1',
        ],
    ),
]);

$user = Auth::user();

$user->isAllowedTo('server:List', Server::class); // true
```

To check permissions on a specific resource ARN, you may pass the object itself to the ARN parameter:

```php
$server = Server::find('1');
$user->isAllowedTo('server:Delete', $server); // true
```

As you have seen previously, on the actor model you can specify the account identifier for them. In an ARN like `arn:php:default:local:123:server`, the part `123` is the account ID, or the account identifier. Thus, setting `resolveArnAccountId` to return `123`, the policies will allow the actor to `server:List` on that specific resource.

The resource ID gets resolved automatically to the model primary key, but you can override it:

```php
class Server extends Model implements Arnable
{
    use HasArn;

    public function arnResourceId()
    {
        return $this->getKey();
    }
}
```

### Using ARNables with teams

On a more complex note, having a model that groups more actors, like a `Team` having more `User`s is pretty common, especially if using [Laravel Jetstream](https//jetstream.laravel.com).

You still need to implement the policy checking at the user level, but with regard to resolving the "account ID" to be more like Team ID, as long as the resources are created under `Team`.

```php
class Team extends Model
{
    //
}
```

```php
use RenokiCo\LaravelAcl\Concerns\HasPolicies;
use RenokiCo\LaravelAcl\Contracts\RuledByPolicies;

class User extends Authenticatable implements RuledByPolicies
{
    use HasPolicies;

    public function resolveArnAccountId()
    {
        return $this->current_team_id;
    }
}
```

Im the ARNables' case, their models should resolve the account ID of the team:

```php
class Server extends Model implements Arnable
{
    use HasArn;

    public function arnResourceAccountId()
    {
        return $this->team_id;
    }
}
```

### More resources

- [Learn how ARNables are solved by ACL](https://github.com/renoki-co/acl#-arnables)
- [Learn about common good practices and guidelines when defining your permissions](https://github.com/renoki-co/acl#-guidelines)

## 🐛 Testing

``` bash
vendor/bin/phpunit
```

## 🤝 Contributing

Please see [CONTRIBUTING](CONTRIBUTING.md) for details.

## 🔒  Security

If you discover any security related issues, please email alex@renoki.org instead of using the issue tracker.

## 🎉 Credits

- [Alex Renoki](https://github.com/rennokki)
- [All Contributors](../../contributors)
