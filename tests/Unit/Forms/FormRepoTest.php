<?php
declare(strict_types=1);

namespace Tests\Unit\Forms;

use FBM\Forms\FormRepo;

final class FormRepoTest extends \BaseTestCase {
    public function testCreateAndGet(): void {
        $id = FormRepo::create([
            'title' => 'My Form',
            'schema' => [
                ['type' => 'text', 'label' => 'Name', 'required' => true],
                ['type' => 'email', 'label' => 'Email', 'required' => true],
            ],
        ]);
        $form = FormRepo::get($id);
        $this->assertNotNull($form);
        $this->assertSame('My Form', $form['title']);
        $this->assertCount(2, $form['schema']['fields']);
        $this->assertSame('field_1', $form['schema']['fields'][0]['id']);
    }

    public function testUpdateAndList(): void {
        $id = FormRepo::create([
            'title' => 'Update',
            'schema' => [ ['type' => 'text', 'label' => 'A'] ],
        ]);
        FormRepo::update($id, [
            'title' => 'Updated',
            'schema' => [
                ['type' => 'text', 'label' => 'A'],
                ['type' => 'checkbox', 'label' => 'Agree'],
            ],
        ]);
        $form = FormRepo::get($id);
        $this->assertSame('Updated', $form['title']);
        $this->assertCount(2, $form['schema']['fields']);
        $list = FormRepo::list();
        $titles = array_column($list, 'title');
        $this->assertContains('Updated', $titles);
    }
}
