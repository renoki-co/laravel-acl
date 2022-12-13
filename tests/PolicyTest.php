<?php

namespace RenokiCo\LaravelAcl\Test;

use RenokiCo\Acl\Acl;
use RenokiCo\Acl\Policy;
use RenokiCo\Acl\Statement;
use RenokiCo\LaravelAcl\LaravelAcl;
use RenokiCo\LaravelAcl\RegisteredArnable;
use RenokiCo\LaravelAcl\Test\Models\User;
use RenokiCo\LaravelAcl\Test\Models\Vps;

class PolicyTest extends TestCase
{
    public function test_register_arnables()
    {
        $this->assertSame([], LaravelAcl::getRegisteredResources());

        LaravelAcl::registerArnables([
            Vps::class,
        ]);

        $this->assertInstanceOf(RegisteredArnable::class, $registeredArnable = Vps::getRegisteredArnable());

        $this->assertSame([
            'vps:Describe',
            'vps:Update',
            'vps:Delete',
            'vps:Reboot',
        ], $registeredArnable->actions);

        $this->assertSame([
            'vps:List',
            'vps:Create',
            'vps:CheckAvailability',
        ], $registeredArnable->agnosticActions);

        $this->assertSame(Vps::arnResourceType(), $registeredArnable->name);

        LaravelAcl::registerArnables([
            Vps::class,
        ]);

        $this->assertInstanceOf(RegisteredArnable::class, $registeredArnable = Vps::getRegisteredArnable());

        $this->assertSame([
            'vps:Describe',
            'vps:Update',
            'vps:Delete',
            'vps:Reboot',
        ], $registeredArnable->actions);

        $this->assertSame([
            'vps:List',
            'vps:Create',
            'vps:CheckAvailability',
        ], $registeredArnable->agnosticActions);

        $this->assertSame(Vps::arnResourceType(), $registeredArnable->name);
    }

    public function test_loading_policies()
    {
        /** @var User $user */
        $user = factory(User::class)->create();

        $aclPolicy = new Policy([
            new Statement(
                action: '*',
                resource: '*',
            ),
        ]);

        $storedPolicy = $user->attachPolicy('AdministratorAccess', $aclPolicy);

        $this->assertTrue($user->isAllowedTo('vps:List', Vps::class));

        $this->assertTrue($storedPolicy->subject->is($user));
    }

    public function test_actor_policy_allow_specific_actions_on_specific_arns()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                action: 'vps:List',
                resource: [
                    'arn:php:default:local:1:vps',
                ],
            ),
            Statement::make(
                action: 'vps:Describe',
                resource: [
                    'arn:php:default:local:1:vps/1',
                ],
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);

        $this->assertTrue($user->isAllowedTo('vps:List', Vps::class));
        $this->assertTrue($user->isAllowedTo('vps:Describe', $vps));

        $this->assertFalse($user->isAllowedTo('vps:List', $vps));
        $this->assertFalse($user->isAllowedTo('vps:Describe', Vps::class));
    }

    public function test_actor_policy_allow_specific_actions_on_wildcard_resources()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                action: [
                    'vps:List',
                    'vps:Describe',
                ],
                resource: '*',
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);
        $otherAccountVps = factory(Vps::class)->create(['user_id' => '999']);

        $this->assertTrue($user->isAllowedTo('vps:Describe', $vps));
        $this->assertFalse($user->isAllowedTo('vps:Describe', $otherAccountVps));

        $this->assertTrue($user->isAllowedTo('vps:List', Vps::class));
        $this->assertFalse($user->isAllowedTo('vps:List', 'arn:php:default:local:2:vps'));
    }

    public function test_actor_policy_denies_specific_resource_from_wildcard_allow()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                action: 'vps:Describe',
                resource: [
                    'arn:php:default:local:1:vps/*',
                ],
            ),
            Statement::make(
                effect: 'Deny',
                action: 'vps:Describe',
                resource: [
                    'arn:php:default:local:1:vps/1',
                ],
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);
        $otherAccountVps = factory(Vps::class)->create(['user_id' => '999']);

        $this->assertFalse($user->isAllowedTo('vps:Describe', $vps));
        $this->assertFalse($user->isAllowedTo('vps:Describe', Vps::class));

        $this->assertTrue($user->isAllowedTo('vps:Describe', $vps->fill(['id' => 2])));
        $this->assertFalse($user->isAllowedTo('vps:Describe', $otherAccountVps));
    }

    public function test_actor_policy_denies_specific_action_on_every_resource()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                effect: 'Deny',
                action: 'vps:Describe',
                resource: '*',
            ),
            Statement::make(
                effect: 'Allow',
                action: 'vps:List',
                resource: '*',
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);

        $this->assertTrue($user->isAllowedTo('vps:List', 'arn:php:local:local:1:vps'));
        $this->assertFalse($user->isAllowedTo('vps:List', 'arn:php:local:local:2:vps'));

        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:local:local:1:vps/1'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:local:local:1:vps/2'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:local:local:2:vps/1'));
    }

    public function test_actor_policy_denies_all_actions_on_a_single_resource_even_when_other_allows_exist()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                effect: 'Deny',
                action: '*',
                resource: 'arn:php:default:local:*:vps/1',
            ),
            Statement::make(
                effect: 'Allow',
                action: 'vps:Describe',
                resource: [
                    'arn:php:default:local:*:vps/1',
                    'arn:php:default:local:*:vps/2',
                ],
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);

        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:default:local:1:vps/1'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:default:local:2:vps/1'));
        $this->assertTrue($user->isAllowedTo('vps:Describe', 'arn:php:default:local:1:vps/2'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:default:local:2:vps/2'));

        $this->assertFalse($user->isAllowedTo('vps:Delete', 'arn:php:default:local:1:vps/1'));
        $this->assertFalse($user->isAllowedTo('vps:Delete', 'arn:php:default:local:2:vps/1'));
    }

    public function test_actor_policy_denies_specific_action_on_all_resources_even_when_other_allows_exist()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                effect: 'Deny',
                action: 'vps:Shutdown',
                resource: 'arn:php:default:local:*:vps/*',
            ),
            Statement::make(
                effect: 'Allow',
                action: 'vps:*',
                resource: [
                    'arn:php:default:local:*:vps',
                    'arn:php:default:local:*:vps/*',
                ],
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);

        $vps = factory(Vps::class)->create(['user_id' => $user->id]);

        $this->assertTrue($user->isAllowedTo('vps:Describe', 'arn:php:default:local:1:vps/1'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:default:local:2:vps/1'));

        $this->assertTrue($user->isAllowedTo('vps:Describe', 'arn:php:default:local:1:vps/2'));
        $this->assertTrue($user->isAllowedTo('vps:List', 'arn:php:default:local:1:vps'));
        $this->assertFalse($user->isAllowedTo('vps:Describe', 'arn:php:default:local:2:vps/2'));
        $this->assertFalse($user->isAllowedTo('vps:List', 'arn:php:default:local:2:vps'));

        $this->assertFalse($user->isAllowedTo('vps:Shutdown', 'arn:php:default:local:1:vps/1'));
        $this->assertFalse($user->isAllowedTo('vps:Shutdown', 'arn:php:default:local:2:vps/1'));
    }

    public function add()
    {
        $policy = Acl::createPolicy([
            Statement::make(
                action: [
                    'vps:List',
                    'vps:Describe',
                ],
                resource: '*',
            ),
        ]);

        $user = factory(User::class)->create();
        $user->attachPolicy('Test', $policy);
    }
}
