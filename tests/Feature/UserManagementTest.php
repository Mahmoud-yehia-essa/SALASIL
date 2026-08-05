<?php

use App\Models\User;

test('authenticated admin can view add client page', function () {
    $user = User::factory()->create([
        'role' => 'admin',
    ]);

    $response = $this->actingAs($user)->get(route('add.user'));

    $response->assertStatus(200);
    $response->assertSee('Add New Client');
});

test('authenticated admin can store a new client', function () {
    $admin = User::factory()->create([
        'role' => 'admin',
    ]);

    $clientData = [
        'fname' => 'Sami',
        'lname' => 'Al-Otaibi',
        'email' => 'sami.otaibi@salasil-test.com',
        'phone' => '551234567',
        'country_code' => '+966',
        'role' => 'individual_customer',
        'status' => 'active',
        'password' => 'secret123',
    ];

    $response = $this->actingAs($admin)->post(route('store.user'), $clientData);

    $response->assertRedirect(route('all.owners'));
    $this->assertDatabaseHas('users', [
        'email' => 'sami.otaibi@salasil-test.com',
        'fname' => 'Sami',
        'role' => 'individual_customer',
    ]);
});

test('authenticated admin can change status via ajax', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)->postJson(route('change.status.ajax'), [
        'user_id' => $client->id,
        'status' => 'banned',
    ]);

    $response->assertStatus(200);
    $response->assertJson([
        'status' => 'success',
    ]);
    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'status' => 'banned',
    ]);
});

test('authenticated admin can toggle client status', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['status' => 'active']);

    $response = $this->actingAs($admin)->get(route('status.user', $client->id));

    $response->assertRedirect();
    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'status' => 'inactive',
    ]);
});

test('authenticated admin can edit and update a client', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create(['fname' => 'OriginalName', 'role' => 'individual_customer']);

    $editResponse = $this->actingAs($admin)->get(route('edit.user', $client->id));
    $editResponse->assertStatus(200);
    $editResponse->assertSee('Edit Client Profile');

    $updateData = [
        'id' => $client->id,
        'fname' => 'UpdatedName',
        'lname' => 'Essa',
        'email' => $client->email,
        'role' => 'company_customer',
        'status' => 'active',
    ];

    $updateResponse = $this->actingAs($admin)->post(route('update.user'), $updateData);
    $updateResponse->assertRedirect(route('all.owners'));

    $this->assertDatabaseHas('users', [
        'id' => $client->id,
        'fname' => 'UpdatedName',
        'role' => 'company_customer',
    ]);
});

test('authenticated admin can delete a client', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    $client = User::factory()->create();

    $response = $this->actingAs($admin)->get(route('delete.user', $client->id));

    $response->assertRedirect();
    $this->assertSoftDeleted('users', [
        'id' => $client->id,
    ]);
});
